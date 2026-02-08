# Search Index Lifecycle and Rebuilds

Defines how search indexes (`wp_kb_search_index`) are maintained, invalidated, and rebuilt.

## 1. Real-Time Indexing (Sync)

The search index is a **shadow copy** of file-based content, maintained in real-time.

### Triggers
- **New Version Created** (`kb_version_created`):
  - Ingested content is immediately stripped of tags and indexed.
  - Metadata (source, hash) is stored.
- **Fork Updated** (`kb_fork_updated`):
  - User edits to Forks are re-indexed on save.
  - `updated_at` timestamp is refreshed.
- **Deletion**:
  - When a Knowledge Object is deleted, its index entry is removed.

### Implementation
- **Service**: `SearchIndexService`
- **Logic**:
  1.  Extract content from `kb_version` or `kb_fork`.
  2.  Strip HTML tags (`wp_strip_all_tags`).
  3.  Upsert into `wp_kb_search_index` (INSERT or UPDATE).

## 2. Invalidation Strategy

Since the index is a derivative of immutable Versions (and mutable Forks), invalidation logic is simple:

- **Immutable Versions**: Never need "update", only "insert" or "delete".
- **Forks**: Updated in-place on every save.
- **Rebuilds**: Required only if:
    - The schema of `wp_kb_search_index` changes.
    - The text extraction logic (stripping) changes.
    - Database corruption occurs.

## 3. Rebuild Modes (Planned)

### Partial Rebuild
- Re-index a specific object or Project.

### Full Rebuild
- Truncate `wp_kb_search_index`.
- Iterate all `kb_version` and `kb_fork` posts.
- Re-read file content and index.

## 4. Performance

- **Non-Blocking**: Indexing happens during the HTTP request (currently) but is fast enough for individual items.
- **Bulk Import**: Should defer indexing to background jobs (future).
