# Knowledge → Dashboard (UI Design)

This document defines a **clean, modern, minimalistic design** for the **Knowledge / Dashboard** screen in the WordPress admin.

The goal is **situational awareness**, not control sprawl.

---

## Design Intent

- One-screen overview
- Zero clutter
- No dense tables by default
- Everything clickable, nothing noisy

The Dashboard answers **four questions only**:
1. Is the system healthy?
2. What is happening right now?
3. What changed recently?
4. What needs my attention?

---

## Layout Overview (12-column grid)

```
┌──────────────────────────────────────────────────────┐
│ Knowledge › Dashboard                                │
│──────────────────────────────────────────────────────│
│ [ Status ]            [ Activity ]                   │
│                                                      │
│ [ Recent Knowledge (wide) ]                          │
│                                                      │
│ [ Alerts & Required Actions ]                        │
└──────────────────────────────────────────────────────┘
```

No tabs by default. Tabs hide information; dashboards surface it.

---

## Section 1 — System Status (Top Left)

**Visual style:**
- Card
- Soft border
- No heavy colours

**Content:**
```
System Status
─────────────
● Ingestion Queue:   Running (3 jobs)
● AI Services:       Ready
● Storage:           Healthy
● Offline Mode:      No
```

**Rules:**
- Green/amber/red dots only
- No charts
- Clicking any line drills into Operations

---

## Section 2 — Activity (Top Right)

**Purpose:** What is happening *now*

```
Activity
────────
• Bulk import (12/50 processed)
• Embeddings generating (Project: Energy)
• Source update detected (1)
```

**Rules:**
- Max 5 items
- Real-time but calm
- No timestamps unless hovered

---

## Section 3 — Recent Knowledge (Primary Focus)

**Full-width card**

```
Recent Knowledge
────────────────
• Climate Policy 2024
  - New version detected

• AI Regulation Whitepaper
  - Fork created

• Energy Research Project
  - Summary accepted
```

**Rules:**
- Human-readable events only
- Each item links to its most relevant screen
- No raw IDs

---

## Section 4 — Alerts & Required Actions

**Only visible if non-empty**

```
Requires Attention
──────────────────
! Source update requires review
! Failed embedding job (retry)
```

**Rules:**
- Alerts are rare
- Each alert must be actionable
- No dismiss-without-action

---

## Visual Language

### Typography
- Use WordPress admin default font
- Section headers: medium weight
- Body text: normal

### Colour
- White background
- Light grey card borders
- Status dots only for colour

### Spacing
- Generous padding
- Clear separation between cards

---

## Explicit Exclusions

The dashboard must NOT include:
- Charts
- Long tables
- Settings
- Configuration toggles
- AI prompts

Those belong elsewhere.

---

## Interaction Philosophy

- Dashboard is **read-first**
- Actions are secondary and contextual
- Clicking always leads to depth, not popups

---

## Summary

This dashboard is:
- Calm
- Informative
- Boring in the right way

If it ever feels "busy", it has failed.

