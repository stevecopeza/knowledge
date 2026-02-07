# Role & Capability Matrix

This document defines how WordPress roles and capabilities map to Knowledge-system permissions and menu visibility.

## Principles
- Capabilities, not roles, gate behavior
- Roles are convenience groupings
- Menu visibility must never exceed capability grants

## Core Capabilities
- kb_view
- kb_ingest
- kb_annotate
- kb_review
- kb_edit
- kb_manage_projects
- kb_manage_ai
- kb_admin

## Recommended Role Mapping
Reader: kb_view
Contributor: kb_view, kb_ingest, kb_annotate
Editor: + kb_edit, kb_manage_projects
Reviewer: + kb_review
Administrator: all capabilities

## Menu Visibility Rules
- Knowledge menu requires kb_view
- Admin-only screens require kb_admin

If a user lacks a capability, the action must not be reachable.
