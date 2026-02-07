# Knowledge System — Developer Onboarding Guide

This guide explains **how to approach, understand, and implement** the Knowledge system. It is intentionally practical and opinionated.

---

## 1. What You Are Building

You are not building a typical WordPress plugin.

You are building a **long-lived knowledge system** with:
- Immutable data
- Explicit lifecycle rules
- Strong separation between canonical data and derived artifacts

If you treat this as CRUD with AI features, you will break it.

---

## 2. Read These First (In Order)

Before writing code, read:

1. `00_overview/knowledge_system_overview.md`
2. `01_invariants/01_design_invariants.md`
3. `02_domain_model/01_entities.md`
4. `03_storage/02_filesystem_layout.md`
5. `08_ai_integration/01_ai_contracts.md`

If anything you plan violates these, stop.

---

## 3. Mental Model to Keep

- **Canonical data is sacred**
- Versions are immutable
- Forks are deliberate
- AI output is disposable
- Everything must survive offline

When in doubt, choose correctness over convenience.

---

## 4. WordPress-Specific Guidance

### Custom Post Types

CPTs represent:
- Articles
- Versions
- Forks
- Projects

Do not overload CPTs with transient state.

---

### Filesystem vs Database

- Database = identity, relationships, metadata
- Filesystem = content, media, AI artifacts

Never store large blobs in postmeta.

---

## 5. Background Jobs Are Core

Ingestion, summarisation, embeddings, and updates:
- Must be async
- Must be resumable
- Must be observable

If a job can fail silently, the design is wrong.

---

## 6. AI Integration Rules

- AI may read, never silently write
- All AI output is draft until accepted
- AI components must be replaceable

Treat AI as an external consultant, not a system owner.

---

## 7. Common Mistakes to Avoid

- Editing versions instead of forking
- Hiding failures instead of surfacing them
- Letting UI bypass capability checks
- Rebuilding derived data implicitly

---

## 8. How to Extend the System Safely

When adding features:
1. Identify the canonical data
2. Decide if it is immutable
3. Decide who owns acceptance
4. Decide how it behaves offline

If you cannot answer all four, do not implement yet.

---

## Closing Advice

This system rewards restraint.

If a feature feels easy but violates the invariants, it is wrong.

Build slowly. Build explicitly.

