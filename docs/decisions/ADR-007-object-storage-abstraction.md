# ADR-007 — Abstracción de almacenamiento de objetos

- Estado: Aceptado
- Fecha: 2026-09-07

## Contexto

Desarrollo puede usar storage local, mientras producción/futuro puede requerir MinIO, S3 o R2.

## Decisión

El dominio/aplicación dependerá de `ObjectStorageProvider` para upload, estado/metadata, URL temporal y archivo/borrado. Keys y IDs son internos; storage privado por defecto.

## Consecuencias

Se pueden intercambiar proveedores y probar con fakes. Capacidades específicas quedan en adaptadores. Migrar objetos requiere inventario, copy/checksum y convivencia de providers, nunca cambio silencioso de URLs.
