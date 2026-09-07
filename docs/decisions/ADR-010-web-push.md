# ADR-010 — Notificaciones in-app + Web Push

- Estado: Aceptado
- Fecha: 2026-09-07

## Contexto

La PWA necesita avisos oportunos sin convertir el canal en spam ni depender de una entrega externa para conservarlos.

## Decisión

Persistir notificación in-app y entregar Web Push asíncrono mediante estándares VAPID, suscripciones por dispositivo, preferencias, deduplicación y deep links autorizados.

## Consecuencias

Un fallo push no pierde inbox. Se requieren consentimiento, limpieza de endpoints inválidos, métricas y payload mínimo. Otros canales se añaden como adaptadores, no alterando eventos de dominio.
