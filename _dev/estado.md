# Estado del plugin

## Última actualización

2026-06-18

## Resumen humano

Plugin funcional en v2.0.1.8 dev. Migración a React completada. Flujo multi-fuente con audiencia global implementado. Action Scheduler integrado. Updater activo vía GitHub Releases. El Plan A completo está aplicado. Email String Editor E1-E4 está implementado: admin seguro y aplicación `gettext` limitada al contexto de emails WooCommerce.

## Estado general

Funcional con Plan A aplicado, Email String Editor E1-E5 añadido y R1 React implementado. A4 post-fix validado por usuario. Pendiente decidir alcance 22MW-BACK, QA funcional general, documentación de release y validación ZIP antes de publicar.

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
- A4 implementado y compilado: preview obsoleta bloquea envío; programación y fecha/hora no invalidan preview.
- A5 implementado: escape de `{customer_name}` y validación de email destino.
- A6 implementado: borrado individual y por IDs limitado a envíos completados o cancelados.
- A7 implementado: workflow release excluye `_dev/`.
- Entorno Node local preparado con `node v22.11.0` y `npm v10.9.0`.
- `node_modules/` copiado desde el plugin anterior.
- Email String Editor E1 implementado: cargador, clases base, bootstrap y submenú WooCommerce.
- Email String Editor E2 implementado: admin MVP con idioma, plantilla, buscador, listado de strings y cambios guardados.
- Email String Editor E2.1 implementado: búsqueda global en todas las plantillas permitidas si no se elige plantilla.
- Email String Editor E2.2 implementado: edición de todos los idiomas disponibles desde la misma pantalla de cada string/email.
- Email String Editor E2.3 implementado: búsqueda multiidioma sobre original, traducciones WooCommerce y personalizaciones guardadas.
- Email String Editor E3 implementado: guardado/borrado en `pbm_email_string_overrides` con lectura compatible de `wc_custom_email_strings`.
- Email String Editor E3.1 implementado: guardado multiidioma por string/email desde una única pantalla.
- Email String Editor E3.2 implementado: edición directa desde la pestaña Cambios guardados.
- Email String Editor E4 implementado: aplicación real de overrides con `gettext` solo mientras WooCommerce renderiza emails.
- R1 implementado: interfaz admin del Editor de emails migrada a React con AJAX seguro.
- QA A4 post-fix reportado OK por usuario.
- Plan B 22MW-BACK creado como propuesta por fases; pendiente decisión de piloto.
- Ajuste visual aplicado: fuentes a la izquierda; emails manuales, lista global de audiencias y botón de vista previa a la derecha; resultado de preview encima de `Asunto`, sin tocar lógica funcional.
- Header del Broadcast principal incluye acceso directo al Editor de emails.
- Header del Editor de emails iguala el estilo visual del Broadcast principal e incluye enlace de vuelta a Broadcast.
- Editor de emails reutiliza estilos existentes del Broadcast para mantener coherencia visual sin crear un sistema paralelo.
- Tabs del Editor de emails usan botones visuales tipo fuente y la descripción queda dentro del mismo bloque.
- Placeholder del campo Buscar del Email String Editor actualizado por el texto: "Si no eliges plantilla, la búsqueda recorre todas las plantillas permitidas.".
- Select de Email / plantilla del Email String Editor mantiene el texto: "Todas las plantillas al buscar.".
- Bugfix HPOS destinatarios por producto implementado: fallback a escaneo por `line_items` cuando la tabla lookup no devuelve destinatarios.

## En curso

- QA funcional del Plan A completo.
- QA Email String Editor E1-E5 reportado como OK por el usuario.
- Validación real de ZIP/release antes de publicar.

## Bloqueado

- QA funcional bloqueado hasta permiso explícito porque puede crear envíos, logs o acciones programadas.
- Release, ZIP, tag, push o deploy bloqueados hasta checklist release.
- QA de aplicación real de overrides Email String Editor reportada como OK por el usuario.

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
- QA Email String Editor E1-E5 reportado como OK por el usuario.
- Validación real de ZIP/release pendiente antes de publicar.

### Media prioridad

- `CHANGELOG.md` y `README.md` no reflejan todavía la entrada `2.0.1`, aunque `readme.txt` y cabecera sí están en `2.0.1`.
- `package.json` y `package-lock.json` mantienen versión `1.1.0`; pendiente decidir si es versión interna del build o si debe sincronizarse.
- El workflow release empaqueta solo archivos runtime con `rsync --filter=':- .gitignore'` e incluye verificación contra `node_modules/`, `_dev/`, `.git/`, `.github/`, `src/`, `package.json` y `package-lock.json`. ZIP de prueba en temporal: OK.
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
- QA de A4: programación/fecha OK reportado por usuario; queda QA general de preview si se hace bloque funcional completo.
- Decidir alcance del piloto 22MW-BACK.
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
- Email String Editor E4 QA OK: aplica overrides solo en emails WooCommerce según reporte del usuario.
- Que el fallback HPOS devuelve emails para producto `380` con los pedidos `655`, `656`, `711`.

## No volver a investigar

- Ruta real del plugin: `app/public/wp-content/plugins/Woo-Broadcast-Mailer/`.
- Rama de trabajo del plugin: `devWooBM`.
- Versión dev actual del plugin: `2.0.1.8`.
- Versión pública base: `2.0.1`.
- Text domain correcto del plugin: `wc-pbm`.
- Los planes de `_dev/_md/` (`PLAN_MULTI_FUENTE` y `PLAN_MIGRACION`) están implementados en v2.0.0. No re-investigar como pendientes.
- React admin está implementado.
- Flujo multi-fuente está implementado.
- Action Scheduler está integrado y A1 añade comprobación obligatoria antes de confirmar envíos.
- Updater GitHub Releases existe en `includes/updater.php`.
- Node local disponible en `/Users/22mw/.local/node-install/node-v22.11.0-darwin-arm64/bin`.
- `node_modules/` se copió desde `/Users/22mw/Local Sites/test/app/public/wp-content/plugins/woo-broadcast-mailer/node_modules/`.
- Email String Editor E1-E4 está implementado; E4 usa contexto de email WooCommerce para limitar `gettext`.
