# AI Integration — Chat UI & RAG Flow

This document describes the implementation of the **Chat UI** and the underlying **RAG (Retrieval Augmented Generation)** flow.

---

## 1. Overview

The Chat UI allows administrators to ask natural language questions about the ingested knowledge base. It leverages the **Embedding Pipeline** to retrieve relevant context and the **Ollama** LLM to generate answers.

---

## 2. Architecture

### Frontend
- **Location**: `Knowledge > Ask AI` (Admin Menu)
- **Technology**: jQuery, standard WordPress Admin styles.
- **Interaction**:
    - User types a question.
    - AJAX request sends the question to the backend.
    - UI shows a "Thinking..." state.
    - Answer is streamed or displayed upon completion (currently block response).

### Backend (`ChatHandler`)
- **Endpoint**: `wp_ajax_knowledge_chat`
- **Process**:
    1.  **Embed Query**: The user's question is converted into a vector using `OllamaClient::embed()`.
    2.  **Vector Search**: `VectorStore::search()` scans existing embeddings for cosine similarity.
    3.  **Context Assembly**: The top 3-5 matching chunks are retrieved.
    4.  **Prompt Construction**: A strict system prompt is combined with the retrieved chunks and the user's question.
    5.  **Generation**: `OllamaClient::chat()` is called to generate the final answer.

---

## 3. RAG Prompt Strategy

The system uses a **Grounding Prompt** to minimize hallucinations:

> "You are a helpful assistant. Use ONLY the following context to answer the question. If the answer is not in the context, say you don't know."

This ensures the AI relies on the *Knowledge Plugin's* data rather than its training data.

---

## 4. Security & Permissions

- **Capability**: `manage_options` (Admins only for MVP).
- **Nonce Verification**: All AJAX requests are verified with `knowledge_chat_nonce`.
- **Sanitization**: User input is sanitized before processing.

---

## 5. Limitations (MVP)

- **Context Window**: Limited by the LLM's context window (e.g., 8k tokens for Llama 3).
- **History**: No multi-turn conversation memory (each question is independent).
- **Speed**: Dependent on local hardware (Ollama inference speed) and vector search size.
