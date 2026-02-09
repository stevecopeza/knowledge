# Knowledge System — Current State vs Target State

## Purpose
This document explicitly records the gap between the **current implementation** and the **intended Knowledge Workspace vision**.

Documentation must never imply functionality that does not exist.

---

## Current State (Implemented)

The system currently functions as a **Personal Librarian**:

- Robust ingestion from URLs, HTML, and bulk JSON
- Strong deduplication and metadata extraction
- Local-first storage with versioning
- Deterministic + AI-assisted search (RAG)
- Admin and frontend AI chat
- Elementor-based Archive and Search widgets

This layer is stable and production-grade.

---

## Skeletal State (Present but Incomplete)

### Projects
- CPT exists
- No article or note membership
- No scoped views or logic

### Taxonomies
- Categories and Tags functional
- Limited behavioural meaning

---

## Missing State (Not Implemented)

### Notes & Highlights
- No data model
- No UI
- No search or AI integration

### Collaboration
- No shared annotations
- No visibility scopes beyond personal forks

---

## Target State

The target system is a **Knowledge Workspace**:
- Projects act as research contexts
- Notes and highlights capture human reasoning
- Collaboration is limited, explicit, and non-social

---

## Rule

Features must move from:
Missing → Skeletal → Functional → Robust

Skipping stages is forbidden.
