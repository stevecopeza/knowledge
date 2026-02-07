# API — Authentication and Tokens

This document defines how API authentication, token issuance, and scope enforcement work. Authentication is explicit, revocable, and permission-aligned.

---

## 1. Token Principles

- Tokens represent a user or service identity
- Tokens are scoped, not all-powerful
- Tokens are revocable at any time

---

## 2. Token Types

### User Tokens
- Issued to authenticated WordPress users
- Inherit user permissions unless restricted

### Service Tokens
- Issued for integrations (e.g. mobile app, browser extension)
- Explicitly scoped

---

## 3. Scopes

Example scopes:
- ingest:url
- ingest:bulk
- read:status

Scopes restrict API actions regardless of user role.

---

## 4. Rotation and Revocation

- Tokens may be rotated without downtime
- Revocation is immediate

---

## Closing Note

Authentication is a security boundary. Convenience must never weaken scope enforcement.

