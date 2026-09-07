# Gamificación

## Dos dimensiones separadas

1. **Logros de aprendizaje**: progreso educativo verificable, participación o hitos académicos.
2. **Reputación de contribución**: ayuda útil y aprobada a la memoria/comunidad.

No existe un único score que mezcle ambos. La experiencia reconoce variedad y evita leaderboards agresivos o vergüenza por baja actividad.

## Motor de contribución

Acciones candidatas: primer vídeo útil de clase, upload aprobado, material faltante recuperado, corrección/comentario útil, reporte válido, moderación y fotos aprobadas. El evento se registra después de aprobación/validación, con clave idempotente, actor, tenant, recurso, tipo, fecha y metadata mínima.

Un ledger de reputación agrega entradas positivas/compensatorias; no se edita un saldo. Reglas versionadas establecen puntos, límites diarios/por acción, umbrales y elegibilidad. Likes pueden ser señal acotada, nunca conversión ilimitada directa.

## Badges

Automáticos, manuales, progresivos bronze/silver/gold, custom por academia, limitados/evento e históricos. Ejemplos: First Contribution, Documentalist, Archive Guardian, Missing Piece, Community Helper/Guardian, Contributor of the Month, Founding Member y Event 2026. Community Spirit, Exceptional Collaborator y Academy Ambassador son manuales por defecto.

Una concesión registra definición/versión, razón, actor/sistema y fecha. Revocación excepcional conserva historia y motivo.

## Recognition Wall

Visible dentro de academia según privacidad: badges recientes, aportes del mes y reconocimientos especiales. Rotar categorías, evitar ranking permanente y permitir moderación/opt-out donde corresponda.

## Recomendación de moderadores

Puede combinar aprobaciones, tasa de calidad, ausencia de incidentes y utilidad sostenida. Solo genera “Potential moderator”. Una persona autorizada revisa y asigna permisos; la asignación queda auditada.

## Antiabuso y pruebas

Rate/action caps, deduplicación, reversión al retirar aprobación, controles de auto-like/colusión, moderación y revisión de reglas. Probar concurrencia, reintento, cambio de regla sin recalcular historia, límites, tenant y que ningún badge/punto otorga RBAC.
