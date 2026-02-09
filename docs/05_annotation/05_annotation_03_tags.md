# Annotation — Tags

This document defines how **Tags** are applied to Notes for organization, discovery, and filtering.

---

## 1. Purpose

Tags allow users to categorize and retrieve Notes using semantic keywords. They bridge the gap between ad-hoc annotation and structured taxonomy.

They are:
- User-generated
- Searchable
- Reusable across Notes

---

## 2. Interaction Model

### 2.1 Creation via Text
Users can apply tags directly within the Note content during creation or editing.
- **Syntax**: Hash prefix (e.g., `#important`, `#todo`).
- **Parsing**: Tags are extracted automatically upon save.
- **Visuals**: The tag text remains in the note body but is also extracted for structured display.

### 2.2 Explicit Management
Users can manage tags via a dedicated UI control (Edit Mode).
- **Add**: Users can add tags via a `+` button.
- **Autocomplete**: Typing in the tag input searches existing tags.
- **Creation**: New tags can be created on the fly if they do not exist.

---

## 3. Display

- Tags are displayed distinctly from the note body.
- **Location**: Between the Note content and the Author metadata.
- **Style**: Visual badges or pills.

---

## 4. Search & Indexing

- Tags are first-class citizens in search.
- Searching for a tag (e.g., "important") retrieves associated Notes.
- Tags are stored as a taxonomy (`kb_tag`) associated with the `kb_note` post type.

---

## 5. Persistence

- Extracted tags are stored as WordPress terms in the `kb_tag` taxonomy.
- Association is many-to-many.
- Tags persist even if the text `#tag` is removed from the body, unless explicitly removed from the tag list. (Refinement: *Actually, if parsed from text, syncing might be tricky. The user said "any test that the user types thats preceded by a #". Ideally, we sync them. But for explicit UI management, they are stored as terms.*)

**Clarification on Sync**:
- **Text-to-Tag**: Typing `#foo` adds the tag `foo`.
- **UI-to-Tag**: Adding `foo` via UI adds the tag `foo`.
- **Removal**: Removing `#foo` from text does *not* automatically remove the tag (to prevent accidental loss), but removing the tag from UI is definitive.

---

## Closing Note

Tags provide the flexible glue for knowledge organization. They must be easy to add and powerful to search.
