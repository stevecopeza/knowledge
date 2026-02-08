# AI Integration — Configuration & Setup

This document details the configuration and setup required to enable AI features (RAG, Summarisation, Categorisation) within the Knowledge system.

---

## 1. Overview: Multi-Provider Architecture

The system supports **Multiple AI Providers** arranged in a prioritized **Failover Sequence**. This ensures high availability and flexibility by allowing the system to fall back to alternative providers if the primary one is unreachable.

### Key Concepts
- **Provider Chain**: An ordered list of configured AI connections (e.g., Primary: Local Ollama, Secondary: OpenAI).
- **Failover Logic**: The system attempts to use the first provider in the list. If it fails (connection error, timeout), it automatically attempts the next provider.
- **Provenance**: Every AI-generated artifact (summary, tag, answer) records the specific **Provider ID** and **Model Name** used to generate it.

---

## 2. Configuration Interface

**Menu:** Knowledge → AI Settings

The settings page allows administrators to manage the list of AI providers.

### 2.1 Managing Providers

- **Add Provider**: Click "Add Provider" to configure a new connection.
    - **Type**: Select the provider type (e.g., `Ollama`, `OpenAI`).
    - **Name**: Give the provider a friendly name (e.g., "Office GPU Server", "Backup OpenAI").
    - **Connection Details**: Enter the required fields for the selected type (URL, API Key).
    - **Model Selection**: Upon entering valid connection details (URL or API Key), the system automatically fetches available models from the provider and offers them in a dropdown list. You can also type a custom model name.
    - **Live Validation**: Connection checks run automatically when you leave the URL or API Key fields. A spinner and "Checking..." text indicate validation is in progress.

- **Edit Provider**: Click the "Edit" button on any provider row to modify its configuration.
    - The form will populate with existing details.
    - Saving changes updates the entry in-place without altering its priority or ID.
    - Changing the provider type (e.g., from Ollama to OpenAI) automatically adjusts the required fields.

- **Connection Status**: Each provider row and the edit form feature a color-coded status band on the right edge:
    - 🟢 **Green**: Connected and available.
    - 🔴 **Red**: Disconnected or unreachable.
    - 🟡 **Yellow**: Checking connection status...
    - Status checks occur on page load, when adding a new provider, after editing, and when modifying connection fields.

- **Reorder**: Drag and drop providers to change their priority. The top provider is always the default.

- **Remove**: Delete a provider from the chain.

### 2.2 Provider Types & Parameters

| Provider Type | Required Parameters | Description |
| :--- | :--- | :--- |
| **Ollama** | `URL` (e.g., `http://localhost:11434`)<br>`Model` (e.g., `llama3`) | Self-hosted, local-first LLM. Recommended for privacy and cost. |
| **OpenAI** | `API Key`<br>`Model` (e.g., `gpt-4o`) | Cloud-based commercial LLM. High reliability backup. |
| **Anthropic** | `API Key`<br>`Model` (e.g., `claude-3-opus`) | Alternative cloud provider. |

---

## 3. Storage & Data Model

Configuration is stored in the WordPress `options` table as a serialized array of provider objects.

### 3.1 Option Key: `knowledge_ai_providers`

Structure:
```json
[
  {
    "id": "uuid-1234",
    "type": "ollama",
    "name": "Local Server",
    "enabled": true,
    "config": {
      "url": "http://192.168.5.183:11434",
      "model": "llama3"
    }
  },
  {
    "id": "uuid-5678",
    "type": "openai",
    "name": "Emergency Backup",
    "enabled": true,
    "config": {
      "api_key": "sk-...",
      "model": "gpt-4o"
    }
  }
]
```

---

## 4. Provenance Recording

To ensure transparency and trust, the system records "AI Attribution" for all generated content.

- **Meta Key**: `_kb_ai_provenance`
- **Value**:
    ```json
    {
      "provider_id": "uuid-1234",
      "provider_name": "Local Server",
      "model": "llama3",
      "timestamp": "2024-05-20T10:00:00Z"
    }
    ```

This allows the UI to display exactly which AI wrote a summary or suggested a tag.

---

## 5. Troubleshooting

- **"All Providers Failed"**: If the system cannot reach any provider in the chain, the operation (e.g., Chat, Ingestion) will fail gracefully with a user-facing error message.
- **"Wrong Model Used"**: Check the provider order. The system always uses the first *available* provider. If your primary is offline, it may be using the secondary (which might be a different model).
