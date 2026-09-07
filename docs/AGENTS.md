# Especialistas y coordinación

Los agentes son roles de trabajo, no identidades con privilegios permanentes. El orquestador asigna tareas con archivos exclusivos cuando sea posible; QA, Seguridad e Integración revisan de forma independiente. Ningún agente declara integración por sí solo.

| Agente | Responsabilidad principal | Entregables y límites |
|---|---|---|
| `00-orchestrator` | Plan, dependencias, asignación, orden de fases y memoria durable | Crea tareas, evita conflictos, exige evidencia; no da por válidos resultados no ejecutados. |
| `01-architect` | Límites del sistema, dominio, datos, contratos y ADR | Revisa impacto evolutivo y tenant; no revierte ADR aceptados sin uno nuevo. |
| `02-backend` | API Laravel, casos de uso, persistencia y jobs | Implementa bajo policies/tenant; no acopla dominio a proveedores. |
| `03-auth-tenancy-rbac` | Identidad, sesiones, invitaciones, acceso temporal, tenants, roles y permisos | Mantiene matriz RBAC y pruebas de IDOR/cross-tenant; no delega autorización al cliente. |
| `04-frontend-pwa` | React, UX móvil, i18n, accesibilidad, PWA y actualización | Prueba estados y build; no cachea media sensible/pesada por defecto. |
| `05-groups-classes` | Grupos, responsables, membresía histórica, promoción, clases y agenda | Protege alcance de grupo y conserva historia. |
| `06-media` | Upload, proveedores, FFmpeg/HLS, reproductor, versiones y favoritos | Separa propósitos/lifecycle; originales profesionales siempre privados. |
| `07-community` | Feed, posts, comentarios, Like, vistos, reportes y moderación | Comunidad simple; sin chat privado, llamadas ni reacciones complejas. |
| `08-notifications` | Notificaciones in-app, preferencias, Web Push y deep links | Controla frecuencia, consentimiento e idempotencia. |
| `09-gamification` | Contribuciones aprobadas, límites, badges, reconocimientos y recomendaciones | Separa aprendizaje/reputación; jamás concede roles automáticamente. |
| `10-events-commerce-royalties` | Eventos, álbumes, media profesional, compras, derechos y ledger | Históricos inmutables; checkout manual primero, automatizable después. |
| `11-security` | Threat modeling y revisión adversarial | Puede bloquear por IDOR, tenant leaks, uploads, URLs, XSS/CSRF o escalación; no se limita a happy path. |
| `12-qa` | Plan y ejecución independiente de pruebas | Reproduce, rompe y documenta evidencia; puede bloquear integración. |
| `13-devops` | Docker, CI, Coolify, observabilidad, backups y restore | No incluye secretos; distingue diseño de operación validada. |
| `14-demo-data` | Factories, seeds deterministas, cuentas demo y fixtures seguros | Impide ejecución en producción y evita archivos grandes. |
| `15-integrator` | Revisión del diff y puerta final de calidad | Ejecuta suite, análisis, builds, migración/seed limpio y E2E aplicable; integra solo sin regresiones críticas. |

## Protocolo de tarea

Cada tarea usa `docs/tasks/TASK_TEMPLATE.md` y declara: ID, objetivo, alcance/no-alcance, dependencias, archivos, aceptación, pruebas, riesgos tenant/seguridad, evidencia y estado. Los cambios sensibles requieren revisión `11-security`; todos requieren revisión proporcional de `12-qa`; solo `15-integrator` registra validación final y commit integrado.

## Trabajo paralelo

Puede paralelizarse cuando los archivos y contratos están acordados y no existe dependencia de salida. Cambios en esquema, contratos compartidos, autenticación, routing, service worker o configuración de CI se serializan o se coordinan explícitamente. `00-orchestrator` resuelve propiedad de archivos antes de iniciar.
