---
name: Admin header architecture
description: How the admin panel's HTML document is assembled across header/footer includes — and the double-head trap.
---

# Admin panel document structure

`admin/admin_header.php` is a **navbar fragment**, NOT a full HTML head. It emits a CSRF meta, a `<style>` block (dropdown + admin dark-mode `!important` overrides), and the top bar + secondary nav markup. It contains no `<!DOCTYPE>`, `<head>`, or `<body>`.

The full document head (`<!DOCTYPE>`…`</head>`, Tailwind CDN + brand config, fonts, Font Awesome) comes from `includes/header.php` (the shared user head, which ends at `</head>` and does NOT open `<body>`).

## The two admin page patterns
- **41 pages** (e.g. admin_users.php): `require includes/header.php` (head) THEN `require admin_header.php` (navbar fragment), content, then literal `</body></html>` at the end of the page file. No explicit `<body>` open tag anywhere — the browser auto-inserts it. No admin_footer.php.
- **admin_dashboard.php**: the lone exception — historically included `admin_header.php` standalone with NO `includes/header.php`, so it had no `<head>`/Tailwind at all (pre-existing bug). Fixed by adding `require_once __DIR__.'/../includes/header.php';` before the admin_header include, making it consistent with the other 41. It still closes with its own literal `</body></html>`.

`admin/admin_footer.php` is effectively empty (`<?php`) and included by almost no pages.

## TRAP — do not double the head
**Why:** I once added a full `<!DOCTYPE><head>…</head><body>` to `admin_header.php` to "fix" the missing head. That broke all 41 pages with a duplicate doctype/head (they already get the head from `includes/header.php`). 

**How to apply:** Keep `admin_header.php` as a fragment. To give a standalone admin page a head, include `includes/header.php` (it provides the brand Tailwind config + `.bg-brand-gradient`, `shadow-card`, `primary`/`gold` tokens). Brand-token classes used in the admin navbar resolve only because `includes/header.php` is loaded first.
