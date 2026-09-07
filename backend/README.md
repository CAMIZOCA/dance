# Dance backend

Laravel 13 API foundation for the multi-tenant dance academy platform.

## Local baseline

Copy `.env.example` to `.env`, start PostgreSQL and Redis, then run:

```bash
composer install
php artisan key:generate
composer fresh-demo
composer test
composer analyse
composer format:check
```

La suite local rápida usa SQLite en memoria. CI ejecuta además la suite completa con `phpunit.postgres.xml` sobre PostgreSQL 17; desarrollo y producción usan PostgreSQL y Redis.

Los seeds demo solo se permiten cuando `APP_ENV` es `local` o `testing`; fallan cerrados en cualquier otro entorno. La contraseña exclusivamente de desarrollo es `DanceDemo2026!`; las cuentas canónicas incluyen `admin@demo.local`, `academy.admin@demo.local`, `teacher@demo.local`, `moderator@demo.local`, `student@demo.local` y `photographer@demo.local`.

Tenant-owned models use `TenantContext` and fail closed when no organization is active. HTTP tenant resolution and full authentication are deliberately deferred to Phase 1.
