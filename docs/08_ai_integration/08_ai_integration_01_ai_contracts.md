# AI Integration — AI Contracts

This document defines the **contractual boundaries** between the knowledge system and AI components. AI is treated as a replaceable consumer of knowledge, never as an authority.

---

## 1. Read Contract

AI systems may read:
- Version and Fork content
- Accepted Summaries
- Notes and Highlights (permission-scoped)
- Metadata required for context

AI systems may not read:
- Private objects outside scope
- Archived content (by default)

---

## 2. Write Contract

AI systems may write only:
- Draft Summaries
- Annotations (non-authoritative)
- Logs of their own activity

AI systems may not:
- Modify canonical content
- Change lifecycle state
- Alter permissions

---

## 3. Acceptance Rules

- All AI outputs require explicit human acceptance to become active
- AI outputs are never auto-promoted

---

## 4. Replaceability

AI components must be:
- Hot-swappable
- Stateless with respect to canonical data

All AI artifacts must be rebuildable.

---

## Closing Note

These contracts ensure AI augments knowledge without undermining trust.

