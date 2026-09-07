# Modelo de dominio

## Agregados y relaciones

- **Organization/Academy**: tenant, branding, contacto, política de acceso, dominios y sedes.
- **User** es identidad global; **OrganizationMembership** vincula usuario y tenant con estado/expiración. Roles y permisos se asignan en tenant y, cuando aplica, en grupo.
- **Branch**, **DanceStyle**, **Level** son catálogos configurables del tenant. Nivel no equivale a grupo.
- **Group** combina propósito, sede/estilo/nivel opcionales y settings. **GroupMembershipPeriod** conserva intervalos, responsabilidad y motivo; admite múltiples grupos y promociones.
- **DanceClass** registra fecha, docentes, notas, contenido cubierto, media y prácticas; no desaparece al cambiar membresía.
- **EducationalContent** representa paso, figura, secuencia, coreografía o técnica; admite tags, relaciones, asignaciones y demostraciones.
- **MediaAsset** describe el objeto lógico y propósito; **MediaVersion** conserva cada versión/estado; **MediaVariant** apunta a original, preview, thumbnail, HLS o calidad.
- **Post**, **Comment**, **Like**, **UserContentState** y **Report/ModerationAction** forman la comunidad privada.
- **Notification** y **PushDelivery** separan evento visible de intentos de transporte.
- **ContributionEvent**, **ReputationEntry**, **BadgeAward** y **LearningAchievement** mantienen reputación separada del aprendizaje.
- **Event** contiene **Album** y media con audiencia; **ProfessionalMediaProfile** agrega creador, derechos, precio y royalties sin bifurcar foto/vídeo.
- **Sale/Purchase**, **SaleItem**, **RoyaltyRuleSnapshot**, **RoyaltyTransaction**, **Payout** y **Refund** forman el ledger comercial.
- **AuditLog** registra acciones administrativas/seguridad inmutables.

## Invariantes clave

1. Relaciones entre entidades tenant-owned solo apuntan al mismo `organization_id`.
2. Una membresía expirada bloquea contenido protegido, pero no borra historia.
3. Como máximo una versión de media está marcada actual por asset; reemplazar archiva, restaurar crea/transiciona sin borrar evidencia.
4. Un Like por usuario/recurso/tenant; reversible.
5. Upload estudiantil no es visible hasta aprobación si la política lo exige; reputación nace de aprobación, no del upload bruto.
6. Un moderador actúa solo en su alcance; puntos jamás otorgan permisos.
7. Original profesional permanece privado; una compra aprobada concede un entitlement auditable, no una URL permanente pública.
8. Reglas de royalty se capturan al vender; cambios futuros no recalculan historia.

## Estados relevantes

- Acceso: `active`, `inactive`, `reactivation_requested`; fechas `access_expires_at`, `reactivated_at`.
- Media: `initiated`, `uploading`, `uploaded`, `quarantined`, `processing`, `pending_review`, `approved`, `rejected`, `failed`, `archived`.
- Membresía de grupo: `pending`, `active`, `ended`, `suspended` con intervalos.
- Compra: `requested`, `pending_payment`, `approved`, `rejected`, `refunded`.
- Moderación: `open`, `under_review`, `resolved`, `dismissed`.

## Términos que no deben confundirse

- Sesión de navegador ≠ acceso/membresía de academia.
- Nivel ≠ grupo.
- Media comunitaria ≠ media profesional.
- Logro de aprendizaje ≠ reputación de contribución.
- Asset lógico ≠ versión ≠ variante física.
