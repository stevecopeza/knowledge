# Installation and First-Run Guide

This guide details how to install, configure, and initialize the Knowledge plugin for the first time.

## 1. Prerequisites

Before installing, ensure your environment meets the following requirements:

*   **WordPress**: Version 6.0 or higher.
*   **PHP**: Version 7.4 or higher (8.1+ recommended).
*   **Database**: MySQL 5.7+ or MariaDB 10.3+.
*   **Filesystem**: Write permissions for `wp-content/` (to create `kb-data/`).
*   **Dependencies**:
    *   [Ollama](https://ollama.ai/) (optional, for local AI features).
    *   OpenAI API Key (optional, for cloud AI features).

## 2. Installation

### Option A: Manual Installation
1.  Download the `knowledge.zip` release package.
2.  Navigate to your WordPress Admin Dashboard -> **Plugins** -> **Add New**.
3.  Click **Upload Plugin** and select the zip file.
4.  Click **Install Now** and then **Activate**.

### Option B: Developer/Source Installation
1.  Clone the repository into your plugins directory:
    ```bash
    cd wp-content/plugins
    git clone https://github.com/stevecopeza/knowledge.git
    ```
2.  Install dependencies via Composer:
    ```bash
    cd knowledge
    composer install --no-dev --optimize-autoloader
    ```
3.  Activate the plugin via WP Admin or WP-CLI:
    ```bash
    wp plugin activate knowledge
    ```

## 3. First-Run Initialization

Upon activation, the plugin automatically performs the following:

1.  **Database Setup**: Creates custom tables (`kb_search_index`, etc.) using `dbDelta`.
2.  **Directory Creation**: Creates the local filesystem storage at `wp-content/kb-data/` with the following structure:
    *   `versions/` - Stores immutable article content.
    *   `temp/` - Staging area for ingestion.
    *   `locks/` - Concurrency control.
3.  **Capability Registration**: Adds `manage_knowledge` capabilities to Administrators.

### Verification
Check **Knowledge > Status** in the admin menu. You should see:
*   **Filesystem**: Writable (Green)
*   **Database**: Connected (Green)
*   **AI Provider**: Not Configured (Yellow)

## 4. Configuration

### 4.1 AI Settings
Navigate to **Knowledge > Settings > AI**.

*   **Provider**: Select **Ollama** (Local) or **OpenAI** (Cloud).
*   **Ollama Setup**:
    *   Ensure Ollama is running (`ollama serve`).
    *   Base URL: `http://host.docker.internal:11434` (if in Docker) or `http://localhost:11434`.
    *   Click **Test Connection**.
*   **OpenAI Setup**:
    *   Enter your API Key.
    *   Select Model (e.g., `gpt-4-turbo`).

### 4.2 Elementor Integration
The plugin registers custom widgets for Elementor. No additional setup is required.
*   **Knowledge Archive**: Displays a grid of articles. Supports a "Re-check" button for source validation.
*   **Knowledge Search**: Provides a search bar with AI integration.

## 5. Ingesting Your First Article

1.  Navigate to **Knowledge > Add New**.
2.  **Source URL**: Enter a URL to ingest (e.g., a Wikipedia page or documentation).
3.  **Ingest**: Click the "Ingest" button.
4.  **Process**:
    *   The system fetches the HTML.
    *   Cleans and normalizes the content.
    *   Generates a content hash.
    *   Creates an `Article` (Identity) and `Version` (Content).
    *   (Optional) AI generates a summary and tags.
5.  **View**: Click "View Article" to see the result.

## 6. Troubleshooting

*   **"Directory not writable"**: Ensure your web server (e.g., `www-data`) has write access to `wp-content/`.
*   **"Ollama Connection Failed"**: Check if Ollama is allowed to accept external connections (`OLLAMA_HOST=0.0.0.0`).
*   **Search not working**: 
    *   Ensure the `kb_search_index` table exists.
    *   Navigate to **Knowledge > Settings > AI** and click **Rebuild Knowledge Index** to regenerate embeddings.
*   **Re-check Button Missing**: Ensure the "Show Re-check Button" toggle is enabled in the Elementor Widget settings.
