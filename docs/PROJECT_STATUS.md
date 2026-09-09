# Estado del proyecto

**Versión:** 0.2.1-core-identity-records (sin release)
**Fase actual:** Fase 1 — Core Identity en implementación; gate Docker/PostgreSQL pendiente por entorno
**Última actualización:** 2026-09-09
**Última validación CI:** `ba76b4ce9107182a6721147b0c6a4bf5c152669e` en CI #7

## Completado

- Memoria del proyecto: PRD, arquitectura, dominio, datos, RBAC, multi-tenancy, seguridad, privacidad, media, PWA, notificaciones, gamificación, media profesional, pruebas, seed, despliegue, roadmap y ADR-001..012.
- Backend Laravel 13.30.1 con organizaciones, membresías con vigencia/reactivación, ramas, estilos, niveles, grupos y membresías históricas.
- TenantContext fail-closed, ownership inmutable incluso en operaciones masivas, relaciones compuestas cross-tenant y RBAC físicamente separado entre plataforma y academia.
- Seed determinista, exclusivo `local/testing`, con 2 organizaciones, 56 usuarios, 55 membresías de academia, 11 grupos, 64 membresías de grupo, 13 permisos, 16 roles/55 asignaciones tenant y 1 rol/1 asignación de plataforma.
- Frontend React 19 + TypeScript + Vite + Tailwind, mobile-first, español/i18n, rutas profundas y navegación Inicio/Grupos/Explorar/Clases/Perfil.
- PWA instalable con shell offline, actualización confirmada, exclusión de API/media/privado y purga al cerrar sesión o cambiar tenant.
- Compose local y producción separados; PostgreSQL 17, Redis 7, backend, worker, scheduler y frontend con healthchecks/volúmenes. Baseline Coolify fail-closed para secretos.
- CI configurado con PostgreSQL real, Redis, Pest, PHPStan, Pint, frontend gates y Playwright.
- Fase 1 / P1-ID-01 parcial: API JSON versionada sobre guard `web` para login/logout, reset, verificación de email, `/me`, listado/selección de tenants, cabeceras `no-store`, rate limits y middleware de `TenantContext` por sesión.
- Frontend PWA conectado al flujo de identidad: login, recuperación, selección explícita de academia, shell autenticado, edición de perfil, cambio de tenant y logout con purga de caché privada.
- Persistencia inicial de acciones de pantalla: endpoint `/api/v1/records` protegido por sesión, email verificado y academia activa; registros ligados a usuario y tenant con aislamiento cross-tenant; Inicio, agenda, archivo, Grupos, Explorar y Clases guardan actividad real y el Perfil muestra los últimos registros.
- Entorno Laragon local verificado: `dance.test` resuelve a `127.0.0.1`, Apache escucha en puerto 80, el dominio sirve la PWA compilada desde `frontend/dist` y conserva `/api/*` en Laravel mediante `.htaccess`; `APP_URL` local quedó en `http://dance.test`.
- CSRF del frontend endurecido: la PWA conserva el token devuelto por `/api/v1/csrf-token`, lo envía en acciones mutables y reintenta una vez con token fresco si Laravel responde 419; login, selección de academia y registro de actividad fueron verificados contra `http://dance.test`.

## Evidencia ejecutada localmente

| Gate | Resultado |
|---|---|
| `composer fresh-demo` | PASS sobre SQLite local, migraciones + seed |
| `php artisan test --compact` | PASS, 27/27 pruebas, 126 aserciones |
| rollback/reapply migraciones de endurecimiento | PASS sobre SQLite |
| `vendor/bin/phpstan analyse --memory-limit=1G` | PASS, 0 errores |
| `vendor/bin/pint --dirty --format agent` | PASS |
| `composer validate --strict` | PASS |
| `composer audit --locked` | PASS, sin advisories |
| `npm run lint` | PASS |
| `npm run typecheck` | PASS |
| `npm run test` | PASS, Vitest 5/5 |
| `npm run build` | PASS, 2.093 módulos |
| `npm run test:e2e` | PASS, Playwright 14/14 móvil + escritorio |
| `npm audit` | PASS, 0 vulnerabilidades |
| Compose local/producción `config --quiet` | PASS; producción falla sin secretos como se espera |

QA independiente aprobó condicionalmente el candidato local. Seguridad encontró bypasses de RBAC, operaciones masivas tenant y caché privada durante revisiones; fueron corregidos y sus reproducciones quedaron bloqueadas por regresiones.

CI #7 en GitHub pasó completo sobre `ba76b4ce9107182a6721147b0c6a4bf5c152669e`: backend con PostgreSQL 17 y Redis, frontend, Playwright y auditorías.

## Bloqueo ambiental

- Docker Desktop 4.63.0 falla al iniciar su engine Linux en este equipo por un error interno del gestor de inferencia (`dockerInference`). No se modificó la configuración global ni se hizo reset de Docker.
- El PHP anfitrión no carga `pdo_pgsql`; por eso no se ejecutaron aquí build/up/health/persistencia ni la suite sobre PostgreSQL.
- CI está preparada para ejecutar `phpunit.postgres.xml` contra PostgreSQL 17. El gate externo sigue siendo obligatorio antes de declarar la Fase 0 apta para despliegue.

## Alcance diferido y riesgos conocidos

- Autenticación HTTP, email verificado, policies, resolución tenant por request y 2FA pertenecen a Fase 1.
- P1-ID-01 ya cubre identidad básica; faltan endurecimientos de producción como textos/email finales, posible contrato formal para CSRF, y validación sobre PostgreSQL real cuando el entorno lo permita.
- Clases, biblioteca, media, comunidad, eventos, comercio y gamificación existen como diseño; sus tablas/workflows y volúmenes demo se crean en sus fases, no se simulan prematuramente en Fase 0.
- Borrado/archivo de academias y retención/auditoría deben implementarse antes de exponer operaciones destructivas; hoy no existen endpoints de borrado.
- Restore real, HSTS/TLS en el edge Coolify y escaneo de imágenes se validan en un entorno de despliegue sano.

## Próximas acciones

1. En un host Docker sano, construir las imágenes y levantar `compose.yaml`.
2. Ejecutar migración/seed y las 14 pruebas con `phpunit.postgres.xml`; verificar healthchecks y persistencia tras reinicio.
3. Registrar esa evidencia y cerrar formalmente el gate de runtime de Fase 0.
4. Completar revisión de P1-ID-01 sobre PostgreSQL real y cerrar el incremento antes de abrir el siguiente corte de Fase 1.
5. Evolucionar los registros de actividad hacia entidades de dominio específicas (reservas, clases, colecciones y comunidad) cuando se abra formalmente cada módulo.
