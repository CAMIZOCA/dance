# Media profesional, compras y royalties

## Modelo unificado

Foto y vídeo usan `MediaAsset`/versiones/variantes comunes. Un perfil profesional añade creador, titular de copyright, créditos, derechos de uso, resolución, estado comercial, precio/moneda, watermark y royalty rule. Puede ser gratis sin perder atribución/derechos.

## Variantes y acceso

- Original: privado, nunca URL pública estable.
- Preview: menor tamaño/bitrate y watermark configurable.
- Thumbnail: pequeño para listados.
- Producto autorizado: descarga/stream temporal según entitlement, derechos y expiración.

Álbum/evento define audiencia por academia, grupos o usuarios. Acceder a preview tampoco evita tenant y audiencia.

## Compra manual inicial

Usuario pulsa Comprar/Solicitar → se crea solicitud con ID estable → enlace de WhatsApp/contacto prellenado sin datos sensibles → administración gestiona pago → persona autorizada aprueba → se crea venta, items y entitlement → usuario accede mediante URL temporal. La operación se audita y es idempotente. El diseño deja un puerto de checkout para futura automatización.

## Ledger de royalties

Al aprobar una venta se captura una `RoyaltyRuleSnapshot`; cada item genera entradas inmutables para creador, academia y plataforma. Balance = créditos − refunds/ajustes − payouts, derivado de ledger. No guardar un saldo mutable como verdad ni recalcular ventas antiguas al cambiar porcentajes.

Ejemplo conceptual $10 = creador $7 + academia $2 + plataforma $1; es ilustrativo y configurable, no regla predeterminada confirmada. Redondeo, moneda, impuestos y fees deben definirse antes de comercio real.

Refund crea entradas compensatorias y ajusta/revoca entitlement según política. Payout agrupa transacciones elegibles y conserva período, método, referencia, actor y estado.

## Derechos, privacidad y seguridad

Registrar licencia/uso comercial y consentimiento/imagen donde corresponda; no afirmar propiedad por el mero upload. Separar capacidad de cambiar precio, aprobar compra y aprobar payout. URLs cortas, storage privado, logs minimizados y auditoría de precio, venta, entitlement, descarga y refund.

## Pruebas críticas

Original inaccesible sin entitlement; preview correcto; usuario/tenant/grupo ajeno denegado; doble aprobación no duplica ledger; regla posterior no altera historia; refund compensa; payout no excede elegible; cambio de precio auditado y concurrencia segura.
