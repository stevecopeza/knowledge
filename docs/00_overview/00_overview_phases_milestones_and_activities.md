# Knowledge System — Phases, Milestones, and Activities

This document defines a **pragmatic delivery plan** for the Knowledge system. It breaks work into **phases**, **milestones**, and **concrete activities**, aligned with the existing specifications and MVP cut.

The intent is to enable steady progress without architectural shortcuts.

---

## Phase 0 — Foundations (Non‑Negotiable)

**Goal:** Establish a safe, correct base before any visible features.

### Milestone 0.1 — Project Skeleton

**Activities:**
- Create WordPress plugin skeleton
- Register autoloading and namespaces
- Define base directory structure (`kb-data/`)
- Add activation / deactivation hooks

---

### Milestone 0.2 — Core Capabilities & Roles

**Activities:**
- Register Knowledge capabilities
- Define default role → capability mapping
- Enforce capability checks at service layer

---

### Milestone 0.3 — Event & Logging Infrastructure

**Activities:**
- Define event model
- Implement audit log storage
- Surface basic admin visibility

---

## Phase 1 — Canonical Knowledge Model (MVP Core)

**Goal:** Store knowledge correctly before interacting with it.

### Milestone 1.1 — Custom Post Types & Taxonomies

**Activities:**
- Implement CPTs: Article, Version, Fork, Project
- Implement taxonomies: Category, Tag
- Enforce immutability rules

---

### Milestone 1.2 — Filesystem Storage

**Activities:**
- Implement filesystem abstraction
- Store Version and Fork content
- Implement image download + optimisation
- Implement deduplication by hash

---

### Milestone 1.3 — Lifecycle Enforcement

**Activities:**
- Enforce Version supersession
- Prevent in-place edits
- Implement archive semantics

---

## Phase 2 — Ingestion & Normalisation

**Goal:** Get content into the system reliably.

### Milestone 2.1 — URL Ingestion

**Activities:**
- URL capture endpoint (admin + API)
- Async ingestion job
- HTML normalisation
- Metadata extraction

---

### Milestone 2.2 — Bulk Imports

**Activities:**
- CSV / list ingestion
- Progress tracking
- Partial failure handling

---

### Milestone 2.3 — Update Detection

**Activities:**
- Hash comparison
- Manual recrawl UI
- New Version creation

---

## Phase 3 — Annotation & Human Insight

**Goal:** Allow humans to add meaning.

### Milestone 3.1 — Highlights

**Activities:**
- Text anchoring model
- Highlight persistence
- Degradation handling

---

### Milestone 3.2 — Notes

**Activities:**
- Note creation & editing
- Visibility scopes
- Search indexing

---

### Milestone 3.3 — Discussions

**Activities:**
- Threaded discussion model
- Explicit references
- Lifecycle states

---

## Phase 4 — Organisation & Projects

**Goal:** Enable structured research.

### Milestone 4.1 — Categories & Tags

**Activities:**
- Category management UI
- Tag assignment
- Category-scoped defaults

---

### Milestone 4.2 — Projects

**Activities:**
- Project creation
- Knowledge membership
- Intent statements

---

### Milestone 4.3 — Async Research Tasks

**Activities:**
- Task queue
- Progress indicators
- Notifications

---

## Phase 5 — Search & Retrieval

**Goal:** Make knowledge findable offline.

### Milestone 5.1 — Deterministic Search

**Activities:**
- Text indexing
- Permission-aware queries
- Scoped search

---

### Milestone 5.2 — Index Lifecycle

**Activities:**
- Invalidation rules
- Rebuild commands
- Graceful degradation

---

## Phase 6 — AI Integration (Local)

**Goal:** Augment, not replace, human reasoning.

### Milestone 6.1 — AI Contracts Enforcement

**Activities:**
- Read/write boundaries
- Draft-only outputs

---

### Milestone 6.2 — Summarisation

**Activities:**
- AI-generated summaries
- Human acceptance flow

---

### Milestone 6.3 — Embeddings & RAG

**Activities:**
- Embedding generation
- Ollama directory build
- Explainable RAG queries

---

## Phase 7 — Operations & Resilience

**Goal:** Make the system durable.

### Milestone 7.1 — Background Jobs

**Activities:**
- Job persistence
- Retry logic
- Failure surfacing

---

### Milestone 7.2 — Backup & Restore

**Activities:**
- Backup validation
- Restore testing

---

### Milestone 7.3 — Offline Modes

**Activities:**
- Offline detection
- Deferred execution

---

## Phase 8 — Polish & Hardening

**Goal:** Prepare for real users.

### Milestone 8.1 — Admin & Ingestion Refinement

**Activities:**
- Dashboard implementation
- Bulk failure management (Retry/Delete)
- Ingestion robustness (timeouts, headers)
- Featured image scoring

---

### Milestone 8.2 — Frontend Polish (Elementor)

**Activities:**
- Elementor Archive Widget (Pagination, Endless Scroll, Re-check)
- Elementor Search Widget (AI/Chat Integration, Re-check, Display Controls)
- Mobile/Touch interaction refinements
- Card hover effects and delays

---

### Milestone 8.3 — Upgrade Testing

**Activities:**
- Migration testing
- Skipped-version scenarios

---

## Phase 9 — Documentation & Release Prep

**Goal:** Finalize documentation and prepare for v1.0 release.

### Milestone 9.1 — Documentation

**Activities:**
- Update Mobile/Touch design docs
- Document Elementor Widget usage
- Finalize Installation Guide

---

### Milestone 9.2 — Release

**Activities:**
- Version Bump
- Create release package


---

## Final Note

Phases are **sequenced deliberately**. Skipping earlier phases will create hidden technical debt that violates the system’s core invariants.

Build in order. Move forward only when invariants hold.

