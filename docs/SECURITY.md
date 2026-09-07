# Seguridad

## Objetivos

Confidencialidad entre academias, integridad de historia/ledger, disponibilidad razonable y trazabilidad de acciones sensibles. El frontend nunca es una frontera de seguridad.

## Controles base

- HTTPS, HSTS en producción, cookies `Secure`, `HttpOnly`, `SameSite`, rotación/revocación de sesión y CSRF en autenticación por cookie.
- Hash de contraseña provisto por Laravel con algoritmo/coste vigente; verificación de email, reset seguro y rate limits.
- 2FA disponible y política para roles altos; recuperación y cambios auditados.
- Policies server-side, validación de tenant primero, respuestas que reduzcan enumeración e IDOR tests.
- Validación/escape frente a XSS, CSP planificada, SQL parametrizado/ORM y headers de seguridad.
- Secrets en plataforma/secret manager; nunca Git, frontend, logs ni imágenes.
- Logs de auditoría para cambios de rol, membresía, aprobación, reemplazo/restauración, precio/compra y acciones administrativas.

## Upload y media

Uploads tienen sesión autorizada, límites por usuario/tenant, nombres generados, tamaño y extensión permitidos, MIME detectado por contenido, cuarentena, hash y estado. Considerar antivirus/sandbox antes de producción según riesgo. FFmpeg procesa con recursos limitados y entradas no confiables aisladas. No servir cuarentena u originales profesionales desde webroot. Descargas mediante entitlement y URL corta firmada; la autorización se verifica antes de firmar.

## Amenazas prioritarias

| Amenaza | Defensa esperada |
|---|---|
| Cross-tenant/IDOR | TenantContext, policies, queries acotadas, constraints y tests adversariales. |
| Escalación | Permisos granulares, denegar por defecto, separación de funciones, 2FA/auditoría. |
| MIME spoof/malware | Inspección real, cuarentena, límites, escaneo y procesamiento aislado. |
| Exposición profesional | Storage privado, variantes separadas, URLs temporales, entitlement. |
| XSS/CSRF | Escape/sanitización, CSP, tokens/cookies correctas y tests. |
| Abuso/spam | Rate limits tenant/user, moderación, idempotencia y cuotas. |
| Replay/doble proceso | Tokens de un uso, claves idempotentes y constraints. |
| SSRF/provider abuse | Allowlists, clientes restringidos y no aceptar URLs arbitrarias. |

## Auditoría

Registro append-only con actor, acción, target tipado, tenant, timestamp UTC, request/correlation ID y metadata minimizada. Acceso restringido y exportación protegida. No guardar contraseñas, tokens, originales ni datos personales completos innecesarios.

## Gate de seguridad

`11-security` revisa autenticación, autorización, tenant, upload/media, comercio y operaciones. Un hallazgo crítico/alto bloquea integración. Se registran pruebas realizadas; un diseño escrito no equivale a pentest ni certificación.
