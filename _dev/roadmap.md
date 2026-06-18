# Roadmap

## Plan A — Bloque técnico mínimo antes de QA/release

Este bloque agrupa los fixes detectados en auditoría. Cada código A debe mantenerse visible para poder consultarlo y ejecutarlo por partes.

| Código | Nombre | Estado | Prioridad | Qué contiene | Archivos probables | Validación |
|---|---|---|---|---|---|---|
| A1 | Action Scheduler obligatorio + aviso admin | Hecho | Alta | Comprobar disponibilidad de Action Scheduler, mostrar estado en admin y bloquear envío/programación si falta. | `woo-broadcast-mailer.php`, `includes/functions-email.php`, `includes/ajax-handlers.php` | `php -l`, `git diff --check`, revisión visual admin |
| A2 | Estados/logs reales | Pendiente | Alta | Evitar que `completed` signifique solo lotes programados; definir estado real de cola/ejecución/finalización. | `includes/ajax-handlers.php`, `includes/functions-scheduled.php`, `src/admin/components/ScheduledLogsPanel.js` si aplica | `php -l`, QA logs/estado |
| A3 | Snapshot seguro de destinatarios | Pendiente | Alta | No borrar `pbm_scheduled_recipients_{id}` hasta confirmar programación correcta de lotes. | `includes/functions-scheduled.php`, `includes/functions-email.php` | `php -l`, QA programación |
| A4 | Preview no obsoleto | Pendiente | Alta | Invalidar preview cuando cambie audiencia, manuales, fuente, selector, lote o fecha antes de enviar. | `src/admin/App.js` y build posterior | build, QA visual |
| A5 | Escape de `{customer_name}` | Hecho | Media/Alta | Escapar el nombre del destinatario antes de insertarlo en email HTML y validar email destino. | `includes/functions-email.php` | `php -l`, `git diff --check`; prueba con caracteres HTML queda pendiente de QA |
| A6 | Borrado por IDs seguro | Hecho | Media/Alta | Impedir que el borrado individual y por IDs elimine envíos `pending`, `running` o inexistentes. | `includes/ajax-handlers.php`, `includes/functions-scheduled.php` | `php -l`, `git diff --check`; QA endpoint/listado pendiente |
| A7 | ZIP/release sin `_dev/` | Pendiente antes de release | Alta release | Ajustar workflow/exclusiones para que `_dev/` no entre en ZIP/release. | `.github/workflows/release.yml` | revisar ZIP generado |

## Urgente

- A2 — Estados/logs reales.
- A3 — Snapshot seguro de destinatarios.
- A4 — Preview no obsoleto.
- A7 — ZIP/release sin `_dev/` antes de release.

## Recomendado

- Validar A6 en listado/endpoint con registros completados, cancelados, pendientes y en ejecución.
- Validar A1 en rutas AJAX si se hace QA funcional.
- QA funcional controlado del flujo principal: preview de audiencia global, envío instantáneo, envío programado, logs y borrado.
- Añadir límites máximos server-side a `batch_size` y `emails_per_hour`.
- Sustituir logs HTML con `dangerouslySetInnerHTML` por JSON renderizado en React, o aplicar whitelist estricta.
- Sincronizar documentación de release `2.0.1`: `CHANGELOG.md` y, si aplica, `README.md`.
- Decidir si `package.json` y `package-lock.json` deben seguir en versión interna `1.1.0` o sincronizarse con versión plugin `2.0.1`.
- Confirmar si `functions-scheduled.php` contiene flujo legacy todavía usado o si puede retirarse en una fase de limpieza controlada.
- Validar que `build/` esté sincronizado con `src/`.
- Revisar host/HTTPS del paquete descargado por updater.
- Probar updater en staging antes de release.

## Futuro

- Incorporar Email String Editor como módulo planificado, no copiando `_dev/_md/WooEmailStringEditor.php` tal cual.
- Fase recomendada Email String Editor: submenú separado, selección por categoría/origen/plantilla/idioma y compatibilidad con `wc_custom_email_strings`.
- Fase 2 Mail Mint: ampliar integración más allá de listas básicas si se requiere.
- Mejora de logs: mostrar desglose de audiencia compuesta por fuente en historial.
- Separar responsabilidades de archivos grandes si se aborda una mejora mediana o grande.
- Añadir estados más expresivos para envíos: `queued`, `running`, `completed`, `failed`, `cancelled`.
- Revisar integración Mail Mint contra documentación/API real si se amplía funcionalidad.

## Bloqueado

- QA funcional bloqueado hasta permiso explícito para pruebas que puedan crear envíos, logs o acciones programadas.
- Limpieza de legacy bloqueada hasta confirmar si esas funciones siguen llamadas.
- Eliminación de `_dev/_md/` bloqueada hasta decisión explícita del usuario.
- Release, ZIP, tag, push o deploy bloqueados hasta permiso explícito y checklist release.
- Incorporación Email String Editor bloqueada hasta decidir submenú/pestaña, alcance de `gettext`, idioma multiidioma y estrategia de datos.

## Descartado

- Reabrir la migración React como si estuviera pendiente.
- Reabrir el plan multi-fuente como si estuviera pendiente.
- Cambiar el text domain `wc-pbm` por `vfwoo`.
- Hacer refactor grande sin tarea específica aprobada.
- Copiar `WooEmailStringEditor.php` tal cual dentro del plugin sin adaptación.

## Hecho

- A1 aplicado: Action Scheduler obligatorio + aviso admin.
- A5 aplicado: escape de `{customer_name}` y validación de email destino.
- A6 aplicado: borrado individual y por IDs limitado a envíos completados o cancelados.
