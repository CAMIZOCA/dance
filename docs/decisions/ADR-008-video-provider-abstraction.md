# ADR-008 — Abstracción de proveedor de vídeo

- Estado: Aceptado
- Fecha: 2026-09-07

## Contexto

El inicio self-hosted prevé FFmpeg, pero escalabilidad o operación pueden favorecer streaming gestionado.

## Decisión

Definir `VideoProvider` con ingest/upload, procesamiento, status, playback/download autorizado, archivo/borrado y metadata. Adaptador inicial previsto: local + FFmpeg; Cloudflare Stream u otros son futuros.

## Consecuencias

El estado de negocio no depende del proveedor; se normalizan estados y variantes. HLS/calidades son capacidades negociadas. Cambiar proveedor exige migrar assets/versiones y mantener reproducción durante transición.
