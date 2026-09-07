# Reglas permanentes del repositorio

## Propósito y alcance

Este repositorio implementa una PWA móvil y un futuro SaaS multi-tenant para academias de baile. WhatsApp conserva su papel de comunicación inmediata; el producto preserva, organiza y enseña la memoria digital de cada academia.

Antes de trabajar, todo agente debe leer este archivo, `docs/PROJECT_STATUS.md`, los ADR relevantes, `git status`, la implementación existente y sus pruebas. La fase activa limita el alcance: no se inicia una fase posterior sin cerrar y validar la anterior.

## Restricciones de arquitectura

- Backend Laravel, frontend React + TypeScript + Vite, PostgreSQL, Redis y Docker; despliegue previsto en Coolify.
- Las academias (`organizations`) son tenants de primera clase. Toda entidad propiedad de una academia incluye `organization_id` y toda consulta/autorización sensible delimita el tenant en servidor.
- Ninguna decisión de autorización depende de ocultar UI. Se usan permisos granulares y políticas; los roles solo agrupan permisos.
- El dominio no depende de un proveedor concreto de vídeo u objetos. Se programa contra contratos de aplicación.
- El borrado de historia educativa, membresías, versiones, auditoría y contabilidad se evita mediante estados, archivo o soft delete según el modelo.
- No se exponen originales profesionales mediante rutas públicas; se entregan por autorización y URL temporal.

## Convenciones

- Documentación y experiencia inicial en español; cadenas de UI pasan por i18n.
- PHP sigue PSR-12 y Laravel Pint; TypeScript estricto, ESLint y componentes accesibles mobile-first.
- Identificadores públicos no sustituyen la autorización. Usar claves foráneas, índices y restricciones únicas tenant-safe.
- Fechas persistidas en UTC; presentación en zona horaria de academia/usuario.
- Migraciones son aditivas y reversibles cuando sea razonable. No modificar una migración ya aplicada/compartida; crear otra y documentar riesgos de datos.
- Fixtures deterministas, pequeños y exclusivamente de desarrollo/pruebas. Nunca sembrar credenciales demo en producción.

## Seguridad y privacidad

- Validar entrada y autorización en servidor, incluidos uploads, descargas, jobs y URLs firmadas.
- Probar IDOR, acceso cruzado de tenant y escalación de privilegios en cada módulo sensible.
- Aplicar CSRF, cookies seguras, hash robusto, email verificado, revocación de sesión, rate limits, logs de auditoría y 2FA disponible (potencialmente obligatorio en privilegios altos).
- Validar MIME por contenido, extensión, tamaño y política; poner uploads en cuarentena antes de publicar.
- Minimizar datos personales, contemplar menores, retención configurable y revisión legal de textos de privacidad.
- Nunca escribir secretos, contraseñas reales, tokens o datos personales en Git, logs o fixtures.

## Pruebas y comandos esperados

Los comandos exactos se confirman cuando el scaffold quede integrado:

```bash
# backend
php artisan test
vendor/bin/phpstan analyse
vendor/bin/pint --test

# frontend
npm run test
npm run lint
npm run typecheck
npm run build

# E2E
npx playwright test
```

Además, validar migración limpia, seed determinista y flujos tenant-safe. No declarar un comando exitoso si no fue ejecutado.

## Git, coordinación y documentación

- Usar Conventional Commits, ramas/worktrees cuando haya trabajo paralelo y nunca force-push de historia compartida.
- El orquestador asigna un ID, alcance, no-objetivos, dependencias, criterios, pruebas y riesgos antes de implementar.
- Evitar ediciones simultáneas del mismo archivo; el integrador resuelve y valida el resultado combinado.
- Actualizar documentación y `docs/PROJECT_STATUS.md` tras una integración significativa, indicando evidencia exacta.
- No revertir un ADR aceptado silenciosamente: crear un ADR nuevo con razones e impacto de migración.

## Definición de terminado

Una tarea termina solo cuando requisitos, persistencia, autorización y aislamiento tenant están implementados; pruebas, análisis, lint y builds aplicables pasan; QA y seguridad independientes no dejan bloqueos críticos; documentación y estado están actualizados; y existe un commit identificable. Si algo no se ejecutó, se registra como pendiente.

Las responsabilidades de los 16 especialistas se detallan en `docs/AGENTS.md`.
