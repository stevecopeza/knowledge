# Process Workflow — Bulk Ingestion

## Purpose
Handle large sets of sources with partial success and resilience.

## Trigger
- User uploads CSV or list

## Workflow Steps
1. Register bulk import
2. Create per-item ingestion jobs
3. Process items independently
4. Track per-item state
5. Aggregate progress

## Failure Handling
- Individual failures do not stop batch
- Failed items are retryable

## Outcome
- Successful items ingested
- User sees full import report

