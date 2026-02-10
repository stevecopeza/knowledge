# Deduplication Strategy Update & Audit

**Date:** 2026-02-10
**Status:** Analysis & Planning
**Scope:** Data Integrity, Ingestion Service

## 1. Problem Statement

An audit of the Knowledge Base revealed duplicates that bypassed the initial "No Duplicate Strategy." These duplicates fragment knowledge, waste storage, and clutter search results.

### 1.1 Detected Duplicate Types

1.  **Race Condition Duplicates (Critical)**
    *   **Description:** Two identical articles created simultaneously (same second) with the exact same Source URL.
    *   **Example:** "How AI Is Reshaping Freelance Work..." (IDs 1131 & 1132).
    *   **Root Cause:** Lack of concurrency locking. Two ingestion processes (e.g., batch import) checked for existence -> found nothing -> both proceeded to create.

2.  **Redirect/Source Duplicates**
    *   **Description:** Identical content ingested from different URLs that resolve to the same final destination or are just different entry points (e.g., `flip.it` redirects).
    *   **Example:** "Boost Efficiency..." (IDs 740 & 766).
    *   **Root Cause:** Strategy relied on strict string matching of the *Input URL*. Since `flip.it/A` != `flip.it/B`, they were treated as unique.

3.  **Near-Duplicates (Canonical Divergence)**
    *   **Description:** Same article title and subject, but content differs slightly (hash mismatch) due to scraping different versions (Aggregator wrapper vs. Direct Source).
    *   **Example:** "The World's 20 Most Stunning Natural Places..." (Aggregator vs. Direct).
    *   **Root Cause:** Missing canonical resolution. The system didn't resolve the aggregator link to the final URL *before* deduplication.

## 2. Root Cause Analysis

The initial strategy failed because it was **URL-Centric** and **Local-Only**:
1.  **Missing Canonical Resolution:** Did not resolve HTTP redirects before checking existence.
2.  **No Global Content Check:** Checked content hash only against *existing versions of the same article*, not across the entire database.
3.  **No Concurrency Control:** No locking mechanism to prevent simultaneous writes for the same resource.

## 3. Hardening Strategy

To fix this and prevent future occurrences, we will implement a 3-Layer Defense:

### 3.1 Layer 1: Canonical Resolution (Pre-Ingestion)
*   **Action:** Before any processing, follow HTTP redirects to get the `final_url`.
*   **Check:** Check if `final_url` exists in `_kb_source_url`.
*   **Outcome:** `flip.it/A` and `flip.it/B` both resolve to `example.com/post`, catching the duplicate immediately.

### 3.2 Layer 2: Global Content Hashing (Post-Fetch)
*   **Action:** Calculate the MD5 hash of the normalized content.
*   **Check:** Query `_kb_content_hash` across **all** `kb_version` posts (Global Index).
*   **Outcome:** If content matches an existing article (even if URLs differ completely), treat as a Duplicate/Version update rather than a new Article.

### 3.3 Layer 3: Concurrency Locking (Write-Time)
*   **Action:** Implement a short-lived lock (using WP Transients or DB locks) on the normalized URL hash during the ingestion transaction.
*   **Outcome:** If Process A is writing, Process B waits or fails gracefully, preventing race conditions.

## 4. Remediation Plan (Cleanup)

We will execute a cleanup script to resolve existing duplicates:

1.  **Merge Exact Duplicates (Race Conditions):**
    *   Identify pairs with identical `_kb_source_url`.
    *   Keep the one with the lowest ID (First).
    *   Reassign any child posts (Versions, Notes) from the Duplicate to the Original.
    *   Delete the Duplicate.

2.  **Merge Content Duplicates (Redirects):**
    *   Identify pairs with identical `_kb_content_hash`.
    *   Keep the one with the "Better" URL (non-shortened) or oldest ID.
    *   Add the Duplicate's URL as an "Alias Source" (future feature) or simply log it.
    *   Delete the Duplicate.

3.  **Manual Review for Near-Duplicates:**
    *   Flag articles with identical Titles but different hashes for user review.
