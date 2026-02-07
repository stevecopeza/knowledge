# Storage — Database Schema

This document defines how the domain model maps onto **WordPress database structures**. WordPress remains the authoritative system of record for all canonical metadata and relationships.

This schema is intentionally conservative and additive.

---

## 1. General Principles

- Custom Post Types (CPTs) represent first-class entities
- Taxonomies represent classification, not behavior
- Post meta stores immutable identifiers and state
- No business logic depends on serialized blobs

---

## 2. Core Custom Post Types

### 2.1 `kb_article`
Represents the logical identity of an Article.

**Key fields**
- `post_title` — stable human label
- `post_status` — active / archived

**Meta**
- `article_uuid`

---

### 2.2 `kb_version`
Represents an immutable Version.

**Meta**
- `version_uuid`
- `article_uuid`
- `source_uuid`
- `content_hash`
- `lifecycle_state`
- `created_at`

---

### 2.3 `kb_fork`
Represents a Fork.

**Meta**
- `fork_uuid`
- `parent_version_uuid`
- `lifecycle_state`

---

### 2.4 `kb_summary`
Represents a Summary.

**Meta**
- `summary_uuid`
- `parent_object_uuid`
- `generator_type`
- `lifecycle_state`

---

### 2.5 `kb_highlight`
Represents a Highlight.

**Meta**
- `highlight_uuid`
- `parent_object_uuid`
- `quoted_text`
- `offset_start`
- `offset_end`

---

### 2.6 `kb_note`
Represents a Note.

**Meta**
- `note_uuid`
- `parent_object_uuid`
- `visibility_scope`

---

### 2.7 `kb_discussion`
Represents a Discussion thread.

**Meta**
- `discussion_uuid`

---

### 2.8 `kb_project`
Represents a Project.

**Meta**
- `project_uuid`

---

## 3. Custom Tables

### 3.1 `wp_kb_search_index`
Represents the full-text search index for file-based content.

**Columns**
- `id` (bigint, PK)
- `object_uuid` (varchar, index)
- `object_type` (varchar: 'version', 'fork', 'note')
- `content` (longtext, FullText Index)
- `title` (text)
- `metadata` (json)
- `updated_at` (datetime)

**Notes**
- This table is a shadow copy of file content
- Used solely for `MATCH AGAINST` queries
- Not the canonical source of truth

- `lifecycle_state`

---

## 3. Taxonomies

### 3.1 `kb_category`
Primary semantic classification.

### 3.2 `kb_tag`
Secondary flexible labeling.

---

## 4. Relationships

Relationships are stored via:
- UUID references in post meta
- Explicit join tables only if required for performance

WordPress post relationships are not inferred via hierarchy.

---

## 5. State Management

- Lifecycle state is stored as explicit meta
- State transitions are validated in the domain layer

---

## Closing Note

This schema is intentionally minimal. Additional fields may be added, but existing fields must never be repurposed or removed.

