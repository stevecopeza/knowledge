# AI Integration — Configuration & Setup

This document details the configuration and setup required to enable AI features (RAG) within the Knowledge system.

---

## 1. Prerequisites

To use AI features, you must have:
1.  **Ollama** installed and running locally (or accessible via network).
2.  At least one LLM model pulled (e.g., `llama3`, `mistral`).
3.  The Knowledge plugin installed.

---

## 2. Configuration Interface

**Menu:** Knowledge → AI Settings

The settings page allows you to configure the connection to your Ollama instance.

### 2.1 Settings Fields

| Field | Default | Description |
| :--- | :--- | :--- |
| **Ollama URL** | `http://localhost:11434` | The HTTP endpoint for the Ollama API. Ensure CORS headers allow access if running on a different host. |
| **Model Name** | `llama3` | The specific model to use for generation and embeddings. |

### 2.2 Connection Status & Model Discovery

- **Status Check:** The page automatically tests the connection to the provided URL upon load.
    - ✅ **Connected**: The system can reach Ollama.
    - ❌ **Not Connected**: The system cannot reach Ollama. Check if the service is running.

- **Model Dropdown:**
    - If connected, the **Model Name** field becomes a dropdown list populated with models found on the Ollama instance (via `/api/tags`).
    - If disconnected, it reverts to a standard text input field to allow manual correction.

---

## 3. Storage Options

Configuration is stored in the WordPress `options` table:

- `knowledge_ollama_url`: The base URL.
- `knowledge_ollama_model`: The selected model name.

---

## 4. Troubleshooting

**Common Issues:**

- **"Not Connected"**:
    - Is Ollama running? (`ollama serve`)
    - Is the URL correct? (Default is port 11434)
    - Is WordPress running in Docker/Container? If so, `localhost` might refer to the container, not the host. Use `host.docker.internal` or the host IP.

- **Model List Empty**:
    - Have you pulled any models? Run `ollama pull llama3`.
