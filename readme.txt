=== Woo Broadcast Mailer ===
Contributors: 22mw
Tags: woocommerce, email, broadcast, scheduler, mailmint
Requires at least: 6.5
Tested up to: 6.8
Requires PHP: 8.1
Stable tag: 2.3.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Emailing sencillo para WooCommerce: envía avisos puntuales a compradores, productos, listas disponibles en WordPress, Broadcast Lists o emails manuales. Incluye plantillas de asunto y body, bloques rápidos, programación, logs y editor de textos de emails WooCommerce.

== Description ==

Woo Broadcast Mailer permite comunicar avisos puntuales desde WooCommerce sin montar una campaña completa en una plataforma externa.

Fuentes disponibles:
- Compradores de productos WooCommerce
- Roles de WordPress
- Listas de Mail Mint, si está disponible
- Listas disponibles en WordPress desde plugins como AcyMailing, FluentCRM, MailPoet o Newsletter, según integración disponible
- Broadcast Lists propias
- Emails manuales

Incluye:
- Audiencia global combinada con deduplicación
- Vista previa de destinatarios únicos antes de enviar
- Exclusión temporal de destinatarios desde la vista previa
- Broadcast Lists reutilizables
- Plantillas de mensaje que guardan asunto y body
- Bloques rápidos: imagen, botón, bloque destacado y separador
- Opción para enviar HTML sin plantilla global de WP HTML Mail
- Envío instantáneo o programado
- Lotes y límite de emails por hora
- Gestión de envíos y logs
- Compatibilidad HPOS
- Editor de textos de emails WooCommerce y plugins relacionados por idioma
- Actualizaciones mediante GitHub Releases

== Installation ==

1. Sube la carpeta `woo-broadcast-mailer` a `/wp-content/plugins/`.
2. Activa el plugin desde el panel de Plugins.
3. Ve a WooCommerce > Broadcast Mailer.

== Changelog ==

= 2.3.0 =
* Mejorado el tratamiento de HTML de broadcast para conservar mejor el espaciado visual.
* Añadida limpieza de saltos automáticos entre bloques HTML y listas.
* Añadido estado fallido para envíos con problemas de logs y borrado seguro de envíos atascados sin acciones pendientes.
* Las plantillas de mensaje ahora guardan y cargan asunto + body.

= 2.2.0 =
* Consolidada la gestión de Broadcast Lists reutilizables y exclusión temporal de destinatarios.
* Corregido el cierre de envíos instantáneos para llegar a Completado.
* Añadida opción para enviar HTML sin plantilla global de WP HTML Mail.
* Añadidas plantillas de mensaje para guardar, cargar y borrar solo el body del email.
* Añadidos bloques rápidos con imagen, botón, bloque destacado y separador configurable.
* Añadido Email String Editor para adaptar textos WooCommerce/plugins por idioma.

= 2.1.2 =
* Añadidas plantillas de mensaje para guardar, cargar y borrar solo el body del email.
* Añadidos bloques rápidos para insertar imagen, botón, bloque destacado y separador.
* Añadida selección de imagen desde la biblioteca de medios de WordPress.
* Añadidos selectores de color y configuración de separador.

= 2.1.1 =
* Añadida gestión de Broadcast Lists reutilizables.
* Añadida exclusión temporal de emails desde la vista previa.
* Corregido el cierre de envíos instantáneos para llegar a Completado.
* Añadida opción para enviar HTML sin plantilla global de WP HTML Mail.

= 2.1.0 =
* Añadida documentación comercial interna con landing visual y mensaje más claro.
* Separado el valor del plugin en dos bloques: broadcast puntual y editor de strings de emails WooCommerce.
* Aclarado el posicionamiento: no sustituye plugins de newsletter, sino que ayuda con envíos puntuales y control de textos.

= 2.0.3 =
* Mejorado el Email String Editor para localizar strings en plantillas block y clases de email de WooCommerce.
* Añadido soporte para overrides de contenido adicional de emails, incluyendo textos con {store_email} y %s.
* Corregida la aplicación del texto dinámico del footer de emails WooCommerce.
* Añadido modo ocultar texto en overrides del editor de emails.
* Build admin actualizado.

= 2.0.2 =
* Consolidada la serie dev 2.0.1.1 a 2.0.1.9 en una release estable.
* Mejorados Action Scheduler, estados/logs, snapshots, preview obsoleta, escape de nombre y borrado seguro.
* Añadido Email String Editor con React, búsqueda multiidioma y aplicación limitada a emails WooCommerce.
* Añadido fallback HPOS para destinatarios por producto.
* Ajustado workflow de release para generar ZIP limpio sin archivos internos ni dependencias de desarrollo.

= 2.0.0 =
* Migración principal del admin a React.
* Audiencia global combinada con producto, rol, Mail Mint y emails manuales.
* Vista previa React de destinatarios únicos.
* Flujo React de envío instantáneo y programado.
* Gestión de programados/logs en cards React.
* Unificación visual con logo 22MW en header.

= 1.1.0 =
* Flujo unificado de envío instantáneo/programado.
* Capa extensible de fuentes y primera integración de Mail Mint.

== Upgrade Notice ==

= 2.3.0 =
Mejora estable de HTML en broadcasts, plantillas de asunto + body y gestión de envíos atascados.

= 2.2.0 =
Release estable con Broadcast Lists reutilizables, plantillas de body, bloques rápidos, corrección de estado en envíos instantáneos y editor de textos WooCommerce/plugins por idioma.

= 2.1.2 =
Mejora del editor de mensajes con plantillas de body y bloques rápidos reutilizables.

= 2.1.1 =
Mejora operativa con Broadcast Lists, corrección de estado en envíos instantáneos y opción para enviar HTML sin plantilla global.

= 2.1.0 =
Release con consolidación comercial y posicionamiento más claro para presentación interna y distribución controlada.

= 2.0.3 =
Mejora estable del Editor de emails WooCommerce. Recomendado si personalizas textos de emails block o contenido adicional.

= 2.0.2 =
Release estable con endurecimiento técnico, Editor de emails WooCommerce y ZIP de release limpio. Recomendado limpiar caché del navegador tras actualizar el admin.

= 2.0.0 =
Gran actualización del panel admin y del flujo de audiencias/envío. Recomendado limpiar caché tras actualizar.
