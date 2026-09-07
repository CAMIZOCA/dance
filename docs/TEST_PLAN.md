# Plan de pruebas

## Pirámide y herramientas objetivo

- Backend Pest/PHPUnit: dominio, casos de uso, API, policies, jobs y persistencia.
- Frontend Vitest + Testing Library: componentes, hooks, estados y utilidades.
- Contrato/integración: PostgreSQL/Redis/providers falsos y API cliente-servidor.
- E2E Playwright: flujos críticos en viewport móvil y escritorio mínimo.
- Gates: Larastan/PHPStan, Pint, ESLint, TypeScript y build Vite.

Las herramientas son objetivo de Fase 0; `PROJECT_STATUS.md` registra cuáles están realmente configuradas/ejecutadas.

## Matriz crítica

| Flujo | Evidencia mínima |
|---|---|
| Persistencia de Like | like, refresh, persiste; unlike, refresh, desaparece; concurrencia no duplica. |
| Upload estudiante | permitido → pending → aprobación correcta → visible → contribución/badge idempotentes. |
| Cross tenant | IDs reales de B desde usuario A en web/API/media: denegados sin fuga. |
| Múltiples grupos | acceso a ambos propios y denegación de grupo no relacionado. |
| Promoción | termina Basic, inicia Intermediate, conserva historia y acceso temporal según setting. |
| Reemplazo vídeo | v1 archivada, v2 actual, historial visible, restore auditado. |
| Moderación | moderador de grupo acepta allí y es denegado fuera. |
| PWA update | detecta versión, muestra acción, activa/carga nueva sin loop. |

## Cobertura transversal

Cada feature evalúa happy path, validación, permisos, tenant, membresía expirada, datos inexistentes, concurrencia/idempotencia, refresh/persistencia, accesibilidad básica y logging sin secretos. Upload añade MIME spoof, tamaño, extensión, cuarentena y fallo de worker. Comercio añade dobles aprobaciones, refunds y ledger.

## Entornos y datos

Pruebas usan PostgreSQL (no sustituir semántica crítica por SQLite), Redis aislado cuando aplique, clock/provider fake y seeds/factories deterministas. E2E nunca usa producción. Archivos fixture son diminutos y seguros.

## Gate de Fase 0

Instalación limpia; servicios healthy; migración limpia y seed; backend tests; análisis/formato; frontend tests/lint/typecheck/build; Playwright smoke; verificación manual mínima de PWA; inspección de secretos; restore documentado o marcado pendiente. Reportar comando, fecha, entorno, resultado y fallos íntegros.

## Independencia

`12-qa` no acepta solo el reporte del implementador; reproduce y busca fallos. `11-security` revisa los límites sensibles. `15-integrator` ejecuta la matriz final sobre el resultado combinado.
