# Estado del plugin

## Última actualización

2026-06-18

## Resumen humano

Plugin funcional en v2.0.1. Migración a React completada. Flujo multi-fuente con audiencia global implementado. Action Scheduler integrado. Updater activo vía GitHub Releases. El Plan A queda ordenado y consultable: A1, A5 y A6 están aplicados; A2, A3, A4 y A7 siguen pendientes según prioridad.

## Estado general

Funcional con A1, A5 y A6 aplicados. Siguen pendientes otros puntos del Plan A antes de release.

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
- A5 implementado: escape de `{customer_name}` y validación de email destino.
- A6 implementado: borrado individual y por IDs limitado a envíos completados o cancelados.
- Plan A ordenado en `roadmap.md` y `visual.html` con códigos A1-A7.

## En curso

- Plan A parcialmente aplicado.

## Bloqueado

- QA funcional bloqueado hasta permiso explícito porque puede crear envíos, logs o acciones programadas.
- Release bloqueada hasta corregir exclusiones del ZIP, documentación/versionado, build y validaciones.

## Plan A — Estado resumido

- A1 — Action Scheduler obligatorio + aviso admin: hecho.
- A2 — Estados/logs reales: pendiente.
- A3 — Snapshot seguro de destinatarios: pendiente.
- A4 — Preview no obsoleto: pendiente.
- A5 — Escape de `{customer_name}`: hecho.
- A6 — Borrado por IDs seguro: hecho.
- A7 — ZIP/release sin `_dev/`: pendiente antes de release.

## Riesgos visibles

### Alta prioridad

- A2: El estado `completed` puede significar lotes programados, no emails realmente enviados.
- A3: El snapshot `pbm_scheduled_recipients_{id}` se borra al iniciar ejecución programada; si falla la programación posterior, se pierde.
- A4: El preview puede quedar obsoleto si cambia la audiencia antes de enviar.
- A7: El workflow release puede incluir `_dev/` en el ZIP.

### Media prioridad

- `CHANGELOG.md` y `README.md` no reflejan todavía la entrada `2.0.1`, aunque `readme.txt` y cabecera sí están en `2.0.1`.
- `package.json` y `package-lock.json` mantienen versión `1.1.0`; pendiente decidir si es versión interna del build o si debe sincronizarse.
- El workflow release no ejecuta build ni validaciones.
- `ScheduledLogsPanel.js` usa `dangerouslySetInnerHTML` para logs generados por AJAX.

### Baja prioridad / mantenimiento

- `includes/ajax-handlers.php`, `includes/functions-products.php`, `src/admin/App.js` y `assets/css/admin.css` son archivos grandes.
- `functions-scheduled.php` conserva HTML/JS/CSS legacy con estilos inline. Pendiente validar si sigue en uso.
- Mail Mint se integra por tablas internas, no por API pública confirmada.

## Próximo paso recomendado

- Elegir siguiente punto del Plan A. Recomendado por riesgo: A2 o A3.

## Pendiente de validar

- QA de A6 con registros completados, cancelados, pendientes y en ejecución.
- Prueba de A5 con nombre que contenga caracteres HTML.
- Que las rutas AJAX devuelven error si Action Scheduler no está disponible.
- Que Action Scheduler ejecute lotes en entorno real durante QA funcional.
- Que `wp_mail()` tenga transporte configurado y entregue correctamente.
- Que Mail Mint esté activo y sus tablas coincidan con lo esperado.
- Que WPML esté activo y guarde idioma de pedidos como espera el plugin.
- Que los logs representen estado final real de entrega.
- Que `build/` esté sincronizado con `src/`.
- Que el updater descargue correctamente la release actual.
- Que el ZIP final excluya `_dev/` y archivos internos.

## No volver a investigar

- Ruta real del plugin: `app/public/wp-content/plugins/Woo-Broadcast-Mailer/`.
- Rama de trabajo del plugin: `devWooBM`.
- Versión actual del plugin: `2.0.1`.
- Text domain correcto del plugin: `wc-pbm`.
- Los planes de `_dev/_md/` (`PLAN_MULTI_FUENTE` y `PLAN_MIGRACION`) están implementados en v2.0.0. No re-investigar como pendientes.
- React admin está implementado.
- Flujo multi-fuente está implementado.
- Action Scheduler está integrado y A1 añade comprobación obligatoria antes de confirmar envíos.
- Updater GitHub Releases existe en `includes/updater.php`.
