# Storage — Image Handling

This document defines how images and other embedded media are ingested, stored, optimised, deduplicated, and made available offline and to AI/RAG processes.

---

## 1. Principles

- Images are first-class content assets
- All images must be locally available for offline use
- Images are never hot-linked at runtime
- Image handling must be deterministic and reversible

---

## 2. Image Ingestion

Images may be ingested from:
- External URLs embedded in Articles
- Uploaded files
- API-based ingestion

Rules:
- Original image is fetched once
- Fetch failures are recorded as events
- Retry is explicit and visible

---

## 3. Deduplication

- Images are deduplicated using a strong content hash
- Identical images across Articles or Versions share storage

Rules:
- Deduplication never alters semantic references
- Removing an Article does not delete shared images

---

## 4. Storage Layout

Images are stored under the media directory:

```
/media/{hash}/
├── original
├── optimised
└── metadata.json
```

Metadata includes:
- Source URL(s)
- Dimensions
- Format
- Optimisation profile

---

## 5. Optimisation

Optimisation is mandatory and offline-safe.

Rules:
- Generate size-appropriate variants
- Preserve original for audit
- Optimised versions are used by default

---

## 6. Referencing Images

- Articles reference images via internal paths
- References are rewritten during normalisation

Rules:
- No external URLs in rendered content

---

## 7. AI and RAG Usage

Images may be exposed to AI systems as:
- Associated metadata
- Captions or extracted text (if available)

Rules:
- AI systems may not modify images
- Image-derived data is derived and rebuildable

---

## 8. Offline Guarantees

- All images required to render content must be present locally
- Broken image references are unacceptable

---

## Closing Note

Image handling is part of content integrity. Any shortcut that breaks offline use or deduplication is invalid.

