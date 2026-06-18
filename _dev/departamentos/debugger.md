# Debugger

## Última actualización

2026-06-19

## Resumen humano

QA funcional reportó inicialmente que con 3 pedidos de prueba con varios productos, el plugin mostraba `0 emails`. Nuevo bug A4: al tocar “Programar envío”, la preview se marcaba obsoleta sin cambiar destinatarios.

## Descubierto

- WooCommerce/MCP devuelve 3 pedidos recientes en el rango consultado: `655`, `656`, `711`.
- Los 3 pedidos están en `processing`.
- Los pedidos tienen `billing_email` populated.
- El pedido `655` contiene el producto `380` (`IVA - 4`) en `line_items`.
- El producto `380` existe y está publicado.
- El flujo del plugin para producto usa `get_recipients_from_orders()`.
- Si HPOS está activo, `get_recipients_from_orders()` delega directamente en `get_recipients_from_order_lookup()` y retorna ese resultado.
- `get_recipients_from_order_lookup()` primero obtiene IDs desde `wp_wc_order_product_lookup`. Si esa tabla no devuelve IDs, retorna `0 emails` sin escanear los `line_items` reales del pedido.

## Hecho

- Revisado `includes/functions-products.php`.
- Revisado `includes/ajax-handlers.php`.
- Consultados pedidos recientes por MCP WooCommerce.
- Consultados productos `380`, `381`, `382` por MCP WooCommerce.
- Aplicado fallback HPOS: si `get_recipients_from_order_lookup()` no devuelve destinatarios, se escanean pedidos por `line_items`.

## Hipótesis probable

- La causa más probable es que HPOS esté activo y la tabla `wp_wc_order_product_lookup` esté vacía, incompleta o no sincronizada con los pedidos de prueba.
- Hipótesis secundaria: filtro WPML del pedido excluye pedidos si `wpml_language` del pedido no coincide con el idioma del producto seleccionado.

## Pendiente

- Confirmar si HPOS está activo en el sitio.
- Confirmar si `wp_wc_order_product_lookup` contiene filas para los pedidos `655`, `656`, `711`.
- Confirmar idioma del producto y meta `wpml_language` de pedidos si existe WPML activo.

## Riesgos o bloqueos

- La consulta directa a la tabla lookup requiere validación de solo lectura en entorno local.
- No se han ejecutado envíos ni acciones programadas.

## Próximo paso recomendado

- Validar manualmente A4: previsualizar, activar/desactivar programación y cambiar fecha/hora sin que aparezca aviso de preview obsoleta.
- Validar que cambiar audiencia, manuales, lote o emails por hora sí invalida preview.

## No volver a investigar

- Los pedidos de prueba existen, están en `processing` y tienen emails de facturación.
- El producto `380` existe y aparece en el pedido `655`.
- El punto sensible estaba en el flujo HPOS/lookup, no en la ausencia visible de emails de pedido.
- Fix aplicado en `includes/functions-products.php`.
- A4: la causa confirmada estaba en `src/admin/App.js`; `buildPreviewSignature()` incluía `scheduleEnabled` y `scheduledDatetime`.

## Evidencia revisada

- `includes/functions-products.php`
- `includes/ajax-handlers.php`
- MCP `wc_orders_list`
- MCP `wc_product_get` para productos `380`, `381`, `382`
