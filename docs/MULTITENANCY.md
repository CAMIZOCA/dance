# Multi-tenancy

## Estrategia

Base de datos compartida y esquema compartido con discriminador `organization_id` (ADR-005). Una identidad puede pertenecer a varias academias; la petición selecciona un tenant explícito y valida una membresía vigente. La resolución puede provenir de dominio/subdominio, ruta o selección autenticada, nunca solo de un parámetro no confiable.

## Reglas de aislamiento

1. Toda entidad privada propiedad de academia contiene `organization_id` no nulo.
2. Repositorios/queries reciben `TenantContext`; relaciones anidadas se validan contra el mismo tenant.
3. Policies validan tenant + permiso + alcance + estado del acceso.
4. Cachés, locks, jobs, rutas de objetos, canales realtime, búsquedas y métricas incluyen tenant en la clave/payload.
5. Exports, notificaciones y URLs firmadas no cruzan contexto.
6. Administración de plataforma es acceso excepcional, explícito, de privilegio alto y auditado.

Los global scopes pueden reducir errores, pero no son la única defensa. Se consideran controles adicionales mediante constraints, pruebas y, si la operación lo justifica, PostgreSQL Row Level Security; RLS no se asume implementado en Fase 0.

## Marca blanca y dominio

La academia configura nombre, logos, colores, contactos, WhatsApp, sedes e identidad PWA. Dominios se verifican antes de activar y se mapean inequívocamente. Datos de branding se cachean por tenant y versión.

## Ciclo de acceso

`organization_membership` contiene estado, `access_expires_at` y reactivación. Expiración (inicialmente tres meses configurable) bloquea contenido, independiente de la sesión web, sin borrar historial. Reactivación requiere solicitud/aprobación según política y queda auditada.

## Pruebas obligatorias

- Usuario A intenta URLs/API/IDs de grupos, posts, media, álbumes, comentarios y compras de B: denegado sin fuga.
- Colisión de IDs/slugs/caché entre A y B.
- Jobs y notificaciones procesan el tenant serializado y validado.
- Usuario multi-academia cambia contexto sin arrastrar permisos.
- Super admin sin flujo de soporte explícito no obtiene URLs privadas por accidente.
- Seed con al menos dos organizaciones verifica consultas y relaciones.

## Escalabilidad futura

El discriminador permite operación inicial simple. Particionado, réplicas o base por tenant requerirán un ADR nuevo, estrategia de routing/migración y compatibilidad de analytics/backup.
