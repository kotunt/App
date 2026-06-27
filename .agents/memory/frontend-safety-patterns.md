---
name: Frontend safety & modal patterns
description: Reusable conventions for modal show/hide and URL output safety in this PHP/Tailwind app
---

## Tailwind modal show/hide
Use ONE mechanism: give the overlay both `hidden` and `flex` classes (`class="... hidden flex ..."`)
and toggle ONLY the `hidden` class via `classList.add/remove('hidden')`. Tailwind's `.hidden`
is emitted after `.flex`, so `hidden` wins when present and `flex` applies when removed.

**Why:** Mixing inline `style.display='flex'` (open) with `classList.add('hidden')` (close) leaves
the inline `display:flex` overriding the class `display:none`, so modals get stuck open. Hit this on
the dream-book and tutorial-video includes.

**How to apply:** Any new modal/overlay — never set `element.style.display` if you also toggle a class.

## URL output safety (href/src from DB)
`htmlspecialchars()` alone is NOT enough for URL sinks — it still allows `javascript:`/`data:` schemes.
Wrap DB-sourced URLs in `safe_url($url)` (defined in `includes/header.php`, function_exists-guarded)
which allowlists http/https (+ optional extra schemes) and returns `#` otherwise; then still
`htmlspecialchars()` the result.

**Why:** Stored XSS vector via admin-editable settings/content (promo link_url, external game
launch_url, video URLs).

**How to apply:** Every dynamic `href`/`src` that comes from the DB. `safe_url` is available after
`header.php` is included (all front pages include it before body output).
