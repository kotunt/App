---
name: Include path depth conventions & dual routing
description: Correct __DIR__ relative depths for includes/views, and which routing pattern (legacy root vs MVC) is actually live.
---

# Include path depths (relative to `__DIR__`)

`includes/`, `core/`, `lang/` live at the **workspace root**. There is NO `src/includes` and NO root `views/` dir. `config.php` lives at `core/config.php` (not root).

- From `src/views/*.php` → header/footer is `__DIR__ . '/../../includes/...'` (TWO `../`). Reference: `login_view.php`, `profile_view.php` use this correctly.
- From `src/controllers/*.php` → root dirs are `'/../../core'`, `'/../../lang'` (TWO `../`); but the VIEW is `'/../views/<name>.php'` (ONE `../`, since views are siblings under `src/`). `DepositController.renderView` does this right.
- From `admin/*.php` → header is `__DIR__ . '/../includes/header.php'` (ONE `../`); admin_header is `__DIR__ . '/admin_header.php'`. Reference: `admin_users.php`.

**Why:** several files had the wrong depth (deposit_view/bet_history_view used ONE `../` → resolved to missing `src/includes`; ProfileController loaded the view with TWO `../` → missing `workspace/views`). These fatal only for logged-in users (login guard 302-redirects anonymous requests, hiding the crash).

# Dual routing: legacy root vs MVC — know which is LIVE

The app mixes two patterns. Some `src/controllers`+`src/views` are DEAD (no entry point routes to them):
- **Live** via MVC: `deposit.php`→DepositController→`deposit_view.php`; `profile.php`→ProfileController→`profile_view.php`; also index/login/register/forgot_password/change_password.
- **Live** as legacy inline root files (do NOT go through a controller/view): `bet_history.php` (full page inline, correct `/includes/` paths).
- **DEAD**: `BetHistoryController` + `src/views/bet_history_view.php` (nothing routes to them), and `admin/users_view.php` (live admin page is `admin_users.php`).

**How to apply:** before editing/polishing a `src/views` or controller file, confirm a root entry actually routes to it (`rg -ln "XController" *.php`). Don't waste effort on dead files, and when fixing paths, prioritize the live ones.
