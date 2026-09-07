# PHASE-0 — Foundation

**Estado:** integrada y validada localmente; runtime Docker/PostgreSQL bloqueado por entorno
**Responsable:** `00-orchestrator`
**Gate final:** `15-integrator`, con QA y Seguridad independientes

## Objetivo

Obtener un repositorio reproducible y documentado con scaffolds, infraestructura de desarrollo, datos demo y todos los gates técnicos ejecutados, sin implementar módulos de Fase 1.

## Paquetes de trabajo

- P0-DOC: reglas, PRD, arquitectura, modelo, ADR y estado.
- P0-BE: scaffold Laravel, test/análisis/formato y base de factories.
- P0-FE: React/Vite/PWA base, test/lint/typecheck/build y Playwright.
- P0-INF: Docker, PostgreSQL, Redis, CI y baseline Coolify.
- P0-SEED: dos tenants, relaciones/escenarios y cuentas development-only.
- P0-INT: integración, fresh rebuild, suites, QA/seguridad y commit.

## No-objetivos

Autenticación/producto completos, feed, procesamiento real de media, comercio y cualquier fase posterior.

## Aceptación

Instalación limpia documentada; servicios healthy; migraciones y seed deterministas; suites/análisis/lint/typecheck/build/E2E smoke pasan; aislamiento demo comprobable; secretos/demo protegidos; documentación refleja evidencia exacta y existe commit integrado.

## Estado de evidencia

P0-DOC, P0-BE, P0-FE, P0-INF y P0-SEED están integrados. Backend pasa 14 pruebas/62 aserciones, PHPStan y Pint; frontend pasa Vitest 2/2, lint, typecheck, build y Playwright 12/12. Compose local/producción pasa validación estática y producción falla cerrada sin secretos.

P0-INT conserva un gate externo pendiente: Docker Desktop no inicia su engine en este host y PHP no tiene `pdo_pgsql`, por lo que aún deben ejecutarse build/up/health/persistencia y la suite PostgreSQL en CI o en un host sano. No se inicia Fase 1 hasta registrar ese resultado.
