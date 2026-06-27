---
name: PHP built-in server ini config
description: How to set global PHP ini directives (e.g. session cookie flags) when the app is served by the PHP built-in server
---

The app is served by the PHP built-in server (`php -S 0.0.0.0:5000` in `start.sh`).

**Rule:** the built-in server does NOT process `.user.ini`, and no php.ini was auto-loaded
(`php -i` showed `Loaded Configuration File => (none)`). To apply global ini directives
across all requests, you must pass a config file explicitly: `php -c /home/runner/workspace/php.ini -S ...`.

**Why:** session cookie hardening (`session.cookie_httponly/secure/samesite/use_strict_mode`)
had to apply to ~57 files that call `session_start()` directly — editing each is fragile.
A project `php.ini` + `-c` flag is the single global lever.

**How to apply:** edit `php.ini` at repo root and restart the `Start application` workflow.
Verify with `curl -sI .../login.php | rg -i set-cookie` (expect HttpOnly; Secure; SameSite).
Note: the Replit dev proxy rewrites SameSite to `None` for the iframe; direct localhost shows the configured `Lax`.
`cookie_secure=1` requires HTTPS — fine on Replit (always HTTPS), would break plain-HTTP deploys.
