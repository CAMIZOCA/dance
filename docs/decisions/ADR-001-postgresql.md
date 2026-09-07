# ADR-001 — PostgreSQL

- Estado: Aceptado
- Fecha: 2026-09-07

## Contexto

El dominio necesita integridad relacional, transacciones, índices tenant-safe, búsqueda inicial y constraints para versiones/ledger.

## Decisión

PostgreSQL será la base de datos transaccional y fuente de verdad. Las pruebas de integración usarán PostgreSQL cuando dependan de su semántica.

## Consecuencias

Se aprovechan FKs, índices parciales, JSONB/FTS con criterio y transacciones. Operación requiere backups/restores y tuning. SQLite no validará gates críticos. Migrar de motor exigiría ADR nuevo y plan de datos.
