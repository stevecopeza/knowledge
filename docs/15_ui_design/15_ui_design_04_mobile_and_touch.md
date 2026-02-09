# UI Design — Mobile & Touch Optimization

This document outlines the design standards and technical implementations for ensuring the Knowledge system is fully usable on mobile devices and touch screens.

---

## 1. Philosophy

The Knowledge system adheres to a **"Touch-First, Desktop-Enhanced"** philosophy for interaction, while maintaining a **"Desktop-First"** density for information.

1.  **Touch Targets**: All interactive elements must meet WCAG 2.1 AA standards (min 44x44px target size).
2.  **No Hover Dependency**: Critical functionality must never rely solely on `:hover` states, as these are inaccessible on touch devices.
3.  **Legibility**: Text size and contrast must adjust for handheld viewing conditions (variable lighting, smaller screens).
4.  **Input Zoom Prevention**: Form inputs must prevent OS-level auto-zoom behaviors that disrupt context.

---

## 2. Frontend Implementation

### 2.1 Knowledge Cards
The grid of knowledge articles uses a responsive layout that adapts to touch contexts.

- **Hover/Touch States**:
    - **Desktop**: 
        - Metadata and summary appear on hover (`opacity: 0` -> `1`).
        - A **1-second delay** is applied to the transition to prevent jarring "flashing" effects while scrolling quickly.
    - **Touch**: 
        - Metadata is hidden by default (showing the full image).
        - Tapping the card triggers the hover state, revealing the white overlay, summary, tags, and action buttons.
    - **Implementation**:
      ```css
      /* Desktop: 1s Delay */
      @media (hover: hover) {
          .knowledge-card:hover .knowledge-card-hover-content {
              transition-delay: 1s;
          }
      }

      /* Mobile: Standard Touch Behavior (No forced opacity) */
      @media (hover: none) {
          .knowledge-card-hover-content {
              background: #ffffff;
              padding: 16px;
              justify-content: center;
              /* opacity: 0 by default, becomes 1 on :active/:hover (tap) */
          }
      }
      ```

- **Interactive Elements**:
    - **Hover Content**: Acts as the primary link to the article.
    - **Re-check Button**: A secondary action button (`z-index: 25`) positioned above the hover content (`z-index: 20`). On touch devices, the first tap reveals the hover state (including this button), and a direct tap on the button triggers the re-check action instead of navigation.
    - **Menu Buttons**: The "More Options" (three dots) button uses negative margins to increase the hit area.

### 2.2 Grid Layout
- **Desktop**: 3 columns (standard).
- **Tablet**: 2 columns (`max-width: 900px`).
- **Mobile**: 1 column (`max-width: 600px`).

---

## 3. Admin Interface Implementation

The WordPress Admin interface is optimized via `knowledge-admin.css`.

### 3.1 Form Inputs
Mobile browsers (especially iOS Safari) auto-zoom if input text size is less than 16px.

- **Rule**: All inputs (`text`, `url`, `select`, etc.) must have `font-size: 16px` on screens narrower than 782px.
- **Sizing**: Inputs have a minimum height of 44px to facilitate easy tapping.

### 3.2 Chat Interface ("Ask AI")
- **Layout**: Controls stack vertically on mobile to maximize input width.
- **Send Button**: Full width on mobile for easy thumb reach.
- **Message Bubbles**: Margins are reduced (5%) to maximize content width on small screens.

### 3.3 Provider Settings (Drag-and-Drop)
- **Handles**: Drag handles are sized appropriately for touch.
- **Status Indicators**: Visual bands (Green/Red) provide immediate feedback without needing tooltips.

---

## 4. Testing & Verification

### 4.1 Device Coverage
Testing is performed against:
- **Desktop**: Chrome / Firefox / Safari
- **Mobile**: iOS Safari (iPhone), Android Chrome

### 4.2 Key Scenarios
1.  **Ingestion**: Can I paste a URL and click "Ingest" on a phone?
2.  **Chat**: Can I type a query, send it, and read the response without zooming?
3.  **Navigation**: Can I tap the "Edit" or "Fork" buttons on a card without accidentally opening the article?
4.  **Re-check**: Can I tap a card to reveal the "Re-check" button and tap it successfully?
