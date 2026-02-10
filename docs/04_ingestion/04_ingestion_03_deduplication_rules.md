# Ingestion — Deduplication Rules

> **⚠️ STATUS UPDATE (2026-02-10):** The strategies defined below are currently being hardened. An audit revealed gaps in redirect resolution and concurrency locking. See [Deduplication Strategy Update](../../docs/10_operations/10_operations_07_deduplication_strategy_update.md) for the active remediation plan.

This document defines how duplicate content is detected and handled during ingestion. Deduplication is conservative and applies only to **source content**, never to user-derived forks.

---

## 1. Deduplication Scope

- Applies to Sources and Versions
- Does **not** apply to Forks
- Occurs during ingestion and update checks

---

## 2. Canonical Identity

A Source is considered duplicate when:
- Content hash matches an existing Version, or
- URL resolves to an existing canonical Source

Hybrid heuristics may be used, but hash match is authoritative.

---

## 3. Duplicate Handling

### 3.1 Input-Level Deduplication (Batch Jobs)
When submitting a batch or bulk import:
- The system filters duplicate URLs from the input list **before** processing begins.
- This prevents unnecessary job creation for redundant entries in the same batch.

### 3.2 Storage-Level Deduplication
When a duplicate is detected during ingestion:
- No new Article is created
- The existing Article is reused
- Source metadata is updated with additional provenance

---

## 4. Near-Duplicates

Near-duplicates (e.g. mirrors, minor edits) result in:
- New Version creation
- Supersession of the previous Version

---

## 5. User Visibility

Deduplication decisions must be:
- Logged
- Inspectable
- Reversible only by re-ingestion

---

## Closing Note

Deduplication protects signal quality. Over-aggressive merging is prohibited.

