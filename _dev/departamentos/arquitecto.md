# Arquitecto

## Última actualización

2026-06-18

## Resumen humano

Arquitectura principal confirmada: React admin + AJAX + Action Scheduler + tablas propias. Plan A ya está aplicado. Email String Editor E1-E3 queda implementado como módulo propio, admin clásico y submenú WooCommerce, sin activar todavía aplicación `gettext` real en emails.

## Descubierto

- Flujo principal confirmado: React construye audiencia, AJAX resuelve/envía, PHP programa lotes y Action Scheduler ejecuta `wp_mail()`.
- Admin React convive con formulario legacy oculto para `wp_editor`, selectores y hidden inputs.
- Hooks principales: `plugins_loaded`, `admin_menu`, `admin_enqueue_scripts`, `admin_post_pbm_save_settings`, `admin_init`, AJAX admin y Action Scheduler.
- Action Scheduler usa dos niveles: `pbm_execute_scheduled_email` y `pbm_process_email_batch`.
- Datos propios: tablas `pbm_scheduled_emails` y `pbm_scheduled_logs`; options `pbm_delivery_meta_{id}` y `pbm_scheduled_recipients_{id}`.
- Integraciones opcionales: WooCommerce Subscriptions, WPML y Mail Mint.
- Mail Mint se integra por tablas internas, no por API pública confirmada.
- Email String Editor se incorporó en `includes/email-string-editor/` con clases separadas.

## Hecho

- Revisión de arquitectura completada.
- Plan A aplicado y consolidado.
- Email String Editor E1-E3 implementado:
  - cargador `includes/email-string-editor.php`;
  - coordinador `Email_String_Editor`;
  - scanner de plantillas permitidas;
  - repositorio `pbm_email_string_overrides`;
  - compatibilidad de lectura con `wc_custom_email_strings`;
  - resolver de idiomas;
  - pantalla admin MVP;
  - guardado y borrado con `admin-post` prefijado.

## Pendiente

- QA admin de Email String Editor E1-E3.
- Confirmar hooks WooCommerce seguros para activar contexto email antes de E4.
- Implementar E4 solo cuando esté claro cómo limitar `gettext` a emails WooCommerce.
- Confirmar si `render_scheduled_emails_tab()` y `ajax_create_scheduled_email()` siguen en uso.
- Revisar integración Mail Mint contra documentación/API real si se amplía.

## No volver a investigar

- Arquitectura confirmada: React admin + AJAX + Action Scheduler + tablas propias.
- Tablas propias confirmadas: `pbm_scheduled_emails` y `pbm_scheduled_logs`.
- Hooks Action Scheduler confirmados: `pbm_execute_scheduled_email` y `pbm_process_email_batch`.
- Updater GitHub Releases confirmado en `includes/updater.php`.
- Email String Editor E1-E3 está integrado como módulo propio; no se copió el plugin heredado tal cual.

## Riesgos o bloqueos

- E4 es el riesgo principal: `gettext` global puede afectar checkout/admin si no se limita al contexto email.
- Resolver idioma real del email sigue pendiente para sitios WPML/Polylang.
- QA funcional puede crear envíos/logs/acciones; requiere permiso separado.

## Próximo paso recomendado

- QA admin de E1-E3.
- Después decidir E4 con hook seguro de contexto email.
