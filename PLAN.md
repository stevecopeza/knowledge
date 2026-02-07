# Implementation Plan

This document tracks the implementation progress of the Knowledge Plugin.

## 🟢 Phase 0: Foundation & Infrastructure
**Goal:** A working plugin that installs correctly, creates the DB schema, and secures the filesystem.

- [ ] **Scaffolding**
    - [ ] `knowledge.php` main file
    - [ ] `composer.json` (Autoloading, PHP 8.0+)
    - [ ] Service Container / Dependency Injection setup
- [ ] **Database Layer**
    - [ ] CPT Registration (`kb_article`, `kb_version`, `kb_fork`, `kb_project`)
    - [ ] Taxonomy Registration (`kb_category`, `kb_tag`)
    - [ ] Custom Table Migration (`wp_kb_search_index`)
- [ ] **Filesystem Layer**
    - [ ] Directory creation (`/wp-content/kb-data/{articles,versions,media,ai}`)
    - [ ] Security: Generate `.htaccess` (Deny All)
    - [ ] Implement `FileProxyController` for authorized access

## 🟡 Phase 1: The Core Loop (Ingest & Store)
**Goal:** I can paste a URL and see it saved as a Version on the disk.

- [ ] **Domain Models**
    - [ ] `Article` Entity
    - [ ] `Version` Entity (Immutable)
    - [ ] `Source` Value Object
- [ ] **Ingestion Service**
    - [ ] `HtmlFetcher` (HTTP Client)
    - [ ] `ContentNormalizer` (Clean HTML)
    - [ ] `StorageEngine` (Write to disk, update DB)
    - [ ] `AssetDownloader` (Fetch images, save to `/media`)
- [ ] **Staging Area**
    - [ ] Implement atomic move from `/temp` to `/versions`

## 🔴 Phase 2: Consumption & Management
**Goal:** I can view my Knowledge Base in WP Admin and manage it.

- [ ] **Admin UI**
    - [ ] Custom Columns for CPTs
    - [ ] "Ingest New" Meta Box / Dashboard Widget
    - [ ] Read-Only Viewer for Versions (Markdown/HTML renderer)
- [ ] **Forking Logic**
    - [ ] "Fork Version" Action
    - [ ] Editor for Forks

## 🔴 Phase 3: Search & Discovery
**Goal:** I can find things I saved.

- [ ] **Indexer**
    - [ ] `SearchIndexService` (Syncs file content -> `wp_kb_search_index`)
    - [ ] Hook into `save_post` / Ingestion pipeline
- [ ] **Search Handler**
    - [ ] Intercept WP Search
    - [ ] `MATCH AGAINST` query implementation

## 🔴 Phase 4: AI Integration (RAG)
**Goal:** I can ask questions to my Knowledge Base.

- [ ] **Ollama Connector**
    - [ ] HTTP Client for `localhost:11434`
- [ ] **Embedding Pipeline**
    - [ ] Chunking Service
    - [ ] Embedding Generator Job
    - [ ] Vector Storage (Flat file / In-memory scan)
- [ ] **Chat UI**
    - [ ] Simple Admin Chat Widget

---
**Legend:**
🟢 Ready to Start  |  🟡 Planned  |  🔴 Blocked / Future
