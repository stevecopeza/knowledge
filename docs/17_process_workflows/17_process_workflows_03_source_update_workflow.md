# Process Workflow — Source Update Detection

## Purpose
Detect upstream changes without overwriting knowledge.

## Trigger
- Manual recrawl or scheduled check

## Workflow Steps
1. Fetch current source
2. Compute new content hash
3. Compare to latest Version
4. If changed, create new Version
5. Mark previous Version Superseded
6. Notify user

## Outcome
- Multiple Versions preserved
- User decides next action

