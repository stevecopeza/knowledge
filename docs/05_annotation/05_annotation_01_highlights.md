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

Each Highlight is anchored using a **dual-anchor strategy**:

- Quoted text (exact excerpt)
- Positional offsets (start/end)

Both anchors are stored.

---

## 3. Robustness Rules

- Rendering changes must not invalidate Highlights
- Minor whitespace or markup changes are tolerated
- If offsets drift, quoted text is authoritative

---

## 4. Failure Handling

If a Highlight cannot be reliably resolved:
- It is marked **Degraded**
- The original quoted text is preserved
- User is notified

No automatic reattachment occurs.

---

## 5. Scope and Attachment

- Highlights attach to exactly one Version or Fork
- Highlights never span multiple objects

---

## Closing Note

Highlights are foundational. Any implementation that risks silent misalignment violates this model.

