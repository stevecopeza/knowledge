# Ingestion — Bulk Imports

This document defines how bulk ingestion operates for large sets of sources (e.g. link lists, spreadsheets). Bulk imports are first-class operations with explicit state and visibility.

---

## 1. Bulk Import Definition

A bulk import is a structured submission containing multiple ingestion items, processed asynchronously.

Examples:
- CSV or spreadsheet of URLs
- Plain-text URL lists
- Export files from other tools

---

## 2. Import Lifecycle

### States
- **Created** — import registered
- **Queued** — items queued for capture
- **Processing** — ingestion in progress
- **Completed** — all items processed
- **Completed with Errors** — partial success
- **Failed** — systemic failure

State is persisted and visible.

---

## 3. Item-Level Processing

Each item in a bulk import:
- Is processed independently
- Has its own capture and refinement lifecycle
- Records success or failure

Failures do not block other items.

---

## 4. Progress Visibility

The system must expose:
- Overall import progress
- Per-item status
- Error summaries

---

## 5. Retry Semantics

- Failed items may be retried individually or in bulk
- Retry does not reset successful items

---

## 6. Metadata Application

Optional metadata provided in bulk imports (e.g. Category, Tags, Project):
- Is applied at capture time
- May be overridden later

---

## Closing Note

Bulk imports are designed for resilience and transparency. Partial success is acceptable; silent failure is not.

