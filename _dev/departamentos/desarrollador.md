# Desarrollador

## Última actualización

2026-06-29

## Resumen humano

LOG2/LOG3 implementados como MVP sin nuevas tablas ni React: snapshot descriptivo de audiencia en metadatos y eventos básicos por destinatario en option no autoload.

## Descubierto

- `{customer_name}` dependía de `$recipient['name']`; en emails manuales o listas sin nombre podía quedar vacío.
- El email destino permite buscar un usuario WordPress con `get_user_by('email', $email)` para completar nombre/apellidos.
- TinyMCE ya incluye formatos P/H1/H2/H3/H4; no hacía falta tocar esa parte.
- La toolbar podía mostrar controles de color duplicados si se combinaba configuración custom con toolbar secundaria por defecto.
- El flujo React de envío ya usaba vista previa obligatoria, exclusiones temporales y snapshot para programaciones.
- `pbm_delivery_meta_{id}` permite guardar `audience_mode` y `audience_config` sin migración de base de datos.
- `pbm_scheduled_logs` mantiene totales por lote; el detalle por destinatario queda separado en `pbm_delivery_events_{id}`.

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

## Pendiente

- QA real de shortcodes con usuario existente y email manual.
- QA visual de TinyMCE en navegador tras limpiar caché si hace falta.
- QA email real con tamaños/fuentes/colores.
- QA AD4 pendiente: rol dinámico, Broadcast List dinámica, manuales, exclusiones y logs en entorno controlado.
- QA controlado LOG2/LOG3 pendiente porque requiere crear/ejecutar envíos y logs reales.

## No volver a investigar

- No se añadirá editor Node para esta necesidad; TinyMCE nativo cubre el alcance actual.
- Formatos P/H1/H2/H3/H4 ya existen en TinyMCE.
- `{unsubscribe_note}` queda fuera hasta tener sistema real de baja/exclusión.
- Audiencia dinámica programada usa `audience_mode=dynamic` en `pbm_delivery_meta_{id}`; si falta, el comportamiento es `fixed`.
- LOG2/LOG3 no añade migración: usa `audience_snapshot` dentro de `pbm_delivery_meta_{id}` y `pbm_delivery_events_{id}` como option no autoload.

## Riesgos o bloqueos

- Los datos de nombre/apellidos solo existen si el email pertenece a usuario WordPress con metas completadas o si la fuente trae nombre.
- La visualización final de fuentes/colores depende del cliente de email.
- Si una fuente dinámica queda vacía o no disponible al ejecutar, el envío se cancela usando el log de error existente.
- `pbm_delivery_events_{id}` guarda emails destinatarios; sigue pendiente definir política de retención/privacidad antes de uso real en producción.

## Próximo paso recomendado

- Pasar a QA controlado de AD4/LOG2/LOG3 solo con permiso porque crea envíos, acciones programadas y logs.
