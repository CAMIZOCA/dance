# ADR-005 — Multi-tenancy por discriminador de fila

- Estado: Aceptado
- Fecha: 2026-09-07

## Contexto

El primer despliegue es una academia, pero el producto debe aislar muchas academias sin coste operacional prematuro de una base por tenant.

## Decisión

Compartir base/esquema y añadir `organization_id` a toda entidad tenant-owned. Usar TenantContext explícito, queries acotadas, policies, constraints y pruebas cross-tenant. Global scopes no son única defensa.

## Consecuencias

Operación y analytics simples, con riesgo alto ante query mal acotada mitigado por defensa en profundidad. Cache, jobs, storage y búsqueda incluyen tenant. RLS puede añadirse tras evaluación; base por tenant requiere ADR/migración nuevos.
