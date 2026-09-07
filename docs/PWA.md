# PWA

## Objetivo

Aplicación instalable, móvil y rápida, con navegación básica útil ante conectividad irregular. PWA no significa almacenar toda la academia offline.

## Componentes

- Manifest con nombre/short name, `start_url`, scope, colores e iconos adaptables; branding por academia cuando el entorno lo permita.
- Service worker versionado que precachea shell compilado y aplica estrategias explícitas por tipo.
- Flujo de instalación simple y skippable dentro del onboarding.
- Detección de worker/build nuevo: mostrar “Nueva versión disponible → Actualizar”.
- Versión mínima soportada servida por backend/config para forzar una actualización segura cuando exista incompatibilidad.

## Política de caché

- Cache-first: assets compilados con hash.
- Network-first con fallback controlado: shell/navegación y metadatos no sensibles elegidos.
- Stale-while-revalidate: thumbnails/schedules solo si autorización y partición de tenant son seguras.
- No cachear automáticamente vídeos grandes, originales profesionales, tokens, respuestas administrativas ni datos sensibles.

Caches incluyen versión y tenant cuando aplica. Logout, revocación, expiración o cambio de tenant elimina entradas privadas pertinentes. No se promete creación/edit offline en la primera versión.

## Onboarding

Bienvenida → instalar (si disponible) → solicitar notificaciones en contexto → mostrar grupos → empezar. Debe ser skippable salvo pasos realmente obligatorios y no pedir permisos del navegador antes de explicar su valor.

## Actualización

El frontend compara versión activa/servida, preserva trabajo seguro, activa el nuevo worker por acción y recarga una vez controlada. Si está por debajo de mínimo, bloquea operaciones incompatibles y guía a actualizar. Evitar loops y disponer de recuperación/limpieza de caché.

## Validación

Build de producción, Lighthouse/criterios de installability orientativos, modo standalone, iPhone/Android reales o representativos, offline shell, deep links, cambio de tenant/logout y Playwright para aviso/activación de actualización. Nada de ello figura como ejecutado hasta registrarse en `PROJECT_STATUS.md`.
