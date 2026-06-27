---
name: Auth Helper Aliases
description: Auth functions available in bootstrap and their locations
---

**Rule:** `core/auth_helper.php` contains `require_admin()`, `require_admin_login()`, `require_main_admin()`. It is included via `bootstrap.php`.

**Why:** Original codebase had missing auth functions that admin pages relied on. Added aliases so all admin pages load without fatal errors.

**How to apply:** If new admin pages need auth checks, use functions from `core/auth_helper.php`.
