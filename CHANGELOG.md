# Changelog

Este proyecto sigue [Keep a Changelog](https://keepachangelog.com/es-ES/1.1.0/) y usará versionado semántico cuando existan releases.

## [Unreleased]

### Added

- Documentación inicial de producto, arquitectura, seguridad, dominio, datos y operación para la Fase 0.
- ADR-001 a ADR-012 y reglas permanentes/scoped para agentes.
- Definición de 16 especialistas y plantilla de tareas verificables.
- Laravel 13 con fundación multi-tenant, RBAC separado por plano, constraints, factories y seed demo determinista.
- React 19/TypeScript/Vite/Tailwind con shell móvil, i18n, rutas, PWA offline y actualización explícita.
- Dockerfiles, Compose local, Compose endurecido para producción/Coolify, PostgreSQL, Redis, worker y scheduler.
- CI con PostgreSQL 17, análisis backend, frontend y Playwright.
- Pruebas adversariales de aislamiento tenant, seed, caché privada y actualización PWA.

### Security

- Bloqueadas lecturas, escrituras individuales y operaciones masivas cross-tenant.
- Separados roles/permisos de plataforma y academia mediante modelos y constraints.
- Caché PWA limitada a shell/assets públicos; API, respuestas privadas y media quedan excluidas.
- Seed demo limitado a `local/testing`; producción exige secretos persistentes y servicios de datos privados.

[Unreleased]: https://example.invalid/dance/compare/HEAD
