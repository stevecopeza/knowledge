# Knowledge System (WordPress Plugin)

**A local-first, ownership-first knowledge repository designed for epistemic durability.**

> "Treat knowledge as durable infrastructure, not disposable notes."

## 📖 Overview

The Knowledge system is a WordPress plugin built for individuals and organizations who need to retain, analyze, and reason over information long-term. It solves the problem of **fragile knowledge**—link rot, silent content changes, and opaque AI answers—by enforcing strict ownership and provenance.

Unlike standard note-taking apps or "Second Brain" tools, this system prioritizes **trust and correctness** over speed or convenience.

## 🏗 Core Philosophy

1.  **Immutable Truth**: When you ingest a source (URL, PDF, Text), it creates a **Version** that is never overwritten.
2.  **Controlled Divergence**: If you want to edit or reinterpret content, you create a **Fork**. You never silently rewrite history.
3.  **Local-First**: Content is stored in a deterministic filesystem layout (`/kb-data/`), not just the database. It works offline.
4.  **AI as Servant**: AI (LLMs, RAG) is used to summarize and reason, but it is **never authoritative**. It reads from your knowledge base but cannot write to it without your explicit approval.

## ⚡ Key Features

*   **Robust Ingestion**: Capture full HTML/Text from URLs and Documents.
*   **Deep Versioning**: Keep the original source forever. Compare versions over time.
*   **Active Reading & Annotation**: Highlight text and add notes in the sidebar. Notes are anchored to immutable text versions.
*   **Projects & Scoped Context**: Organize knowledge into Projects. Run Search and RAG queries scoped *only* to a specific project's context.
*   **Local AI / RAG**: Run RAG (Retrieval Augmented Generation) queries against your data using local models (Ollama) or cloud providers (OpenAI).
*   **Multi-Provider AI**: Configure failover chains for AI providers (e.g., try Local Ollama first, failover to OpenAI).
*   **Elementor Integration**: Dedicated "Knowledge Archive" and "Knowledge Search" widgets with advanced controls (Infinite Scroll, Note Indicators, Results Dividers).
*   **Mobile Optimized**: Fully responsive Admin UI and Frontend for touch devices.
*   **Filesystem Backed**: All content and media are stored as plain files. The database is just an index.

## 🛠 Technical Architecture

*   **Platform**: WordPress (PHP)
*   **Storage**:
    *   **Metadata**: WordPress Database (Custom Post Types: `kb_article`, `kb_version`, `kb_fork`, `kb_project`, `kb_note`)
    *   **Content**: Local Filesystem (`/wp-content/kb-data/`)
    *   **Search**: Custom Shadow Index (`wp_kb_search_index`) + Local Vector Store
*   **AI Integration**: HTTP API to local inference server (e.g., Ollama) or OpenAI API.

## 🚀 Getting Started

### Prerequisites
*   WordPress 6.0+
*   PHP 8.0+
*   Write access to `/wp-content/` (for creating the `kb-data` directory)
*   (Optional) Ollama running locally for local AI features.
*   (Optional) OpenAI API Key for cloud-based AI features.

### Installation
1.  Clone this repository into your `wp-content/plugins/` directory.
    ```bash
    cd wp-content/plugins
    git clone https://github.com/stevecopeza/knowledge.git
    ```
2.  Activate the plugin in WordPress Admin.
3.  The system will automatically create the `/wp-content/kb-data/` directory structure.

## 📂 Documentation

Detailed architectural specifications can be found in the `docs/` directory:

*   [**Executive Summary**](docs/00_overview/00_overview_executive_summary.md)
*   [**Domain Model**](docs/02_domain_model/02_domain_model_01_entities.md)
*   [**Storage Spec**](docs/03_storage/03_storage_01_database_schema.md)
*   [**Annotations & Notes**](docs/05_annotation/05_annotation_01_highlights.md)
*   [**Projects & Scope**](docs/06_projects/06_projects_04_concept_and_scope.md)
*   [**Search & RAG**](docs/07_search_and_rag/07_search_and_rag_01_search_layers.md)
*   [**AI Integration**](docs/08_ai_integration/08_ai_integration_01_ai_contracts.md)
*   [**Elementor Integration**](docs/14_frontend/14_frontend_04_elementor_integration.md)

## 🤝 Contributing

This project is currently in **Beta / MVP Phase**.
We prioritize **correctness** over features.

## 📄 License

Proprietary / Internal Use (TBD)
