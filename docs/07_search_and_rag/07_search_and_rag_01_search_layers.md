# Search & RAG — Search Layers

This document defines the **search architecture** of the system. Search is layered, deterministic first, and AI-assisted second.

---

## 1. Principles

- Deterministic search is foundational
- AI augments, never replaces, search
- Search must work fully offline

---

## 2. Deterministic Search

Deterministic search indexes:
- Article titles
- Version and Fork content
- Summaries
- Notes
- Highlights

**Implementation:**
- **Technology**: Custom WordPress table (`wp_kb_search_index`) with FullText support.
- **Strategy**: "Shadow Content". Text content from the filesystem is mirrored into this table.
- **Sync**: Updated via background jobs on save.

Rules:
- Permission-aware
- Scope-aware (Project, Repository)

---

## 3. Semantic Search (Optional Layer)

Semantic search may be enabled using embeddings.

Rules:
- Semantic results are explainable
- Deterministic results are always available

---

## 4. Search Scope

Search may be scoped to:
- Entire repository
- Project
- Specific Categories

Default scope is explicit.

---

## Closing Note

Search prioritises trust and predictability over novelty.

