# Implementation Plan

This document tracks the implementation progress of the Knowledge Plugin.

## 🟢 Phase 0: Foundation & Infrastructure
**Goal:** A working plugin that installs correctly, creates the DB schema, and secures the filesystem.

- [x] **Scaffolding**
    - [x] `knowledge.php` main file
    - [x] `composer.json` (Autoloading, PHP 8.0+)
    - [x] Service Container / Dependency Injection setup
- [x] **Database Layer**
    - [x] CPT Registration (`kb_article`, `kb_version`, `kb_fork`, `kb_project`)
    - [x] Taxonomy Registration (`kb_category`, `kb_tag`)
    - [x] Custom Table Migration (`wp_kb_search_index`)
- [x] **Filesystem Layer**
    - [x] Directory creation (`/wp-content/kb-data/{articles,versions,media,ai}`)
    - [x] Security: Generate `.htaccess` (Deny All)
    - [x] Implement `FileProxyController` for authorized access

## 🟢 Phase 1: The Core Loop (Ingest & Store)
**Goal:** I can paste a URL and see it saved as a Version on the disk.

- [x] **Domain Models**
    - [x] `Article` Entity
    - [x] `Version` Entity (Immutable)
    - [x] `Source` Value Object
- [x] **Ingestion Service**
    - [x] `HtmlFetcher` (HTTP Client)
    - [x] `ContentNormalizer` (Clean HTML, Extract Metadata, Min Viability Checks)
    - [x] `StorageEngine` (Write to disk, update DB)
    - [x] Deduplication Logic (Reuse Article, Hash Check)
    - [x] `AssetDownloader` (Fetch images, save to `/media`, Data URI skipping)
    - [x] Async Ingestion Architecture (Background Job + UI Polling)
- [x] **Staging Area**
    - [x] Implement atomic move from `/temp` to `/versions`

## ✅ Phase 2: Consumption & Management
**Goal:** I can view my Knowledge Base in WP Admin and manage it.

- [x] **Admin UI**
    - [x] Custom Columns for CPTs
    - [x] Read-Only Viewer for Versions (Markdown/HTML renderer)
    - [x] Ingestion Status UI (Async Progress Feedback)
    - [x] Bulk Ingestion UI (Multi-line URL input)
- [x] **Forking Logic**
    - [x] "Fork Version" Action
    - [x] Editor for Forks (Uses standard WP Editor, syncs to disk)

## ✅ Phase 3: Search & Discovery
**Goal:** I can find things I saved.

- [x] **Indexer**
    - [x] `SearchIndexService` (Syncs file content -> `wp_kb_search_index`)
    - [x] Hook into `save_post` / Ingestion pipeline (Versions & Forks)
- [x] **Search Handler**
    - [x] Intercept WP Search (`pre_get_posts`)
    - [x] `MATCH AGAINST` query implementation

## ✅ Phase 3.5: Testing & Reliability
**Goal:** Ensure the system is robust and verifiable.

- [x] **E2E Testing Infrastructure**
    - [x] Puppeteer Setup (Headless Chrome)
    - [x] `package.json` & NPM scripts
    - [x] Login & Admin Menu Visibility Test (`tests/e2e/login.test.js`)
- [x] **Environment Validation**
    - [x] WP-CLI & Database Checks
    - [x] User-level access verification

## 🟢 Phase 4: AI Integration (RAG)
**Goal:** I can ask questions to my Knowledge Base.

- [x] **AI Provider Service (Multi-Provider)**
    - [x] Abstract `ProviderInterface` (Support for Ollama, OpenAI, etc.)
    - [x] `ProviderManager` with Failover Logic
    - [x] Admin Settings UI (Drag-and-drop ordering, Connection Checks)
    - [x] HTTP Client for `localhost:11434` (`OllamaClient`)
    - [x] OpenAI Client
- [x] **Embedding Pipeline**
    - [x] Chunking Service
    - [x] Embedding Generator Job
    - [x] Vector Storage (Flat file / In-memory scan)
- [x] **Chat UI**
    - [x] Admin Chat Widget (Responsive, Touch-Optimized)
    - [x] Mode Selection (RAG Only, LLM Only, Combined)

- [x] **Content Analysis Service**
    - [x] `AIAnalysisService` (Categorization, Tagging, Summarization)
    - [x] Background Job Integration (`knowledge_ai_analyze_article`)
    - [x] Provenance Tracking (`_kb_ai_provenance`)

## ✅ Phase 5: Mobile & Touch Optimization
**Goal:** The system is usable on mobile devices and touch screens.

- [x] **Frontend Cards**
    - [x] Touch-friendly hover states (Gradient overlays)
    - [x] Larger touch targets (Menu buttons)
    - [x] Responsive Grid Layout
- [x] **Admin UI**
    - [x] Responsive Chat Interface (Stacking controls)
    - [x] Touch-optimized Inputs (44px min-height, 16px font-size)
    - [x] Responsive Provider Settings (Drag-and-drop lists)
    - [x] Mobile-friendly Ingestion Forms

## ✅ Phase 6: Elementor Integration
**Goal:** Enable visual building of Knowledge Archives with full design control.

- [x] **Widget Specification**
    - [x] Define Widget Controls & Settings (Docs)
    - [x] Create `ElementorIntegration` Service
- [x] **Widget Implementation**
    - [x] Register Widget with Elementor Manager
    - [x] Implement Query Controls (Source, Taxonomies, Exclusions)
    - [x] Implement Layout Controls (Grid/List, Responsive Columns)
    - [x] Implement Style Controls (Typography, Colors, Borders, Shadows)
    - [x] Implement Pagination Logic
- [x] **Verification**
    - [x] Test with Elementor Editor (Live Preview)
    - [x] Test Frontend Output
    - [x] Verify Mobile Responsiveness

## 🟢 Phase 7: Scaling & Reliability (Current)
**Goal:** Ensure the system handles large datasets and remains stable under load.

- [x] **Karakeep Import (Large Volume)**
    - [x] Batch Job Architecture (Chunking 10k+ items)
    - [x] Dedicated Error Logging & Reporting
    - [x] Retry Mechanism for Failed Batches
- [x] **AI Integration Refinement**
    - [x] Fix Model Dropdown Population (AJAX/UI)
    - [x] Enhance Connection Status Feedback (Visual Indicators)
    - [x] Bulk Categorization Backfill Logic
- [ ] **Performance Tuning**
    - [ ] Optimize Search Index for >10k items
    - [ ] Transient Caching for External API Calls

## 🟢 Phase 8: UX & Robustness (Recent)
**Goal:** Polish the user experience and improve handling of edge cases.

- [x] **Ingestion Improvements**
    - [x] **Featured Image Scoring**: Prioritize large content images over avatars (scored by size, aspect ratio, position).
    - [x] **Robust Fetching**: Enhanced timeout handling (120s) and Browser Header spoofing for slow/protected sites (e.g., flip.it).
- [x] **Admin UX**
    - [x] **Bulk Failure Management**: Added checkboxes and Bulk Actions (Resubmit/Delete) to Failed Ingestions table.
    - [x] **Article Tooltips**: Added hover "Info" tooltip to Article list showing AI Summary and Tags.
- [x] **Frontend Polish**
    - [x] **Pagination**: Added Numeric, Load More, and Endless Scroll options to Elementor Widget.
    - [x] **Card Interactions**: Implemented full-card hover effects with white overlay, summary, and tags.
    - [x] **Mobile Optimization**: refined touch interactions for cards (tap to reveal summary) and improved responsive layout.
    - [x] **Hover Delay**: Added 1s delay to desktop hover to prevent jarring scrolling.

## 🟢 Phase 9: Documentation & Release Prep (Next)
**Goal:** Finalize documentation and prepare for v1.0 release.

- [ ] **Documentation**
    - [ ] Update Mobile/Touch design docs.
    - [ ] Document Elementor Widget usage.
    - [ ] Finalize Installation Guide.
- [ ] **Release**
    - [ ] Bump version numbers.
    - [ ] Create release package.

---
**Legend:**
🟢 Ready to Start  |  🟡 Planned  |  🔴 Blocked / Future
