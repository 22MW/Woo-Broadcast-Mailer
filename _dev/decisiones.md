# Decisiones

## Última actualización

2026-06-11

## Decisiones confirmadas

- **React para el panel admin**: migración completa a `@wordpress/components` + `@wordpress/element` completada en v2.0.0. Build con `@wordpress/scripts`.
- **Action Scheduler para envíos**: todos los envíos (instantáneos y programados) corren en background vía Action Scheduler incluido con WooCommerce.
- **Arquitectura multi-fuente**: audiencia global acumulada desde múltiples fuentes con deduplicación server-side. Payload por IDs, resolución en backend.
- **GitHub Releases para actualizaciones**: updater en `includes/updater.php`, no requiere WordPress.org.
- **HPOS declarado compatible**: `FeaturesUtil::declare_compatibility('custom_order_tables')` activo.
- **Text domain: `wc-pbm`**: el archivo `_md/REGLAS_DESARROLLO.md` indicaba `vfwoo` — eso es incorrecto y corresponde a otro plugin. El text domain real es `wc-pbm`.
- **2 tablas propias**: `wp_pbm_scheduled_emails` (envíos) + `wp_pbm_scheduled_logs` (ejecuciones). Se crean en activación con `dbDelta`.
- **`_dev/` como memoria operativa**: se migra desde `_md/` (2026-06-11). `_md/` se mantiene hasta confirmación de borrado.

## Origen de los planes en `_md/`

- `PLAN_MULTI_FUENTE.md`: plan del flujo de audiencia global. **Implementado en v2.0.0.**
- `PLAN_MIGRACION_WORDPRESS_COMPONENTS.md`: plan de migración a React. **Implementado en v2.0.0.**
- `REGLAS_DESARROLLO.md`: reglas de desarrollo previas al sistema `.kilo/`. Supersedidas por `.kilo/rules/globales.md` del workspace.

## Pendientes de decisión

- ¿Borrar `_md/` del repo ahora que existe `_dev/`?

## No reabrir sin motivo

- La migración a React está completa. No replantear stack frontend sin motivo concreto.
- El flujo multi-fuente con deduplicación server-side está implementado y funciona. No replantear sin bug confirmado.
