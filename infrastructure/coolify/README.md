# Línea base de Coolify

Coolify debe desplegar este repositorio usando `compose.production.yaml` desde la raíz. `compose.yaml` es exclusivamente local y publica PostgreSQL/Redis para desarrollo.

Variables obligatorias: `APP_KEY`, `APP_URL`, `POSTGRES_DB`, `POSTGRES_USER`, `POSTGRES_PASSWORD` y `REDIS_PASSWORD`. El archivo de producción falla al renderizar si falta alguna y no publica PostgreSQL ni Redis al host.

En producción:

1. Generar secretos únicos y persistentes; nunca copiar la clave de desarrollo de `.env.example`.
2. Configurar el dominio/HTTPS en Coolify y enrutar la aplicación sin exponer servicios internos.
   El proxy HTTPS debe añadir `Strict-Transport-Security` (HSTS) únicamente en el dominio público HTTPS; se verifica con un smoke test después del despliegue y no se fuerza en los Nginx locales HTTP.
3. Montar volúmenes persistentes para PostgreSQL y almacenamiento privado.
4. Ejecutar migraciones como tarea de despliegue controlada.
5. Ejecutar el worker de cola como servicio separado y supervisado.
6. Configurar copias verificadas de base de datos, media y configuración.

Validar `docker compose -f compose.production.yaml config` con variables de prueba antes de registrar el servicio. La receta local es una base reproducible, no una configuración final de producción.
