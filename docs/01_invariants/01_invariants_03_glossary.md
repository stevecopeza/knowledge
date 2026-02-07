# Glossary

This glossary defines **canonical terminology** used throughout the documentation. Terms are precise and binding. If a term appears elsewhere, it must conform to the meaning defined here.

---

## Article
A unit of ingested content derived from an external or internal source (e.g. URL, document, pasted text). An Article may have multiple Versions.

---

## Source
The original origin of an Article, such as a URL, document file, or manual input. A Source may produce multiple Versions over time.

---

## Version
An immutable snapshot of an Article at a specific point in time, reflecting the Source content as ingested. Versions are never modified once created.

---

## Fork
A user-controlled derivative of a Version that diverges from the original Source content. Forks retain lineage metadata linking back to the originating Version but may be edited independently.

---

## Knowledge Object
Any first-class entity managed by the system, including Articles, Versions, Forks, Highlights, Notes, Discussions, Summaries, Scores, and Projects.

---

## Summary
A condensed representation of a Version or Fork, typically AI-generated, intended to aid comprehension. Summaries are editable and never authoritative over the underlying content.

---

## Highlight
A user-selected excerpt of text within a Version or Fork, anchored by quoted text and positional offsets.

---

## Note
A user-authored annotation attached to a Highlight or directly to a Version or Fork. Notes are searchable and may be permission-scoped.

---

## Discussion
A structured, non-social thread of commentary referencing specific text or Knowledge Objects. Discussions are intended for analysis, not engagement metrics.

---

## Project
A research workspace that groups Knowledge Objects under a shared intent or goal. Projects may define scope, processing rules, and AI context boundaries.

---

## Category
A primary topical classification applied to Articles and Projects. Categories define semantic context and influence scoring and retrieval behavior.

---

## Tag
A secondary, flexible label applied to Knowledge Objects for ad-hoc organisation and filtering.

---

## Score
A human-assigned rating or assessment applied to a Knowledge Object within a defined context (e.g. category). Scores influence weighting but do not assert truth.

---

## Annotation
A non-authoritative, descriptive AI- or human-generated comment about a Knowledge Object (e.g. potential bias indicators). Annotations do not modify content.

---

## Ingestion
The process of capturing content into the system, which may occur in stages from raw capture to fully processed knowledge.

---

## Deduplication
The identification and merging of identical Source content based on content hashing and heuristics. Deduplication applies only to Sources and Versions, not Forks.

---

## Progressive Refinement
The model whereby ingested content is incrementally enriched (e.g. summaries, embeddings) through asynchronous processes.

---

## RAG (Retrieval-Augmented Generation)
An AI interaction model where responses are generated using retrieved Knowledge Objects as grounded context.

---

## Explainability
The ability to trace search results or AI outputs back to specific Knowledge Objects, versions, and weighting factors.

---

## Archive
A reversible state applied to a Knowledge Object indicating it is inactive but retained for reference and audit purposes.

---

## Canonical Data
The authoritative representation of a Knowledge Object as stored in WordPress. Canonical data is never silently altered by automation.

---

## Derived Data
Data generated from Canonical Data (e.g. summaries, embeddings) that can be rebuilt or discarded without loss of meaning.

---

## Background Job
An asynchronous task responsible for ingestion, processing, or AI-related work, with visible state and retry capability.

---

## Offline Mode
A system state in which no external connectivity is available, but all existing knowledge remains usable.

---

## Closing Note

This glossary is normative. Introducing new terms or redefining existing ones requires explicit revision of this document.

