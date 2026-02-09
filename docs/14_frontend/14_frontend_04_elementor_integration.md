# Elementor Integration & Widgets

The Knowledge system includes dedicated **Elementor Widgets** to visually build and design your knowledge base without writing shortcodes or PHP.

## 1. Overview

The following widgets are automatically available when Elementor is active:

1.  **Knowledge Archive:** A grid layout for displaying articles with filtering and pagination.
2.  **Knowledge Search:** A powerful search bar supporting both standard keyword search and AI-assisted answers (RAG/LLM).

## 2. Knowledge Archive Widget

- **Widget Name:** Knowledge Archive
- **Category:** Knowledge Base
- **Icon:** `eicon-posts-grid`

### 2.1 Widget Controls (Archive)

The widget provides three main tabs of configuration: **Content**, **Style**, and **Advanced** (standard Elementor).

#### Query (Content Tab)
Controls which articles are displayed in the grid.
- **Limit:** Number of articles to show (Default: 6).
- **Order By:** Sort articles by Date, Title, Last Modified, or Random.
- **Order:** Ascending (ASC) or Descending (DESC).

#### Layout (Content Tab)
Controls the structural arrangement and visible elements.
- **Columns:** Responsive control for grid columns (Desktop/Tablet/Mobile).
    - Desktop Default: 3
    - Tablet Default: 2
    - Mobile Default: 1
- **Title Length:** Limit the number of characters in the title (0 = Full Title).
- **Show Image:** Toggle the featured image display.
- **Show Summary:** Toggle the article summary/excerpt.
- **Show Category:** Toggle the visible category badges (Default: Yes).
    - **Category Position:** Choose between 'Inline' (below title) or 'Top Right' (floating over image).
- **Show Badges (Tags):** Toggle tag badges displayed on hover (Default: Yes).
- **Show Meta:** Toggle metadata footer (Source, Date, Options button).
- **Show Avatar:** Toggle the author's avatar in the footer (Default: No).
- **Pagination Type:** Choose how to load more content:
    - **None:** Display only the initial batch.
    - **Numeric:** Standard page numbers (1, 2, 3...).
    - **Load More Button:** A manual "Load More" button below the grid.
    - **Endless Scroll:** Automatically loads content as the user scrolls.

### 2.2 Style Controls (Archive)

#### Card
Fully customize the look of individual article cards.
- **Border:** Set border type, width, color, and radius.
- **Box Shadow:** Apply custom shadows to cards (Normal and Hover states).

#### Content
Customize the inner content area (Title and Body).
- **Title:**
    - **Typography:** Font family, size, weight, transform, style, decoration, line-height, letter spacing.
    - **Color:** Text color for the article heading.
    - **Margin:** Responsive margin settings.
- **Content Box:**
    - **Padding:** Responsive padding for the card body area.

#### Category
Customize the appearance of category badges (visible when "Show Category" is enabled).
- **Typography:** Font control for category labels.
- **Color:** Text color.
- **Background Color:** Background color of the badge.
- **Border:** Border settings (Type, Width, Color).
- **Border Radius:** Rounded corners.
- **Padding:** Internal spacing.
- **Margin:** External spacing.

#### Pagination
Customize the pagination links (if enabled).
- **Typography:** Font family, size, weight, etc.
- **Color:** Text color for standard page numbers or buttons.
- **Active/Hover Color:** Text color for the current page or button hover state.
- **Load More Button:** (When "Load More" is selected)
    - Custom colors, padding, borders, and rounded corners.
- **Loading Text:** Customize the text shown while fetching (e.g., "Loading...").
- **End Message:** Customize the text shown when no more posts are available.

## 3. Knowledge Search Widget

- **Widget Name:** Knowledge Search
- **Category:** Knowledge Base
- **Icon:** `eicon-search`

### 3.1 Widget Controls (Search)

#### Search Settings (Content Tab)
- **Placeholder:** Text shown in the input field (e.g., "Search knowledge...").
- **Button Text:** Text for the submit button (e.g., "Search" or icon only).
- **Search Mode:**
    - **Standard (WP Search):** Uses standard WordPress keyword search but renders results via AJAX directly on the page (no redirect).
    - **AI / Chat:** Asynchronous AI interface (stays on page, shows AI answer).
- **Show Other Content:** Toggle (Yes/No). When "No", hides other page content (like a default Archive) when search results are active.
    - **Content Selector to Hide:** CSS selector for the content to hide (Default targets Knowledge Archives).

#### AI Configuration (Content Tab)
*Available when Search Mode is 'AI / Chat'.*
- **AI Mode:**
    - **RAG Only:** Answers based *only* on retrieved context from the knowledge base (Strict).
    - **LLM Only:** Answers based on general model knowledge (Creative).
    - **Combined:** Merges RAG context with general knowledge.
    - **Combined (Prioritised):** Prioritizes RAG context but fills gaps with LLM.
    - **Combined (Balanced):** Equal weight to both.
- **Filter by Category:** Restrict search/context to specific Knowledge Categories. (Multi-select).

#### Layout (Content Tab)
- **Input Size:** Small, Medium, Large.
- **Button Position:** Inline (attached) or Separate.

#### Results Layout (Content Tab)
*Controls the grid display of search results (for both Standard and AI modes).*
- **Columns:** Responsive control for grid columns.
- **Title Length:** Limit title characters.
- **Show Image:** Toggle featured image.
- **Show Summary:** Toggle summary text.
- **Show Category, Badges, Meta, Avatar:** Toggle various card elements.
- **Show Re-check Button:** Toggle a "Re-check" button on the card hover state to request article update/ingestion.

### 3.2 Style Controls (Search)

#### Input Field
- **Typography:** Font settings for input text and placeholder.
- **Colors:** Text, Background, Border, Focus colors.
- **Border Radius:** Rounding of the input field.
- **Padding:** Internal spacing.

#### Button
- **Typography:** Font settings.
- **Colors:** Normal and Hover states (Text & Background).
- **Border:** Width, Type, Radius.

#### AI Response Box
*Controls the appearance of the AI answer container (only in AI Mode).*
- **Typography:** Font settings for the answer text.
- **Container:** Background color, padding, border, shadow.
- **Provenance:** Style for the "Sources" or "Context" citations.

#### Results Divider
*Controls the visual separator between AI/Search inputs and the Results Grid.*
- **Show Divider:** Toggle visibility.
- **Style:** Solid, Double, Dotted, Dashed.
- **Color, Weight, Width, Gap:** Full styling control.

#### Results Card Styling
*Includes full styling controls for the results grid cards, identical to the Archive Widget:*
- **Results Card:** Borders, Shadows.
- **Results Content:** Typography, Colors, Margins.
- **Results Summary:** Typography, Colors.
- **Results Tags:** Colors, Backgrounds, Borders.
- **Results Category:** Colors, Backgrounds, Borders.

## 4. Usage Guide

1.  **Edit a Page with Elementor.**
2.  **Search for "Knowledge"** in the widget panel.
3.  **Drag and Drop** the desired widget ("Archive" or "Search") onto your page.
4.  **Configure:** Use the Content tab to set up queries or search modes.
5.  **Style:** Use the Style tab to match your site's branding.

## 5. Technical Details

- **Archive Class:** `Knowledge\Integration\Elementor\Widgets\KnowledgeArchiveWidget`
- **Search Class:** `Knowledge\Integration\Elementor\Widgets\KnowledgeSearchWidget`
- **Base Class:** `\Elementor\Widget_Base`
- **Rendering:** Uses `Knowledge\Infrastructure\FrontendRenderer::render_card()` for consistent output with the shortcode implementation.
- **Performance:** Uses standard `WP_Query` and optimized asset loading.

## 5. Troubleshooting

- **Widget not appearing?** Ensure the Knowledge plugin is active and Elementor is installed/active.
- **Styles missing?** The widget relies on the plugin's global CSS (`knowledge-frontend.css`). Ensure your theme doesn't aggressively dequeue plugin styles.
