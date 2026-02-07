# Knowledge Admin Mockups (WordPress Context)

This document provides **clearly labeled, WordPress-admin–style mockups** for each **Knowledge** submenu item. These are **structural wireframes**, not visual designs. They are intended to guide developers and product discussions.

Conventions:
- `[ ]` = input / filter
- `( )` = action button
- `▶` = expandable
- `—` = separator

---

## 1. Knowledge → Dashboard

```
┌ Knowledge › Dashboard ─────────────────────────────┐
│ Overview | Activity Log | System Health             │
├────────────────────────────────────────────────────┤
│ ▣ Ingestion Status                                 │
│   - 3 jobs running                                 │
│   - 1 failed                                       │
│                                                     │
│ ▣ Recent Articles                                  │
│   - Article A (updated)                             │
│   - Article B (new)                                 │
│                                                     │
│ ▣ Alerts                                           │
│   ! Source update requires review                  │
└────────────────────────────────────────────────────┘
```

---

## 2. Knowledge → Articles

```
┌ Knowledge › Articles ──────────────────────────────┐
│ [Search…] [Category ▼] [Tag ▼] [Status ▼]           │
├────────────────────────────────────────────────────┤
│ ☐ Article Title        Category     Status          │
│ ☐ Climate Policy 2024 Politics     Active          │
│ ☐ AI Whitepaper       Technology   Archived        │
└────────────────────────────────────────────────────┘
```

### Single Article (Tabs)
```
Tabs: Versions | Forks | Summaries | Discussions | Metadata
```

---

## 3. Knowledge → Versions

```
┌ Knowledge › Versions ──────────────────────────────┐
│ [Search…] [Article ▼] [State ▼]                     │
├────────────────────────────────────────────────────┤
│ Version ID   Article            State               │
│ v3           Climate Policy     Available           │
│ v2           Climate Policy     Superseded          │
└────────────────────────────────────────────────────┘
```

### Single Version (Tabs)
```
Tabs: Content | Highlights | Notes | Summaries | Version History
```

---

## 4. Knowledge → Projects

```
┌ Knowledge › Projects ──────────────────────────────┐
│ (New Project) [Status ▼]                            │
├────────────────────────────────────────────────────┤
│ Project Name        Status      Progress            │
│ Energy Research     Active      ████░░░░            │
│ Election Study      Completed   ████████            │
└────────────────────────────────────────────────────┘
```

### Single Project (Tabs)
```
Tabs: Overview | Knowledge | Tasks | Progress | Notifications | Settings
```

---

## 5. Knowledge → Categories

```
┌ Knowledge › Categories ────────────────────────────┐
│ Categories | Scoring Rules                          │
├────────────────────────────────────────────────────┤
│ Category Name        Description                   │
│ Politics             Political analysis            │
│ Science               Peer-reviewed research        │
└────────────────────────────────────────────────────┘
```

---

## 6. Knowledge → Tags

```
┌ Knowledge › Tags ──────────────────────────────────┐
│ [Search…]                                          │
├────────────────────────────────────────────────────┤
│ Tag Name           Usage Count                     │
│ bias               12                              │
│ regulation         7                               │
└────────────────────────────────────────────────────┘
```

---

## 7. Knowledge → Ingestion

```
┌ Knowledge › Ingestion ─────────────────────────────┐
│ Add URL | Bulk Import | Import History | Updates    │
├────────────────────────────────────────────────────┤
│ Add URL:                                            │
│ [ URL __________________________ ] (Ingest)        │
│                                                     │
│ Bulk Import:                                       │
│ (Upload CSV)                                       │
└────────────────────────────────────────────────────┘
```

---

## 8. Knowledge → Search

```
┌ Knowledge › Search ────────────────────────────────┐
│ Deterministic | Semantic | Saved Searches           │
├────────────────────────────────────────────────────┤
│ [Search query __________________ ] (Search)        │
│                                                     │
│ Results:                                           │
│ - Article A (matched title, high score)            │
│ - Version B (matched note)                         │
└────────────────────────────────────────────────────┘
```

---

## 9. Knowledge → AI & RAG

```
┌ Knowledge › AI & RAG ──────────────────────────────┐
│ AI Status | RAG Scopes | Embeddings | Explainability│
├────────────────────────────────────────────────────┤
│ ▣ AI Status: Ready                                 │
│ ▣ Models Loaded: llama, embedding-model            │
│                                                     │
│ ▣ Last RAG Query                                   │
│   Sources used: 4                                  │
└────────────────────────────────────────────────────┘
```

---

## 10. Knowledge → Operations

```
┌ Knowledge › Operations ────────────────────────────┐
│ Background Jobs | Backups | Migrations | Offline    │
├────────────────────────────────────────────────────┤
│ Job Type        State      Last Run                │
│ Ingestion       Running    2 min ago               │
│ Embedding       Failed     Retry available         │
└────────────────────────────────────────────────────┘
```

---

## 11. Knowledge → Settings

```
┌ Knowledge › Settings ──────────────────────────────┐
│ General | Permissions | API Tokens | Notifications  │
│ Advanced                                            │
├────────────────────────────────────────────────────┤
│ [✓] Enable Semantic Search                          │
│ [✓] Offline AI Enabled                              │
│                                                     │
│ (Save Changes)                                     │
└────────────────────────────────────────────────────┘
```

---

## Closing Note

These mockups intentionally mirror **native WordPress admin mental models** (lists, tabs, bulk actions). They are suitable for:
- Developer handoff
- UX wireframing
- MVP scope enforcement

They avoid visual styling to keep focus on structure and responsibility.

