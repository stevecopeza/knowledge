# Knowledge Menu Structure (WordPress Admin)

This document defines the **WordPress admin menu structure** for the Knowledge system, including top-level menus, sub-menus, and any **tabs within pages**. The structure prioritises clarity, progressive disclosure, and alignment with the domain model.

This is a **navigation contract**. UI layout may evolve, but menu semantics must remain stable.

---

## 1. Top-Level Menu

### **Knowledge** (top-level menu)

Purpose: Primary entry point for all knowledge-related operations.

---

## 2. First-Level Submenus

### 2.1 Dashboard

**Menu:** Knowledge → Dashboard

**Purpose:** System-wide overview and operational visibility.

**Contents:**
- Recent ingestions
- Processing status (jobs, queues)
- Recently updated Articles / Versions
- Alerts (failed jobs, update notifications)

**Tabs:**
- Overview (default)
- Activity Log
- System Health

---

### 2.2 Articles

**Menu:** Knowledge → Articles

**Purpose:** Manage logical knowledge items.

**Contents:**
- List of Articles
- Category and Tag filters
- Status (active / archived)

**Tabs (within an Article view):**
- Versions (default)
- Forks
- Summaries
- Discussions
- Metadata

---

### 2.3 Versions

**Menu:** Knowledge → Versions

**Purpose:** Inspect immutable source snapshots.

**Contents:**
- Version list
- Lifecycle state
- Supersession indicators

**Tabs (within a Version view):**
- Content (rendered)
- Highlights
- Notes
- Summaries
- Version History

---

### 2.4 Projects

**Menu:** Knowledge → Projects

**Purpose:** Research workspaces.

**Contents:**
- Project list
- Status (active / paused / completed)

**Tabs (within a Project view):**
- Overview (intent, scope)
- Knowledge (linked objects)
- Tasks (async research jobs)
- Progress
- Notifications
- Settings

---

### 2.5 Categories

**Menu:** Knowledge → Categories

**Purpose:** Primary semantic organisation.

**Contents:**
- Category list
- Category descriptions
- Scoring context definitions

**Tabs:**
- Categories (default)
- Scoring Rules

---

### 2.6 Tags

**Menu:** Knowledge → Tags

**Purpose:** Secondary, ad-hoc organisation.

**Contents:**
- Tag list

**Tabs:** None

---

### 2.7 Ingestion

**Menu:** Knowledge → Ingestion

**Purpose:** Entry point for adding content.

**Tabs:**
- Add URL
- Bulk Import
- Import History
- Update Checks

---

### 2.8 Search

**Menu:** Knowledge → Search

**Purpose:** Unified search across knowledge.

**Tabs:**
- Deterministic Search (default)
- Semantic Search (if enabled)
- Saved Searches

---

### 2.9 AI & RAG

**Menu:** Knowledge → AI & RAG

**Purpose:** AI configuration and inspection.

**Tabs:**
- AI Status
- RAG Scopes
- Embeddings
- Explainability Logs

---

### 2.10 Operations

**Menu:** Knowledge → Operations

**Purpose:** Administrative and maintenance tasks.

**Tabs:**
- Background Jobs
- Backups
- Migrations
- Offline Status

---

### 2.11 Settings

**Menu:** Knowledge → Settings

**Purpose:** Global configuration.

**Tabs:**
- General
- Permissions
- API Tokens
- Notifications
- Advanced

---

## 3. Design Rules (Non-Negotiable)

- No menu item combines **content** and **configuration**
- Tabs are used only when views exceed one mental model
- Default tabs always show read-only or safe actions
- Destructive actions are never on default tabs

---

## Closing Note

This menu structure mirrors the domain model. If a menu item cannot be justified in domain terms, it does not belong in the UI.

