# Domain Model — Lifecycle States

This document defines the **explicit lifecycle states** for Knowledge Objects and the allowed transitions between them. Lifecycle state is authoritative and must be persisted.

Lifecycle state exists to support:
- Asynchronous processing
- Offline-safe behaviour
- Predictable upgrades
- Explainable AI behaviour

---

## 1. General Lifecycle Principles

- Lifecycle state is explicit, not inferred
- State transitions are monotonic unless explicitly reversible
- Background jobs may advance state, never regress it silently

---

## 2. Article Lifecycle

Articles represent logical identity and have a minimal lifecycle.

### States
- **Active** — Article is in use
- **Archived** — Article is inactive but retained

### Transitions
- Active → Archived (manual)
- Archived → Active (manual)

---

## 3. Version Lifecycle

Versions reflect ingestion and processing progress.

### States
1. **Captured** — Raw content stored
2. **Normalised** — Content cleaned and structured
3. **Processed** — Summaries, metadata generated
4. **Embedded** — AI embeddings generated
5. **Available** — Ready for search and RAG
6. **Superseded** — Replaced by a newer source version
7. **Archived** — Retained but inactive

### Transitions
- Captured → Normalised → Processed → Embedded → Available
- Available → Superseded (when newer version appears)
- Any → Archived (manual)

---

## 4. Fork Lifecycle

Forks represent editable derivatives.

### States
- **Draft** — Actively edited
- **Stable** — Intended for reference
- **Archived** — Retained but inactive

### Transitions
- Draft → Stable
- Stable → Archived

---

## 5. Summary Lifecycle

Summaries are derived artifacts.

### States
- **Generated** — Created by AI or human
- **Reviewed** — Human-reviewed
- **Accepted** — Designated default
- **Deprecated** — Replaced or superseded

### Transitions
- Generated → Reviewed → Accepted
- Accepted → Deprecated

---

## 6. Highlight Lifecycle

Highlights are stable once created.

### States
- **Active**
- **Archived**

### Transitions
- Active → Archived

---

## 7. Note Lifecycle

Notes may evolve but remain attached.

### States
- **Active**
- **Archived**

### Transitions
- Active → Archived

---

## 8. Discussion Lifecycle

Discussions are persistent analytical records.

### States
- **Open**
- **Closed**
- **Archived**

### Transitions
- Open → Closed
- Closed → Archived

---

## 9. Project Lifecycle

Projects coordinate research.

### States
- **Active**
- **Paused**
- **Completed**
- **Archived**

### Transitions
- Active ↔ Paused
- Active → Completed
- Completed → Archived

---

## 10. Failure States

Processing failures are orthogonal to lifecycle state.

- Failures are recorded as events
- Failed jobs do not invalidate canonical data
- Retries must be explicit and visible

---

## Closing Note

Lifecycle state is a core part of system correctness. Any implementation that infers state implicitly or mutates state silently violates this model.

