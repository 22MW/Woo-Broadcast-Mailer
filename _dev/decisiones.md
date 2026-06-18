# Decisiones

## Última actualización

2026-06-18

## Decisiones confirmadas

- **React para el panel admin**: migración completa a `@wordpress/components` + `@wordpress/element` completada en v2.0.0. Build con `@wordpress/scripts`.
- **Action Scheduler para envíos**: los envíos instantáneos y programados usan Action Scheduler incluido con WooCommerce.
- **Arquitectura multi-fuente**: audiencia global acumulada desde múltiples fuentes con deduplicación server-side. Payload por IDs, resolución en backend.
- **GitHub Releases para actualizaciones**: updater en `includes/updater.php`, no requiere WordPress.org.
- **HPOS declarado compatible**: `FeaturesUtil::declare_compatibility('custom_order_tables')` activo.
- **Text domain: `wc-pbm`**: el archivo `_md/REGLAS_DESARROLLO.md` indicaba `vfwoo` — eso es incorrecto y corresponde a otro plugin. El text domain real es `wc-pbm`.
- **2 tablas propias**: `wp_pbm_scheduled_emails` (envíos) + `wp_pbm_scheduled_logs` (ejecuciones). Se crean en activación con `dbDelta`.
- **`_dev/` como memoria operativa**: se migra desde `_md/` (2026-06-11). `_md/` queda movido dentro de `_dev/_md/` y pendiente de decisión de borrado.
- **Revisión global inicial 2026-06-18**: se realizó en solo lectura. No se tocó código, base de datos, settings, build ni release.
- **Revisión completa por especialistas 2026-06-18**: arquitectura, seguridad, QA y release revisados en solo lectura.
- **Documento Email String Editor 2026-06-18**: se creó `_dev/incorporacion-email-string-editor.md` como base de planificación.
- **Email String Editor E1-E4**: implementado como MVP admin seguro bajo WooCommerce, con búsqueda global multiidioma, edición multiidioma en pantalla única, edición directa de cambios guardados, datos en `pbm_email_string_overrides`, compatibilidad de lectura con `wc_custom_email_strings` y aplicación real limitada a emails WooCommerce.
- **Email String Editor E4**: aplicación real con `gettext` limitada por `woocommerce_email_header`/`woocommerce_email_footer` y dominio `woocommerce`.
- **Plan A cerrado**: A1-A7 aplicados a nivel de código, React build y workflow release.
- **Node local para build**: Node/npm se instalaron en `/Users/22mw/.local/node-install/node-v22.11.0-darwin-arm64/bin` porque el sistema no encontraba `node`/`npm`.
- **node_modules local**: se copió `node_modules/` desde el plugin anterior `/Users/22mw/Local Sites/test/app/public/wp-content/plugins/woo-broadcast-mailer/node_modules/`.

## Decisiones operativas derivadas de la auditoría

- Antes de QA funcional o release debe priorizarse un bloque técnico mínimo: Action Scheduler, estados/logs, snapshot, preview, borrado por IDs y escape de `{customer_name}`.
- El QA funcional debe hacerse en local/staging, no en producción.
- No se debe generar ZIP/release con el workflow actual sin excluir `_dev/`.
- No hacer refactor grande como primer paso; primero correcciones mínimas y QA.
- El Email String Editor no debe incorporarse copiando `_dev/_md/WooEmailStringEditor.php` tal cual.

## Origen de los planes en `_dev/_md/`

- `PLAN_MULTI_FUENTE.md`: plan del flujo de audiencia global. **Implementado en v2.0.0.**
- `PLAN_MIGRACION_WORDPRESS_COMPONENTS.md`: plan de migración a React. **Implementado en v2.0.0.**
- `REGLAS_DESARROLLO.md`: reglas de desarrollo previas al sistema `.kilo/`. Supersedidas por `.kilo/rules/globales.md` del workspace.
- `WooEmailStringEditor.php`: plugin independiente evaluado para posible incorporación. Ya se adaptó parcialmente como módulo propio E1-E3; no se copió tal cual.

## Pendientes de decisión

- ¿Borrar `_dev/_md/` del repo ahora que existe `_dev/` consolidado?
- ¿Sincronizar versión de `package.json`/`package-lock.json` con la versión del plugin o mantenerla como versión interna del paquete JS?
- ¿Actualizar `CHANGELOG.md` y `README.md` con entrada `2.0.1` antes del próximo push/release?
- ¿Excluir `src/`, `package.json` y `package-lock.json` del ZIP distribuible o mantenerlos?
- Email String Editor E5: validar en email WooCommerce real que los overrides aplican solo dentro del email.
- Email String Editor: ¿qué idioma manda en emails multiidioma cuando no existe meta `wpml_language`: usuario, WPML/Polylang o locale del sitio?
- Email String Editor: ¿MVP solo WooCommerce core o también plugins detectados?

## Recomendaciones acordadas como siguiente paso

- Ejecutar QA funcional controlado del Plan A completo.
- Ejecutar QA de Email String Editor E4 en email WooCommerce controlado.
- Si QA pasa, preparar checklist release: changelog, build, ZIP de prueba y exclusiones.
- Preparar release solo al final: exclusiones, documentación, build, updater y staging.
- Tratar Email String Editor E5 como fase de QA/no regresión antes de release.

## No reabrir sin motivo

- La migración a React está completa. No replantear stack frontend sin motivo concreto.
- El flujo multi-fuente con deduplicación server-side está implementado. No replantear sin bug confirmado.
- El text domain correcto es `wc-pbm`.
