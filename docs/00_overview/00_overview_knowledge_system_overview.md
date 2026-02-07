# Knowledge System — Overview

## 1. What This System Is

The Knowledge system is a **local‑first, ownership‑first knowledge repository** built as a WordPress plugin. It is designed to capture, retain, annotate, and reason over long‑lived information in a way that remains trustworthy over time — even as sources change, tools evolve, or AI models are replaced.

At its core, the system treats **knowledge as durable infrastructure**, not as disposable notes or transient content feeds.

It is explicitly designed to work **offline**, to store content **locally**, and to ensure that **humans remain the final authority** over meaning, interpretation, and acceptance of AI output.

---

## 2. The Problem It Solves

Modern knowledge work suffers from recurring failures:

- Information is scattered across links, apps, and services
- External content disappears, changes silently, or becomes inaccessible
- Notes lose their connection to original sources
- AI tools produce answers without provenance or accountability
- Systems optimise for engagement or convenience instead of correctness

The Knowledge system exists to address these failures by:

- Capturing content **once**, then retaining it permanently
- Preserving **source integrity and version history**
- Binding notes, highlights, and discussions directly to source text
- Allowing AI to assist without ever becoming authoritative

---

## 3. Core Concepts (High Level)

The system is built around a small number of stable concepts:

### Articles and Versions

An **Article** represents a logical piece of knowledge. Each Article may have multiple **Versions**, which are immutable snapshots of the source at a point in time. Versions are never overwritten.

### Forks

A **Fork** is a deliberate, user‑controlled divergence from a Version. Forks allow users to correct, adapt, or reinterpret content while preserving lineage back to the original source.

### Annotations

**Highlights, Notes, and Discussions** are first‑class knowledge objects. They are not comments layered on top, but durable analytical artifacts tied directly to specific text.

### Projects

A **Project** is a research context. Projects group knowledge objects around an intent, define scope for AI reasoning, and coordinate asynchronous research work.

### AI as a Consumer

AI systems may read from the knowledge base, generate summaries, or assist with reasoning — but they **never own or silently modify canonical data**. All AI output is reviewable, discardable, and replaceable.

---

## 4. How the System Works (End‑to‑End)

At a high level, the lifecycle of knowledge looks like this:

1. **Ingest**  
   Content is captured from URLs, documents, APIs, or manual input. Capture is fast and asynchronous.

2. **Normalise and Store**  
   Content, images, and metadata are stored locally in a deterministic filesystem layout. Nothing relies on external availability.

3. **Annotate**  
   Users highlight text, add notes, and create discussions that bind directly to specific versions or forks.

4. **Organise**  
   Categories, tags, and Projects provide semantic and contextual structure.

5. **Search and Retrieve**  
   Deterministic search is always available; semantic and AI‑assisted retrieval augments it when enabled.

6. **Reason (RAG)**  
   AI queries operate over explicitly scoped knowledge, with full explainability of which sources were used and why.

Throughout this process, the system remains usable offline, and all derived data can be rebuilt at any time.

---

## 5. What Makes This Different

Compared to common tools:

- **Notion / Obsidian**: This system prioritises provenance, immutability, and version lineage over free‑form editing.
- **NotebookLM and similar AI tools**: AI is constrained, explainable, and non‑authoritative.
- **Bookmarking or read‑later apps**: Content is retained, enriched, and reasoned over, not merely saved.

The system optimises for **long‑term correctness**, not short‑term convenience.

---

## 6. What This System Is Not

The Knowledge system explicitly does **not** attempt to be:

- A social network or discovery feed
- A public publishing platform
- A truth arbiter or fact‑checker
- An autonomous AI agent

Any feature that implicitly introduces these characteristics is out of scope by design.

---

## 7. Who This Is For

The system is intended for:

- Individuals who care about durable personal knowledge
- Small teams conducting research or analysis
- Organisations that require data ownership, auditability, and offline capability

It is particularly suited to environments where **trust, longevity, and explainability** matter more than speed or virality.

---

## 8. Design Philosophy

The system is governed by a small set of principles:

- Ownership over convenience
- Explicitness over magic
- Immutability over silent change
- Human judgement over automated authority

These principles inform every design decision elsewhere in the documentation.

---

## Closing Perspective

The Knowledge system is intentionally conservative. It is designed to remain useful not just next year, but a decade from now — even as tools, models, and platforms change.

If a feature improves speed at the cost of trust, the system chooses trust.

That trade‑off is deliberate.

