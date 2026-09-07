# ADR-011 — Auditoría append-only

- Estado: Aceptado
- Fecha: 2026-09-07

## Contexto

Cambios administrativos, seguridad, media y comercio requieren atribución y explicación histórica.

## Decisión

Registrar eventos de auditoría append-only con actor, acción, target, tenant, timestamp, correlation ID y metadata minimizada. Correcciones agregan eventos; no reescriben historia.

## Consecuencias

Facilita investigación y accountability, con coste de volumen/retención y acceso sensible. No almacenar secretos ni snapshots personales completos. Export/retención requieren política y permisos.
