# Arquitecto

## Última actualización

2026-06-29

## Relevo breve

Arquitectura LOG2/LOG3 preparada. No se implementó código. La solución mínima conserva tablas actuales, añade metadatos en `pbm_delivery_meta_{id}` y un option de eventos por destinatario para no requerir migración.

## Hecho en esta tarea

- Hecho confirmado por código leído:
  - `pbm_scheduled_logs` solo guarda totales por lote (`total_sent`, `total_failed`, `error_message`), no emails concretos.
  - `process_email_batch()` solo cuenta `sent/failed`; `send_single_email()` devuelve `bool` de `wp_mail()`.
  - `maybe_complete_scheduled_email()` borra `pbm_scheduled_recipients_{id}` al completar; el snapshot de destinatarios no queda como histórico estable.
  - `build_global_audience_meta()` solo guarda conteos por fuente; por eso el resumen actual no puede mostrar nombres.
- LOG2 recomendado sin migración:
  - Añadir en `pbm_delivery_meta_{id}` un `audience_snapshot` descriptivo, no operativo.
  - Estructura mínima: `mode`, `created_at`, `preview_count`, `final_count`, `items[]`, `manual_count`, `excluded_count`, `excluded_emails_count` y `summary`.
  - Cada item: `source`, `selector_value`, `source_label`, `selector_label`, `count`, `status` (`available`/`missing`/`unknown`).
  - Para `dynamic`, guardar snapshot de preview al programar y actualizar `final_count`/`final_items` al ejecutar si se recalcula.
  - Para históricos sin `audience_snapshot`, mantener fallback actual de `get_delivery_audience_label()`.
- Construcción de labels:
  - Preferir reconstrucción en PHP como fuente de verdad al guardar, usando datos sanitizados y APIs nativas.
  - React puede enviar `sourceLabel`/`selectorLabel` como ayuda solo si se sanitiza y se trata como fallback; no confiar en labels del cliente para persistencia definitiva.
  - Mejorar helper equivalente a `get_source_audience_label()` para roles con `wp_roles()->get_names()`, productos con `get_the_title()`, Broadcast Lists con su storage actual y Mail Mint si hay lista disponible; si no, usar fallback `Fuente #ID`.
- LOG3 recomendado sin migración:
  - Crear option no autoload `pbm_delivery_events_{id}` con eventos por destinatario.
  - Registrar eventos desde `process_email_batch()` después de cada `send_single_email()`.
  - Evento mínimo: `email`, `status` (`sent`/`failed`), `timestamp`, `error`, `batch_index` opcional.
  - Error MVP: si `wp_mail()` devuelve `false`, registrar `wp_mail devolvió false`; no guardar cuerpo del email.
  - Retención: borrar `pbm_delivery_events_{id}` junto con `delete_scheduled_email_with_logs()` y borrados masivos.

## Pendiente / riesgos

- Pendiente confirmar estructura/storage exacto de Broadcast Lists y Mail Mint si se quieren labels reales más allá del fallback.
- `wp_mail()` no devuelve causa técnica fiable; capturar `wp_mail_failed` con contexto por destinatario queda como mejora posterior, no MVP obligatorio.
- Guardar eventos por destinatario aumenta datos personales almacenados; requiere política de retención aceptada antes de producción.
- Si el volumen crece mucho, el option por envío puede quedarse corto; entonces convendría tabla propia o columna JSON vía `dbDelta`, fuera del MVP mínimo.

## No volver a investigar

- Action Scheduler ya tiene dos fases: `pbm_execute_scheduled_email` y `pbm_process_email_batch`.
- El completado depende de snapshot/logs; por eso el modo dinámico debe crear snapshot final justo antes de programar lotes.
- Manuales son fijos; exclusiones se aplican al resultado final.
- Para LOG3 no existe log por email en la tabla actual; solo totales por lote.
- Para LOG2 el metadato actual `global.sources` solo permite conteos, no nombres/labels.

## Relevo para

→ Desarrollador, si se aprueba implementación LOG2/LOG3: implementar `audience_snapshot` en delivery meta, fallback histórico, `pbm_delivery_events_{id}` por destinatario, borrado de eventos al eliminar envíos y endpoint/UI de lectura básica.
