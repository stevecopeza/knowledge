# Operations — Backup and Restore

This document defines **backup and restore guarantees** for the knowledge system. Backups must be sufficient to fully reconstruct the repository without external dependencies.

---

## 1. Backup Scope

A complete backup includes:
- WordPress database (knowledge CPTs, taxonomies, meta)
- `/wp-content/kb-data/` filesystem directory

Both are required.

---

## 2. Backup Principles

- Backups are consistent snapshots
- Partial backups are explicitly marked
- Backups are storage-agnostic

---

## 3. Restore Guarantees

A restore operation must:
- Reconstruct all canonical knowledge
- Preserve UUIDs and lineage
- Allow AI artifacts to be rebuilt

---

## 4. Offline Recovery

- Restore must not require internet access
- External sources are not re-fetched automatically

---

## Closing Note

Backup integrity is non-negotiable. Any feature that complicates restoreability is invalid.

