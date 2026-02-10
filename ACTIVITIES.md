# Project Activities & Todo

## 🔄 Recent Activities (Completed)
- **Annotation Engine (MVP)**
    - [x] **Data Model**: Registered `kb_note` CPT and implemented JSON-based target storage (Source UUID + Selector).
    - [x] **Frontend Selection**: Implemented Text Selection API with popover controls.
    - [x] **Sidebar View**: Created a slide-in sidebar to list all notes for the current article version.
    - [x] **Highlight Persistence**: Implemented `restoreHighlights` to re-apply text highlights on page load using exact text matching.
    - [x] **Interaction**: Linked highlights to sidebar notes (click to focus).
    - [x] **Bug Fix**: Resolved issue where Note Popover disappeared on click (event conflict).
    - [x] **Note Modes**: Replaced "Add Selection" button with persistent dropdown (Just Highlight, Excerpt, Copy).
    - [x] **UX Persistence**: Implemented local storage for user's preferred Note Mode.
    - [x] **Visuals**: Added blockquote styling for quoted text in Sidebar.
    - [x] **Refinement**: Modified Note Mode to apply excerpt/copy actions only on submission (keeping textarea clean).
    - [x] **Navigation**: Implemented bidirectional navigation (Article Highlight ↔ Sidebar Note).
    - [x] **Management**: Added Edit and Delete capabilities for notes.
    - [x] **Tagging**: 
        - [x] Implemented live hashtag extraction from note content.
        - [x] Added responsive "search-as-you-type" for tag input.
        - [x] Implemented source-aware tag removal (handling manual vs. text-embedded tags).
    - [x] **Indicators**:
        - [x] Implemented Note Count Indicator (badge) on Article Cards in Archive/Search views.
        - [x] Optimized performance with cached metadata (`_kb_note_count`) to avoid N+1 queries.
        - [x] **Elementor Controls**: Added full styling controls for Note Count Indicators (Typography, Color, Background, Border, Padding, Margin) to both Archive and Search widgets.
        - [x] **Search Widget Fix**: Resolved issue where Search Widget ignored Note Count position settings by passing options correctly to AJAX handler.
    - [x] **Formatting**: Improved excerpt rendering with contiguous blue line and robust whitespace handling.
    - [x] **Metadata**: Added Author Name, Avatar, and Time to Note display.
    - [x] **Security**: Restricted Note creation/viewing and Article Re-check features to logged-in users.
- **Data Integrity & Deduplication (Phase 7.5)**
    - [x] **Concurrency Locking**: Implemented transient-based atomic locking (60s) to prevent race conditions during parallel ingestion of the same URL.
    - [x] **Global Hash Check**: Implemented global content hash verification (Layer 2) to detect and link "Near Duplicates" (same content, different URL) to existing articles.
    - [x] **Canonical Resolution**: Implemented HTTP HEAD/GET based canonical URL resolution (Layer 1) to normalize redirects (e.g., `flip.it`) before ingestion.
    - [x] **Data Cleanup**: Created and ran `merge_duplicates.php` to consolidate 7 sets of duplicate articles/versions caused by race conditions.
    - [x] **Documentation**: Updated deduplication strategy docs and plan.
- **Documentation & Release Prep**
    - [x] **Installation Guide**: Finalized comprehensive guide including prerequisites, manual/dev installation, first-run initialization, and troubleshooting.
    - [x] **Mobile/Touch Design**: Updated documentation to reflect "Touch-First" philosophy, interaction delays, and specific mobile behaviors (tap-to-reveal).
- **Performance Optimization**
    - [x] **Search Index**: Optimized for >10k items by increasing search limit to 1000 and verifying database index usage.
- **Re-check Capability**
    - [x] Added "Re-check" button to Knowledge Search Widget (hover state) with full styling controls.
    - [x] Added "Re-check" button to Knowledge Archive Widget (hover state) with full styling controls.
    - [x] Implemented priority queue handling in backend (re-checks move to top of queue).
    - [x] Ensured Re-check button persists across AJAX pagination (Load More / Infinite Scroll).
- **Search Widget UX Enhancements**
    - [x] Enabled Archive-style display controls (grid, image, summary, etc.) for Standard Search mode.
    - [x] Implemented "Results Divider" with customizable style, color, and spacing.
    - [x] Positioned Results Divider *below* the search results area.
    - [x] Added "Show Other Content" toggle to automatically hide/show page content (e.g., existing archives) when searching.
    - [x] Added AJAX support for Standard Search to render results dynamically without page reload.

## 📝 Todo List

### 🚀 Performance & Scaling
- [ ] **Transient Caching**: Implement caching for external API calls (AI providers, extraction services).

### 📚 Documentation
- [x] **Elementor Widgets**: Documented all settings for Archive and Search widgets (including new Search modes, Re-check button, and Results Divider).

### 📦 Release Preparation
- [ ] **Version Bump**: Update plugin version in main file and `package.json`.
- [ ] **Release Package**: Create a build script or manual process for generating the distribution zip.

### 🧪 Testing & QA
- [ ] **Regression Testing**: Verify Standard Search and AI Chat modes on mobile devices.
- [ ] **Cross-Browser Testing**: Check "Show Other Content" toggle behavior on Safari/Firefox.
