# Operations — Background Jobs

This document defines how **background jobs** operate. Background jobs are first-class system components responsible for ingestion, processing, and AI-related work.

---

## 1. Principles

- Jobs are persistent and resumable
- Jobs are observable and auditable
- Failure is isolated and non-destructive

---

## 2. Job Types

Examples include:
- Ingestion capture
- Normalisation
- Summarisation
- Embedding generation
- Project research tasks
- **Batch Import Jobs**:
    - Handles high-volume ingestion (>50 items).
    - Uses `kb_import_job` CPT to track progress.
    - Stores URL lists in filesystem to avoid database bloat.
    - Processes in chunks (default: 10) to respect timeouts.
- **Reconciliation / Health Check**:
    - Verifies DB records match Filesystem artifacts.
    - Flags "Ghost" records (DB entry without file).
    - Flags "Orphan" files (File without DB entry).

---

## 3. Job Lifecycle

States:
- Queued
- Running
- Completed
- Failed

State is persisted and queryable.

## 4. Monitoring & Visibility (JobTracker)

To ensure operational transparency, the system uses a **JobTracker** service to monitor execution.

- **Active Jobs**: Jobs currently executing in a PHP process. These are tracked in real-time via the `JobTracker` class, which maintains a registry of running tasks (e.g., "Embedding Generation", "Ingestion").
- **Scheduled Queue**: Jobs waiting in the WordPress Cron system to be picked up.

**Dashboard Visibility**:
The **Operations** dashboard exposes both lists:
1.  **Active Jobs Table**: Shows job type, description, and start time.
2.  **Scheduled Queue Table**: Shows upcoming cron events and their arguments.

This dual-view prevents the "invisible job" problem where a job disappears from the Cron queue (because it started) but hasn't finished yet.

## 5. Failure Management (FailureLog)

Failures in background jobs (specifically ingestion) are not ephemeral. They are persisted until resolved.

- **Storage**: Failed jobs are logged to the database (max 50 entries) via `FailureLog`.
- **Visibility**: Displayed in the **Operations** dashboard with timestamps and error messages.
- **Resolution**:
    - **Single Action**: Resubmit or Delete individual failures via row buttons.
    - **Bulk Action**: Select multiple failures and perform "Resubmit" or "Delete" operations in batch.
    - **Resubmit**: Re-schedules the job immediately and removes the error log.
    - **Delete**: Acknowledges the error and removes the log without action.

---

## 6. Retry Semantics

- Retries are explicit
- Backoff strategies are configurable
- Repeated failures surface alerts

---

## Closing Note

Background jobs are operational infrastructure. Treating them as implementation details is invalid.

