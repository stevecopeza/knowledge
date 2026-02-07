# AI Integration — Image AI Handling

This document defines how **images and visual media** are made available to AI systems. Image handling is conservative and metadata-driven.

---

## 1. Principles

- Images support context; they do not replace text
- AI access to images is explicit and permission-scoped
- Image-derived data is non-authoritative

---

## 2. Image Exposure to AI

AI systems may access:
- Optimised image variants
- Image metadata (dimensions, captions, source)

AI systems may not:
- Modify images
- Generate new canonical image content

---

## 3. Derived Visual Data

Optional derived data includes:
- OCR text
- Captions
- Detected entities

Rules:
- Derived data is stored as annotations
- Human review is required for acceptance

---

## 4. Offline Operation

- All image AI processing uses local models
- No external vision APIs are required

---

## Closing Note

Images enrich understanding but remain secondary to textual knowledge.

