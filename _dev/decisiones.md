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
- **Documento Email String Editor 2026-06-18**: se creó `_dev/incorporacion-email-string-editor.md` como documento de planificación. No autoriza implementación.

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
- `WooEmailStringEditor.php`: plugin independiente evaluado para posible incorporación. Debe adaptarse como módulo planificado si se aprueba.

## Pendientes de decisión

- ¿Borrar `_dev/_md/` del repo ahora que existe `_dev/` consolidado?
- ¿Sincronizar versión de `package.json`/`package-lock.json` con la versión del plugin o mantenerla como versión interna del paquete JS?
- ¿Actualizar `CHANGELOG.md` y `README.md` con entrada `2.0.1` antes del próximo push/release?
- ¿Cambiar semántica de estado para que `completed` signifique realmente todos los lotes finalizados?
- ¿Aplicar ahora el bloque técnico mínimo antes de QA?
- ¿Excluir `src/`, `package.json` y `package-lock.json` del ZIP distribuible o mantenerlos?
- Email String Editor: ¿submenú separado o pestaña React?
- Email String Editor: ¿afectar solo emails WooCommerce o todo dominio `woocommerce`?
- Email String Editor: ¿qué idioma manda en emails multiidioma: pedido, usuario, WPML/Polylang o locale del sitio?
- Email String Editor: ¿mantener `wc_custom_email_strings` o migrar a `pbm_email_string_overrides`?
- Email String Editor: ¿MVP solo WooCommerce core o también plugins detectados?

## Recomendaciones acordadas como siguiente paso

- Priorizar correcciones mínimas antes de QA funcional.
- Ejecutar QA funcional controlado después de corregir riesgos principales.
- Preparar release solo al final: exclusiones, documentación, build, updater y staging.
- Tratar Email String Editor como funcionalidad futura bloqueada por decisiones, no como implementación inmediata.

## No reabrir sin motivo

- La migración a React está completa. No replantear stack frontend sin motivo concreto.
- El flujo multi-fuente con deduplicación server-side está implementado. No replantear sin bug confirmado.
- El text domain correcto es `wc-pbm`.
