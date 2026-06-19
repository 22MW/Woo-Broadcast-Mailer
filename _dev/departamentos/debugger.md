# Debugger

## Última actualización

2026-06-19

## Resumen humano

Diagnósticos activos cerrados: fallback HPOS para destinatarios por producto, A4 preview al programar y visibilidad/aplicación de títulos, headings y subjects en Email String Editor.

## Descubierto

- WooCommerce/MCP devolvió pedidos de prueba `655`, `656`, `711` con emails de facturación.
- El producto `380` existe y aparece en el pedido `655`.
- HPOS lookup podía devolver `0 emails` aunque los pedidos tuvieran `line_items` válidos.
- A4 marcaba preview como obsoleta al activar programación o cambiar fecha/hora aunque no cambiaban destinatarios.
- El Email String Editor solo escaneaba strings estáticos dentro de funciones de traducción.
- Los H1 de WooCommerce se imprimen desde `$email_heading` en `email-header.php`, no como string traducible directo.
- WooCommerce expone filtros dinámicos `woocommerce_email_heading_{id}` y `woocommerce_email_subject_{id}`.

## Hecho

- Aplicado fallback HPOS: si lookup no devuelve destinatarios, se escanean pedidos por `line_items`.
- A4 corregido: programación y fecha/hora no invalidan preview.
- Email String Editor ampliado para listar `email_title`, `email_heading` y `email_subject` desde el objeto `WC_Email` asociado a cada plantilla.
- Añadidos filtros para aplicar overrides a headings y subjects dinámicos de WooCommerce.
- Añadido marcador interno `__pbm_hidden__` para ocultar strings sin usar espacios ni hacks visuales.

## Pendiente

- QA funcional completo de envíos reales.
- Confirmar updater en staging.
- Validar en navegador que el Editor de emails ya encuentra títulos/H1/subjects.
- Validar con email real que el override de heading/subject se aplica.
- Validar con email real que `Ocultar este texto` renderiza vacío y se puede revertir.

## Riesgos o bloqueos

- QA funcional puede crear envíos/logs/acciones programadas.
- Algunos headings/subjects pueden contener variables formateadas por WooCommerce; si cambia el texto final, el override debe coincidir con el original mostrado.

## Próximo paso recomendado

- Abrir `WooCommerce > Editor de emails`, buscar por título/H1 esperado y confirmar que aparece.
- Guardar un override de heading en entorno controlado y enviar email de prueba.

## No volver a investigar

- Los pedidos de prueba existen, están en `processing` y tienen emails de facturación.
- El producto `380` existe y aparece en el pedido `655`.
- El fallo HPOS estaba en lookup sin fallback.
- El H1 del email WooCommerce viene de `$email_heading`.
- Para headings/subjects hay que usar datos del objeto `WC_Email` y filtros dinámicos WooCommerce.

## Evidencia revisada

- `includes/functions-products.php`
- `includes/ajax-handlers.php`
- `src/admin/App.js`
- `includes/email-string-editor/class-template-scanner.php`
- `includes/email-string-editor/class-email-string-editor.php`
- `includes/email-string-editor/class-gettext-filter.php`
- WooCommerce `templates/emails/email-header.php`
- WooCommerce `includes/emails/class-wc-email.php`
