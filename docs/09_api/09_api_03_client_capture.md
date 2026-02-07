# API — Client Capture

This document defines expectations for **client-side capture** (browser extensions, mobile apps, shortcuts). Clients are thin and defer logic to the server.

---

## 1. Principles

- Clients submit intent, not processed content
- Server performs ingestion and refinement
- Clients are resilient to offline conditions

---

## 2. Browser Capture

Browser capture submits:
- Current URL
- Optional user metadata

Rules:
- Capture is fire-and-forget
- User receives async status

---

## 3. Mobile Capture

Mobile capture supports:
- Share-sheet ingestion
- Background submission when connectivity resumes

---

## 4. Idempotency

- Clients include idempotency keys
- Duplicate submissions are safely ignored

---

## Closing Note

Client capture is intentionally simple to ensure reliability.

