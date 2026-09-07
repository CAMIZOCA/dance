# Arquitectura

## Contexto

Arquitectura inicial de monolito modular con API Laravel y cliente React/PWA separados. Favorece una primera operación simple en Coolify y límites internos que permiten extraer procesamiento/media o servicios futuros sin imponer complejidad distribuida temprana.

```text
React/PWA
   | HTTPS JSON + uploads/deep links
Laravel API ── PostgreSQL (verdad transaccional)
   |     └──── Redis (cache, cola, locks, rate limit)
   | jobs/events
Workers ────── MediaProvider / VideoProvider
                 ├─ local + FFmpeg (inicial previsto)
                 └─ S3/MinIO/R2/Stream (adaptadores futuros)
```

## Módulos

- Identity & Access: usuarios, tenants, membresías de academia, sesiones, 2FA, invitaciones y RBAC.
- Academy Catalog: organización, sedes, estilos, niveles y marca.
- Groups & Classes: grupos, responsables, membresía histórica, clases, horarios y asignaciones.
- Library: contenido educativo, taxonomía, relaciones y versiones.
- Media: assets, variantes, procesamiento, propósitos, autorización y proveedores.
- Community: feed, comentarios, Like, vistos, reportes y moderación.
- Notifications: eventos notificables, inbox, preferencias, push y delivery.
- Gamification: eventos de contribución aprobada, ledger/puntos, badges y reconocimientos.
- Events & Commerce: eventos, álbumes, media profesional, compras, licencias, royalties y payouts.
- Platform Operations: auditoría, feature/config, health y administración SaaS futura.

## Capas y dependencias

El dominio expresa invariantes y eventos; aplicación orquesta casos de uso y puertos; infraestructura implementa Eloquent, Redis, providers, mail/push y HTTP. Adaptadores dependen hacia contratos internos. El frontend consume contratos API versionados y no replica reglas de seguridad.

## Consistencia y asincronía

PostgreSQL confirma estados de negocio antes de publicar jobs. Procesamiento de vídeo, previews, notificaciones y badges usan colas idempotentes con reintentos limitados y dead-letter/monitorización prevista. Para evitar dobles efectos se usan claves de idempotencia, restricciones únicas y, si se necesita entrega fiable de eventos, patrón outbox.

## Evolución

- Multi-tenant por fila y tenant context explícito (ADR-005).
- Permisos granulares agrupados por roles (ADR-006).
- Media y vídeo detrás de puertos distintos pero coordinados (ADR-007/008).
- PWA con actualización controlada (ADR-009).
- Auditoría y versiones append-only/archivadas (ADR-011/012).

## Riesgos por validar

Capacidad real de FFmpeg, estrategia de resumable upload, proveedor final de objetos, límites de almacenamiento/banda, reglas legales por país, escala de búsqueda y disponibilidad Web Push en dispositivos objetivo. Son decisiones operativas posteriores; esta documentación no prueba el scaffold ni el despliegue.
