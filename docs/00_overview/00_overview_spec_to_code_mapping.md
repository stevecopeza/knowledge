# Knowledge System — Spec → Code Mapping

This document maps **conceptual specifications** to **concrete WordPress implementation constructs**. Its purpose is to eliminate ambiguity during implementation and prevent architectural drift.

This is not an implementation guide, but a **binding reference** between the spec and code.

---

## 1. High-Level Mapping Overview

| Spec Concept | Primary Implementation |
|-------------|------------------------|
| Articles | Custom Post Type (`kb_article`) |
| Versions | Custom Post Type (`kb_version`) |
| Forks | Custom Post Type (`kb_fork`) |
| Projects | Custom Post Type (`kb_project`) |
| Categories | Custom Taxonomy (`kb_category`) |
| Tags | Custom Taxonomy (`kb_tag`) |
| Annotations | Custom Tables + Post Meta |
| AI Artifacts | Filesystem (`kb-data/ai/`) |
| Media | Filesystem (`kb-data/media/`) |
| Search Indexes | Custom Tables / External Index |

---

## 2. Custom Post Types (Canonical Objects)

### 2.1 `kb_article`

Represents the **logical knowledge object**.

- Stores identity and high-level metadata
- No large content blobs
- Never directly edited for content

Primary meta:
- UUID
- Status (active / archived)

---

### 2.2 `kb_version`

Represents an **immutable snapshot** of source content.

- One-to-many with Articles
- Content stored on filesystem

Primary meta:
- UUID
- Article UUID
- Source URL
- Version timestamp

---

### 2.3 `kb_fork`

Represents a **user-controlled divergence** from a Version.

- Linked to exactly one Version
- Editable

Primary meta:
- UUID
- Parent Version UUID
- Fork reason

---

### 2.4 `kb_project`

Represents a **research context**.

- References Articles, Versions, Forks
- Defines AI scope

Primary meta:
- UUID
- Intent statement

---

## 3. Taxonomies

### 3.1 `kb_category`

- Primary semantic classification
- Optional scoring context

---

### 3.2 `kb_tag`

- Lightweight, ad-hoc classification

---

## 4. Annotations

Annotations are **not posts**.

### 4.1 Storage

- Custom tables:
  - `kb_highlights`
  - `kb_notes`
  - `kb_discussions`

Each row includes:
- UUID
- Target object UUID
- Anchor data
- Author
- Timestamps

---

## 5. Filesystem Layout

Root directory:
```
/wp-content/kb-data/
```

Subdirectories:
```
content/        # Version and Fork text
media/          # Images and assets
ai/             # Embeddings, RAG, summaries
manifests/      # Export / RAG manifests
```

Filesystem is authoritative for large content.

---

## 6. Background Jobs

### Implementation

- Custom job table (recommended)
- Or Action Scheduler (acceptable)

Jobs include:
- Ingestion
- Normalisation
- Summarisation
- Embedding generation

Each job:
- Has persistent state
- Is retryable
- Emits events

---

## 7. Search Implementation

### Deterministic Search

- WordPress search overridden or augmented
- Custom index tables recommended

### Semantic Search

- Embedding vectors stored separately
- Never queried synchronously on save

---

## 8. AI Integration

### Models

- Local only (e.g. Ollama)
- No SaaS dependency

### Contracts

- Read-only access to canonical data
- Write access only to draft artifacts

---

## 9. Permissions & Capabilities

- Capabilities registered on activation
- Checked at domain layer
- UI checks are secondary

---

## 10. Anti-Patterns (Explicit)

Do NOT:
- Store version content in post content
- Use post revisions for versions
- Let AI write directly to canonical CPTs
- Use postmeta for large blobs

---

## Closing Note

If a piece of code cannot be mapped back to a spec concept in this document, it likely does not belong in the system.

