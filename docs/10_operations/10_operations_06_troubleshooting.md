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
