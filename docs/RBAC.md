# RBAC y autorización

## Modelo

Se usa autorización basada en permisos, con roles como paquetes configurables. Una asignación tiene tenant y opcionalmente alcance (`organization`, `branch`, `group`, recurso propio). El servidor resuelve el tenant activo, membresía vigente, permiso y relación con el recurso en cada acción.

## Roles base

| Rol | Alcance típico | Capacidades generales |
|---|---|---|
| Platform Super Admin | plataforma | Operación multi-tenant explícita y auditada; no acceso implícito silencioso a media privada. |
| Academy Admin | academia | Configuración, miembros, catálogos, grupos, contenidos y reportes del tenant. |
| Coordinator | academia/sedes | Operación académica delegada sin configuración crítica por defecto. |
| Teacher | grupos/clases | Gestionar sus clases, asignar contenido y aprobar aportes en alcance. |
| Assistant Teacher | grupos | Apoyo académico con subconjunto explícito. |
| Moderator | grupos/academia | Revisar reportes y contenido únicamente en alcance. |
| Student | membresías de grupo | Consumir, guardar, interactuar y subir si settings lo permiten. |
| Photographer | eventos/media asignada | Gestionar su media profesional y metadatos permitidos. |
| Videographer | eventos/media asignada | Equivalente para vídeo profesional. |

## Familias de permisos

`academy.manage`, `members.manage`, `roles.assign`, `groups.*`, `classes.*`, `library.*`, `media.upload`, `media.review`, `media.replace`, `media.restore`, `media.download`, `posts.*`, `comments.*`, `moderation.*`, `events.*`, `professional_media.*`, `purchases.approve`, `royalties.view/manage`, `badges.award`, `audit.view`.

La lista definitiva vive en seed/config versionado y se prueba. Permisos peligrosos se separan: aprobar compras no implica cambiar royalties; moderar no implica administrar miembros; subir no implica publicar.

## Reglas críticas

- Denegar por defecto. Comprobar tenant antes que permiso para evitar existencia lateral.
- Verificar pertenencia y vigencia en cada acceso a grupos, clases, feed y media.
- Responsabilidades por grupo pueden coexistir con rol estudiantil en otro grupo.
- Platform Super Admin usa rutas/acciones explícitas, 2FA y auditoría reforzada.
- Recomendación de moderador nunca concede permisos; requiere decisión humana auditada.
- Jobs, exports, URLs firmadas y canales de broadcast aplican las mismas fronteras.

## Pruebas mínimas

Matriz allow/deny por rol y alcance; cross-tenant con IDs válidos; moderador fuera de grupo; membresía expirada; multi-grupo; promoción con historia; acceso a original profesional; reasignación y revocación de permisos/sesiones.
