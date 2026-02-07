# Design Invariants

This document defines the **non-negotiable design invariants** of the Knowledge Repository system. These invariants act as a constitution: all future design, implementation, and extension work **must comply** with them. Any feature or change that violates an invariant is invalid by definition.

These are intentionally written in plain English, but each invariant has direct architectural consequences.

---

## 1. Ownership and Control

**Invariant 1.1 — User Ownership**  
All ingested content, derived artifacts, annotations, and AI outputs are owned by the WordPress site owner. No external service is authoritative over stored knowledge.

*Implications:*
- No hard dependency on SaaS services
- External tools may assist, but never own or mutate canonical data
- Data must be exportable in non-proprietary formats

---

## 2. WordPress as System of Record

**Invariant 2.1 — WordPress Authority**  
WordPress is the authoritative system of record for all knowledge metadata, relationships, permissions, and lifecycle state.

*Implications:*
- Core entities map to Custom Post Types and taxonomies
- WordPress roles and capabilities are the baseline permission system
- No shadow databases that become authoritative

---

## 3. Immutability of Knowledge

**Invariant 3.1 — Immutable Source Versions**  
Once a source article version is ingested and annotated, it must never be silently modified or overwritten.

**Invariant 3.2 — Explicit Change Only**  
Any change to content must result in either:
- A new version, or
- A local fork with explicit lineage

*Implications:*
- Notes and highlights always bind to a specific version or fork
- Silent updates are prohibited

---

## 4. Forks Are Not Duplicates

**Invariant 4.1 — Fork Lineage**  
A fork represents a deliberate, user-controlled divergence from a source version.

**Invariant 4.2 — Deduplication Scope**  
Deduplication applies only to source ingestion, never to forks.

*Implications:*
- Forks retain references to their origin
- Forks are first-class knowledge objects

---

## 5. Offline-First Consumption

**Invariant 5.1 — Full Offline Use**  
All stored knowledge must remain readable, searchable, and annotatable with zero internet connectivity.

**Invariant 5.2 — Online-Required Ingestion**  
Connectivity is required only for acquiring new external content.

*Implications:*
- Images, text, and metadata must be locally stored
- AI/RAG operations must function with local models only

---

## 6. AI Is a Consumer, Not an Authority

**Invariant 6.1 — AI Non-Authority**  
AI systems may generate summaries, annotations, or suggestions, but never become authoritative over canonical knowledge.

**Invariant 6.2 — Explicit Human Acceptance**  
Any AI-generated artifact must be reviewable and editable by a human.

*Implications:*
- AI outputs are always derived data
- AI artifacts must be rebuildable or discardable

---

## 7. Determinism Over Magic

**Invariant 7.1 — Explainable Behaviour**  
Search results, RAG responses, and AI outputs must be explainable in terms of:
- Source selection
- Weighting factors
- Scope boundaries

**Invariant 7.2 — No Hidden Heuristics**  
Undocumented or opaque relevance logic is not permitted.

---

## 8. Progressive Refinement

**Invariant 8.1 — Cheap Ingestion First**  
Content ingestion may initially be incomplete or unprocessed.

**Invariant 8.2 — Deferred Enrichment**  
Summarisation, embedding, scoring, and optimisation may occur asynchronously.

*Implications:*
- Bulk imports must be fast and resilient
- Processing pipelines must be restartable

---

## 9. Long-Lived Data Guarantees

**Invariant 9.1 — Backward Compatibility**  
Once knowledge is stored, future versions of the plugin must preserve data integrity, even if versions are skipped.

**Invariant 9.2 — Archive, Not Delete**  
Knowledge objects may be archived but not permanently deleted by default.

---

## 10. Explicit Non-Goals Are Binding

**Invariant 10.1 — Scope Discipline**  
The system explicitly excludes:
- Social media integration
- Content discovery feeds
- External publishing platforms

Features that implicitly introduce these are invalid.

---

## 11. Replaceability of Components

**Invariant 11.1 — Replaceable AI Stack**  
AI models, embedding strategies, and vector implementations must be replaceable without data loss.

**Invariant 11.2 — Stable Knowledge Core**  
The knowledge model must outlive any single AI or storage technology.

---

## Closing Note

These invariants are the foundation of all subsequent documents. They are intentionally strict. Flexibility is achieved **around** them, never **through** them.

Any future discussion or implementation decision must be traceable back to compliance with this document.

