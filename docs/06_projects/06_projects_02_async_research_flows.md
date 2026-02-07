# Projects — Async Research Flows

This document defines how Projects support **asynchronous research operations**, including ingestion, analysis, and AI-assisted tasks.

---

## 1. Research as Workflows

Within a Project, research is expressed as explicit workflows, not ad-hoc actions.

Examples:
- Ingest a set of sources
- Generate summaries
- Identify contradictions

---

## 2. Task Model

Research tasks:
- Are queued asynchronously
- Are scoped to a Project
- Have explicit state

States include:
- Queued
- Running
- Completed
- Failed

---

## 3. Progress Tracking

The system must expose:
- Per-task progress
- Aggregate Project progress

Progress is visible and persistent.

---

## 4. Failure and Retry

- Task failures do not affect canonical data
- Retries are explicit and scoped
- Partial completion is acceptable

---

## 5. Notifications

Projects may emit notifications for:
- Task completion
- Failures
- Milestones

Notification scope is configurable.

---

## Closing Note

Async research flows transform Projects from containers into active workspaces.

