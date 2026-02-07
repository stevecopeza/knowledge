# Search & RAG — Weighting and Scoring

This document defines how **human scoring and contextual signals** influence search ranking and RAG weighting. Scoring informs relevance; it does not assert truth.

---

## 1. Principles

- Human judgement is primary
- Scores are contextual, not global
- Weighting must be explainable

---

## 2. Score Types

Scores may represent:
- Credibility (within a Category)
- Usefulness
- Relevance to a Project

Scores are numeric and reviewer-authored.

---

## 3. Application to Search

Search ranking may consider:
- Text relevance
- Recency (Version state)
- Human scores

Rules:
- Scores adjust weighting, not inclusion
- Low scores do not hide content by default

---

## 4. Application to RAG

RAG retrieval may:
- Prefer higher-scored objects
- De-prioritise archived or superseded versions

All weighting factors must be surfaced in explanations.

---

## Closing Note

Scoring supports discernment, not authority. Any opaque weighting is invalid.

