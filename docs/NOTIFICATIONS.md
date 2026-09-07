# Notificaciones

## Arquitectura

Un evento de dominio genera una notificación in-app para destinatarios autorizados. La entrega Web Push es un canal separado y asíncrono; fallar el push no pierde la notificación. Suscripciones se almacenan por usuario/dispositivo/tenant con endpoint, claves, estado, expiración y timestamps; secretos VAPID permanecen fuera de Git.

## Eventos iniciales

Nuevo vídeo de grupo, práctica asignada, cambio importante de horario, alta en grupo, post nuevo, actualización de contenido, anuncio de evento/álbum y reactivación aprobada. Cada tipo define audiencia, prioridad, agregación, preferencia y deep link tenant-safe.

## Flujo

1. Caso de uso confirma cambio y emite evento/outbox.
2. Resolver destinatarios en tenant, grupo y membresía vigentes.
3. Aplicar preferencias, permisos, quiet hours/digest futuros y reglas anti-fatiga.
4. Crear inbox idempotente y encolar deliveries.
5. Worker envía payload mínimo. Respuesta inválida desactiva suscripción.
6. Click abre un deep link estable; el backend vuelve a autorizar el recurso.

## Preferencias y privacidad

Opt-in por navegador con explicación contextual. Usuario controla categorías y canal; avisos críticos deben estar jurídicamente/productivamente definidos, no asumidos. Payload de lock screen evita nombre de menores, contenido privado detallado o URL de media firmada.

## Prevención de exceso

Agrupar eventos repetidos, deduplicar por clave, limitar frecuencia y no avisar al actor de su propia acción salvo necesidad. Preferir digest para feed de baja prioridad; cambios importantes conservan prioridad explícita.

## Pruebas

Preferencias allow/deny, destinatarios multi-grupo, membresía expirada, cross-tenant, idempotencia, endpoint 404/410, retry, deep link autorizado/no autorizado y clic tras revocación. Web Push real requiere entorno HTTPS/dispositivo y se reporta por separado.
