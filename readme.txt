=== Woo Broadcast Mailer ===
Contributors: 22mw
Tags: woocommerce, email, broadcast, scheduler, mailmint
Requires at least: 6.5
Tested up to: 6.8
Requires PHP: 8.1
Stable tag: 2.0.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Sistema de envío masivo para WooCommerce con audiencia global combinada, envío instantáneo/programado, logs y editor de textos de emails.

== Description ==

Woo Broadcast Mailer permite enviar comunicaciones desde WooCommerce a audiencias segmentadas.

Fuentes disponibles:
- Compradores de productos WooCommerce
- Roles de WordPress
- Listas de Mail Mint, si está disponible
- Emails manuales

Incluye:
- Audiencia global combinada con deduplicación
- Vista previa de destinatarios únicos antes de enviar
- Envío instantáneo o programado
- Lotes y límite de emails por hora
- Gestión de envíos y logs
- Compatibilidad HPOS
- Editor de textos de emails WooCommerce por idioma
- Actualizaciones mediante GitHub Releases

== Installation ==

1. Sube la carpeta `woo-broadcast-mailer` a `/wp-content/plugins/`.
2. Activa el plugin desde el panel de Plugins.
3. Ve a WooCommerce > Broadcast Mailer.

== Changelog ==

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

= 2.0.2 =
Release estable con endurecimiento técnico, Editor de emails WooCommerce y ZIP de release limpio. Recomendado limpiar caché del navegador tras actualizar el admin.

= 2.0.0 =
Gran actualización del panel admin y del flujo de audiencias/envío. Recomendado limpiar caché tras actualizar.
