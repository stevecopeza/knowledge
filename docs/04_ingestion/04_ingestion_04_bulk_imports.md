# Ingestion — Bulk Imports

This document defines how bulk ingestion operates for large sets of sources.

## 1. Global Deduplication Strategy

To prevent redundant data and processing, the system enforces a strict **Global Deduplication Policy** across all ingestion methods (Single, Bulk, API).

### 1.1 The Rule
**A Source URL corresponds to exactly one Knowledge Article (`kb_article`).**

### 1.2 Implementation
1.  **Normalization:** All URLs are normalized before storage or comparison:
    -   Trim whitespace.
    -   Standardize schemes (if needed).
    -   (Future) Strip tracking parameters (utm_*, etc.).
2.  **Pre-Flight Check (Batch):**
    -   When a Bulk Import is submitted, the system checks the database for existing `_kb_source_url` entries.
    -   **Action:** Existing URLs are **skipped** from the batch job entirely. They are not queued.
    -   **Reporting:** The user is informed of how many items were skipped due to duplication.
3.  **Upsert (Single Ingestion):**
    -   If a single URL is ingested and it matches an existing Article:
        -   **Content Match:** If the content hash is identical to the latest version -> **No Action** (Idempotent).
        -   **Content Changed:** If the content differs -> **Create New Version** (History preserved).

---

## 2. MVP Implementation (Current)

The current system supports **Simple Bulk Ingestion** via the Ingestion Dashboard.

### Workflow
1. **Input**: User pastes a list of URLs (one per line) into the text area.
2. **Pre-Check (Mandatory)**: 
   - User clicks "Check URLs".
   - System validates format and checks for existing duplicates in the Knowledge Base.
   - Duplicates are removed from the list, and a summary is displayed (e.g., "5 duplicates removed").
   - "Bulk Ingest" button becomes enabled only after a successful check.
3. **Submission**: 
   - User clicks "Bulk Ingest".
   - Remaining URLs are scheduled for background processing.
4. **Clear**: User can click "Clear" to reset the form.

### Capabilities
- **Input:** Plain-text list of URLs.
- **Deduplication:** Pre-flight check against existing `kb_source_url` metadata.
- **Processing:** Staggered background jobs to prevent overload.
- **Attribution:** All imported articles are automatically assigned to the user who performed the import.

### Limitations (MVP)
- No aggregate "Import" entity (items are processed independently).
- No centralized progress bar for the batch (status is per-item).
- Large batches (>100) may impact WP Cron performance.

---

## 3. Batch Processing Architecture (Karakeep & High Volume)

To support larger datasets (e.g., Karakeep JSON exports with 10k+ items), a **Batch Job** architecture has been introduced. This moves beyond the simple MVP list processing.

### Key Components
- **Job Entity**: `kb_import_job` (Hidden CPT) tracks the overall batch status.
- **Chunking**: A background cron (`knowledge_process_import_queue`) processes items in small chunks (e.g., 10 items/run) to avoid timeouts.
- **Error Log**: A dedicated JSON/meta log tracks failed URLs for reporting and retry.
- **Attribution**: The user initiating the import is recorded as the `post_author` for:
    - The `kb_import_job` itself.
    - All resulting `kb_article` posts.
    - All resulting `kb_version` posts.
- **Deduplication**: Input URLs are automatically deduplicated against the **database** before the job is created.

See [Karakeep Import Spec](04_ingestion_06_karakeep_import.md) for detailed implementation.

---

## 4. Future Vision (Target State)

A bulk import is a structured submission containing multiple ingestion items, processed asynchronously.

Examples:
- CSV or spreadsheet of URLs
- Plain-text URL lists
- Export files from other tools

---

## 5. Import Lifecycle

### States
- **Created** — import registered
- **Queued** — items queued for capture
- **Processing** — ingestion in progress
- **Completed** — all items processed
- **Completed with Errors** — partial success
- **Failed** — systemic failure

State is persisted and visible.

---

## 6. Item-Level Processing

Each item in a bulk import:
- Is processed independently
- Has its own capture and refinement lifecycle
- Records success or failure

Failures do not block other items.

---

## 7. Progress Visibility

The system must expose:
- Overall import progress
- Per-item status
- Error summaries

---

## 8. Retry Semantics

- Failed items may be retried individually or in bulk
- Retry does not reset successful items
