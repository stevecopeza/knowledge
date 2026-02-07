# Domain Model — Entities

This document defines all **first-class domain entities** in the system. Each entity has a clear responsibility, lifecycle expectations, and explicit boundaries. No entity exists implicitly.

All entities defined here must comply with the Design Invariants and Glossary.

---

## 1. Article

**Purpose**  
Represents a logical piece of content ingested from a Source.

**Key properties**
- Stable identity across versions
- Linked to one or more Versions
- Classified by Categories and Tags

**Notes**
- An Article is not directly editable
- All content interaction happens at the Version or Fork level

---

## 2. Source

**Purpose**  
Represents the origin of an Article (e.g. URL, file, manual input).

**Key properties**
- Source type (URL, file, text, API)
- Canonical identifier (e.g. URL)
- Fetch metadata (timestamps, headers)

**Notes**
- A Source may produce multiple Versions over time
- Source identity is used in deduplication

---

## 3. Version

**Purpose**  
An immutable snapshot of Source content at ingestion time.

**Key properties**
- Content hash
- Creation timestamp
- Link to Source and Article

**Notes**
- Versions are never modified
- All Highlights bind to a specific Version

---

## 4. Fork

**Purpose**  
A user-controlled derivative of a Version.

**Key properties**
- Parent Version reference
- Editable content body
- Divergence metadata

**Notes**
- Forks are first-class and persist independently
- Forks are not deduplicated

---

## 5. Summary

**Purpose**  
A condensed representation of a Version or Fork.

**Key properties**
- Summary text
- Generator (AI or human)
- Acceptance state

**Notes**
- Summaries are derived data
- Human edits do not affect canonical content

---

## 6. Highlight

**Purpose**  
Anchors a specific excerpt of text within a Version or Fork.

**Key properties**
- Quoted text
- Positional offsets
- Parent Version or Fork

**Notes**
- Highlights must survive minor formatting changes

---

## 7. Note

**Purpose**  
A user-authored annotation.

**Key properties**
- Note body
- Author
- Visibility scope

**Notes**
- Notes may attach to Highlights or directly to Versions/Forks

---

## 8. Discussion

**Purpose**  
A structured commentary thread referencing text or Knowledge Objects.

**Key properties**
- Referenced object(s)
- Ordered contributions

**Notes**
- Discussions are non-social and non-metric-driven

---

## 9. Score

**Purpose**  
A human-assigned assessment of a Knowledge Object.

**Key properties**
- Numeric value
- Context (Category)
- Reviewer

**Notes**
- Scores influence weighting but do not assert truth

---

## 10. Annotation

**Purpose**  
A descriptive, non-authoritative observation about a Knowledge Object.

**Key properties**
- Annotation text
- Origin (AI or human)

**Notes**
- Annotations never modify content

---

## 11. Project

**Purpose**  
A research workspace grouping Knowledge Objects.

**Key properties**
- Intent description
- Scoped membership
- Processing rules

**Notes**
- Projects may define AI context boundaries

---

## 12. Category

**Purpose**  
Primary topical classification.

**Key properties**
- Stable semantic meaning

**Notes**
- Categories influence scoring and retrieval

---

## 13. Tag

**Purpose**  
Secondary, flexible label.

**Key properties**
- Free-form text

**Notes**
- Tags have no semantic weight

---

## Closing Note

These entities are exhaustive. Introducing new entity types requires explicit amendment to this document.

