# ADR-004 — Redis

- Estado: Aceptado
- Fecha: 2026-09-07

## Contexto

Procesamiento, notificaciones, rate limits, locks y caché requieren coordinación rápida fuera del request.

## Decisión

Redis soportará colas, caché, locks y rate limiting, con prefijos/keys tenant-aware. PostgreSQL conserva estados de negocio.

## Consecuencias

Workers y observabilidad son necesarios; jobs deben ser idempotentes. Una pérdida de Redis no puede destruir historia transaccional. Persistencia/HA se ajustan por entorno.
