# Troubleshooting Guide

Common issues and their resolutions.

## 1. Images Not Loading (404 Errors)

**Symptom**: Images in articles appear broken, or direct links to `/kb-file/...` return a 404 error.

**Cause**: WordPress rewrite rules are out of sync. This often happens after plugin activation or updates that change routing logic.

**Resolution**:
1.  Go to **Knowledge > Operations**.
2.  Click **Flush Rewrite Rules**.
3.  Refresh the page.

## 2. Missing Article Content

**Symptom**: Article page loads but the body is empty or shows "No content versions found".

**Cause**:
*   The article has no versions (ingestion failed).
*   The `content.html` file is missing from `wp-content/kb-data/versions/{uuid}/`.
*   The `FrontendRenderer` logic is failing to find the latest version.

**Resolution**:
*   Check **Knowledge > Dashboard** for ingestion errors.
*   Verify file existence on disk.
*   Check the **Versions** tab in the Article editor to ensure a version exists.

## 3. Duplicate Titles

**Symptom**: The article title appears twice on the frontend.

**Cause**: The ingested HTML includes an `<h1>` tag, and the theme also renders the post title.

**Resolution**: The `FrontendRenderer` automatically strips the first `<h1>` tag. If duplicates persist, ensure the content uses standard HTML structure.

## 4. AI Connection Issues

**Symptom**: AI Providers show "Disconnected" (Red) status, or analysis jobs fail.

**Cause**:
*   **Timeouts**: Local models (like Llama/Mistral) can be slow to respond, triggering the default 180s timeout.
*   **Network**: The web server cannot reach the AI host (e.g., Docker container IP vs. localhost).
*   **Configuration**: Incorrect model name or API key.

**Resolution**:
1.  **Check IP**: Ensure the URL is reachable from the WordPress server. For local setups, use the LAN IP (e.g., `192.168.x.x`) instead of `localhost`.
2.  **Verify Model**: Use the "Check" feature in AI Settings. If the dropdown is empty, try manually typing the model name (e.g., `mistral:latest`).
3.  **Logs**: Check `wp-content/debug.log` for specific cURL errors (e.g., `Operation timed out`).
4.  **Failover**: If the primary provider fails, the system attempts the next enabled provider in the list. Ensure backups are configured if reliability is critical.
