# Datos demo deterministas

## Objetivo y seguridad

Permitir inspección humana y pruebas de aislamiento/relaciones sin media pesada. Seed protegido por entorno (`local`, `testing` o flag seguro explícito) y debe fallar en producción. Todos los datos son ficticios; contraseña demo se fija/documenta al integrar mediante variable o valor exclusivamente local, nunca reutilizable en producción.

## Volumen objetivo

Dos organizaciones. Principal: **Ritmo Demo Academy**, sedes Centro/Norte, estilos Salsa/Bachata y niveles Essential, Basic, Intermediate, Open y Ensemble. Aproximadamente: 6 docentes, 2 asistentes, 2 moderadores, 40 estudiantes, 1 fotógrafo, 1 videógrafo, 10 grupos, 30 clases, 50 contenidos educativos, 5 eventos, 8 álbumes, 30 posts, comentarios, Likes, favoritos, notificaciones, badges, contribuciones y versiones archivadas.

La segunda organización contiene datos suficientes y nombres claramente distintos para probar denegación cross-tenant.

## Cuentas objetivo

- `admin@demo.local` — Platform Super Admin
- `academy.admin@demo.local` — Academy Admin
- `teacher@demo.local` — Teacher
- `moderator@demo.local` — Moderator
- `student@demo.local` — Student
- `photographer@demo.local` — Photographer

Se incluye `videographer@demo.local`. La contraseña validada de desarrollo es `DanceDemo2026!` y aparece en README con advertencia development-only. El seeder solo admite `local/testing` y falla en cualquier otro entorno.

## Escenarios sembrados en Fase 0

Se incluyen dos tenants, sedes/estilos/niveles/grupos, múltiples grupos por estudiante, membresía histórica, accesos expirado y en reactivación, moderador acotado y roles/permisos separados por plano. Cardinalidad validada: 2 organizaciones, 56 usuarios, 55 membresías de academia, 11 grupos y 64 membresías de grupo.

## Escenarios diferidos a su módulo

En sus fases respectivas se añadirán upload pending, asset aprobado, versiones v1/v2 archivadas y v3 actual, media comunitaria/profesional con placeholders, preview/thumbnail sin original público, compra manual/ledger, posts/likes y badges. No se crean tablas ficticias antes de implementar cada dominio.

## Determinismo

Seed estable (semilla RNG), IDs/referencias recuperables por atributos naturales controlados, fecha base fija `2026-09-07 12:00:00 UTC` y factories sin red. Media futura usará fixtures propios pequeños o placeholders, nunca contenido de terceros ni gigabytes.

## Comando y verificación

Comando: `composer fresh-demo`. Dos reconstrucciones SQLite producen el mismo snapshot temporal/relacional y las pruebas positivas/negativas tenant pasan. La repetición sobre PostgreSQL 17 queda como gate ambiental/CI obligatorio.
