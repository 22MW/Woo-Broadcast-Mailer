# Decisiones

## Última actualización

2026-06-19

## Decisiones confirmadas

- **Release actual**: `2.0.2` publicada en GitHub Releases.
- **Ramas alineadas**: `main` y `devWooBM` apuntan al commit `dd51840` tras release `2.0.2`.
- **ZIP release mínimo**: el ZIP incluye solo runtime: `woo-broadcast-mailer.php`, `includes/`, `assets/`, `build/`, `readme.txt`, `LICENSE`, `uninstall.php`.
- **ZIP release excluye**: `_dev/`, `_md/`, `.git/`, `.github/`, `node_modules/`, `src/`, `package.json`, `package-lock.json`, `dist/`, cachés, logs y archivos locales.
- **React para admin**: panel principal y Editor de emails usan React con `@wordpress/components`, `@wordpress/element` y `@wordpress/i18n`.
- **Action Scheduler**: obligatorio para envío instantáneo y programado.
- **Arquitectura multi-fuente**: audiencia global acumulada con deduplicación server-side.
- **GitHub Releases**: canal de distribución y actualizaciones del plugin.
- **HPOS compatible**: compatibilidad declarada y fallback de destinatarios por producto implementado.
- **Text domain correcto**: `wc-pbm`.
- **Tablas propias**: `wp_pbm_scheduled_emails` y `wp_pbm_scheduled_logs`.
- **Email String Editor**: módulo propio bajo WooCommerce, no copia directa del plugin heredado.
- **Email String Editor E4**: overrides limitados por contexto de emails WooCommerce y dominio `woocommerce`.
- **QA reportado OK por usuario**: A4 post-fix y Email String Editor E1-E5.
- **22MW-BACK**: queda como posible mejora visual por fases; no aplicar al plugin entero de golpe.
- **`package.json`/`package-lock.json`**: mantienen versión interna `1.1.0`; no entran en ZIP release y no bloquean `2.0.2`.
- **`_dev/_md/`**: queda como histórico heredado; no borrar sin permiso explícito.

## Decisiones operativas

- QA funcional debe hacerse en local/staging porque puede crear envíos, logs y acciones programadas.
- No hacer deploy a producción sin confirmar entorno, backup/staging y rollback.
- Documentación pública debe ser breve y orientada a uso real.
- Resumen comercial debe basarse solo en funcionalidades confirmadas.
- No presentar Mail Mint como integración profunda: actualmente es fase 1.
- No presentar WPML como traducción completa del plugin: se usa para segmentación por idioma de pedido cuando existe dato.

## Pendientes de decisión

- Borrar o conservar definitivamente `_dev/_md/`.
- Ejecutar QA funcional completo antes de usar en producción.
- Probar updater en staging.
- Definir si 22MW-BACK empieza por Broadcast principal o Editor de emails.
- Definir si el plugin se venderá como herramienta interna, producto privado o plugin público.

## No reabrir sin motivo

- La migración React está completada.
- El flujo multi-fuente está implementado.
- El text domain correcto es `wc-pbm`.
- El ZIP de release `2.0.2` ya está limpio.
- La release `2.0.2` ya está publicada.
