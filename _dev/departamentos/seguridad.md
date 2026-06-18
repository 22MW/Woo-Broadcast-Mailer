# Seguridad

## Última actualización

2026-06-18

## Resumen humano

Revisión de seguridad realizada en modo completo y solo lectura. La base de seguridad admin es razonable. A5 y A6 ya fueron aplicados: el nombre del destinatario se escapa antes de insertarse en email HTML y el borrado por ID queda limitado a envíos completados o cancelados.

## Descubierto

- Todos los handlers AJAX revisados usan `check_ajax_referer()` y `current_user_can('manage_woocommerce')`.
- No se detectaron endpoints REST en los archivos revisados.
- Los archivos PHP revisados bloquean acceso directo con `ABSPATH`, salvo `uninstall.php`, que usa `WP_UNINSTALL_PLUGIN`.
- SQL con entrada dinámica usa `prepare()`, whitelists o casteos.
- Logs HTML se escapan en PHP antes de insertarse en React, pero React usa `dangerouslySetInnerHTML`.

## Hecho

- Revisión de nonces, capabilities, sanitización, escaping, SQL, AJAX, updater, uninstall y React completada en solo lectura.
- A5 aplicado: `send_single_email()` valida email destino y escapa `{customer_name}` con `esc_html()` antes de insertarlo en HTML.
- A6 aplicado: borrado individual y por IDs bloqueado para estados distintos de `completed` o `cancelled`.

## Pendiente

- Sustituir logs HTML por JSON renderizado en React o aplicar whitelist estricta si se mantiene HTML.
- Validar host/HTTPS del paquete descargado por updater.
- Añadir límites server-side máximos a `batch_size` y `emails_per_hour`.
- QA de A6 con registros completados, cancelados, pendientes y en ejecución.

## No volver a investigar

- AJAX admin está protegido por nonce y capability en los handlers revisados.
- `uninstall.php` solo borra si existe constante/opción de borrado y está protegido por `WP_UNINSTALL_PLUGIN`.
- No se detectaron rutas REST.
- A5 ya está aplicado.
- A6 ya está aplicado.

## Riesgos o bloqueos

- Hardening: `dangerouslySetInnerHTML` depende de que el HTML del endpoint siga siempre escapado.
- Hardening: updater debería validar URL/host de paquete.

## Próximo paso recomendado

- Continuar con A2/A3 o hacer QA controlado si se autoriza.
