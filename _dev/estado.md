# Estado del plugin

## Última actualización

2026-06-18

## Resumen humano

Plugin funcional en v2.0.1.2 dev. Migración a React completada. Flujo multi-fuente con audiencia global implementado. Action Scheduler integrado. Updater activo vía GitHub Releases. El Plan A completo está aplicado: A1-A7 cerrados a nivel de código/workflow/build. Queda QA funcional y validación real de ZIP/release.

## Estado general

Funcional con Plan A aplicado. Pendiente QA funcional, documentación de release y validación ZIP antes de publicar.

## Hecho

- Archivo principal: `woo-broadcast-mailer.php` con hooks, menú WC, AJAX y activación.
- Panel admin migrado a React (`src/admin/`).
- Flujo unificado: envío instantáneo y programado desde mismo formulario.
- Fuentes de destinatarios: Producto Woo, Rol WP, Lista Mail Mint, emails manuales.
- Deduplicación de emails antes de envío.
- Audiencia global acumulada con resumen (bruto, duplicados, únicos).
- Vista previa de destinatarios únicos vía AJAX.
- Gestión de envíos y logs en React (cards, paginación, badges, borrado masivo).
- 2 tablas BD: `wp_pbm_scheduled_emails` + `wp_pbm_scheduled_logs`.
- Compatibilidad HPOS declarada.
- Compatibilidad WooCommerce Subscriptions.
- Filtrado por idioma WPML.
- Updater vía GitHub Releases (`includes/updater.php`).
- Build con `@wordpress/scripts`.
- Repo propio confirmado dentro del plugin.
- Rama de trabajo confirmada: `devWooBM`.
- Revisión global inicial 2026-06-18 realizada en solo lectura.
- Revisión completa por especialistas 2026-06-18 realizada en solo lectura: arquitectura, seguridad, QA y release.
- A1 implementado: Action Scheduler obligatorio + aviso admin.
- A2 implementado: `completed` ya no se marca al programar lotes, sino al cubrir logs acumulados.
- A3 implementado: snapshot de destinatarios no se borra al leerlo; se limpia al completar o eliminar.
- A4 implementado y compilado: preview obsoleta bloquea envío.
- A5 implementado: escape de `{customer_name}` y validación de email destino.
- A6 implementado: borrado individual y por IDs limitado a envíos completados o cancelados.
- A7 implementado: workflow release excluye `_dev/`.
- Entorno Node local preparado con `node v22.11.0` y `npm v10.9.0`.
- `node_modules/` copiado desde el plugin anterior.

## En curso

- QA funcional del Plan A completo.
- Validación real de ZIP/release antes de publicar.

## Bloqueado

- QA funcional bloqueado hasta permiso explícito porque puede crear envíos, logs o acciones programadas.
- Release, ZIP, tag, push o deploy bloqueados hasta checklist release.
- Incorporación Email String Editor bloqueada hasta decisiones funcionales.

## Plan A — Estado resumido

- A1 — Action Scheduler obligatorio + aviso admin: hecho.
- A2 — Estados/logs reales: hecho.
- A3 — Snapshot seguro de destinatarios: hecho.
- A4 — Preview no obsoleto: hecho.
- A5 — Escape de `{customer_name}`: hecho.
- A6 — Borrado por IDs seguro: hecho.
- A7 — ZIP/release sin `_dev/`: hecho.

## Riesgos visibles

### Alta prioridad

- QA funcional pendiente del Plan A completo.
- Validación real de ZIP/release pendiente antes de publicar.

### Media prioridad

- `CHANGELOG.md` y `README.md` no reflejan todavía la entrada `2.0.1`, aunque `readme.txt` y cabecera sí están en `2.0.1`.
- `package.json` y `package-lock.json` mantienen versión `1.1.0`; pendiente decidir si es versión interna del build o si debe sincronizarse.
- El workflow release todavía no ejecuta build ni validaciones; ahora excluye `_dev/` pero puede empaquetar lo que ya exista en `build/`.
- `ScheduledLogsPanel.js` usa `dangerouslySetInnerHTML` para logs generados por AJAX.

### Baja prioridad / mantenimiento

- `includes/ajax-handlers.php`, `includes/functions-products.php`, `src/admin/App.js` y `assets/css/admin.css` son archivos grandes.
- `functions-scheduled.php` conserva HTML/JS/CSS legacy con estilos inline. Pendiente validar si sigue en uso.
- Mail Mint se integra por tablas internas, no por API pública confirmada.

## Próximo paso recomendado

- Ejecutar QA funcional controlado del Plan A completo.
- Si QA pasa, preparar checklist release: changelog, build, ZIP de prueba y exclusiones.

## Pendiente de validar

- QA de A2 con envío instantáneo, envío programado, ejecución de lotes y logs acumulados.
- QA de A4: preview, cambio de audiencia/configuración y bloqueo de envío.
- QA de A6 con registros completados, cancelados, pendientes y en ejecución.
- Prueba de A5 con nombre que contenga caracteres HTML.
- Que las rutas AJAX devuelven error si Action Scheduler no está disponible.
- Que Action Scheduler ejecute lotes en entorno real durante QA funcional.
- Que `wp_mail()` tenga transporte configurado y entregue correctamente.
- Que Mail Mint esté activo y sus tablas coincidan con lo esperado.
- Que WPML esté activo y guarde idioma de pedidos como espera el plugin.
- Que los logs representen estado final real de entrega.
- Que el updater descargue correctamente la release actual.
- Que el ZIP final excluya `_dev/` y archivos internos.

## No volver a investigar

- Ruta real del plugin: `app/public/wp-content/plugins/Woo-Broadcast-Mailer/`.
- Rama de trabajo del plugin: `devWooBM`.
- Versión dev actual del plugin: `2.0.1.2`.
- Versión pública base: `2.0.1`.
- Text domain correcto del plugin: `wc-pbm`.
- Los planes de `_dev/_md/` (`PLAN_MULTI_FUENTE` y `PLAN_MIGRACION`) están implementados en v2.0.0. No re-investigar como pendientes.
- React admin está implementado.
- Flujo multi-fuente está implementado.
- Action Scheduler está integrado y A1 añade comprobación obligatoria antes de confirmar envíos.
- Updater GitHub Releases existe en `includes/updater.php`.
- Node local disponible en `/Users/22mw/.local/node-install/node-v22.11.0-darwin-arm64/bin`.
- `node_modules/` se copió desde `/Users/22mw/Local Sites/test/app/public/wp-content/plugins/woo-broadcast-mailer/node_modules/`.
