# AI Integration — Ollama Directory Specification

This document defines the **filesystem contract** exposed to Ollama or other local RAG consumers. The contract is deterministic, inspectable, and rebuildable.

---

## 1. Purpose

The Ollama directory provides a structured, AI-consumable view of the knowledge repository without exposing WordPress internals.

**Transport Protocol:**
- The system communicates with Ollama via **HTTP API** (default `http://localhost:11434`).
- The system does **not** manage the Ollama process lifecycle (start/stop). It assumes the service is running.

---

## 2. Root Layout

```
/ai/rag/
├── manifests/
├── documents/
├── chunks/
└── media/
```

---

## 3. Manifests

Manifests define what is included in a RAG context.

```
/manifests/{scope_id}.json
```

Each manifest includes:
- Scope definition (Project, Category, explicit objects)
- Included object UUIDs
- Exclusion rules
- Generation timestamp

---

## 4. Documents

```
/documents/{object_uuid}.md
```

Rules:
- One document per Version or Fork
- Includes metadata header (YAML or JSON)
- References local media paths

---

## 5. Chunks

```
/chunks/{object_uuid}/{chunk_id}.txt
```

Rules:
- Deterministic chunking
- Stable chunk identifiers

---

## 6. Media Exposure

```
/media/{hash}/optimised
```

Rules:
- Only optimised variants exposed
- Media metadata available to AI

---

## 7. Rebuild Rules

- Entire `/ai/rag` directory may be rebuilt at any time
- Canonical data must never depend on this directory

---

## Closing Note

This directory is the sole AI-facing contract. Changing it requires versioning and migration support.

