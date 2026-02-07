# Domain Model — Relationships

This document defines the **allowed relationships** between domain entities. Any relationship not explicitly defined here is invalid.

Relationships are directional and intentional. They encode meaning, not convenience.

---

## 1. Article ↔ Source

- An **Article** is derived from one or more **Sources**.
- A **Source** may contribute to only one logical Article identity.

**Rules**
- A Source cannot belong to multiple Articles.
- An Article must have at least one Source.

---

## 2. Article ↔ Version

- An **Article** has one or more **Versions**.
- A **Version** belongs to exactly one Article.

**Rules**
- Versions are ordered chronologically.
- Only one Version may be marked as the "latest source version" at any time.

---

## 3. Version ↔ Fork

- A **Fork** is derived from exactly one **Version**.
- A **Version** may have zero or more Forks.

**Rules**
- Forks retain immutable references to their parent Version.
- Forks may diverge permanently.

---

## 4. Version / Fork ↔ Summary

- A **Summary** belongs to exactly one Version or Fork.

**Rules**
- Multiple Summaries may exist per Version/Fork.
- At most one Summary may be designated as the default.

---

## 5. Version / Fork ↔ Highlight

- A **Highlight** belongs to exactly one Version or Fork.

**Rules**
- Highlights may not span multiple Versions or Forks.
- Highlights must remain valid even if rendering changes.

---

## 6. Highlight / Version / Fork ↔ Note

- A **Note** may attach to:
  - A Highlight, or
  - A Version, or
  - A Fork

**Rules**
- Notes attach to exactly one parent.
- Notes do not move automatically across Versions.

---

## 7. Knowledge Object ↔ Discussion

- A **Discussion** may reference one or more Knowledge Objects.

**Rules**
- Discussions are contextual, not hierarchical.
- Discussions do not own content.

---

## 8. Knowledge Object ↔ Score

- A **Score** applies to exactly one Knowledge Object.

**Rules**
- Scores are contextual (e.g. Category-specific).
- Multiple Scores may exist per object, from different reviewers.

---

## 9. Knowledge Object ↔ Annotation

- An **Annotation** applies to exactly one Knowledge Object.

**Rules**
- Annotations are descriptive only.
- Annotations do not alter behavior directly.

---

## 10. Project ↔ Knowledge Object

- A **Project** may include any number of Knowledge Objects.
- A Knowledge Object may belong to multiple Projects.

**Rules**
- Projects reference objects; they do not own them.
- Removal from a Project does not affect the object lifecycle.

---

## 11. Article / Project ↔ Category

- Articles and Projects may be assigned one or more Categories.

**Rules**
- Categories define semantic context.
- Categories influence scoring and retrieval logic.

---

## 12. Knowledge Object ↔ Tag

- Any Knowledge Object may have zero or more Tags.

**Rules**
- Tags have no semantic enforcement.

---

## Closing Note

These relationships are intentionally restrictive. They ensure:
- Clear lineage
- Stable references
- Predictable AI grounding

Any implementation convenience that violates these relationships is invalid.

