---
name: Home page (index.php) render chain
description: index.php must include header.php + footer.php manually or the home page loses all CSS
---

The front-end pages render as: `includes/header.php` (outputs `<!DOCTYPE>`…`</head>` incl. the
Tailwind CDN + styles — it does NOT open `<body>`) → page body → `includes/footer.php` (bottom
nav + `</body></html>`). `src/views/home_view.php` supplies its own `<body>` and does NOT close it.

**Quirk:** `index.php` is the only page that uses the `bootstrap.php` pattern (most other root
pages use the legacy `core/auth_check.php` + `core/db_connect.php` includes). When index.php was
refactored to bootstrap it must STILL `require_once includes/header.php` before the view and
`require_once includes/footer.php` after it — otherwise the home page renders with no `<head>`,
so Tailwind never loads and the whole page shows as unstyled raw HTML.

**Why:** header.php holds the `<head>`/Tailwind; home_view.php only emits `<body>`; footer.php
emits the closing tags + bottom nav. Skipping header/footer = broken-looking home page.

**How to test a logged-in view:** pages sit behind auth (hashed passwords). Make a throwaway
script at web root that does `require bootstrap.php; $_SESSION['user_id']=<id>; require header.php;
require the view; require footer.php;`, screenshot `/that_file.php`, then delete it.
