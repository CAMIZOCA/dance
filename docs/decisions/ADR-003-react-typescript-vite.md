# ADR-003 — React + TypeScript + Vite

- Estado: Aceptado
- Fecha: 2026-09-07

## Contexto

La experiencia requiere UI móvil interactiva, PWA, reproductor y un cliente desacoplado de la API.

## Decisión

React con TypeScript estricto y Vite será el frontend. i18n, accesibilidad y una capa coherente de acceso a API son obligatorias.

## Consecuencias

Buen tooling y build moderno; se deben controlar dependencias, estado y tamaño del bundle. El cliente no implementa seguridad decisiva. Cambiar framework requiere ADR y migración gradual.
