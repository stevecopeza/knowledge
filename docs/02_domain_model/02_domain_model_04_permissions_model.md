# Domain Model — Permissions Model

This document defines the **permissions and visibility rules** governing Knowledge Objects. Permissions are explicit, hierarchical, and conservative by default.

Permissions exist to support:
- Individual use
- Optional team collaboration
- Organisational governance

---

## 1. General Principles

- Deny by default
- Visibility is explicit
- Permissions apply to **actions**, not UI elements
- WordPress roles and capabilities are the baseline

---

## 2. Permission Scopes

Permissions may be applied at the following scopes:

- **Global** — applies across the repository
- **Project** — applies only within a Project
- **Object** — applies to a specific Knowledge Object

Lower scopes override higher ones only when explicitly allowed.

---

## 3. Core Roles (Logical)

These are logical roles; they may map to WordPress roles/capabilities.

- **Owner** — full control
- **Editor** — create and modify content
- **Reviewer** — score, comment, review summaries
- **Reader** — view-only access

---

## 4. Article Permissions

- View Article metadata — Reader+
- Create Fork — Editor+
- Archive Article — Owner

Articles themselves are not editable.

---

## 5. Version Permissions

- View Version — Reader+
- Promote Version — Editor+
- Archive Version — Owner

Versions are immutable.

---

## 6. Fork Permissions

- Create Fork — Editor+
- Edit Fork content — Editor+
- Mark Fork Stable — Editor+
- Archive Fork — Owner

---

## 7. Summary Permissions

- View Summary — Reader+
- Edit Summary — Editor+
- Accept Summary — Reviewer+
- Deprecate Summary — Editor+

---

## 8. Highlight & Note Permissions

- Create Highlight — Editor+
- Create Note — Editor+
- View Private Notes — Author, Owner
- View Shared Notes — Permission-scoped

Notes are private by default.

---

## 9. Discussion Permissions

- Create Discussion — Editor+
- Contribute — Permission-scoped
- Close Discussion — Reviewer+

---

## 10. Score Permissions

- Assign Score — Reviewer+
- View Scores — Reader+

Scores are contextual and non-authoritative.

---

## 11. Project Permissions

- Create Project — Editor+
- Modify Project Scope — Editor+
- View Project — Permission-scoped
- Archive Project — Owner

Projects may impose stricter permissions than global defaults.

---

## 12. AI Interaction Permissions

- Run AI Queries — Permission-scoped
- Trigger Re-embedding — Editor+
- Accept AI Output — Human only

AI systems never bypass permissions.

---

## Closing Note

Permissions are part of the data model. UI convenience must never weaken or bypass these rules.

