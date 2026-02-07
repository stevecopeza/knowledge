# Ingestion — Sources and Formats

This document defines **what can be ingested** and in **which formats**. The goal is broad compatibility with minimal assumptions.

---

## 1. Supported Source Types

### 1.1 URLs
- Web articles
- Blog posts
- Documentation pages

Rules:
- URL is treated as Source identity
- Redirects are resolved and recorded

---

### 1.2 Documents
- PDF
- Word-compatible formats
- Markdown
- Plain text

Rules:
- Original file is preserved in `/media/`
- Text is extracted deterministically
- **MVP Constraint**: "Text Extraction Only". Formatting/Layout in PDFs is not preserved in the `content.md`.
- **Implementation**: Use pure PHP libraries (e.g., `smalot/pdfparser`) to avoid binary dependencies.

---

### 1.3 Manual Input
- Pasted text
- Editor-authored content

Rules:
- Manual input still creates a Source
- Provenance is marked as manual

---

### 1.4 API Submissions
- REST-based ingestion
- Authenticated via tokens

Rules:
- API submissions are treated identically to UI submissions

---

## 2. Bulk Import Formats

### 2.1 URL Lists
- Plain text (one URL per line)
- CSV
- Spreadsheet (CSV-compatible)

Optional columns:
- Category
- Tags
- Project

---

### 2.2 Migration Imports

- Generic import from other tools via export files
- No tool-specific coupling required

---

## 3. Deferred Processing

- Bulk imports enqueue capture only
- Enrichment runs later

---

## Closing Note

Ingestion formats prioritise accessibility over completeness. New formats may be added without altering core behavior.

