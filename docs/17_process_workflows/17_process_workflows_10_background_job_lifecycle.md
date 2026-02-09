# Process Workflow — Background Job Lifecycle

## 1. Purpose
To provide reliable, non-blocking asynchronous processing for resource-intensive tasks such as AI analysis, ingestion, and search indexing.

---

## 2. Architecture

The system uses a hybrid approach:
1.  **Atomic Jobs**: Uses WordPress's `wp_schedule_single_event` for individual, low-volume tasks.
2.  **Batch Jobs**: Uses `kb_import_job` CPT and `BatchImportService` for high-volume operations (e.g., Karakeep imports).
    - **Persistence**: Job state (Pending, Processing, Completed, Failed) is stored in the CPT.
    - **Payload**: Large datasets (URLs) are stored in the filesystem (`/imports/job_{id}.json`).
    - **Chunking**: A recurring cron (`knowledge_process_import_queue`) processes items in small batches (e.g., 10 at a time).

---

## 3. Workflow Steps

### 3.1 Atomic Job Creation
**Trigger**: User action (e.g., "Ingest Single URL").
**Action**: Schedules a single event with arguments.

### 3.2 Batch Job Creation
**Trigger**: Bulk Import (>50 items).
**Action**:
1. Creates `kb_import_job` post.
2. Writes URLs to JSON file.
3. Sets status to `pending`.
4. Recurring cron picks up the job.

### 3.3 Job Execution
**Atomic**:
- **Trigger**: WP-Cron.
- **Process**: Executes task, updates DB.

**Batch**:
- **Trigger**: `knowledge_process_import_queue` (every 2 mins).
- **Process**:
    1. Finds `pending` or `processing` jobs.
    2. Reads next chunk of URLs from file.
    3. Dispatches atomic ingestion events for that chunk.
    4. Updates job progress counters.

### 3.4 Completion & Failure
- **Success**: Job status updates to `publish` (Completed).
- **Failure**:
    - **Transient**: Retried by next cron run (if stuck).
    - **Fatal**: Logged to `FailureLog` or job marked as `failed`.

---

## 4. Bulk Operations Specifics

When processing large datasets:
- **Small Batches (<50)**: Staggered `wp_schedule_single_event` (1-5s spacing).
- **Large Batches (>50)**: **Batch Import Service**.
    - Prevents PHP timeout by chunking.
    - Provides persistent progress tracking (Processed X of Y).
    - Resumable after server restart.

---

## 5. Key Hooks

- `knowledge_async_ingest_file`: Handles file processing and post creation.
- `knowledge_async_analyze_article`: Handles AI categorization and tagging.
