# Storage — Versioning and Forks

This document defines how **source updates, versions, and forks** are handled over time. The goal is to preserve research integrity while allowing evolution.

---

## 1. Versioning Principles

- Versions are immutable snapshots
- Versions represent the state of a Source at a point in time
- Versions are ordered but not overwritten

---

## 2. Source Update Detection

Source updates may be detected via:
- Manual recrawl
- Scheduled checks
- API-triggered refresh

Detection mechanisms are pluggable, but outcomes are deterministic.

---

## 3. Creating a New Version

A new Version is created when:
- Source content hash changes
- Significant structural changes are detected

Rules:
- The existing Version is marked **Superseded**
- The new Version becomes **Latest Source Version**
- No existing annotations are moved automatically

---

## 4. Fork Creation

Forks are created explicitly by users.

Rules:
- Forks inherit content from a specific Version
- Forks record parent Version UUID
- Forks may diverge arbitrarily
- **Pinning**: Forks are strictly pinned to their parent Version. They do NOT automatically update when a new Version of the Article appears.
- **Rebase**: "Rebasing" a Fork onto a new Version is a manual, user-initiated copy-paste operation in MVP.

---

## 5. Fork vs New Version

- **Version**: reflects external source change
- **Fork**: reflects internal user change

These concepts must never be conflated.

---

## 6. Notes and Highlights Across Versions

- Notes and Highlights remain bound to their original Version or Fork
- Optional tooling may assist in copying annotations forward
- Automatic migration is prohibited

---

## 7. Display and Audit

The system must expose:
- Version timelines
- Fork lineage
- Supersession events

---

## Closing Note

Versioning rules are essential for trust. Any implementation that overwrites or auto-merges content violates this model.

