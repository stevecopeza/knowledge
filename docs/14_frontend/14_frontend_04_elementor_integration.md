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
- **Show Re-check Button:** Toggle the "Re-check" button.
    - **Note:** This button appears on the card's hover state (or on tap for mobile) and allows users to flag an article for source verification.
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

#### AI Configuration (Content Tab - AI Mode Only)
- **AI Mode:**
    - **RAG Only (Strict):** Answers only from knowledge base context.
    - **LLM Only (Creative):** General AI chat without context.
    - **Combined:** Uses RAG context but allows general knowledge fallback.
- **Filter by Category:** Restrict the AI's search scope to specific knowledge categories.

#### Results Layout (Content Tab)
Controls how search results are displayed (Standard Mode).
- **Columns:** Responsive grid columns (same as Archive).
- **Show Image/Summary/Category/Tags/Meta:** Toggle visibility of card elements.
- **Show Re-check Button:** Enable the source verification request button on results.

### 3.2 Style Controls (Search)

#### Input Field & Button
- **Typography & Colors:** Full control over the search input text, placeholder, and background.
- **Border & Radius:** Style the input box and search button (including hover states).
- **Padding:** Adjust internal spacing for comfortable typing.

#### Results Divider
Style the separator between the search bar and the results grid.
- **Show Divider:** Toggle visibility.
- **Style:** Solid, Dotted, Dashed, etc.
- **Color & Weight:** Custom appearance.
- **Gap:** Spacing above and below the divider.

#### Result Cards
*The Search widget shares the same comprehensive card styling options as the Archive widget:*
- **Card:** Borders, Shadows, Radius.
- **Content:** Title typography, Body padding.
- **Badges:** Category and Tag styling.
- **Summary:** Text color and typography.

#### AI Response Box (AI Mode Only)
Style the container where the AI answer appears.
- **Typography:** Font settings for the answer text.
- **Background & Border:** Container appearance.
- **Box Shadow:** Depth effects.
- **Padding:** Internal spacing.
