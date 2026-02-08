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
5. **AI Analysis (Background)**:
    - **Trigger**: Successful Version creation (`knowledge_ai_analyze_article` event).
    - **Action**: Submit content to configured AI Provider Chain (Failover enabled).
    - **Prompt Strategy**: Requests strictly formatted JSON containing `category`, `tags` (3-5), and `summary` (max 100 words).
    - **Outputs**:
        - **Category**: Matches against existing `kb_category` terms; creates new if no match.
        - **Tags**: Sets `kb_tag` terms (append mode).
        - **Summary**: 
            - Saved as `kb_summary` post (child of Article).
            - Synced to `_kb_ai_summary` post meta for efficient frontend retrieval.
    - **Provenance**: Records Provider ID, Model Name, and Timestamp in `_kb_ai_provenance` meta.
    - **Failure**: 
        - If analysis fails or JSON is malformed, Article remains published.
        - Flagged with `needs-review` tag.
        - Error logged to system logs.
6. **Completion**: Update job status to "Success" or "Failed" (transient/db).
7. **Notification**: UI Polls and displays result.

## Failure Handling
- Network or parse failure → job Failed, visible to user
- Duplicate detected → reuse existing Article/Version

## Outcome
- Article available for annotation and organisation

