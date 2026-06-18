# Analista

## Última actualización

2026-06-19

## Resumen humano

Análisis funcional realizado sobre Email String Editor y sobre posible aplicación de 22MW-BACK al admin completo. 22MW-BACK es posible, pero recomendado por piloto controlado, no como rediseño entero de golpe.

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
- Evaluado 22MW-BACK para Woo Broadcast Mailer: posible, tamaño medio-alto, recomendado por fases.
- Recomendación 22MW-BACK: empezar por piloto en Broadcast React principal y extender después al Editor de emails React si valida bien.

## Pendiente

- Decidir si el piloto 22MW-BACK empieza por Broadcast principal o por Editor de emails React.
- Decidir si dark/light entra en el piloto.
- Decidir si el rediseño será solo visual o también reorganización funcional de menús.
- Decidir si Woo Broadcast Mailer será referencia reusable 22MW-BACK para otros plugins.
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
- Si se aprueba 22MW-BACK, hacer Fase 0 de inventario de pantallas y luego piloto visual en Broadcast React principal.
