---
name: Thai 2D3D Platform Setup
description: How the project runs on Replit - MySQL, PHP, key files
---

**Setup:**
- MySQL 8.0 via `installSystemDependencies`, data dir `/home/runner/mysql-data`, socket `/home/runner/mysql-run/mysql.sock`
- PHP 8.2 built-in server on `0.0.0.0:5000`
- `start.sh` initializes MySQL, imports `thai_2d3d_db.sql`, starts PHP server
- Admin credentials: phone `09000000001`, password `Admin@1234`

**Key symlinks:** `src/core -> ../core`, `src/lang -> ../lang`

**Why:** Replit has no MySQL service by default; must init data dir and start MySQL in start.sh every time.

**How to apply:** Any restart requires start.sh to re-start MySQL (data persists in /home/runner/mysql-data).
