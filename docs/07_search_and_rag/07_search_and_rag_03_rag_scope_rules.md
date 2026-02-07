# Search & RAG — RAG Scope Rules

This document defines how **context is selected** for Retrieval-Augmented Generation (RAG). Scope control is essential to relevance and trust.

---

## 1. Default Scope

- RAG operates within an explicit scope
- Default scope is **Project-bound** when inside a Project
- Outside Projects, scope is Repository-bound

---

## 2. Scope Configuration

Users may configure RAG scope to include:
- Specific Projects
- Selected Categories
- Explicit object sets

Scope changes are explicit and visible.

---

## 3. Archived Content

- Archived content is excluded from RAG by default
- Archived content remains searchable

Users may override this behavior.

---

## 4. Permissions Enforcement

- RAG never bypasses permissions
- Private notes and objects are excluded unless permitted

---

## Closing Note

RAG scope discipline prevents context pollution and preserves answer quality.

