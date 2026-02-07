# Storage — Filesystem Layout

This document defines the **canonical filesystem layout** used for storing content, media, and AI-related artifacts. The filesystem complements the WordPress database and is required for offline operation and AI/RAG access.

---

## 1. General Principles

- Filesystem storage is authoritative for **content bodies and media**
- Database stores references, metadata, and state
- All filesystem paths are deterministic and rebuildable
- No file is required to be writable by AI processes

---

## 2. Root Layout

```
/wp-content/kb-data/
├── articles/
├── versions/
├── forks/
├── media/
├── ai/
└── temp/
```

---

## 3. Articles Directory

```
/articles/{article_uuid}/
```

Contains high-level, human-readable artifacts:
- `index.md` — article overview
- `metadata.json` — denormalised metadata snapshot

---

## 4. Versions Directory

```
/versions/{version_uuid}/
├── content.md
├── content.html
├── metadata.json
└── assets/
```

Rules:
- Content is immutable
- Assets include locally stored images

---

## 5. Forks Directory

```
/forks/{fork_uuid}/
├── content.md
├── content.html
├── metadata.json
└── assets/
```

Rules:
- Content is editable
- Fork lineage is recorded in metadata

---

## 6. Media Directory

```
/media/{hash}/original
/media/{hash}/optimised
```

Rules:
- Media is deduplicated by hash
- Optimised variants are generated for offline use

---

## 7. AI Directory

```
/ai/
├── embeddings/
├── chunks/
├── manifests/
└── logs/
```

Rules:
- All AI artifacts are derived data
- Manifests map knowledge objects to AI inputs
- AI directories are rebuildable

---

## 8. Access Control & Security

The `kb-data` directory is **private by default**.

**Rules:**
- **Direct Access Blocked**: A `.htaccess` (or Nginx equivalent) must exist at the root of `/kb-data/` to `Deny from all`.
- **Proxy Serving**: Authorized users access files via a PHP proxy endpoint (e.g., `?kb_action=file_proxy&path=...`) which verifies `current_user_can('read_knowledge')`.
- **Bypass**: The only exception is the `/ai/` directory if strictly local tools require direct access (configured explicitly).

---

## 8. Temporary Directory

```
/temp/
```

Used for ingestion and processing. Contents may be purged safely.

---

## 9. Offline Guarantees

- All directories except `/temp` must remain available offline
- No runtime dependency on external URLs

---

## Closing Note

Filesystem layout is part of the public contract. Any change must preserve backward compatibility.

