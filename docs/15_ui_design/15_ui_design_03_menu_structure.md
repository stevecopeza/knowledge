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

### 2.5 Ingestion

**Menu:** Knowledge → Ingestion

**Purpose:** Manual content ingestion tools.

**Contents:**
- Single URL Ingestion Form
- Bulk Import (CSV/Text)
- Async Job Status

---

### 2.6 Search

**Menu:** Knowledge → Search

**Purpose:** Internal search and discovery interface.

**Contents:**
- Search Input (Keyword / Semantic)
- Filters (Date, Source, Project)
- Results View

---

### 2.7 Ask AI

**Menu:** Knowledge → Ask AI

**Purpose:** Chat interface for RAG-based Q&A.

**Contents:**
- Chat Window
- History View
- Context Inspector

---

### 2.8 AI Settings

**Menu:** Knowledge → AI Settings

**Purpose:** Configuration for AI integrations (RAG).

**Contents:**
- Connection Settings (Ollama URL)
- Model Selection (Dropdown with status check)
- Embedding Configuration

---

### 2.9 Operations

**Menu:** Knowledge → Operations

**Purpose:** System maintenance and background tasks.

**Contents:**
- Flush Rewrite Rules (Fix 404s)
- Background Job Monitor
- Cache Management
- Re-indexing Tools
- Export / Backup

---

## 3. Taxonomy Submenus

These appear under the "Articles" menu in standard WordPress fashion, but are part of the information architecture.

### 3.1 Categories

**Menu:** Knowledge → Categories

**Purpose:** Primary semantic organisation.

### 3.2 Tags

**Menu:** Knowledge → Tags

**Purpose:** Secondary, ad-hoc organisation.
