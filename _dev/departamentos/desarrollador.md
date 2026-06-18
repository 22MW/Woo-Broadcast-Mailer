# Desarrollador

## Última actualización

2026-06-18

## Resumen humano

Plan A completo aplicado: A1-A7. Email String Editor E1-E3 implementado como MVP admin seguro. A4 fue compilado con Node local y `build/` quedó sincronizado con `src/admin/App.js`.

## Descubierto

- Action Scheduler está presente en el entorno local por MCP WooCommerce.
- El código anterior comprobaba `function_exists('as_schedule_single_action')`, pero podía devolver éxito aunque no se programara acción.
- `send_single_email()` insertaba `{customer_name}` sin escape contextual.
- El borrado individual y por IDs dependía de la visibilidad del frontend, pero el backend no verificaba estado antes de borrar.
- El estado `completed` se marcaba justo después de programar lotes, no al finalizar ejecución real.
- El snapshot `pbm_scheduled_recipients_{id}` se borraba al leerlo, antes de confirmar que los lotes se programaron/ejecutaron.
- La preview podía quedar obsoleta si cambiaba audiencia/configuración antes de enviar.
- El workflow release no excluía `_dev/`.
- Email String Editor debía integrarse como módulo propio, no copiando el plugin heredado tal cual.

## Hecho

### A1

- Añadidos helpers:
  - `is_action_scheduler_available()`
  - `get_action_scheduler_status_message()`
  - `get_action_scheduler_unavailable_message()`
- Añadido estado visible de Action Scheduler en el admin del plugin.
- `schedule_email_batches()` devuelve `0` si Action Scheduler no está disponible y ya no intenta programar parcialmente dentro del bucle.
- `ajax_send_broadcast()` bloquea antes de crear envíos si Action Scheduler no está disponible.
- Envío programado e instantáneo usan la comprobación centralizada.
- `ajax_create_scheduled_email()`, `ajax_cancel_scheduled_email()` y `ajax_run_scheduled_now()` bloquean si Action Scheduler no está disponible.

### A2

- El envío instantáneo ya no cambia a `completed` tras programar lotes.
- El envío programado ya no cambia a `completed` tras programar lotes.
- Añadidos helpers:
  - `get_expected_messages_count()`
  - `maybe_complete_scheduled_email()`
- `process_email_batch()` llama a `maybe_complete_scheduled_email()` después de crear cada log.
- Si un envío programado no consigue programar lotes, se registra error y pasa a `cancelled`.

### A3

- `get_scheduled_recipients_snapshot()` ya no borra la opción al leerla.
- El snapshot se conserva durante ejecución.
- `maybe_complete_scheduled_email()` borra `pbm_scheduled_recipients_{id}` al completar.
- `delete_scheduled_email_with_logs()` borra snapshot al eliminar.
- `bulk_delete_scheduled_by_status()` borra snapshot al borrar completados/cancelados.

### A4

- Añadida firma de preview en `src/admin/App.js`.
- La firma cubre audiencia global, emails manuales, lote, emails por hora, programación y fecha.
- Si cambia la audiencia/configuración tras previsualizar, se muestra aviso.
- El botón de envío queda bloqueado si la preview está obsoleta.
- Se compiló `build/` con Node local.

### A5

- `send_single_email()` valida el email destino con `sanitize_email()` e `is_email()`.
- `{customer_name}` se sustituye con nombre escapado mediante `esc_html()`.

### A6

- Añadido `can_delete_scheduled_email()`.
- `delete_scheduled_email_with_logs()` ya no borra si el envío no está en `completed` o `cancelled`.
- `ajax_delete_scheduled_email()` bloquea borrado individual de envíos no borrables.
- `ajax_bulk_delete_scheduled_ids()` bloquea el lote completo si incluye algún ID no borrable o inexistente.

### A7

- `.github/workflows/release.yml` excluye `_dev/` del ZIP/release.

### Email String Editor E1-E3.2 + E2.3

- Añadido cargador `includes/email-string-editor.php`.
- Añadidas clases base bajo `includes/email-string-editor/`.
- Módulo inicializado desde `woo-broadcast-mailer.php`.
- Añadido submenú `WooCommerce > Editor de emails`.
- Añadido selector de idioma, selector de plantilla, buscador y listado de strings.
- Añadida búsqueda global en todas las plantillas permitidas cuando no se elige plantilla.
- Añadida búsqueda multiidioma comparando original, traducciones WooCommerce y personalizaciones guardadas.
- Añadida edición de todos los idiomas disponibles dentro de cada string/email.
- Añadido guardado/borrado en `pbm_email_string_overrides`.
- Añadido guardado multiidioma desde la misma pantalla.
- Añadida edición directa desde la pestaña Cambios guardados.
- Añadida lectura compatible de `wc_custom_email_strings`.
- E4 activado con filtro `gettext`/`gettext_with_context` limitado al contexto de emails WooCommerce.
- La aplicación real usa `woocommerce_email_header` para activar contexto y `woocommerce_email_footer` para desactivarlo.
- El idioma se resuelve desde `wpml_language` del pedido si existe, con fallback a locale actual.

### Bugfix destinatarios producto HPOS

- Aplicado fallback en `get_recipients_from_orders()`.
- Si HPOS está activo pero `get_recipients_from_order_lookup()` no devuelve destinatarios, ahora se escanean pedidos por `line_items`.
- Mantiene la deduplicación por email y el filtro de idioma existente.

## Pendiente

- QA funcional real del aviso admin y rutas AJAX.
- QA de A2 con envío instantáneo, envío programado, ejecución de lotes y logs acumulados.
- QA de A4: preview, cambio de audiencia/configuración y bloqueo de envío.
- Prueba específica con caracteres HTML en nombre de destinatario.
- QA de borrado con registros `completed`, `cancelled`, `pending` y `running`.
- Validar ZIP/release real antes de publicar.
- QA admin de Email String Editor E1-E3.1.
- Validar QA funcional del bugfix HPOS con producto `380` y pedidos `655`, `656`, `711`.
- QA de E4 en email WooCommerce controlado: confirmar que aplica override y no afecta admin/frontend.

## No volver a investigar

- A1 implementado: Action Scheduler obligatorio y aviso admin.
- A2 implementado: `completed` queda ligado a logs acumulados.
- A3 implementado: snapshot se conserva hasta completar/eliminar.
- A4 implementado y compilado: preview obsoleta bloquea envío.
- A5 implementado: escape de `{customer_name}` y validación de email destino.
- A6 implementado: borrado por ID limitado a `completed` y `cancelled`.
- A7 implementado: workflow release excluye `_dev/`.
- Email String Editor E1-E4 implementado; `gettext` queda limitado por contexto de email WooCommerce.
- No se ejecutaron envíos ni acciones programadas durante estas implementaciones.
- Node local disponible en `/Users/22mw/.local/node-install/node-v22.11.0-darwin-arm64/bin`.
- `node_modules/` se copió desde el plugin anterior.

## Riesgos o bloqueos

- QA funcional puede crear envíos/logs/acciones; requiere permiso separado.
- Si Action Scheduler existe parcialmente con funciones disponibles pero falla al programar internamente, eso requerirá validación funcional posterior.

## Próximo paso recomendado

- Ejecutar QA funcional controlado del Plan A completo.
