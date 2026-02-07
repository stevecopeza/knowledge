# Ingestion — Update Detection

This document defines how the system detects and handles updates to previously ingested Sources. Update detection is conservative, explicit, and user-controlled.

---

## 1. Update Detection Triggers

Updates may be detected via:
- Manual recrawl (user-initiated)
- Scheduled checks (configurable)
- API-triggered refresh

No automatic background polling occurs without configuration.

---

## 2. Change Detection Criteria

A Source is considered changed when:
- Content hash differs from the latest Version
- Structural changes exceed configured thresholds

Minor cosmetic changes may be ignored based on configuration.

---

## 3. New Version Creation

When a change is detected:
- A new Version is created
- The previous Version is marked **Superseded**
- No existing annotations are altered

---

## 4. User Notification

The system must notify users when:
- A new Version is available
- A Source has materially changed

Notifications include:
- Summary of changes
- Links to version comparison

---

## 5. Promotion Rules

Users may:
- Accept the new Version
- Ignore it
- Fork it

No automatic promotion occurs.

---

## Closing Note

Update detection preserves trust by avoiding silent changes and forcing explicit user decisions.

