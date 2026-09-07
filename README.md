# Dance Academy Platform

PWA móvil y base SaaS multi-tenant para preservar, organizar y enseñar la memoria digital de academias de baile. Complementa WhatsApp con espacios privados para grupos, clases, biblioteca, comunidad, eventos y medios.

## Estado

La Fase 0 está integrada y validada localmente con SQLite/Chromium. El gate de runtime Docker + PostgreSQL 17 está configurado en CI, pero continúa pendiente en este equipo porque Docker Desktop no consigue iniciar su engine. El estado exacto y las limitaciones están en `docs/PROJECT_STATUS.md`.

## Stack fijado

- Laravel 13.30.1, PHP 8.4.1+
- React 19, TypeScript estricto, Vite 7 y Tailwind CSS 4
- PostgreSQL 17 y Redis 7
- Docker Compose y baseline separado para Coolify
- Pest/PHPUnit, Vitest, Playwright, Larastan/PHPStan, Pint y ESLint

## Inicio local con Docker

```powershell
Copy-Item .env.example .env
docker compose up --build -d
docker compose exec backend php artisan migrate:fresh --seed --force
```

Servicios: frontend `http://localhost:5173`, backend `http://localhost:8080`, PostgreSQL `5432` y Redis `6379`. `compose.yaml` es solo para desarrollo.

Producción usa `compose.production.yaml`, exige secretos explícitos y no publica PostgreSQL/Redis. Consultar `docs/DEPLOYMENT.md` e `infrastructure/coolify/README.md`.

## Desarrollo sin Docker

```powershell
Set-Location backend
composer install
Copy-Item .env.example .env
php artisan key:generate
composer fresh-demo
php artisan test --compact
composer analyse
composer format:check

Set-Location ../frontend
npm ci
npm run lint
npm run typecheck
npm run test
npm run build
npm run test:e2e
```

## Cuentas demo

Solo existen al ejecutar el seed en `local` o `testing`; el seeder falla cerrado en cualquier otro entorno.

| Perfil | Correo |
|---|---|
| Platform Super Admin | `admin@demo.local` |
| Academy Admin | `academy.admin@demo.local` |
| Teacher | `teacher@demo.local` |
| Moderator | `moderator@demo.local` |
| Student | `student@demo.local` |
| Photographer | `photographer@demo.local` |

Contraseña común de desarrollo: `DanceDemo2026!`. Nunca debe utilizarse fuera del entorno demo.

## Estructura

```text
backend/          Laravel, tenancy/RBAC foundation, seed y pruebas
frontend/         React/PWA mobile-first y pruebas
infrastructure/   Nginx y baseline Coolify
docs/             PRD, arquitectura, ADR, seguridad, tareas y estado
legacy-symfony/   manifiestos heredados; fuera del build activo
```

Crédito inicial configurable: **Developed by Marvin Baptista / Medio Digital**.
