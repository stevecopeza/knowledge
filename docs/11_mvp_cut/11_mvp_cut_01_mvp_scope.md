# MVP Cut — Scope Definition

This document defines the **Minimum Viable Product (MVP)** scope. MVP here means *coherent, durable, and trustworthy*, not minimal effort.

Anything not listed here is **explicitly excluded from MVP**, even if designed elsewhere.

---

## 1. Core Capabilities (Must Exist)

### 1.1 Knowledge Capture
- URL ingestion
- Manual text ingestion
- Bulk URL import (CSV / text)
- Async ingestion with visible progress

---

### 1.2 Knowledge Storage
- Articles, Versions, Forks
- Local filesystem storage
- Image download, optimisation, deduplication

---

### 1.3 Annotation
- Highlights (robust anchoring)
- Notes (private by default)
- Discussions (non-social)

---

### 1.4 Organisation
- Categories
- Tags
- Projects (basic semantics)

---

### 1.5 Search
- Deterministic full-text search
- Permission-aware results
- Offline functionality

---

### 1.6 AI (Local)
- AI-generated summaries (draft)
- Local embeddings
- Local RAG queries
- Explainable answers

---

### 1.7 Operations
- Background job system
- Upgrade-safe migrations
- Backup and restore support

---

## 2. Explicit MVP Exclusions

- Semantic search tuning
- Advanced scoring models
- External notifications
- Mobile applications
- Multi-org governance tooling

---

## Closing Note

MVP is defined by *correctness and trust*, not feature count.

