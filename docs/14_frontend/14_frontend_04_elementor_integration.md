# Elementor Integration

The Knowledge system includes a dedicated **Elementor Widget** called **"Knowledge Archive"**. This widget allows you to visually build and design knowledge base archives without writing shortcodes or PHP.

## 1. Overview

- **Widget Name:** Knowledge Archive
- **Category:** Knowledge Base
- **Icon:** `eicon-posts-grid`
- **Availability:** Automatically available when Elementor is active.

## 2. Widget Controls

The widget provides three main tabs of configuration: **Content**, **Style**, and **Advanced** (standard Elementor).

### 2.1 Content Tab

#### Query
Controls which articles are displayed in the grid.
- **Limit:** Number of articles to show (Default: 6).
- **Order By:** Sort articles by Date, Title, Last Modified, or Random.
- **Order:** Ascending (ASC) or Descending (DESC).

#### Layout
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

### 2.2 Style Tab

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

## 3. Usage Guide

1.  **Edit a Page with Elementor.**
2.  **Search for "Knowledge Archive"** in the widget panel.
3.  **Drag and Drop** the widget onto your page.
4.  **Configure the Query:**
    - Set the `Limit` to your desired number of posts per page.
    - Enable `Pagination` if you have many articles.
5.  **Customize Layout:**
    - Adjust `Columns` for different devices (e.g., 1 column on Mobile, 3 on Desktop).
    - Toggle visibility of Categories, Tags, Images, etc.
6.  **Style the Elements:**
    - Go to the **Style** tab.
    - Use the **Content** section to adjust Title fonts and spacing.
    - Use the **Category** section to create pill-style or tag-style badges.
    - Use the **Card** section to add borders or shadows.

## 4. Technical Details

- **Class:** `Knowledge\Integration\Elementor\Widgets\KnowledgeArchiveWidget`
- **Base Class:** `\Elementor\Widget_Base`
- **Rendering:** Uses `Knowledge\Infrastructure\FrontendRenderer::render_card()` for consistent output with the shortcode implementation.
- **Performance:** Uses standard `WP_Query` and optimized asset loading.

## 5. Troubleshooting

- **Widget not appearing?** Ensure the Knowledge plugin is active and Elementor is installed/active.
- **Styles missing?** The widget relies on the plugin's global CSS (`knowledge-frontend.css`). Ensure your theme doesn't aggressively dequeue plugin styles.
