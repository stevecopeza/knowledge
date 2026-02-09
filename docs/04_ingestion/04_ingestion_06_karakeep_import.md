# Ingestion — Karakeep Import Specification

This document defines the specification for importing content from **Karakeep** JSON exports.

---

## 1. Overview

Karakeep import allows users to bulk-ingest bookmarks exported from the Karakeep application. The system processes the uploaded JSON file, extracts target URLs, and queues them for the standard asynchronous ingestion pipeline.

---

## 2. File Format Specification

The system accepts a `.json` file with the following structure:

```json
{
  "bookmarks": [
    {
      "createdAt": 1770617036,
      "title": "Article Title",
      "tags": ["tag1", "tag2"],
      "content": {
        "type": "link",
        "url": "https://example.com/target-article"
      },
      "note": null,
      "archived": false
    }
  ]
}
```

### Critical Fields
| Field Path | Requirement | Description |
| :--- | :--- | :--- |
| `bookmarks` | Required | Root array containing bookmark objects. |
| `content.url` | Required | The target URL to ingest. Must be a valid HTTP/HTTPS URL. |

### Ignored Fields (Phase 1)
The following fields are present in the export but **ignored** during Phase 1:
- `title` (The system will extract the canonical title from the target page)
- `tags` (The system will generate AI tags based on content)
- `note`
- `createdAt`

---

## 3. User Interface

**Location:** Knowledge → Ingestion

A new section **"Karakeep Import"** will be added to the Ingestion dashboard.

### UI Components
1.  **File Upload**: A standard file input accepting `.json` files.
2.  **Action Button**: "Upload & Process".
3.  **Feedback Area**:
    - Displays success message: "Found {X} valid URLs. Ingestion queued."
    - Displays error message: "Invalid JSON format" or "No bookmarks found".

---

## 4. Processing Logic

### 4.1 Parsing & Validation
1.  **File Validation**: Verify MIME type is `application/json` or `text/plain` and extension is `.json`.
2.  **JSON Decoding**: Parse the file content. Fail if JSON is malformed.
3.  **Structure Check**: Verify existence of `bookmarks` array.

### 4.2 Extraction
Iterate through `bookmarks` array:
1.  Check for existence of `content.url`.
2.  Validate URL format (using `filter_var`).
3.  Discard entries with missing or invalid URLs.

### 4.3 Execution Strategy: Batch Processing
To handle large volumes (e.g., 10,000+ bookmarks) without timing out or overloading the server, the system uses a **Batch Job Entity**.

1.  **Job Creation**:
    - The uploaded JSON is saved as a hidden Custom Post Type (`kb_import_job`).
    - The list of extracted URLs is stored in a JSON file attached to the job (to avoid DB size limits).
    - Status set to `pending`.

2.  **Background Processing**:
    - A recurring cron task (`knowledge_process_import_queue`) runs every **2 minutes**.
    - It picks up the oldest `pending` or `processing` job.
    - It processes a "chunk" of URLs (e.g., 10 URLs per run).
    - **Progress Update**: Updates the job's `processed_count` meta field.
    - **Completion**: When all URLs are processed, status is set to `completed`.

3.  **Concurrency**:
    - Only **one** import job runs at a time to ensure stability.

---

## 5. Error Handling & Reporting

The system tracks the status of *every* URL within a batch job.

### 5.1 Failure Tracking
- **Log Storage**: A dedicated error log is maintained for each job (stored as a JSON file or serialised meta).
- **Error Data**:
    ```json
    {
      "url": "https://broken-link.com",
      "error": "404 Not Found",
      "timestamp": 1716200000
    }
    ```

### 5.2 UI Reporting
- **Job History**: The Ingestion Dashboard lists past import jobs.
- **Status Columns**: Total | Processed | Failed | Status.
- **Error View**:
    - Clicking a job reveals the "Error Report".
    - **Download Failures**: A button to download a JSON/CSV of just the failed URLs for easy re-import.

---

## 6. Security & Limits

- **Max File Size**: Defined by PHP `upload_max_filesize` (typically 2MB-64MB).
- **Sanitization**: All extracted URLs are sanitized via `esc_url_raw` before processing.
- **Batch Limit**: Default chunk size is 10 URLs per 2-minute cycle (300/hour). Configurable via filter.
