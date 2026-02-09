# Annotation — Indicators

This document defines the visual indicators for annotation density across the Knowledge system.

---

## 1. Purpose

Indicators provide immediate visual feedback on the level of user engagement and analysis associated with an article without requiring the user to open it.

## 2. Note Count Indicator

### 2.1 Visualization
- **Icon**: Sticky Note (dashicons-sticky) or similar.
- **Label**: Numeric count of active notes.
- **Visibility**:
  - **Count > 0**: Visible.
  - **Count = 0**: Hidden.
- **Style**: Subtle badge, consistent with existing card metadata (e.g., categories, date).

### 2.2 Locations
The indicator must be visible in all Article representations:
- **Archive Grid**: Knowledge Card footer/meta area.
- **Search Results**: Result card meta area.

## 3. Data Source & Performance

### 3.1 Association
- Notes are attached to a specific `Version` UUID.
- The Article contains the `Current Version` UUID.
- The Indicator reflects the count of notes attached to the **Current Version** of the Article.

### 3.2 Caching Strategy
To ensure performance in list views (avoiding N+1 queries):
- The note count is cached as integer metadata `_kb_note_count` on the **Article** post.
- **Triggers**:
  - `save_post_kb_note`: Increment/Recalculate.
  - `delete_post_kb_note`: Decrement/Recalculate.
  - `kb_version_change`: Recalculate (when an article updates to a new version, the count must reflect the new version's notes—likely 0 initially).

## 4. Interaction
- The indicator is informational.
- Clicking the card still navigates to the Article.
