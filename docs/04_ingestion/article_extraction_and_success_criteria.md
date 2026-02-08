# Article Extraction & Ingestion — Architecture and Success Criteria

This document defines **how article extraction is expected to work** and **what constitutes success** within the Knowledge system.

It is normative. Any extraction implementation that violates this document is incorrect, even if it “works”.

---

## 1. Purpose

Article extraction exists to **convert external sources into durable, local knowledge artifacts** while preserving:
- provenance
- structure
- human readability
- long-term usability

Extraction is not scraping for display. It is **knowledge capture for reasoning**.

---

## 2. Extraction Pipeline (Authoritative Stages)

Extraction is a staged pipeline. Each stage has explicit success and failure conditions.

### Stage 1 — Fetch

**Responsibility**
- Retrieve source content at a specific point in time.

**Mechanisms**
- HTTP fetch by default
- JavaScript-capable fetcher when required (optional, capability-based)

**Success Conditions**
- Source content retrieved
- Fetch timestamp recorded
- HTTP status and headers captured

**Failure Conditions**
- Network failure
- Access denied or blocked
- Empty response

A failed fetch produces **no Version**.

---

### Stage 2 — Normalisation

**Responsibility**
- Convert raw source into clean, readable, standalone HTML.

**Required Outcomes**
- Main textual content extracted
- **Core metadata extracted** (at minimum: title, publication date if available, author if available)
- Non-content elements removed (ads, nav, trackers, sidebars, social share buttons)
- Semantic structure preserved (headings, lists, emphasis)

**Specific Removal Rules**
- **Tags Removed**: `script`, `style`, `noscript`, `iframe`, `header`, `footer`, `nav`, `aside`, `form`, `object`, `embed`.
- **Class/ID Filtering**: Elements containing keywords such as `share`, `social`, `related`, `comment`, `sidebar`, `advert`, `promo`, `newsletter`, `popup`, `banner`, `widget`, `navigation`, `menu`.
- **Protected Elements**: `body`, `html`, `article`, `main` are protected from keyword-based deletion to prevent accidental content loss.
- **Safety Check (Density Preservation)**: Elements flagged by Class/ID filtering (e.g. "banner") are **preserved** if they contain significant text (> 300 characters), unless they match high-confidence noise patterns (e.g. `comment`, `cookie`, `taboola`, `outbrain`). This prevents accidental deletion of content wrappers.

**Minimum Viability Rule**
If extracted content does not meet minimum thresholds (length, paragraph count, semantic density), normalisation **fails**.

**Verification Tool**
A "Test Content Extraction" tool is available in the **Operations** dashboard. This allows administrators to preview the extraction result for a given URL without persisting it, enabling rapid verification of normalization logic against specific sources.

**Failure Conditions**
- Boilerplate-only output (“empty shell”)
- Fragmentary content
- Missing or unusable core metadata (e.g. untitled content with no recoverable context)
- Parser failure

Normalisation failure aborts Version creation.

---

### Stage 3 — Asset Preservation

**Responsibility**
- Preserve meaningful non-text assets locally.

**Scope**
- Images only (by design)

**Success Conditions**
- Each meaningful image downloaded locally
- Image files exceed trivial size threshold
- Valid MIME type confirmed
- HTML references local image paths
- **Featured Image Candidate Identified**: The system attempts to identify the most relevant image (typically the first significant image) to serve as the article's "Featured Image" or thumbnail.

Decorative or tracking images may be excluded.

**Failure Conditions**
- Broken image references
- Placeholder-only images
- Zero-byte or invalid files

Partial asset failure results in **degraded success**, not hard failure.

---

### Stage 4 — Storage & Versioning

**Responsibility**
- Persist extracted content as an immutable Version.

**Filesystem Layout**
```
/versions/{uuid}/
  ├── content.html
  └── metadata.json
```

**Metadata Must Include**
- Source URL
- Fetch timestamp
- Content hash
- Extraction warnings (if any)

**Featured Image Assignment**
If a valid Featured Image candidate was identified during Stage 3, it is automatically:
1.  Uploaded/Sideloaded to the WordPress Media Library.
2.  Attached to the `kb_article` post as its **Featured Image**.
This ensures compatibility with standard WordPress themes and the Knowledge Archive grid.

**Immutability Rule**
Once written, Version artifacts **must never be modified**.

---

## 3. Success Semantics

Success is **not binary**.

### Full Success
- Fetch succeeded
- Normalisation succeeded
- Assets preserved
- Version created without warnings

### Degraded Success
- Core text extracted successfully
- One or more assets failed
- Warnings recorded and surfaced

### Failure
- Fetch or normalisation failed
- No Version created

Failures are explicit and user-visible.

---

## 4. Provenance & Time

Each Version represents:
> “The best possible representation of the source *as fetched at time T*.”

Extraction success is evaluated **relative to that moment**, not against future or ideal states.

---

## 5. User-Visible Outcomes

Users must be able to see:
- Whether ingestion succeeded, degraded, or failed
- What warnings occurred
- What may need review or retry

**Operations Dashboard**:
- **Active Jobs**: Shows currently running ingestion tasks.
- **Failed Ingestions**: A dedicated log of failed attempts with error messages.
- **Actions**: Users can manually **Resubmit** (retry) or **Dismiss** (ignore) failed jobs.

Silent degradation is forbidden.

---

## 6. Non-Goals (Explicit)

Extraction does **not** aim to:
- Reproduce pixel-perfect layouts
- Preserve interactive widgets
- Capture comments or ads
- Execute arbitrary client-side logic beyond content retrieval

---

## 7. Testing & Verification

Extraction is considered complete only when:
- Automated tests pass
- A human can read the extracted article comfortably
- Images render locally
- No silent errors are present

**Failure Handling**:
If extraction fails, the error is recorded in the **Failure Log** (Operations Dashboard).
- Automatic retries are not infinite; persistent failures require human intervention.
- The administrator must either fix the issue (e.g., connectivity) and **Resubmit**, or acknowledge the limitation and **Dismiss** the error.

---

## 8. Performance & Scalability (Large Assets)

To handle sources with significant asset loads (e.g., articles with 100+ images), the system adheres to the **Async/Non-Blocking** principle.

**Architecture for Heavy Ingestion (Implemented):**
1.  **Asynchronous Execution**: Ingestion runs in a background process using **WP Cron** (`knowledge_async_ingest`), decoupled from the synchronous HTTP request of the UI. The UI polls for status using WordPress Transients.
2.  **Time Limits**: The background process respects PHP execution time limits.
3.  **Future Scalability (Strategy)**:
    - **Chunking**: For extreme asset counts, asset downloading should be chunked across multiple background events (e.g., process 20 images, save state, reschedule).
    - **Parallelism**: Use `curl_multi` for parallel asset fetching where bandwidth allows.
    - **Resumability**: Ingestion jobs must be idempotent and resumable if interrupted.

---

## Closing Rule

If an extraction result cannot be trusted by a human reader, it is a failure — regardless of technical success.
