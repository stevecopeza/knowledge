# Projects — Semantics

This document defines **Projects** as first-class research workspaces. Projects are not folders; they are contextual containers that shape ingestion, processing, and AI behavior.

---

## 1. Purpose

Projects exist to:
- Focus research on a specific intent
- Scope ingestion and processing rules
- Provide bounded AI context

---

## 2. Core Properties

A Project has:
- Intent statement (human-readable)
- Membership of Knowledge Objects
- Optional processing rules

---

## 3. Membership Rules

- Any Knowledge Object may belong to multiple Projects
- Membership is by reference, not ownership

Removing an object from a Project does not affect its lifecycle.

---

## 4. Scoped Behavior

Projects may define:
- Default Categories
- AI context boundaries
- Ingestion defaults

Global defaults apply unless overridden.

---

## 5. Non-Goals

Projects do not:
- Imply hierarchy
- Control permissions unless explicitly configured
- Replace Categories or Tags

---

## Closing Note

Projects are intentional contexts. Treating them as folders invalidates their purpose.

