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

- [x] **Ollama Connector**
    - [x] HTTP Client for `localhost:11434` (`OllamaClient`)
    - [x] Settings Page (URL & Model Configuration, Connection Check)
- [x] **Embedding Pipeline**
    - [x] Chunking Service
    - [x] Embedding Generator Job
    - [x] Vector Storage (Flat file / In-memory scan)
- [x] **Chat UI**
    - [x] Simple Admin Chat Widget

---
**Legend:**
🟢 Ready to Start  |  🟡 Planned  |  🔴 Blocked / Future
