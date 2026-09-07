# Despliegue y operación

## Objetivo

Despliegue self-hosted en Coolify detrás de un dominio como `medio-digital.net`, con HTTPS. Este documento describe la línea base prevista; no certifica un despliegue ejecutado.

## Servicios

- Aplicación/API Laravel y servidor web.
- Worker(s) de cola y scheduler como procesos separados.
- Frontend estático o servido tras proxy/CDN según configuración.
- PostgreSQL con volumen persistente.
- Redis con persistencia según uso; nunca fuente de verdad.
- Almacenamiento privado de media (volumen local inicial o S3 compatible) y opcional worker FFmpeg.

## Configuración

Variables separadas para entorno, URL, DB, Redis, mail, VAPID, storage/video provider, claves de firma, CORS/sanctum, branding base, límites y observabilidad. Secretos solo en Coolify/secret store. `APP_DEBUG=false`, logs adecuados y proxies confiables restringidos.

## Release

1. Build reproducible e imágenes escaneadas cuando sea posible.
2. Backup/precheck para migraciones sensibles.
3. Migraciones compatibles con versión anterior (expand/contract).
4. Desplegar API/frontend y reiniciar workers controladamente.
5. Health/readiness, smoke, assets/PWA y cola.
6. Rollback de código; rollback de datos requiere plan específico, no migración destructiva improvisada.

## Backups y restauración

Respaldar PostgreSQL, media/objetos y configuración necesaria cifrada. Definir RPO/RTO, frecuencia, retención, destino separado y alertas después de conocer operación. Probar restore periódico en entorno aislado: DB + referencias/objetos + permisos + checksums. Una copia no probada no se considera recuperación validada.

## Observabilidad

Health sin datos sensibles; métricas de HTTP, cola, DB, Redis, uploads/transcodes y notificaciones; logs estructurados con correlation ID/tenant pseudonimizado; alertas por errores, backlog, disco, backup y certificados. Auditoría de negocio queda separada del log técnico.

## Compose y Coolify

- `compose.yaml`: desarrollo local; publica puertos de datos y usa exclusivamente credenciales/clave demo.
- `compose.production.yaml`: baseline Coolify; exige secretos, usa `APP_ENV=production`, desactiva debug y no publica PostgreSQL/Redis.
- El edge HTTPS debe añadir HSTS; no se fuerza en Nginx local HTTP. Ver `infrastructure/coolify/README.md`.

## Checklist pendiente de producción

DNS/TLS, cookies/CORS, dominios tenant verificados, storage privado, límites FFmpeg, mail/push, 2FA alta jerarquía, legal/privacidad, restore ensayado, rotación de secretos, retención/logging, seed demo bloqueado, migración/rollback, smoke/E2E y versión mínima PWA. `15-integrator` debe aportar evidencia.
