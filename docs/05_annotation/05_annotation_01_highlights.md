# Annotation — Highlights

This document defines how **Highlights** are created, anchored, stored, and preserved over time. Highlights are the primary mechanism for fine-grained knowledge interaction.

---

## 1. Purpose

Highlights allow users to bind meaning to specific excerpts of text within a Version or Fork.

They are:
- Precise
- Stable
- Referential

---

## 2. Anchoring Model

Each Highlight is anchored using a **dual-anchor strategy** (Target):

- **MVP (v1.0)**: Quoted text (`TextQuoteSelector` with `exact` match).
- **Planned**: Positional offsets (start/end) and Context (prefix/suffix).

Currently, `exact` text matching is authoritative.

---

## 3. Robustness Rules

- Rendering changes must not invalidate Highlights
- Minor whitespace or markup changes are tolerated (Future)
- If offsets drift, quoted text is authoritative

---

## 4. Failure Handling

If a Highlight cannot be reliably resolved (e.g. text changed):
- It is marked **Degraded** (not rendered)
- The original quoted text is preserved in the Note
- User is notified (Future)

No automatic reattachment occurs.

---

## 5. Scope and Attachment

- Highlights attach to exactly one Version or Fork
- Highlights never span multiple objects

---

## Closing Note

Highlights are foundational. Any implementation that risks silent misalignment violates this model.

