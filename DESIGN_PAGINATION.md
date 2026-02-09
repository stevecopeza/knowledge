# Knowledge Plugin Pagination Design

## Overview
This document outlines the design and implementation plan for adding robust pagination options to the Knowledge Archive Elementor widget. The goal is to provide a flexible, user-friendly, and designer-centric pagination system that supports standard numeric navigation, manual "Load More" buttons, and modern "Endless Scroll" experiences.

## User & Designer Requirements

### User Experience (UX)
- **Seamless Discovery**: Users browsing knowledge bases often want a continuous flow of information (Endless Scroll) rather than clicking through pages.
- **Mobile Friendliness**: "Load More" buttons are often preferred on mobile to prevent "footer jumping" (where the footer is unreachable because content keeps loading).
- **Navigation Clarity**: For large libraries, Numeric pagination helps users return to specific locations and understand the total scope.
- **Feedback**: Users must see a clear loading indicator when fetching content and a clear "No more posts" message when the end is reached.

### Designer Control (UI)
- **Style Freedom**: Designers need full control over pagination appearance to match the site's brand.
- **States**: Different styles for Normal, Hover, and Active states.
- **Typography & Colors**: Control over font size, weight, text color, background color, borders, and spacing.
- **Layout**: Alignment options (Left, Center, Right).
- **Loading Indicators**: Options to style the loading spinner or text.

## Proposed Functionality

### 1. Pagination Types (Content Tab)
The existing "Show Pagination" switcher will be replaced or expanded into a "Pagination Type" select control:
- **None**: Display only the initial batch of posts.
- **Numeric**: Standard WordPress pagination links (1, 2, 3, Next >). Refreshes the page or uses AJAX (optional).
- **Load More Button**: A button below the grid that appends the next batch of posts when clicked.
- **Endless Scroll**: Automatically loads the next batch when the user scrolls near the bottom of the grid.

### 2. Styling Options (Style Tab)
A new **Pagination** section will be added to the Style tab with the following controls:

#### General
- **Alignment**: Left, Center, Right.
- **Spacing**: Top margin (distance from grid).

#### Numeric / Buttons
- **Typography**: Font family, size, weight, etc.
- **Colors**: Text Color and Background Color for Normal, Hover, and Active states.
- **Borders**: Border Type, Width, Color, Radius.
- **Padding**: Internal padding for buttons/numbers.

#### Loading & Messages
- **Loader Color**: Color of the loading spinner.
- **End of Content Text**: Custom text (e.g., "You've reached the end") and its styling.

## Technical Implementation

### Frontend Architecture
- **JavaScript Controller**: A generic `KnowledgePagination` class in `frontend.js`.
    - **State Management**: Tracks current page, total pages, and loading state.
    - **Events**:
        - `click` for Load More / Numeric.
        - `IntersectionObserver` for Endless Scroll (efficient performance).
    - **AJAX**: Fetches new posts from the server.
    - **DOM Manipulation**: Appends new cards to the existing Grid container.

### Backend Architecture
- **AJAX Endpoint**: `wp_ajax_knowledge_load_more` and `wp_ajax_nopriv_knowledge_load_more`.
    - **Security**: Nonce verification.
    - **Parameters**: `page`, `posts_per_page`, `query_args` (category, tag, order, etc.).
    - **Response**: JSON containing:
        - `html`: Rendered HTML of the new cards (reusing `FrontendRenderer::render_card`).
        - `max_num_pages`: Total pages for calculating "end of content".
        - `found_posts`: Total count.

### Accessibility (a11y)
- **Load More**: Focus management to ensure focus moves to the first new item.
- **Announcements**: `aria-live` regions to announce "Loading..." and "X items loaded".
- **Keyboard Navigation**: Ensure all pagination links are tab-accessible.

## Implementation Steps
1.  **Backend**: Create the AJAX handler in `FrontendRenderer` or a new `AjaxHandler` class.
2.  **Frontend Script**: Create `assets/js/knowledge-frontend.js` with the logic for Load More and Infinite Scroll.
3.  **Elementor Widget (Content)**: Update `register_layout_controls` to include the "Pagination Type" selector.
4.  **Elementor Widget (Style)**: Add `register_pagination_style_controls` with typography, color, and layout options.
5.  **Rendering**: Update `render_archive_shortcode` to output the necessary `data-attributes` (total pages, current page, query signature) for the JS to read.
