# Woo Broadcast Mailer

Plugin WordPress para WooCommerce que permite enviar avisos puntuales a audiencias concretas, reutilizar listas y cuerpos de mensaje, programar envíos por lotes y adaptar textos de emails WooCommerce por idioma.

## Estado actual

- Versión estable: `2.3.0`
- Panel admin principal en React.
- Release ZIP limpio generado por GitHub Releases.
- QA funcional completo de envíos reales pendiente de ejecutar en entorno controlado.

## Qué permite hacer

Woo Broadcast Mailer tiene dos áreas principales:

1. **Broadcast Mailer**: enviar mensajes sencillos a compradores, productos, listas y emails manuales.
2. **Email String Editor**: adaptar textos genéricos de WooCommerce y plugins relacionados sin tocar plantillas.

## Broadcast Mailer

Permite crear una audiencia desde una o varias fuentes y enviar un email ahora o programarlo para más tarde.

Fuentes disponibles:

- Compradores de productos WooCommerce.
- Roles de WordPress.
- Listas de Mail Mint, si está disponible.
- Listas disponibles en WordPress desde plugins como AcyMailing, FluentCRM, MailPoet o Newsletter, según integración disponible.
- Broadcast Lists guardadas dentro del plugin.
- Emails manuales.

Funciones principales:

- Audiencia global combinada.
- Deduplicación de emails antes de enviar.
- Vista previa de destinatarios únicos.
- Exclusión temporal de destinatarios desde la vista previa.
- Broadcast Lists reutilizables.
- Plantillas de mensaje que guardan asunto y body.
- Bloques rápidos para insertar imagen, botón, bloque destacado y separador.
- Selección de imagen desde la biblioteca de medios de WordPress.
- Opción para enviar HTML sin plantilla global de WP HTML Mail.
- Asunto y mensaje personalizados.
- Variable `{customer_name}` en el mensaje.
- Envío instantáneo o programado.
- Tamaño de lote configurable.
- Límite de emails por hora.
- Gestión de envíos y logs.
- Compatibilidad HPOS.
- Compatibilidad WooCommerce Subscriptions.
- Filtrado por idioma de pedido cuando WPML está disponible.

## Email String Editor

Desde `WooCommerce > Editor de emails` se pueden localizar y adaptar textos genéricos de emails WooCommerce y plugins relacionados.

Incluye:

- Búsqueda por plantilla o búsqueda global.
- Búsqueda sobre texto original, traducciones y personalizaciones guardadas.
- Edición por idioma desde la misma pantalla.
- Opción de ocultar textos concretos.
- Listado de cambios guardados.
- Guardado en `pbm_email_string_overrides`.
- Compatibilidad de lectura con datos legacy `wc_custom_email_strings`.
- Aplicación real limitada al contexto de emails WooCommerce.

## Requisitos

- WordPress 6.5 o superior.
- PHP 8.1 o superior.
- WooCommerce 10.0 o superior.
- Action Scheduler, incluido con WooCommerce.
- SMTP recomendado para producción.
- Mail Mint opcional para usar sus listas como fuente.

## Instalación

1. Sube la carpeta `woo-broadcast-mailer` a `/wp-content/plugins/`.
2. Activa el plugin desde Plugins en WordPress.
3. Las tablas internas se crean automáticamente al activar.
4. Ve a `WooCommerce > Broadcast Mailer`.

## Uso básico

### Enviar o programar un broadcast

1. Ve a `WooCommerce > Broadcast Mailer`.
2. Selecciona destinatarios desde productos, roles, listas disponibles, Broadcast Lists o emails manuales.
3. Añade elementos a la lista global de audiencia.
4. Revisa el resumen de audiencia.
5. Haz clic en `Vista Previa de Destinatarios`.
6. Quita destinatarios concretos si no deben recibir ese envío.
7. Completa `Asunto` y `Mensaje`.
8. Opcionalmente inserta bloques rápidos o carga una plantilla de asunto y body.
9. Ajusta tamaño de lote y emails por hora.
10. Opcionalmente activa `Programar envío` y define fecha/hora.
11. Envía.

### Reutilizar listas y mensajes

- Las **Broadcast Lists** guardan destinatarios para usarlos como fuente en futuros envíos.
- Las **plantillas de mensaje** guardan asunto y body del email.
- Las plantillas no guardan destinatarios, programación ni configuración de envío.

### Gestionar envíos y logs

En la sección de envíos puedes:

- Ver envíos instantáneos y programados.
- Consultar logs por envío.
- Cancelar envíos pendientes.
- Borrar envíos completados, cancelados, fallidos o atascados sin acciones pendientes.
- Usar borrado masivo seguro por estado.

### Editar textos de emails WooCommerce

1. Ve a `WooCommerce > Editor de emails`.
2. Elige una plantilla o usa la búsqueda global.
3. Busca el texto original o una traducción.
4. Edita el texto por idioma o márcalo como oculto.
5. Guarda las personalizaciones.
6. Revisa los cambios guardados en la pestaña correspondiente.

## Seguridad y controles

- Nonces en acciones AJAX/admin.
- Capability `manage_woocommerce` para acciones administrativas.
- Sanitización y validación de entradas.
- Escape de `{customer_name}` antes de insertarlo en emails HTML.
- Bloqueo de envío si Action Scheduler no está disponible.
- Snapshot de destinatarios durante ejecución.
- Borrado limitado a envíos completados o cancelados.
- ZIP de release sin `_dev/`, `node_modules/`, `src/`, `package.json`, `package-lock.json`, cachés ni archivos locales.

## Datos internos

Tablas propias:

- `wp_pbm_scheduled_emails`
- `wp_pbm_scheduled_logs`

Opciones relevantes:

- `pbm_email_string_overrides`
- `pbm_broadcast_lists`
- `pbm_message_templates`
- `pbm_delivery_meta_{id}`
- `pbm_scheduled_recipients_{id}` durante ejecución de envíos

## Actualizaciones

El plugin usa GitHub Releases para distribuir versiones.

La release estable `2.3.0` consolida mejoras de HTML en broadcasts, estado fallido para envíos atascados y plantillas de asunto + body.

## Limitaciones conocidas

- QA funcional completo de envíos reales pendiente en entorno controlado.
- La entregabilidad depende del correo del sitio y de la configuración SMTP.
- Mail Mint depende de sus datos internos cuando se usa como fuente.
- Integraciones con otros plugins de email dependen de disponibilidad y estructura de sus listas.
- WPML depende de que el idioma esté guardado en el pedido.
- El updater debe probarse en staging antes de usarlo como canal crítico de producción.

## Changelog resumido

### 2.3.0

- Mejor conservación visual del HTML en clientes de email.
- Limpieza de saltos automáticos entre bloques y listas.
- Estado fallido y borrado seguro de envíos atascados sin acciones pendientes.
- Plantillas de asunto + body.

### 2.2.0

- Broadcast Lists reutilizables.
- Exclusión temporal de destinatarios desde vista previa.
- Corrección del cierre de envíos instantáneos a Completado.
- Plantillas de body.
- Bloques rápidos: imagen, botón, bloque destacado y separador configurable.
- Selección de imagen desde Media Library.
- HTML sin plantilla global de WP HTML Mail.
- Email String Editor para adaptar textos WooCommerce/plugins por idioma.

### 2.0.2

- Endurecimiento de Action Scheduler, estados/logs, snapshots y preview obsoleta.
- Fallback HPOS para destinatarios por producto.
- Email String Editor con React y overrides limitados a emails WooCommerce.
- Workflow de release limpio.

## Soporte

Proyecto personalizado por 22MW. Para soporte o cambios, contactar con el autor.

## Licencia

GPLv2 o posterior.
