---
name: db_connect $conn availability
description: When the global $conn from core/db_connect.php is/ isn't in scope, and how to fetch a connection reliably.
---
# core/db_connect.php $conn scoping

core/db_connect.php now assigns the global `$conn = Database::getInstance()->getConnection()`
unconditionally near the top (before the maintenance-mode check). Historically it was only set
*inside* the maintenance `if (!in_array($current_script, $allowed_scripts))` block, so:
- "allowed scripts" (maintenance.php, login.php, logout.php) never got `$conn`.
- Any file that includes db_connect.php a second time via `require_once` (no-op) and then uses
  `$conn` from a *different* scope (e.g. a view rendered inside a controller method, or footer
  included inside a function) saw `$conn` undefined → "query() on null".

**Rule:** Never assume `$conn` is in scope just because db_connect.php was required somewhere.
Inside any method/function/view, fetch it directly: `$conn = Database::getInstance()->getConnection();`
(global `Database` singleton class, core/classes/Database.php). Its constructor `die()`s on
connect failure, so it never throws uncaught.

**Why:** errorlog.txt was full of "Undefined variable $conn" / "Call to a member function
query() on null" in footer.php, maintenance.php, and deposit_view.php from exactly this scoping gap.
