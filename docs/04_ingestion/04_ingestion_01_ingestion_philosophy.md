# Ingestion — Philosophy

This document defines the **ingestion philosophy** of the system. Ingestion is treated as a resilient, asynchronous pipeline whose primary goal is to capture content reliably, not to perfect it immediately.

---

## 1. Cheap-First Capture

**Rule**: Ingestion must succeed quickly or fail clearly.

- Raw content capture is prioritised
- Enrichment is deferred
- Ingestion never blocks the user

*Rationale:* Low-friction capture is essential for real-world usage and bulk imports.

---

## 2. Progressive Refinement

Ingestion proceeds through stages:
1. Capture (raw) -> **Staging Directory** (`/kb-data/temp/`)
    - Downloads HTML/PDF/Images here first.
    - Validates integrity.
    - Moves to canonical storage (`/versions/`) only on success.
2. Normalisation
    - `ContentNormalizer` cleans HTML and extracts metadata (Author, Date, Description).
    - `AssetDownloader` fetches images, saves them to `/media/{hash}.ext`, and rewrites `<img>` tags to use the secure `?kb_action=file_proxy` URL.
3. Enrichment (summaries, metadata)
4. Embedding

Each stage:
- Is independently retryable
- Records explicit state

---

## 3. Asynchronous by Default

- All ingestion runs in background jobs
- Foreground actions enqueue work only
- Progress is observable

---

## 4. Failure Is Non-Fatal

- Failures are recorded as events
- Partial ingestion is allowed
- Canonical data is never corrupted

---

## 5. Determinism

Given the same input and configuration, ingestion must produce the same output.

---

## Closing Note

Ingestion exists to reduce friction, not to enforce correctness upfront. Correctness emerges through refinement.

