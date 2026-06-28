# Desarrollador

## Última actualización

2026-06-28

## Resumen humano

Ciclo 2.4.0 preparado con cambios mínimos sobre el editor de mensaje y shortcodes de broadcast. No se añadió editor Node nuevo; se extendió TinyMCE nativo y se mantuvo el flujo existente de envío.

## Descubierto

- `{customer_name}` dependía de `$recipient['name']`; en emails manuales o listas sin nombre podía quedar vacío.
- El email destino permite buscar un usuario WordPress con `get_user_by('email', $email)` para completar nombre/apellidos.
- TinyMCE ya incluye formatos P/H1/H2/H3/H4; no hacía falta tocar esa parte.
- La toolbar podía mostrar controles de color duplicados si se combinaba configuración custom con toolbar secundaria por defecto.

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

## Pendiente

- QA real de shortcodes con usuario existente y email manual.
- QA visual de TinyMCE en navegador tras limpiar caché si hace falta.
- QA email real con tamaños/fuentes/colores.

## No volver a investigar

- No se añadirá editor Node para esta necesidad; TinyMCE nativo cubre el alcance actual.
- Formatos P/H1/H2/H3/H4 ya existen en TinyMCE.
- `{unsubscribe_note}` queda fuera hasta tener sistema real de baja/exclusión.

## Riesgos o bloqueos

- Los datos de nombre/apellidos solo existen si el email pertenece a usuario WordPress con metas completadas o si la fuente trae nombre.
- La visualización final de fuentes/colores depende del cliente de email.

## Próximo paso recomendado

- Cerrar release `2.4.0` y validar en email real cuando el usuario autorice QA funcional.
