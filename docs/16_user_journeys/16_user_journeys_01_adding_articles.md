# User Journey — Adding Articles

This document describes the **end-to-end user journey** for adding articles to the Knowledge system. It focuses on intent, system behavior, and user-visible outcomes.

---

## 1. User Intent

The user wants to:
- Capture an external source (web article, document, link)
- Ensure it is stored locally and durably
- Avoid duplicates
- Be able to work with it later, offline

---

## 2. Entry Points

Users can add articles via:
- Knowledge → Ingestion → Add URL
- Bulk import (CSV or list)
- API capture (browser / mobile)

All entry points behave consistently.

---

## 3. Capture Experience

**User action:**
- Pastes a URL or submits a list

**System response:**
- Immediate acknowledgment
- Article added to ingestion queue
- User sees processing state

No blocking or synchronous processing occurs.

---

## 4. Ingestion & Normalisation

Behind the scenes:
- Content is fetched
- HTML is normalised
- Images are downloaded and optimised
- Content hash is computed

If a duplicate is detected, the user is informed and no new Article is created.

---

## 5. Version Creation

- A new Article is created (if none exists)
- A Version snapshot is created
- The Version becomes the active representation

The user never edits this Version directly.

---

## 6. Completion State

**User sees:**
- Article appears in Knowledge → Articles
- Version is viewable
- Summary may be pending (AI draft)

The article is now available for annotation, organisation, and projects.

---

## Closing Note

Adding articles is intentionally low-friction, but never opaque. The user always knows what the system has done.

