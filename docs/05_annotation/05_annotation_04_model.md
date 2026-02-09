# Notes & Annotations — Domain Model

## Types
- Highlights (anchored text)
- Notes (free-form interpretation)
- Comments (discussion on Articles or Notes)

---

## Attachment Targets
Notes and Comments may attach to:
- Articles
- Versions
- Forks
- Other Notes

---

## Core Rule
Annotations never modify source content.

## 4. Implementation Details (v1.0)
- **CPT**: `kb_note` (supports title, editor, private).
- **Meta**: `_kb_note_source` stores the target Article Version UUID for efficient querying.
- **Storage**: JSON file in `/kb-data/annotations/{UUID}.json` stores the selector (TextQuoteSelector) and target details.
- **Frontend**: `KnowledgeAnnotator` (JS) handles selection, rendering, and REST API communication.

## Closing Note
Annotation data must remain portable. The separation of `kb_note` (content) and JSON (target) allows for flexible re-anchoring strategies.
