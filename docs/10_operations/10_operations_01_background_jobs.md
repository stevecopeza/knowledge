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

---

## 4. Retry Semantics

- Retries are explicit
- Backoff strategies are configurable
- Repeated failures surface alerts

---

## Closing Note

Background jobs are operational infrastructure. Treating them as implementation details is invalid.

