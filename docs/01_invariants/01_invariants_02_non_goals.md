# Explicit Non-Goals

This document defines what the system **will not do**, both now and in the future. These exclusions are intentional and binding. They exist to protect system clarity, data integrity, and long-term maintainability.

Anything listed here is considered **out of scope by design**, not merely deferred.

---

## 1. No Social Platform Behaviour

The system is **not** a social network and will not evolve into one.

### Explicit exclusions
- No public feeds
- No follower / following mechanics
- No likes, reactions, or social scoring
- No virality or engagement-driven algorithms

*Rationale:*  
Social mechanics bias knowledge toward popularity rather than accuracy, relevance, or durability.

---

## 2. No Content Discovery Engine

The system will not attempt to discover content on behalf of the user.

### Explicit exclusions
- No algorithmic recommendations
- No trending topics
- No external crawling for "related" content
- No feed-style consumption model

*Rationale:*  
Discovery is orthogonal to knowledge retention and introduces unnecessary noise, cost, and bias.

---

## 3. No External Publishing Platform

The system is not a publishing or syndication tool.

### Explicit exclusions
- No cross-posting to external platforms
- No public-facing article hosting (outside the local WordPress context)
- No SEO or distribution tooling

*Rationale:*  
Publishing introduces legal, editorial, and compliance concerns that conflict with private knowledge retention.

---

## 4. No AI Autonomy Over Canonical Data

AI systems will not autonomously modify canonical knowledge.

### Explicit exclusions
- No silent rewriting of articles
- No automatic correction of source material
- No auto-promotion of summaries or versions

*Rationale:*  
Knowledge systems must remain trustworthy. Silent mutation erodes confidence and auditability.

---

## 5. No Global Truth Claims

The system will not assert objective truthfulness or factual correctness.

### Explicit exclusions
- No "this is true/false" flags
- No authoritative fact-checking labels
- No single global credibility score

*Rationale:*  
Truth is contextual, domain-dependent, and often contested. The system supports **assessment**, not **judgement**.

---

## 6. No Mandatory Cloud Dependency

The system will not require a cloud service to function.

### Explicit exclusions
- No required external APIs
- No mandatory telemetry
- No cloud-only features

*Rationale:*  
Offline-first and user ownership preclude mandatory cloud coupling.

---

## 7. No UI-Driven Data Semantics

User interface choices must not define data meaning.

### Explicit exclusions
- No UI-only state
- No meaning encoded purely in presentation
- No behaviour dependent on a specific frontend

*Rationale:*  
The data model must remain stable across UI redesigns and clients.

---

## 8. No Hard-Coded AI Models or Vendors

The system will not be tied to a specific AI provider.

### Explicit exclusions
- No vendor-specific embeddings baked into data
- No model-specific assumptions in storage
- No irreversible preprocessing

*Rationale:*  
AI tooling evolves faster than knowledge systems should.

---

## 9. No Hidden Automation

All automation must be visible and controllable.

### Explicit exclusions
- No background jobs without status
- No irreversible automated actions
- No opaque pipelines

*Rationale:*  
Trust requires visibility.

---

## Closing Note

These non-goals are guardrails. They deliberately constrain the system so it remains:
- Focused
- Auditable
- Durable

If a future feature proposal conflicts with this document, it must either be rejected or require an explicit revision of this file.

