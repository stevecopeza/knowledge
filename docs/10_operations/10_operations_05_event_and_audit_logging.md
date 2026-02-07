# Event and Audit Logging

Defines the event and audit log model for the Knowledge system.

## Event Categories
- Ingestion
- Processing
- AI
- Lifecycle
- Permission

## Event Structure
- Type
- Timestamp
- Actor
- Target objects
- Outcome

## Audit vs Operational
Audit events are immutable and long-lived.
Operational events are prunable.

If an action cannot be audited, it must not exist.
