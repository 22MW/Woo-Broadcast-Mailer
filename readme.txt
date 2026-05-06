=== Woo Broadcast Mailer ===
Contributors: 22mw
Tags: woocommerce, email, broadcast, scheduler, mailmint
Requires at least: 6.5
Tested up to: 6.8
Requires PHP: 8.1
Stable tag: 2.0.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Sistema de envío masivo para WooCommerce con flujo React, audiencia global combinada, envío instantáneo/programado y logs.

== Description ==

Woo Broadcast Mailer permite enviar campañas por:
- Producto Woo
- Rol de WordPress
- Lista de Mail Mint (si está disponible)
- Emails manuales

Incluye:
- Vista previa de destinatarios únicos
- Deduplicación de audiencia
- Envío instantáneo y programado
- Lotes + límite por hora
- Gestión de envíos y logs con cards

== Installation ==

1. Sube la carpeta `woo-broadcast-mailer` a `/wp-content/plugins/`.
2. Activa el plugin desde el panel de Plugins.
3. Ve a WooCommerce > Broadcast Mailer.

== Changelog ==

= 2.0.1 =
* Añadido `readme.txt` en formato WordPress para mostrar changelog en el modal de actualización.
* Ajustes de documentación para compatibilidad con flujo de updates.

= 2.0.0 =
* Migración principal del admin a React.
* Limpieza del bloque legacy de selector/preview/envío en admin.
* Audiencia global combinada (producto, rol, Mail Mint, manuales) con deduplicación.
* Selector multi-fuente con búsqueda AJAX y lista global.
* Vista previa React de destinatarios únicos.
* Flujo React de envío instantáneo y programado.
* Integración estable de `wp_editor` clásico en panel React.
* Validaciones backend para audiencia global sin exigir selectores legacy.
* Gestión de programados/logs en cards React: ordenación, selección por página, borrado masivo.
* Paginación de logs: 12 cards por página.
* Badge de estado por color (`pending`, `running`, `completed`, `cancelled`).
* Total de mensajes por card con fallback por logs en históricos.
* Unificación visual (tipografía/colores/botones) + logo 22MW en header.

= 1.1.0 =
* Flujo unificado de envío (instantáneo/programado) en una sola UI.
* Capa extensible de fuentes y primera integración de Mail Mint.

== Upgrade Notice ==

= 2.0.1 =
Actualización de metadatos/readme para mostrar correctamente notas de versión en el actualizador.

= 2.0.0 =
Gran actualización del panel admin y del flujo de audiencias/envío. Recomendado limpiar caché tras actualizar.
