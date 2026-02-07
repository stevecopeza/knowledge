# Search & RAG — Explainability

This document defines the **explainability requirements** for search results and RAG responses. Explainability is mandatory for trust, auditing, and effective human review.

---

## 1. Core Principle

Every non-trivial search result or AI-generated response must be explainable in human-readable terms.

---

## 2. Search Explainability

For a given search result, the system must be able to show:
- Why the result was included
- Which fields matched
- Any weighting factors applied

---

## 3. RAG Explainability

For a given RAG response, the system must expose:
- Which Knowledge Objects were retrieved
- Which Versions or Forks were used
- Why they were selected (scope, score, recency)

---

## 4. Excluded Context

The system must also be able to indicate:
- Which relevant objects were excluded
- The reason for exclusion (archived, out of scope, permissions)

---

## 5. Presentation

Explainability data:
- Is derived, not canonical
- Must not overwhelm by default
- Must be inspectable on demand

---

## Closing Note

Explainability is not a UI feature; it is a system guarantee.

