---
name: Translation Global Scope Fix
description: PHP global variable scope issue with translations inside class methods
---

**Rule:** `lang/language.php` must declare `global $translations;` BEFORE setting `$translations = include(...)`.

**Why:** When `language.php` is `require_once`'d inside a class constructor or method, PHP creates `$translations` in that local scope. The `__()` helper uses `global $translations`, which references the global scope — not the local one. Without the `global` declaration at assignment time, `__()` always returns the raw key.

**How to apply:** Any new language loading code in class methods must declare the variable global before assignment.
