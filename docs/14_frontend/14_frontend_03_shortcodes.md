# Frontend — Shortcodes

This document defines the **explicitly supported frontend shortcodes** for the Knowledge system.

These shortcodes are **read-only presentation mechanisms**. They do not permit editing, ingestion, annotation, or AI interaction.

This document is normative. Any frontend feature not described here is out of scope unless explicitly approved.

---

## 0. Scope and Non-Goals

Frontend shortcodes exist to:
- Render curated views of knowledge
- Support basic discovery and navigation
- Embed knowledge into existing WordPress pages

Frontend shortcodes do **not**:
- Replace the WordPress admin UI
- Expose private or restricted knowledge
- Allow mutation of canonical data
- Bypass permissions or scopes

All shortcodes are permission-aware.

---

## 1. `[knowledge_archive]`

The primary shortcode for displaying **lists or grids of Articles**.

This shortcode operates on **Articles only** (not Versions or Forks). It always renders the *current active Version* of each Article.

### Attributes

| Attribute | Type | Default | Description |
| :--- | :--- | :--- | :--- |
| `limit` | int | `12` | Number of Articles to display. |
| `columns` | int | `3` | Number of grid columns (1–4). |
| `category` | string | `null` | Category slug to filter by. |
| `tag` | string | `null` | Tag slug to filter by. |
| `ids` | string | `null` | Comma-separated list of Article IDs. |
| `orderby` | string | `date` | Sort key: `date`, `title`, `menu_order`. |
| `order` | string | `DESC` | Sort direction: `ASC` or `DESC`. |

### Behavioural Rules

- Articles without a readable active Version are excluded
- Permission checks are enforced per Article
- Ordering never bypasses scope restrictions

### Visual Specification (Card Design)

The archive renders a grid of "Article Cards".

- **Default State**:
    - **Thumbnail** (if available) or Fallback Icon.
    - **Title**: Truncated to 2 lines.
    - **Category**: Primary category badge.
    - **Date**: Relative publication date (e.g., "2 days ago").

- **Hover State (Desktop)**:
    - Card reveals a full white overlay (fade in) with a **1-second delay** to prevent flashing during scrolling.
    - **Summary**: Displays the summary with the following priority:
        1. AI-generated summary (`_kb_ai_summary`).
        2. Manual Excerpt.
        3. Auto-generated 20-word trim from the file-based content (stripping shortcodes/tags).
        4. Fallback text: "View article details...".
    - **Tags**: Displays associated tags as a pill list at the bottom of the card.

- **Mobile State**:
    - **Visibility**: Hover content (summary) is hidden by default.
    - **Touch Targets**: Menu buttons (if present) use expanded 44px+ touch targets.
    - **Interaction**: Tapping the card triggers the hover state (revealing the white overlay and summary). Subsequent interaction navigates to the Article.

### Examples

**Standard Grid**
```
[knowledge_archive]
```

**Featured Articles**
```
[knowledge_archive ids="42,108,56" columns="3"]
```

**Category View**
```
[knowledge_archive category="engineering" limit="6" orderby="title" order="ASC"]
```

---

## 2. `[knowledge_search]`

Renders a **frontend search form** scoped strictly to the Knowledge system.

This shortcode performs **deterministic search only**. AI-assisted or semantic search is explicitly excluded from the frontend.

### Attributes

| Attribute | Type | Default | Description |
| :--- | :--- | :--- | :--- |
| `placeholder` | string | `Search knowledge…` | Placeholder text for the input field. |
| `button_text` | string | `Search` | Submit button label. |

### Behavioural Rules

- Results are permission-filtered
- Results include Articles only
- Search explainability is not shown on frontend

### Example

```
[knowledge_search placeholder="Find a guide…" button_text="Go"]
```

---

## 3. `[knowledge_category_list]`

Displays a navigational list of **Knowledge Categories**.

This shortcode is intended for **exploration**, not analytics.

### Attributes

| Attribute | Type | Default | Description |
| :--- | :--- | :--- | :--- |
| `style` | string | `list` | `list`, `pills`, or `cards`. |
| `show_count` | bool | `true` | Show Article count per category. |
| `hide_empty` | bool | `true` | Hide empty categories. |

### Behavioural Rules

- Category counts respect permissions
- Clicking a category leads to a filtered archive view

### Examples

**Simple List**
```
[knowledge_category_list]
```

**Pill Navigation**
```
[knowledge_category_list style="pills" show_count="false"]
```

---

## 4. Security & Permission Guarantees

- All shortcodes enforce WordPress capability checks
- Private or restricted knowledge is never exposed
- Shortcodes render nothing if the user lacks access

---

## 5. Explicit Non-Goals

The following are intentionally excluded:
- Frontend editing or annotations
- Frontend AI queries or summaries
- Version or Fork selection
- Public unauthenticated access overrides

---

## Closing Rule

Frontend shortcodes are **presentation-only adapters**.

If a shortcode requires business logic, mutation, or AI reasoning, it belongs in the admin interface — not here.
