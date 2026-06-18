# Arquitecto

## Última actualización

2026-06-18

## Resumen humano

Revisión técnica de arquitectura realizada en modo completo y solo lectura. El plugin tiene una arquitectura funcional basada en React admin, AJAX server-side, Action Scheduler y tablas propias. Los riesgos principales están en la semántica de estados, dependencia no bloqueante de Action Scheduler y convivencia de flujo legacy con React.

## Descubierto

- Flujo principal confirmado: React construye audiencia, AJAX resuelve/envía, PHP programa lotes y Action Scheduler ejecuta `wp_mail()`.
- Admin React convive con formulario legacy oculto para `wp_editor`, selectores y hidden inputs.
- Hooks principales: `plugins_loaded`, `admin_menu`, `admin_enqueue_scripts`, `admin_post_pbm_save_settings`, `admin_init`, AJAX admin y Action Scheduler.
- Action Scheduler usa dos niveles: `pbm_execute_scheduled_email` y `pbm_process_email_batch`.
- Datos propios: tablas `pbm_scheduled_emails` y `pbm_scheduled_logs`; options `pbm_delivery_meta_{id}` y `pbm_scheduled_recipients_{id}`.
- Integraciones opcionales: WooCommerce Subscriptions, WPML y Mail Mint.
- Mail Mint se integra por tablas internas, no por API pública confirmada.

## Hecho

- Revisión de arquitectura completada sin escribir archivos ni tocar WordPress/BD.
- Se identificaron riesgos priorizados para roadmap técnico.

## Pendiente

- Validar en entorno real que Action Scheduler esté siempre disponible al ejecutar AJAX.
- Confirmar si `render_scheduled_emails_tab()` y `ajax_create_scheduled_email()` siguen en uso.
- Confirmar que `build/` está sincronizado con `src/`.
- Revisar integración Mail Mint contra documentación/API real si se amplía.

## No volver a investigar

- Arquitectura confirmada: React admin + AJAX + Action Scheduler + tablas propias.
- Tablas propias confirmadas: `pbm_scheduled_emails` y `pbm_scheduled_logs`.
- Hooks Action Scheduler confirmados: `pbm_execute_scheduled_email` y `pbm_process_email_batch`.
- Updater GitHub Releases confirmado en `includes/updater.php`.

## Riesgos o bloqueos

- Action Scheduler no se valida como dependencia dura antes de confirmar éxito.
- `completed` puede significar lotes programados, no entrega final.
- El snapshot `pbm_scheduled_recipients_{id}` se borra al iniciar ejecución; si falla la programación posterior, se pierde.
- Código legacy visual convive con React y puede confundir mantenimiento.
- Archivos grandes aumentan riesgo de regresión en futuras mejoras.

## Próximo paso recomendado

- Primero corregir dependencia explícita de Action Scheduler y modelo de estados.
- Después QA funcional controlado.
- No refactorizar archivos grandes sin tarea específica aprobada.
