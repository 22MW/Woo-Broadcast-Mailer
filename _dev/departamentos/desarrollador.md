# Desarrollador

## Última actualización

2026-06-18

## Resumen humano

Implementados A1, A5 y A6 del Plan A. El plugin ahora comprueba Action Scheduler antes de programar envíos, escapa el nombre del destinatario antes de insertarlo en emails HTML y bloquea el borrado de envíos activos por ID.

## Descubierto

- Action Scheduler está presente en el entorno local por MCP WooCommerce.
- El código anterior comprobaba `function_exists('as_schedule_single_action')`, pero podía devolver éxito aunque no se programara acción.
- `send_single_email()` insertaba `{customer_name}` sin escape contextual.
- El borrado individual y por IDs dependía de la visibilidad del frontend, pero el backend no verificaba estado antes de borrar.

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

### A5

- `send_single_email()` valida el email destino con `sanitize_email()` e `is_email()`.
- `{customer_name}` se sustituye con nombre escapado mediante `esc_html()`.

### A6

- Añadido `can_delete_scheduled_email()`.
- `delete_scheduled_email_with_logs()` ya no borra si el envío no está en `completed` o `cancelled`.
- `ajax_delete_scheduled_email()` bloquea borrado individual de envíos no borrables.
- `ajax_bulk_delete_scheduled_ids()` bloquea el lote completo si incluye algún ID no borrable o inexistente.

## Pendiente

- QA funcional real del aviso admin y rutas AJAX.
- Prueba específica con caracteres HTML en nombre de destinatario.
- QA de borrado con registros `completed`, `cancelled`, `pending` y `running`.
- A2 estado `completed` ambiguo.
- A3 snapshot seguro.
- A4 preview obsoleto.
- A7 ZIP/release.

## No volver a investigar

- A1 implementado: Action Scheduler obligatorio y aviso admin.
- A5 implementado: escape de `{customer_name}` y validación de email destino.
- A6 implementado: borrado por ID limitado a `completed` y `cancelled`.
- No se ejecutaron envíos ni acciones programadas durante estas implementaciones.

## Riesgos o bloqueos

- QA funcional puede crear envíos/logs/acciones; requiere permiso separado.
- Si Action Scheduler existe parcialmente con funciones disponibles pero falla al programar internamente, eso requerirá validación funcional posterior.

## Próximo paso recomendado

- Continuar con A2 o A3.
