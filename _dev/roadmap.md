# Roadmap

## Plan A — Bloque técnico mínimo antes de QA/release

Este bloque agrupa los fixes detectados en auditoría. Cada código A debe mantenerse visible para poder consultarlo y ejecutarlo por partes.

| Código | Nombre | Estado | Prioridad | Qué contiene | Archivos probables | Validación |
|---|---|---|---|---|---|---|
| A1 | Action Scheduler obligatorio + aviso admin | Hecho | Alta | Comprobar disponibilidad de Action Scheduler, mostrar estado en admin y bloquear envío/programación si falta. | `woo-broadcast-mailer.php`, `includes/functions-email.php`, `includes/ajax-handlers.php` | `php -l`, `git diff --check`, revisión visual admin |
| A2 | Estados/logs reales | Hecho | Alta | Evitar que `completed` signifique solo lotes programados; mantener `running` hasta que logs acumulados cubran los mensajes esperados. | `includes/ajax-handlers.php`, `includes/functions-email.php`, `includes/functions-scheduled.php` | `php -l`, `git diff --check`; QA logs/estado pendiente |
| A3 | Snapshot seguro de destinatarios | Hecho | Alta | No borrar `pbm_scheduled_recipients_{id}` al leer snapshot; borrarlo al completar o eliminar envío. | `includes/functions-scheduled.php` | `php -l`; QA programación pendiente |
| A4 | Preview no obsoleto | Hecho | Alta | Invalidar preview cuando cambie audiencia, manuales, lote o emails por hora antes de enviar. Programación y fecha/hora no invalidan preview. | `src/admin/App.js`, `build/index.js` | `npm run build`, QA visual pendiente |
| A5 | Escape de `{customer_name}` | Hecho | Media/Alta | Escapar el nombre del destinatario antes de insertarlo en email HTML y validar email destino. | `includes/functions-email.php` | `php -l`, `git diff --check`; prueba con caracteres HTML queda pendiente de QA |
| A6 | Borrado por IDs seguro | Hecho | Media/Alta | Impedir que el borrado individual y por IDs elimine envíos `pending`, `running` o inexistentes. | `includes/ajax-handlers.php`, `includes/functions-scheduled.php` | `php -l`, `git diff --check`; QA endpoint/listado pendiente |
| A7 | ZIP/release sin `_dev/` | Hecho | Alta release | Excluir `_dev/` del ZIP/release en workflow. | `.github/workflows/release.yml` | revisión workflow; ZIP real pendiente de release |

## Plan E — Email String Editor

| Código | Nombre | Estado | Prioridad | Qué contiene | Archivos probables | Validación |
|---|---|---|---|---|---|---|
| E1 | Módulo base | Hecho | Alta | Cargador, clases base, bootstrap desde plugin principal y submenú WooCommerce. | `woo-broadcast-mailer.php`, `includes/email-string-editor.php`, `includes/email-string-editor/*` | `php -l`, `git diff --check` |
| E2 | Admin MVP | Hecho | Alta | Selector de plantilla, buscador, listado de strings y pantalla de cambios guardados. | `includes/email-string-editor/class-admin-page.php`, `class-template-scanner.php` | `php -l`, QA admin pendiente |
| E2.1 | Búsqueda global | Hecho | Alta | Buscar strings en todas las plantillas permitidas cuando no se selecciona una plantilla concreta. | `includes/email-string-editor/class-admin-page.php` | `php -l`, QA admin pendiente |
| E2.2 | Edición multiidioma en pantalla única | Hecho | Alta | Mostrar todos los idiomas disponibles dentro de cada string/email y permitir editar cada idioma sin cambiar de pantalla. | `includes/email-string-editor/class-admin-page.php` | `php -l`, QA admin pendiente |
| E2.3 | Búsqueda multiidioma | Hecho | Alta | Buscar por original, traducciones WooCommerce por idioma y personalizaciones guardadas. | `includes/email-string-editor/class-admin-page.php` | `php -l`, QA admin pendiente |
| E3 | Guardado/borrado | Hecho | Alta | Guardar en `pbm_email_string_overrides`, leer compatibilidad `wc_custom_email_strings` y borrar personalizaciones. | `includes/email-string-editor/class-string-repository.php`, `class-admin-page.php` | `php -l`, QA admin pendiente |
| E3.1 | Guardado multiidioma | Hecho | Alta | Procesar y guardar personalizaciones por idioma desde la misma pantalla del editor. | `includes/email-string-editor/class-admin-page.php`, `class-string-repository.php` | `php -l`, QA admin pendiente |
| E3.2 | Edición desde cambios guardados | Hecho | Alta | Editar y guardar personalizaciones directamente desde la pestaña Cambios guardados. | `includes/email-string-editor/class-admin-page.php`, `class-email-string-editor.php` | `php -l`, QA admin pendiente |
| E4 | Aplicación en emails | Hecho | Alta | Aplicar overrides con `gettext` solo mientras WooCommerce renderiza emails y resolver idioma real. | `class-gettext-filter.php`, `class-email-string-editor.php`, `email-string-editor.php` | `php -l`, `git diff --check`; QA email real pendiente |
| E5 | QA Email String Editor | Hecho | Alta | Probar pantalla, guardado, borrado, permisos, nonces, aplicación E4 y no regresión WooCommerce. | Admin WordPress / email WooCommerce | QA OK reportado por usuario |

## Plan R — React Editor de emails

| Código | Nombre | Estado | Prioridad | Qué contiene | Archivos probables | Validación |
|---|---|---|---|---|---|---|
| R1 | React Editor de emails | Hecho | Alta | Migrar la pantalla del Editor de emails a React, con AJAX seguro para búsqueda, guardado, edición, borrado y cambios guardados. | `includes/email-string-editor/*`, `src/admin/email-editor/*`, `src/admin/index.js`, `build/index.js` | `php -l`, `npm run build`, `git diff --check`; QA funcional reportado por usuario |

## Plan B — 22MW-BACK admin visual

| Código | Nombre | Estado | Prioridad | Qué contiene | Archivos probables | Validación |
|---|---|---|---|---|---|---|
| B0 | Inventario admin 22MW-BACK | Pendiente | Alta | Confirmar pantallas admin, flujos que no deben romperse y alcance visual exacto. | Admin Broadcast, Editor de emails React, assets admin | Revisión sin cambios |
| B1 | Piloto Broadcast principal | Pendiente | Alta | Aplicar shell/header/menú/cards/formularios 22MW al admin principal sin cambiar lógica funcional. | `src/admin/App.js`, assets admin, build | Build, QA visual y QA de preview/envío sin efectos |
| B2 | Extensión Editor de emails | Pendiente | Media/Alta | Unificar visual del Editor de emails React tras validar B1. | `src/admin/email-editor/*`, assets admin, build | QA búsqueda/guardado/edición/borrado |
| B3 | Submenús y orden de producto | Pendiente | Media | Agrupar navegación si hay densidad suficiente: General, Emails, Envíos, Avanzado, Estado. | React admin | QA navegación |
| B4 | Pulido componentes 22MW | Pendiente | Media | Botones, avisos, loading/error/success, empty states, switches, dark/light si se aprueba. | assets admin, React admin | QA visual dark/light si aplica |

## Urgente

- QA funcional controlado del Plan A completo.
- QA Email String Editor E1-E5 reportado como OK por el usuario.
- QA específico A4: OK reportado por usuario.
- Decidir alcance del piloto 22MW-BACK antes de arquitectura/implementación.
- Validar bugfix HPOS con producto `380` y pedidos `655`, `656`, `711`.
- A7 requiere validación real de ZIP antes de cualquier release.

## Recomendado

- Ejecutar QA de A2 con envío instantáneo, envío programado, ejecución de lotes y logs acumulados.
- Ejecutar QA de A4 en admin: preview, cambio de audiencia/configuración, bloqueo de envío y no invalidación al programar. Estado: OK usuario para programación.
- Validar A6 en listado/endpoint con registros completados, cancelados, pendientes y en ejecución.
- Validar A1 en rutas AJAX si se hace QA funcional.
- Añadir límites máximos server-side a `batch_size` y `emails_per_hour`.
- Sustituir logs HTML con `dangerouslySetInnerHTML` por JSON renderizado en React, o aplicar whitelist estricta.
- Sincronizar documentación de release `2.0.1`: `CHANGELOG.md` y, si aplica, `README.md`.
- Decidir si `package.json` y `package-lock.json` deben seguir en versión interna `1.1.0` o sincronizarse con versión plugin `2.0.1`.
- Confirmar si `functions-scheduled.php` contiene flujo legacy todavía usado o si puede retirarse en una fase de limpieza controlada.
- Revisar host/HTTPS del paquete descargado por updater.
- Probar updater en staging antes de release.

## Futuro

- Fase 2 Mail Mint: ampliar integración más allá de listas básicas si se requiere.
- Mejora de logs: mostrar desglose de audiencia compuesta por fuente en historial.
- Separar responsabilidades de archivos grandes si se aborda una mejora mediana o grande.
- Añadir estados más expresivos para envíos: `queued`, `running`, `completed`, `failed`, `cancelled`.
- Revisar integración Mail Mint contra documentación/API real si se amplía funcionalidad.

## Bloqueado

- QA funcional bloqueado hasta permiso explícito para pruebas que puedan crear envíos, logs o acciones programadas.
- Limpieza de legacy bloqueada hasta confirmar si esas funciones siguen llamadas.
- Eliminación de `_dev/_md/` bloqueada hasta decisión explícita del usuario.
- Release, ZIP, tag y deploy bloqueados hasta permiso explícito y checklist release.
- QA de aplicación real de overrides Email String Editor reportada como OK.

## Descartado

- Reabrir la migración React como si estuviera pendiente.
- Reabrir el plan multi-fuente como si estuviera pendiente.
- Cambiar el text domain `wc-pbm` por `vfwoo`.
- Hacer refactor grande sin tarea específica aprobada.
- Copiar `WooEmailStringEditor.php` tal cual dentro del plugin sin adaptación.

## Hecho

- A1 aplicado: Action Scheduler obligatorio + aviso admin.
- A2 aplicado: `completed` queda ligado a logs acumulados y no solo a lotes programados.
- A3 aplicado: snapshot se conserva hasta completar/eliminar.
- A4 aplicado y compilado: preview obsoleta bloquea envío; programación y fecha/hora no invalidan preview.
- A5 aplicado: escape de `{customer_name}` y validación de email destino.
- A6 aplicado: borrado individual y por IDs limitado a envíos completados o cancelados.
- A7 aplicado: workflow release excluye `_dev/`.
- E1 aplicado: módulo base Email String Editor cargado desde el plugin.
- E2 aplicado: admin MVP con selector de plantilla, buscador y listado de strings.
- E2.1 aplicado: búsqueda global en todas las plantillas permitidas.
- E2.2 aplicado: edición de todos los idiomas disponibles desde la misma pantalla.
- E2.3 aplicado: búsqueda multiidioma por original, traducciones WooCommerce y personalizaciones guardadas.
- E3 aplicado: guardado/borrado en `pbm_email_string_overrides` con lectura compatible de `wc_custom_email_strings`.
- E3.1 aplicado: guardado multiidioma desde una única pantalla.
- E3.2 aplicado: edición directa desde Cambios guardados.
- E4 aplicado: overrides reales limitados al contexto de emails WooCommerce.
- E5 aplicado: QA Email String Editor OK reportado por usuario.
- R1 aplicado: Editor de emails migrado a React con AJAX seguro.
- A4 post-fix validado por usuario: programar envío no invalida preview.
