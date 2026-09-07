# ADR-006 — RBAC basado en permisos y alcance

- Estado: Aceptado
- Fecha: 2026-09-07

## Contexto

Una persona puede ser estudiante, miembro de ensemble y moderadora de un grupo; checks rígidos por rol no expresan ese contexto.

## Decisión

Roles agrupan permisos granulares; asignaciones tienen tenant y alcance opcional. Policies server-side resuelven tenant, acceso vigente, permiso y relación. Denegar por defecto.

## Consecuencias

Más flexibilidad y auditabilidad, a cambio de matriz/pruebas más amplias. Puntos o badges nunca conceden permisos. Cambios de rol/permisos son auditados y sesiones pueden revocarse.
