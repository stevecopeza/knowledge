# AI Integration — Embedding Pipeline

This document defines how **embeddings** are generated, stored, and maintained. Embeddings support semantic search and RAG, but are never canonical data.

---

## 1. Principles

- Embeddings are derived artifacts
- Embeddings are rebuildable and disposable
- Embeddings must work fully offline

---

## 2. Inputs

Embeddings may be generated from:
- Version content
- Fork content
- Accepted Summaries
- Notes (permission-scoped)

Each input is chunked deterministically.

---

## 3. Generation Lifecycle

States:
- Pending
- Generated
- Invalidated

Rules:
- New or updated content invalidates prior embeddings
- Regeneration is explicit and queued

---

## 4. Storage

- Embeddings are stored in the `/ai/embeddings/` directory
- Manifests map embeddings to source objects and chunks

**MVP Implementation Strategy:**
- **Format**: Simple JSON/Binary flat files per object.
- **Retrieval**: In-memory linear scan (brute-force cosine similarity).
- **Scale**: Suitable for < 10,000 vectors.
- **Future**: Migrate to SQLite-vss or dedicated local vector store if scale requires.

---

## 5. Offline Guarantees

- All embedding models are local
- No external calls are required at runtime

---

## Closing Note

Embeddings accelerate retrieval but never define meaning.

