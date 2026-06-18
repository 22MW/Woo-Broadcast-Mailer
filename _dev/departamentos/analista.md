# Analista

## Última actualización

2026-06-18

## Resumen humano

Análisis funcional realizado sobre `_dev/_md/WooEmailStringEditor.php` para posible incorporación en Woo Broadcast Mailer. La funcionalidad es útil, pero debe entrar como módulo planificado, no copiando el plugin tal cual.

## Descubierto

- El archivo permite editar strings traducibles de emails WooCommerce por idioma.
- Guarda personalizaciones en `wc_custom_email_strings`.
- Aplica cambios mediante `gettext` al dominio `woocommerce`.
- La UX actual se basa en seleccionar plantillas por archivo técnico.
- La edición en tabla multicolumna escala mal con muchos idiomas.
- No hay agrupación por categoría, plugin origen, plantilla, idioma activo ni estado de personalización.
- Multiidioma actual depende de `get_locale()` y no confirma WPML/Polylang ni idioma real del pedido/email.

## Hecho

- Documento resumen creado: `_dev/incorporacion-email-string-editor.md`.
- Propuesta funcional definida en fases.
- MVP recomendado: submenú separado, idioma activo, selector por categoría/origen/plantilla y compatibilidad con datos existentes.

## Pendiente

- Decidir si se integra como submenú o pestaña React.
- Decidir si debe afectar solo emails WooCommerce o todo dominio `woocommerce`.
- Decidir idioma dominante en emails multiidioma.
- Decidir si se mantiene `wc_custom_email_strings` o se migra a `pbm_email_string_overrides`.
- Decidir si primera fase cubre solo WooCommerce core o también plugins detectados.

## No volver a investigar

- `WooEmailStringEditor.php` es plugin independiente, no módulo integrado.
- La funcionalidad actual útil es edición de strings de emails WooCommerce por idioma.
- El riesgo funcional principal es que `gettext` puede afectar WooCommerce globalmente, no solo emails.

## Riesgos o bloqueos

- Incorporarlo tal cual puede cambiar strings fuera de emails.
- Multiidioma puede aplicar idioma incorrecto si el email no usa `get_locale()`.
- La UX actual no es adecuada para muchos strings/idiomas.
- No se debe implementar sin cerrar decisiones funcionales.

## Próximo paso recomendado

- Cerrar decisiones pendientes antes de arquitectura definitiva e implementación.
