---
name: DB transaction safety (non-throwing mysqli)
description: Why mysqli execute() failures are silent in this app and how transaction/money code must guard against them.
---

# mysqli runs in non-throwing mode

`App\Core\Database` sets `mysqli_report(MYSQLI_REPORT_OFF)`. Therefore `$stmt->execute()`
returns `false` on failure instead of throwing an exception.

**Rule:** In any `begin_transaction()` block, check every `execute()` return value and
`throw`/handle on `false` BEFORE calling `commit()`. A bare `try/catch` does NOT catch DB
errors here — it only catches exceptions you throw yourself.

**Why:** Without explicit checks, a failed INSERT still falls through to `commit()`, so a
balance can be deducted with no matching withdrawal/bet record (money-flow integrity bug).

**How to apply:** For balance/withdrawal/deposit/bet writes, use
`if (!$stmt->execute()) { ... throw new Exception('DB_ERROR'); }`, and for overdraft safety
deduct with `UPDATE ... SET balance = balance - ? WHERE id = ? AND balance >= ?` then verify
`affected_rows >= 1` (re-checks balance atomically; step-1 checks can be stale by PIN/OTP step).
