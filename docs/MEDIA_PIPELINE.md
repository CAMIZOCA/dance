# Pipeline de media

## Clasificación obligatoria

Cada asset declara propósito: `educational`, `class_history`, `community` o `professional`. Propósito determina revisión, audiencia, retención, descarga, variantes y reglas comerciales; foto/vídeo comparten conceptos de asset, derechos y autorización sin perder procesamiento específico.

## Contratos

`ObjectStorageProvider`: iniciar/finalizar upload, leer metadata/status, generar URL temporal, copiar/mover, archivar/eliminar. Adaptadores previstos: local privado, MinIO, S3 compatible y R2.

`VideoProvider`: upload/ingest, solicitar procesamiento, consultar estado, playback/download autorizado, archivar/eliminar y metadata. Adaptadores previstos: FFmpeg local y futuros servicios como Cloudflare Stream.

El dominio guarda IDs/metadata propios, nunca URLs permanentes del proveedor como verdad de negocio.

## Flujo de upload móvil

1. Cliente solicita una sesión con tenant, actor, propósito, grupo/clase y metadata mínima.
2. Servidor autoriza y entrega upload resumible o firmado, con expiración y límites.
3. Cliente sube por partes y puede salir; reanuda por ID seguro.
4. Finalización verifica tamaño, hash, MIME real/extensión, tenant y cuarentena.
5. Worker inspecciona y genera metadata/variantes; falla de forma recuperable e idempotente.
6. Upload estudiantil entra `pending_review` normalmente. Aprobación crea visibilidad/contribución una sola vez.
7. Publicación emite eventos para feed/notificaciones sin bloquear el request.

## Vídeo local previsto

FFmpeg genera HLS/adaptativo cuando convenga y perfiles prácticos 360p/480p/720p, evitando 4K. Original se conserva o purga según política, derechos y período. Recursos/tiempo están limitados; comandos no incorporan entrada no confiable sin aislamiento.

## Reproductor

Velocidades 0.5x/0.75x/1x/1.25x, seek atrás/adelante, fullscreen, espejo, favorito y calidad/adaptativa. A-B loop es extensión prevista. Telemetría respeta privacidad y no sustituye el estado visto significativo.

## Versionado

Un `media_asset` tiene `media_versions` numeradas y una actual. Reemplazar archiva la versión anterior; restaurar conserva trazabilidad y registra actor/fecha. Cada versión tiene variantes y estado independientes. No sobrescribir objetos con la misma key ni borrar historial silenciosamente.

## Media profesional

Original privado; preview reducido y opcionalmente con watermark; thumbnail pequeño. Acceso al original requiere entitlement válido y URL temporal. Créditos, copyright y uso se capturan en el perfil profesional descrito en `PROFESSIONAL_MEDIA.md`.

## Fallos y observabilidad

Registrar correlation ID, asset/version, proveedor, etapa, intentos y error saneado. Métricas: éxito, duración, cola, tamaño y fallos por etapa/tenant sin filtrar datos. Reintentos no duplican versiones, contribuciones ni notificaciones.
