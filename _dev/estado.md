# Estado del plugin

## Última actualización

2026-06-11

## Resumen humano

Plugin funcional en v2.0.1. Migración a React completada. Flujo multi-fuente con audiencia global implementado. Action Scheduler integrado. Updater activo vía GitHub Releases.

## Estado general

Estable.

## Hecho

- Archivo principal: `woo-broadcast-mailer.php` con hooks, menú WC, AJAX y activación
- Panel admin migrado a React (`src/admin/`)
- Flujo unificado: envío instantáneo y programado desde mismo formulario
- Fuentes de destinatarios: Producto Woo, Rol WP, Lista Mail Mint, emails manuales
- Deduplicación de emails antes de envío
- Audiencia global acumulada con resumen (bruto, duplicados, únicos)
- Vista previa de destinatarios únicos vía AJAX
- Gestión de envíos y logs en React (cards, paginación, badges, borrado masivo)
- 2 tablas BD: `wp_pbm_scheduled_emails` + `wp_pbm_scheduled_logs`
- Compatibilidad HPOS declarada
- Compatibilidad WooCommerce Subscriptions
- Filtrado por idioma WPML
- Updater vía GitHub Releases (`includes/updater.php`)
- Build con `@wordpress/scripts`

## En curso

- Nada en curso confirmado.

## Bloqueado

- Ningún bloqueo confirmado.

## Próximo paso recomendado

- Definir qué trabajo se quiere hacer sobre el plugin.

## No volver a investigar

- Los planes de `_md/` (PLAN_MULTI_FUENTE y PLAN_MIGRACION) están implementados en v2.0.0. No re-investigar como pendientes.
- Text domain correcto del plugin: `wc-pbm` (no `vfwoo` como indicaba `_md/REGLAS_DESARROLLO.md`).
