# Reglas de infraestructura

Aplica además el `AGENTS.md` raíz.

- Docker/Coolify deben ejecutar servicios reproducibles para aplicación, worker, scheduler, PostgreSQL y Redis, con volúmenes persistentes y healthchecks.
- Configuración por variables; secretos fuera del repositorio. Separar desarrollo, CI y producción.
- Imágenes mínimas, procesos no-root cuando sea viable y dependencias fijadas de forma reproducible.
- HTTPS termina en proxy/plataforma; la app debe confiar proxies de forma restringida y producir cookies/URLs seguras.
- Backups cubren PostgreSQL, objetos/media y configuración necesaria. Una copia no cuenta sin prueba documentada de restauración.
- Releases contemplan migraciones compatibles, workers, rollback de aplicación y compatibilidad con la versión PWA mínima.
- Media pesada no viaja por Git ni por la imagen de aplicación. Redis no es fuente de verdad.

Antes de declarar despliegue listo: validar build limpio, healthchecks, persistencia tras reinicio, migraciones, workers, permisos de volúmenes, restore ensayado y ausencia de secretos. La integración final corresponde al agente `15-integrator`.
