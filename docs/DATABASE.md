# Diseño de base de datos

## Convenciones

PostgreSQL es la fuente de verdad. Tablas tenant-owned contienen `organization_id`, timestamps UTC e índices cuyo primer componente suele ser el tenant. IDs internos pueden ser bigint/UUID/ULID según el scaffold; la elección se fijará de manera uniforme antes de migraciones. Slugs/IDs públicos nunca reemplazan autorización.

## Esquema lógico inicial

| Área | Tablas previstas |
|---|---|
| Plataforma | `organizations`, `organization_domains`, `organization_settings`, `branches` |
| Identidad | `users`, `organization_memberships`, `roles`, `permissions`, `role_assignments`, `invitations`, `sessions`, `two_factor_methods` |
| Catálogo/grupos | `dance_styles`, `levels`, `groups`, `group_settings`, `group_membership_periods`, `group_responsibilities` |
| Clases/biblioteca | `dance_classes`, `class_teachers`, `class_content`, `practice_assignments`, `educational_contents`, `content_relations`, `tags`, `taggables`, `group_content_assignments` |
| Media | `media_assets`, `media_versions`, `media_variants`, `media_links`, `upload_sessions`, `media_processing_jobs`, `favorites` |
| Comunidad | `posts`, `comments`, `likes`, `user_content_states`, `reports`, `moderation_actions` |
| Notificaciones | `notifications`, `notification_preferences`, `push_subscriptions`, `notification_deliveries` |
| Gamificación | `contribution_events`, `reputation_entries`, `badge_definitions`, `badge_awards`, `learning_achievements`, `recognition_entries` |
| Eventos/comercio | `events`, `albums`, `album_audiences`, `professional_media_profiles`, `creators`, `sales`, `sale_items`, `entitlements`, `royalty_rule_snapshots`, `royalty_transactions`, `payouts`, `refunds` |
| Operación | `audit_logs`, outbox/jobs según framework |

Los nombres son diseño, no migraciones ejecutadas; pueden afinarse mediante ADR/tareas antes de implementación.

## Restricciones e índices esenciales

- FK compuestas o validación equivalente que impida referencias entre tenants; constraints únicas incluyen `organization_id`.
- `likes`: único `(organization_id, user_id, resource_type, resource_id)`.
- versión: único `(organization_id, media_asset_id, version_number)` y máximo una actual mediante índice único parcial `WHERE is_current`.
- membresías: índices por `(organization_id, user_id, starts_at, ends_at)` y `(organization_id, group_id, status)`; solapamiento se valida según política.
- procesamiento/delivery/contribución: claves únicas de idempotencia.
- búsquedas: índices por tenant/estado/fecha; PostgreSQL FTS/trigram se evaluará antes de motor externo.
- ledger/auditoría: append-only desde permisos de aplicación; correcciones como entradas compensatorias.

## Eliminación y retención

Soft delete/archivo se usa para contenido recuperable; no debe ocultar automáticamente la necesidad de filtrar tenant. Auditoría y transacciones contables no se reescriben. La retención de datos personales/media será configurable y sujeta a política/legal; anonimización o eliminación se diseña sin destruir obligaciones contables.

## Migraciones y seed

Migraciones pequeñas, ordenadas, probadas hacia arriba y abajo cuando sea seguro. No editar migraciones compartidas. El seed demo crea dos organizaciones y solo corre en `local/testing`. La reconstrucción limpia y los rollbacks pasan en SQLite; su repetición sobre PostgreSQL 17 está configurada en CI y sigue pendiente de ejecución externa verificable.
