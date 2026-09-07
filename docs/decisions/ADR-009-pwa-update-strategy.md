# ADR-009 — Estrategia PWA y actualización

- Estado: Aceptado
- Fecha: 2026-09-07

## Contexto

Un service worker puede dejar clientes con una versión antigua incompatible y caches privadas residuales.

## Decisión

Cachear shell/assets versionados y metadatos seguros seleccionados; excluir vídeo grande/sensible. Detectar nueva versión, ofrecer acción explícita y soportar versión mínima forzada. Particionar/limpiar caches por versión y tenant.

## Consecuencias

Mejor resiliencia con complejidad de lifecycle. Requiere E2E de update/logout/cambio tenant y diseño de compatibilidad API. No se promete edición offline inicial.
