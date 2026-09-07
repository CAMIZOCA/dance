# PRD — Memoria digital para academias de baile

## Visión

Crear una PWA mobile-first que preserve, organice y enseñe la historia de una academia: clases, pasos, figuras, secuencias, coreografías, prácticas, comunidad, eventos y medios. WhatsApp comunica el momento; esta plataforma mantiene una memoria privada, permanente, buscable y respaldada.

El primer despliegue atiende una academia, pero toda decisión tenant-owned debe soportar múltiples academias aisladas y futura marca blanca.

## Usuarios y necesidades

- Administración de plataforma/academia: configurar tenant, sedes, acceso, permisos, retención y marca.
- Coordinadores, docentes y asistentes: organizar grupos/clases, asignar práctica y publicar/aprobar material.
- Moderadores: revisar contenido y reportes solo en su alcance.
- Estudiantes: acceder a sus grupos, aprender, guardar, aportar y solicitar reactivación.
- Fotógrafos/videógrafos: acreditar, ofrecer y licenciar media profesional sin exponer originales.

## Alcance funcional

1. Academias, sedes, estilos, niveles configurables, grupos y clases históricas.
2. Identidad, invitación por email/enlace/QR, roles-permisos, membresías múltiples e historial.
3. Biblioteca educativa enlazada y asignable; búsqueda/filtros; vídeos múltiples y versionados.
4. Upload móvil resumible previsto, revisión, procesamiento asíncrono, HLS/calidades y reproductor de práctica.
5. Feed privado simple con posts, media, comentarios/respuestas, Like, fijados, vistos y moderación.
6. Favoritos/My Practice, PWA instalable, shell offline, actualización y Web Push con preferencias/deep links.
7. Eventos/álbumes y separación obligatoria de media comunitaria frente a profesional.
8. Compra manual inicial vía contacto/WhatsApp; acceso aprobado y futura automatización.
9. Royalties mediante ledger auditable, reglas versionadas, ventas, devoluciones y payouts.
10. Logros de aprendizaje separados de reputación por contribución; badges y reconocimiento no competitivo.
11. Acceso de academia configurable (inicialmente tres meses), expiración sin borrar historia y reactivación.
12. Marca blanca futura: nombre, logo, colores, contacto, dominio/subdominio e identidad PWA.

## Requisitos no funcionales

- Aislamiento tenant y autorización servidor probados; protección IDOR.
- Privacidad por diseño, minimización, auditoría y retención configurable, con especial atención a menores.
- UI española inicial sin cadenas rígidas; accesibilidad, objetivos táctiles grandes y uso con una mano.
- Procesamiento asíncrono, jobs idempotentes, media desacoplada y almacenamiento escalable.
- PostgreSQL como verdad, Redis para caché/colas, backups restaurables y observabilidad.
- Historia no destructiva para membresía, media, auditoría y contabilidad.

## Fuera de alcance inicial

Chat privado, voz, llamadas, clon de WhatsApp, reacciones complejas, checkout automático, facturación SaaS completa, 4K por defecto, vídeo offline masivo y asignación automática de moderadores.

## Métricas de producto propuestas

- Activación: usuario entra a un grupo y abre su primera clase/material.
- Memoria recuperada: clases con notas y al menos un contenido asociado.
- Aprendizaje: prácticas guardadas/completadas (cuando se implemente seguimiento).
- Contribución útil: uploads aprobados y aprobación por actor, no volumen bruto.
- Calidad: tasa de fallos de upload/procesamiento, latencia de feed/búsqueda y notificaciones opt-out.
- Seguridad: cero accesos cross-tenant autorizados, incidentes y tiempo de revocación.

Los objetivos numéricos se fijarán con datos reales; no se inventan umbrales en Fase 0.

## Criterios de éxito por release

Cada flujo requiere persistencia tras refresh, autorización, aislamiento tenant, pruebas aplicables, build y documentación. La aceptación humana incluye uso móvil real y datos demo inspeccionables. El roadmap está en `ROADMAP.md`.
