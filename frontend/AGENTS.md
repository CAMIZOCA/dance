# Reglas del frontend

Aplica además el `AGENTS.md` raíz.

- React + TypeScript estricto + Vite; UI mobile-first, accesible, usable con una mano y con objetivos táctiles grandes.
- Cadenas visibles mediante i18n. No fijar textos repetidos ni estilos/roles de academia en componentes.
- El cliente representa permisos para UX, pero nunca se considera frontera de seguridad.
- Estado de servidor mediante una capa de datos coherente; manejar loading, vacío, error, expiración de acceso y reautenticación.
- PWA: shell offline y caché segura de metadatos no sensibles; nunca cachear automáticamente vídeos grandes u originales profesionales.
- Service worker con versionado, detección de actualización, acción explícita y versión mínima soportada.
- Reproductor accesible con velocidades, seek, fullscreen, espejo y calidad; A-B loop queda preparado, no asumido en primera entrega.

Validación mínima aplicable: `npm run test`, `npm run lint`, `npm run typecheck`, `npm run build` y Playwright para flujos críticos. Probar móvil, teclado, contraste, errores y actualización PWA.
