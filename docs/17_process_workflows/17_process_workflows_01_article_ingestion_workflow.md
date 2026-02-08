# Process Workflow — Article Ingestion

## Purpose
Define the exact system steps for ingesting a single article safely and deterministically.

## Trigger
- User submits a URL (UI or API)

## Workflow Steps
1. **Validation**: Validate URL and permissions.
2. **Scheduling**: Schedule async ingestion job (WP Cron / Task Queue).
3. **Feedback**: Return "Job Started" status to UI immediately.
4. **Execution (Background)**:
    a. Fetch source content.
    b. Normalise HTML and extract metadata.
    c. Download and optimise media (Chunked if necessary).
    d. Compute content hash.
    e. Deduplication check.
    f. Create Article (if new).
    g. Create immutable Version.
5. **Completion**: Update job status to "Success" or "Failed" (transient/db).
6. **Notification**: UI Polls and displays result.

## Failure Handling
- Network or parse failure → job Failed, visible to user
- Duplicate detected → reuse existing Article/Version

## Outcome
- Article available for annotation and organisation

