# Decisiones

## Última actualización

2026-06-28

## Decisiones confirmadas

- **Release actual publicada**: `2.3.0` con tag `v2.3.0`.
- **Release en preparación**: `2.4.0` para shortcodes de destinatario y mejoras TinyMCE.
- **Rama de trabajo**: `devWooBM`.
- **Canal de distribución**: GitHub Releases con ZIP generado por workflow.
- **ZIP release mínimo**: incluye solo runtime: `woo-broadcast-mailer.php`, `includes/`, `assets/`, `build/`, `readme.txt`, `LICENSE`, `uninstall.php`.
- **ZIP release excluye**: `_dev/`, `_md/`, `.git/`, `.github/`, `node_modules/`, `src/`, `package.json`, `package-lock.json`, `dist/`, cachés, logs y archivos locales.
- **React para admin**: panel principal y Editor de emails usan React con `@wordpress/components`, `@wordpress/element` y `@wordpress/i18n`.
- **Editor de mensaje**: se mantiene `wp_editor`/TinyMCE nativo; no se añade editor Node nuevo.
- **TinyMCE**: se permiten fuente, tamaño, color de texto y color de fondo; formatos P/H1/H2/H3/H4 ya existen.
- **Shortcodes de broadcast aprobados**: `{customer_name}`, `{first_name}`, `{last_name}`, `{email}`, `{current_date}`.
- **`{unsubscribe_note}` descartado por ahora**: no se añade hasta tener sistema real de baja/exclusión permanente.
- **Plantillas de mensaje**: guardan asunto y body; no guardan destinatarios, programación ni configuración de envío.
- **Action Scheduler**: obligatorio para envío instantáneo y programado.
- **Estados de envío**: `failed` existe para fallos de logs; se pueden borrar `failed` y `running` sin acciones pendientes.
- **Text domain correcto**: `wc-pbm`.
- **`_dev/_md/`**: queda como histórico heredado; no borrar sin permiso explícito.

## Decisiones operativas

- QA funcional debe hacerse en local/staging porque puede crear envíos, logs y acciones programadas.
- No hacer deploy a producción sin confirmar entorno, backup/staging y rollback.
- Documentación pública debe ser breve y orientada a uso real.
- Resumen comercial debe basarse solo en funcionalidades confirmadas.
- No presentar Mail Mint como integración profunda: actualmente es fase 1.
- No presentar WPML como traducción completa del plugin: se usa para segmentación por idioma de pedido cuando existe dato.

## Pendientes de decisión

- Ejecutar QA funcional completo antes de usar en producción.
- Probar updater en staging.
- Borrar o conservar definitivamente `_dev/_md/`.
- Definir si el plugin se venderá como herramienta interna, producto privado o plugin público.
- Definir si se implementará sistema real de baja/exclusión permanente.

## No reabrir sin motivo

- El editor de mensaje seguirá usando TinyMCE nativo.
- La migración React está completada.
- El flujo multi-fuente está implementado.
- El text domain correcto es `wc-pbm`.
- El ZIP de release debe seguir excluyendo `_dev/` y archivos de desarrollo.
