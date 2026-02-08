# Frontend Rendering

Describes how the system renders Knowledge Articles on the WordPress frontend.

## 1. Overview

While the Knowledge system is primarily "Admin-First", it supports viewing articles on the frontend. Since the content is stored immutably in the filesystem (`kb-data`), it must be dynamically injected into the WordPress rendering pipeline.

## 2. Rendering Pipeline

The `FrontendRenderer` class handles the injection logic via the standard WordPress `the_content` filter.

### 2.1 Logic Flow

1.  **Trigger**: User views a single `kb_article` post.
2.  **Lookup**: System queries the **latest** `kb_version` associated with the Article.
3.  **Resolution**: 
    *   Retrieves the UUID from the version's metadata.
    *   Constructs the path to `wp-content/kb-data/versions/{uuid}/content.html`.
4.  **Injection**:
    *   Reads the HTML content from the file.
    *   **Sanitization**:
        *   Strips the first `<h1>` tag from the content to prevent duplicate titles (since the theme usually renders the post title).
    *   Appends the processed HTML to the post content.

### 2.2 Error Handling

*   **No Version Found**: Appends a "No content versions found" message.
*   **File Missing**: Appends an error message if the `content.html` file is missing from the disk.

## 3. Media Serving

Images and other assets referenced in the content are served via the `FileProxyController`.

*   **URL Pattern**: `/kb-file/media/{hash}.jpg`
*   **Security**: Checks if the current user has `read` capabilities.
*   **Operations**: Requires **Flush Rewrite Rules** (via the Operations menu) if 404 errors occur.

## 4. Archive & Shortcodes

The system does not force a specific archive page. Instead, it provides flexible shortcodes to allow administrators to build custom knowledge base landing pages and archives.

See [Shortcodes Documentation](14_frontend_03_shortcodes.md) for a complete list of available shortcodes and their usage.
