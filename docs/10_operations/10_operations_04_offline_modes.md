# Operations — Offline Modes

This document defines how the system behaves under **offline or degraded connectivity conditions**. Offline capability is a core invariant, not a best-effort feature.

---

## 1. Connectivity States

The system recognizes three connectivity states:
- **Online** — full connectivity
- **Degraded** — intermittent or partial connectivity
- **Offline** — no external connectivity

State is detected heuristically and surfaced to the user.

---

## 2. Guaranteed Capabilities (Offline)

When offline, the system must support:
- Reading all stored Articles, Versions, and Forks
- Viewing images and media
- Searching (deterministic search)
- Creating Notes, Highlights, and Discussions
- Running local AI queries (if models are present)

---

## 3. Deferred Capabilities

When offline, the following are deferred:
- New external ingestion
- Source update checks
- External notifications

Deferred actions are queued automatically.

---

## 4. Transition Handling

- Transitioning between states must not corrupt data
- Deferred jobs resume when connectivity returns

---

## Closing Note

Offline behavior must be predictable and conservative. Data integrity always outweighs immediacy.

