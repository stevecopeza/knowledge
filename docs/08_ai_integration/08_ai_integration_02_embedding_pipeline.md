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

**MVP Implementation:**
- **Chunking**: `ChunkingService` splits content into 500-token chunks with 50-token overlap.
- **Generation**: `EmbeddingGenerator` job processes chunks using Ollama (default: `llama3.2`).
- **Storage**: `VectorStore` saves embeddings as JSON files in `wp-content/uploads/knowledge_data/ai/embeddings/`.
- **Retrieval**: In-memory linear scan (brute-force cosine similarity) via `VectorStore::search()`.
- **Scale**: Suitable for < 10,000 vectors.
- **Future**: Migrate to SQLite-vss or dedicated local vector store if scale requires.

---

## 5. Offline Guarantees

- All embedding models are local
- No external calls are required at runtime

---

## Closing Note

Embeddings accelerate retrieval but never define meaning.

