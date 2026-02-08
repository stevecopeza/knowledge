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
    - User selects a **Search Mode** (RAG Only, LLM Only, Combined).
    - AJAX request sends the question and mode to the backend.
    - UI shows a "Thinking..." state.
    - Answer is streamed or displayed upon completion (currently block response).
    - **Status Footer**: A footer displays the active AI Provider URL (e.g., `Using AI Provider: http://192.168.5.183:11434`) for verification.

### Backend (`ChatHandler`)
- **Endpoint**: `wp_ajax_knowledge_chat`
- **Process**:
    1.  **Mode Handling**: The system checks the requested `mode`:
        - **RAG Only** (Default): Strict grounding (see below).
        - **LLM Only**: Skips vector search, asks LLM directly.
        - **Combined**: Performs vector search but allows LLM to use general knowledge if context is insufficient.
    2.  **Embed Query**: (RAG/Combined only) The user's question is converted into a vector using `OllamaClient::embed()`.
    3.  **Vector Search**: (RAG/Combined only) `VectorStore::search()` scans existing embeddings.
    4.  **Context Assembly**: The top 3 matching chunks are retrieved.
    5.  **Prompt Construction**: A specific prompt is selected based on the mode.
    6.  **Generation**: `OllamaClient::chat()` is called to generate the final answer.

---

## 3. RAG Prompt Strategy

The system uses different prompts based on the selected mode:

### 3.1 RAG Only (Strict)
> "Answer the user's question based ONLY on the provided context below. If the answer is not in the context, say you don't know."

### 3.2 Combined (Balanced)
> "Answer the user's question using the provided context. If the context is insufficient, you may use your general knowledge to answer, but please mention if the information comes from outside the knowledge base."

### 3.3 LLM Only
> "Answer the user's question to the best of your ability using your general knowledge."

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
