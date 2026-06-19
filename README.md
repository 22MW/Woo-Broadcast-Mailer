# Woo Broadcast Mailer

Plugin WordPress para WooCommerce que permite enviar emails masivos a audiencias segmentadas con vista previa, programación, control por lotes y logs.

## Estado actual

- Versión estable: `2.0.2`
- Panel admin principal migrado a React.
- Release ZIP limpio generado por GitHub Releases.
- QA funcional completo de envíos reales pendiente de ejecutar en entorno controlado.

## Qué permite hacer

Woo Broadcast Mailer permite crear una audiencia desde una o varias fuentes y enviar un email ahora o programarlo para más tarde.

Fuentes disponibles:

- Compradores de productos WooCommerce.
- Roles de WordPress.
- Listas de Mail Mint, si está disponible.
- Emails manuales.

Funciones principales:

- Audiencia global combinada.
- Deduplicación de emails antes de enviar.
- Vista previa de destinatarios únicos.
- Asunto y mensaje personalizados.
- Variable `{customer_name}` en el mensaje.
- Envío instantáneo o programado.
- Tamaño de lote configurable.
- Límite de emails por hora.
- Gestión de envíos y logs.
- Compatibilidad HPOS.
- Compatibilidad WooCommerce Subscriptions.
- Filtrado por idioma de pedido cuando WPML está disponible.

## Editor de emails WooCommerce

Desde `WooCommerce > Editor de emails` se pueden localizar y personalizar textos de plantillas de emails WooCommerce.

Incluye:

- Búsqueda por plantilla o búsqueda global.
- Búsqueda sobre texto original, traducciones y personalizaciones guardadas.
- Edición por idioma desde la misma pantalla.
- Listado de cambios guardados.
- Guardado en `pbm_email_string_overrides`.
- Compatibilidad de lectura con datos legacy `wc_custom_email_strings`.
- Aplicación real limitada al contexto de emails WooCommerce.

## Requisitos

- WordPress 6.5 o superior.
- PHP 8.1 o superior.
- WooCommerce 10.0 o superior.
- Action Scheduler, incluido con WooCommerce.
- Mail Mint opcional para usar la fuente “Lista Mail Mint”.

## Instalación

1. Sube la carpeta `woo-broadcast-mailer` a `/wp-content/plugins/`.
2. Activa el plugin desde Plugins en WordPress.
3. Las tablas internas se crean automáticamente al activar.
4. Ve a `WooCommerce > Broadcast Mailer`.

## Uso básico

### Enviar o programar un broadcast

1. Ve a `WooCommerce > Broadcast Mailer`.
2. En `Fuente`, elige producto, rol, lista Mail Mint o emails manuales.
3. Añade elementos a la lista global de audiencia.
4. Revisa el resumen de audiencia.
5. Haz clic en `Vista Previa de Destinatarios`.
6. Completa `Asunto` y `Mensaje`.
7. Ajusta tamaño de lote y emails por hora.
8. Opcionalmente activa `Programar envío` y define fecha/hora.
9. Envía.

### Gestionar envíos y logs

En la sección de envíos puedes:

- Ver envíos instantáneos y programados.
- Consultar logs por envío.
- Cancelar envíos pendientes.
- Borrar envíos completados o cancelados.
- Usar borrado masivo seguro por estado.

### Editar textos de emails WooCommerce

1. Ve a `WooCommerce > Editor de emails`.
2. Elige una plantilla o usa la búsqueda global.
3. Busca el texto original o una traducción.
4. Edita el texto por idioma.
5. Guarda las personalizaciones.
6. Revisa los cambios guardados en la pestaña correspondiente.

## Seguridad y controles

- Nonces en acciones AJAX/admin.
- Capability `manage_woocommerce` para el Editor de emails.
- Sanitización y validación de entradas.
- Escape de `{customer_name}` antes de insertarlo en emails HTML.
- Bloqueo de envío si Action Scheduler no está disponible.
- Borrado limitado a envíos completados o cancelados.
- ZIP de release sin `_dev/`, `node_modules/`, `src/`, `package.json`, `package-lock.json`, cachés ni archivos locales.

## Datos internos

Tablas propias:

- `wp_pbm_scheduled_emails`
- `wp_pbm_scheduled_logs`

Opciones relevantes:

- `pbm_email_string_overrides`
- `pbm_scheduled_recipients_{id}` durante ejecución de envíos

## Actualizaciones

El plugin usa GitHub Releases para distribuir versiones.

La release estable `2.0.2` incluye un workflow que genera `woo-broadcast-mailer.zip` limpio solo con archivos runtime.

## Limitaciones conocidas

- QA funcional completo de envíos reales pendiente en entorno controlado.
- Mail Mint está integrado en fase 1 mediante sus tablas internas.
- WPML depende de que el idioma esté guardado en el pedido.
- El updater debe probarse en staging antes de usarlo como canal crítico de producción.

## Changelog resumido

### 2.0.2

- Consolidación de checkpoints dev `2.0.1.1` a `2.0.1.9`.
- Endurecimiento de Action Scheduler, estados/logs, snapshots y preview obsoleta.
- Escape seguro de `{customer_name}` y borrado seguro de envíos.
- Fallback HPOS para destinatarios por producto.
- Email String Editor con React y overrides limitados a emails WooCommerce.
- Workflow de release limpio.

### 2.0.0

- Migración principal del admin a React.
- Audiencia global combinada.
- Vista previa React.
- Envío instantáneo/programado con logs.
- Unificación visual del admin.

## Soporte

Proyecto personalizado por 22MW. Para soporte o cambios, contactar con el autor.

## Licencia

GPLv2 o posterior.
