# Operations — Upgrade and Migrations

This document defines how **upgrades and migrations** are handled to guarantee long-lived data integrity. The system assumes users may skip versions.

---

## 1. Principles

- Backward compatibility is mandatory
- Migrations are additive
- Data loss is unacceptable

---

## 2. Versioning Strategy

- Plugin versions are monotonically increasing
- Each version declares its schema expectations

---

## 3. Migration Execution

- Migrations run on plugin activation or upgrade
- Each migration is idempotent
- Migration state is persisted

---

## 4. Skipped Versions

- Migrations must handle multi-version jumps
- No migration assumes prior intermediate execution

---

## 5. Rollback and Safety

- Failed migrations halt activation
- Canonical data remains untouched until migration success

---

## Closing Note

Upgrade safety is a core system guarantee. Convenience shortcuts are prohibited.

