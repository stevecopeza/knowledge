# API — REST API

This document defines the **public REST API surface** for ingestion and interaction. The API is minimal, stable, and implementation-focused.

---

## 1. Principles

- RESTful, resource-oriented design
- Token-based authentication
- Idempotent operations where possible

**Scope Distinction:**
- **REST API**: For external tools, mobile apps, and CLI agents. Uses Token Auth.
- **Admin AJAX**: For the plugin's own React/WP Admin UI. Uses standard WordPress Cookie Auth (`wp_ajax_kb_*`).

---

## 2. Authentication

- Token-based authentication
- Tokens are scoped and revocable

---

## 3. Ingestion Endpoints

### POST /api/ingest/url

Ingest a single URL.

Payload:
- url
- optional metadata (category, tags, project)

Response:
- ingestion job ID

---

### POST /api/ingest/bulk

Submit a bulk import.

Payload:
- file (CSV/text)
- optional defaults

Response:
- bulk import ID

---

## 4. Status Endpoints

### GET /api/ingest/{id}/status

Returns processing state and progress.

---

## 5. Error Handling

- Errors are explicit and typed
- Partial success is reported

---

## Closing Note

The API is intentionally narrow to reduce long-term maintenance burden.

