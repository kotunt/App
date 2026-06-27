---
name: CSRF token systems
description: The three coexisting CSRF token mechanisms in this PHP lottery app and which to use where
---

Three CSRF schemes coexist — do not invent a fourth:

1. **Admin** — `$_SESSION['admin_csrf_token']`, a stable per-session token (NOT rotated per request,
   so multiple open admin tabs/forms stay valid). Helpers live in `core/auth_helper.php`:
   `get_admin_csrf_token()`, `admin_csrf_field()` (renders hidden `admin_csrf_token` input),
   `validate_admin_csrf_token()`, and `require_admin_csrf()` (call at top of every admin POST handler).
   `admin/admin_header.php` calls `get_admin_csrf_token()` as the single source of truth + renders the meta tag.
2. **User (ad-hoc)** — `$_SESSION['csrf_token']` with field name `csrf_token`; used inline by
   `withdraw.php`, `transfer.php` (each generates + validates by hand).
3. **User (helper)** — `core/security_helper.php` per-form tokens `$_SESSION['csrf_tokens'][$form]`,
   field name `_csrf_token`; `generate_csrf_token()/csrf_input_field()/validate_csrf_token()`. Underused.

**Why this matters:** the inconsistency is the root cause of CSRF gaps found in audits.
**How to apply:** for admin pages always use the auth_helper functions above. CSRF check runs
*inside* the POST handler, which is fine because authz (`require_permission`/`require_main_admin`)
runs earlier in the same file. Admin handlers run before `admin_header.php` is included, but the
token persists in session from the prior GET, so validation still works.
