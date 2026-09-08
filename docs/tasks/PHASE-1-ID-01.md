# P1-ID-01 — Identidad y acceso por tenant

**Fase:** 1 — Core Identity
**Estado:** en implementación
**Responsable:** `00-orchestrator`
**Especialistas:** `03-auth-tenancy-rbac`, `04-frontend-pwa`, `11-security`, `12-qa`

## Objetivo

Entregar el primer incremento vertical de identidad: inicio/cierre de sesión por cookie segura, verificación de email, recuperación de contraseña, consulta del usuario autenticado, selección explícita de academia activa y perfil básico. Toda ruta privada debe denegar por defecto y resolver el tenant desde una membresía vigente del usuario.

## Alcance

- API JSON versionada sobre el guard `web`, protegida por CSRF y rate limits.
- Login, logout, recuperación/restablecimiento de contraseña y reenvío/verificación de email.
- Sesión regenerada al autenticar y revocada al salir.
- Listado de membresías vigentes y selección de tenant almacenada en sesión.
- Middleware que establece y limpia `TenantContext` por petición sin aceptar un tenant arbitrario del cliente.
- Perfil mínimo de nombre/email y estado de verificación.
- Pantallas accesibles de login, recuperación, selección de academia y perfil autenticado.
- Proxy same-origin `/api` en desarrollo y contenedores.

## No objetivos

- Registro público, invitaciones, 2FA, administración de usuarios o edición de roles.
- Grupos, clases, biblioteca, media o comunidad.
- Dominios personalizados y white label completos.

## Contrato inicial

- `POST /api/v1/auth/login`
- `POST /api/v1/auth/logout`
- `POST /api/v1/auth/forgot-password`
- `POST /api/v1/auth/reset-password`
- `POST /api/v1/auth/email/notification`
- `GET /api/v1/auth/email/verify/{id}/{hash}`
- `GET /api/v1/me`
- `PATCH /api/v1/me`
- `GET /api/v1/tenants`
- `PUT /api/v1/tenant`

Los nombres concretos podrán ajustarse a convenciones Laravel sin cambiar la semántica.

## Aceptación y pruebas

- Credenciales inválidas no enumeran usuarios y quedan limitadas por frecuencia.
- Login regenera la sesión; logout invalida sesión y regenera CSRF.
- Rutas privadas rechazan invitado y usuario no verificado según corresponda.
- Tenant inexistente, ajeno, inactivo, expirado o finalizado es rechazado sin fuga.
- Usuario multi-academia puede alternar solo entre membresías vigentes.
- Una petición nueva no hereda `TenantContext` de otra.
- Reset y verificación usan tokens/firmas Laravel y respuestas no enumerables.
- Frontend cubre estados cargando, error, sesión expirada y selección de tenant.
- Pest, PHPStan, Pint, Vitest, ESLint, TypeScript, build y Playwright aplicables pasan.

## Riesgos

- Fijación de sesión, CSRF, enumeración de cuentas, fuerza bruta e IDOR tenant.
- Cookies inseguras fuera de HTTPS y contexto tenant retenido por procesos persistentes.
- Datos privados en caché PWA; las respuestas `/api` deben conservar `no-store`.

## Dirección visual

- **Tesis visual:** acceso editorial cálido y sobrio, conectado con el archivo vivo de danza y sin apariencia de panel SaaS genérico.
- **Contenido:** identidad de academia, formulario directo, ayuda contextual y acción primaria única.
- **Interacción:** entrada breve del formulario, transición compartida entre acceso/recuperación y confirmación clara al cambiar de academia.

