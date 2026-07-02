# Desarrollador

## Última actualización

2026-07-02

## Resumen humano

Fix mínimo MailPoet aplicado: el selector une listas de API pública con segmentos internos predeterminados/globales activos, sin cambiar resolución ni conteo por API pública.

## Descubierto

- `{customer_name}` dependía de `$recipient['name']`; en emails manuales o listas sin nombre podía quedar vacío.
- El email destino permite buscar un usuario WordPress con `get_user_by('email', $email)` para completar nombre/apellidos.
- TinyMCE ya incluye formatos P/H1/H2/H3/H4; no hacía falta tocar esa parte.
- La toolbar podía mostrar controles de color duplicados si se combinaba configuración custom con toolbar secundaria por defecto.
- El flujo React de envío ya usaba vista previa obligatoria, exclusiones temporales y snapshot para programaciones.
- `pbm_delivery_meta_{id}` permite guardar `audience_mode` y `audience_config` sin migración de base de datos.
- `pbm_scheduled_logs` mantiene totales por lote; el detalle por destinatario queda separado en `pbm_delivery_events_{id}`.
- MailPoet se puede resolver con `\MailPoet\API\API::MP('v1')`, `getLists()`, `getSubscribers()` y `getSubscribersCount()` sin tocar tablas.

## Hecho

- Añadida función `get_broadcast_email_placeholders()`.
- Añadidos shortcodes:
  - `{customer_name}`
  - `{first_name}`
  - `{last_name}`
  - `{email}`
  - `{current_date}`
- `{customer_name}` usa nombre recibido y, si falta, intenta resolver usuario por email.
- `{first_name}` y `{last_name}` salen de meta de usuario cuando existe usuario con ese email.
- `{current_date}` usa `wp_date(get_option('date_format'))`.
- TinyMCE ampliado con fuente, tamaño, color de texto y color de fondo.
- Toolbar secundaria (`toolbar2`) vaciada para evitar control de color separado/duplicado.
- Añadidos estilos inline para `h1` y `h2` en emails.
- Añadido selector Fija/Dinámica solo cuando hay programación y vista previa vigente.
- En modo fijo se mantiene el snapshot actual.
- En modo dinámico se guardan fuentes, emails manuales y exclusiones; al ejecutar se recalcula la audiencia y se sobrescribe el snapshot final antes de crear lotes.
- Programaciones sin `audience_mode` se tratan como fijas.
- LOG2: añadido `audience_snapshot` con modo, conteos, items descriptivos, manuales, exclusiones y resumen legible para envíos instantáneos y programados.
- LOG2: `get_delivery_audience_label()` usa snapshot cuando existe y mantiene fallback para históricos.
- LOG2: en audiencia dinámica se actualiza `final_count`, `final_items`, `final_summary` y timestamp al ejecutar.
- LOG3: añadido registro por destinatario tras cada `send_single_email()` con email, estado, timestamp, error básico y lote.
- LOG3: el modal de logs muestra resumen por lote y eventos por destinatario; el borrado individual/masivo elimina eventos.
- No se tocaron React ni build porque el alcance se resolvió con PHP/AJAX existente.
- Build de `src/admin/App.js` generado en `build/index.js`.
- Validación técnica LOG2/LOG3 ejecutada: `php -l` OK en PHP tocados y `git diff --check` OK.
- Añadido helper/viewport toast en `src/admin/App.js` con estado React, `aria-live`, `role="alert"`, `is-hiding`, 2800/3200 ms y tipos `success`, `error`, `warning`.
- Sustituidos mensajes principales de `App.js` por `showToast`; el éxito de envío retrasa la recarga 900 ms para que el toast sea visible.
- Sustituidos `window.alert()` de error en `ScheduledLogsPanel` por toast compartido.
- `AudienceBuilder` ya no renderiza `Notice` local para esos mensajes.
- Añadido CSS `.pbm-admin-toasts` / `.pbm-admin-toast` en `assets/css/admin.css` y build React generado.
- Añadida fuente `mailpoet` en `get_recipient_sources()` con detección segura por `class_exists()` y captura de excepciones.
- Añadido listado de listas MailPoet para selector/search mediante `getLists()`.
- Añadida resolución de destinatarios MailPoet paginada con `getSubscribers(['listId' => id, 'status' => 'subscribed'], limit, offset)`.
- Añadido conteo MailPoet mediante `getSubscribersCount()` cuando se pide count.
- Integrados `mailpoet_list_id`, labels y conteos en preview, count, resolve item, send, audiencia global y dinámica programada.
- Añadida fuente MailPoet en selector React y build generado.
- MP1B: `get_mailpoet_lists_for_selector()` ahora complementa `getLists()` con lectura interna mínima de `mailpoet_segments` para `wp_users` y `woocommerce_users` no borrados.
- MP1B: merge por ID sin duplicados; las etiquetas indican segmentos predeterminados/globales WordPress o WooCommerce.

## Pendiente

- QA real de shortcodes con usuario existente y email manual.
- QA visual de TinyMCE en navegador tras limpiar caché si hace falta.
- QA email real con tamaños/fuentes/colores.
- QA AD4 pendiente: rol dinámico, Broadcast List dinámica, manuales, exclusiones y logs en entorno controlado.
- QA controlado LOG2/LOG3 pendiente porque requiere crear/ejecutar envíos y logs reales.
- QA visual/manual del toast confirmado por usuario para release `2.6.0`.
- QA MailPoet pendiente en entorno con MailPoet activo y listas reales: selector, búsqueda, conteo, preview, envío, dinámica programada y MailPoet desactivado.
- QA MP1B pendiente: confirmar en navegador que aparecen segmentos `wp_users` y `woocommerce_users`, y que el conteo/preview usa solo suscriptores `subscribed`.

## No volver a investigar

- No se añadirá editor Node para esta necesidad; TinyMCE nativo cubre el alcance actual.
- Formatos P/H1/H2/H3/H4 ya existen en TinyMCE.
- `{unsubscribe_note}` queda fuera hasta tener sistema real de baja/exclusión.
- Audiencia dinámica programada usa `audience_mode=dynamic` en `pbm_delivery_meta_{id}`; si falta, el comportamiento es `fixed`.
- LOG2/LOG3 no añade migración: usa `audience_snapshot` dentro de `pbm_delivery_meta_{id}` y `pbm_delivery_events_{id}` como option no autoload.
- Toast admin usa namespace propio `.pbm-admin-toast` / `.pbm-admin-toasts`; no se copió `22mw-back.js` completo.
- QA UI5 fue confirmado por usuario el 2026-07-02; no fue ejecutado por el agente.
- MailPoet debe mantenerse vía API pública; no usar SQL directo a tablas MailPoet.
- La fuente MailPoet solo incluye estado `subscribed`; no incluir bounced, inactive, unconfirmed ni unsubscribed.
- La lectura interna MailPoet queda limitada a descubrir segmentos activos `wp_users` y `woocommerce_users`; conteo y destinatarios siguen por API pública.

## Riesgos o bloqueos

- Los datos de nombre/apellidos solo existen si el email pertenece a usuario WordPress con metas completadas o si la fuente trae nombre.
- La visualización final de fuentes/colores depende del cliente de email.
- Si una fuente dinámica queda vacía o no disponible al ejecutar, el envío se cancela usando el log de error existente.
- `pbm_delivery_events_{id}` guarda emails destinatarios; sigue pendiente definir política de retención/privacidad antes de uso real en producción.
- El éxito de envío recarga la página tras 900 ms; validar si ese tiempo es suficiente en navegador real.
- Si MailPoet está desactivado o la API falla, la fuente queda deshabilitada/oculta por el selector y AJAX devuelve 0 o fuente no disponible sin fatal.
- Si MailPoet cambia el esquema de `mailpoet_segments`, el selector puede no mostrar esos segmentos internos, pero no debe afectar envíos existentes por API.

## Próximo paso recomendado

- Pasar a QA MailPoet controlado de MP1B; para envíos reales, pedir permiso porque puede crear acciones programadas y logs.
