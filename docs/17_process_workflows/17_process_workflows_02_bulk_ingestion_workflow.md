# Process Workflow — Bulk Ingestion

## Purpose
Handle large sets of sources with partial success and resilience.

## Trigger
- User uploads CSV or list

## Workflow Steps
1. Register bulk import
2. Check volume:
    - **<50 items**: Schedule individual async jobs immediately (staggered).
    - **>50 items**: Create `kb_import_job` (Batch Mode).
3. Capture current user context for attribution
4. Process items:
    - **Direct**: Cron picks up single events.
    - **Batch**: `knowledge_process_import_queue` processes chunks of 10.
5. Track per-item state
6. Aggregate progress (via `kb_import_job` meta if Batch Mode)

## Attribution
- The user triggering the import is assigned as the `post_author` for all created articles.

## Failure Handling
- Individual failures do not stop batch
- Failed items are retryable

## Outcome
- Successful items ingested
- User sees full import report

