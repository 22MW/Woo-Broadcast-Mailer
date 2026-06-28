# Roadmap

## Estado release

| Código | Nombre | Estado | Prioridad | Qué contiene | Archivos/áreas | Validación |
|---|---|---|---|---|---|---|
| RLS-2.3.0 | Release estable 2.3.0 | Hecho | Alta | HTML email, estado `failed`, borrado de atascados, plantillas asunto + body. | `includes/functions-email.php`, `includes/functions-scheduled.php`, `includes/ajax-handlers.php`, `src/admin/App.js`, docs | Build OK, `php -l`, tag `v2.3.0` |
| RLS-2.4.0 | Release estable 2.4.0 | En curso | Alta | Shortcodes de destinatario, fallback por email, TinyMCE con fuente/tamaño/color y toolbar corregida. | `woo-broadcast-mailer.php`, `includes/functions-email.php`, docs, `_dev/` | Pendiente validación final, commit, push y tag `v2.4.0` |
| ZIP-2.4.0 | ZIP runtime limpio | Pendiente | Alta | GitHub Actions debe empaquetar solo runtime y excluir internos. | `.github/workflows/release.yml` | Pendiente comprobar release tras tag |

## Plan A — Bloque técnico mínimo

| Código | Nombre | Estado | Prioridad | Qué contiene | Archivos/áreas | Validación |
|---|---|---|---|---|---|---|
| A1 | Action Scheduler obligatorio + aviso admin | Hecho | Alta | Comprobar disponibilidad, mostrar estado y bloquear envío/programación si falta. | `woo-broadcast-mailer.php`, `includes/functions-email.php`, `includes/ajax-handlers.php` | Técnica OK |
| A2 | Estados/logs reales | Hecho | Alta | Mantener envíos en `running` hasta cubrir logs esperados; añadido `failed` en 2.3.0. | `includes/functions-email.php`, `includes/functions-scheduled.php` | Técnica OK; QA funcional pendiente |
| A3 | Snapshot seguro de destinatarios | Hecho | Alta | Conservar snapshot hasta completar/eliminar. | `includes/functions-scheduled.php` | Técnica OK; QA funcional pendiente |
| A4 | Preview no obsoleto | Hecho | Alta | Bloquear envío si cambia audiencia/configuración relevante; programación no invalida preview. | `src/admin/App.js`, `build/index.js` | Build OK; QA usuario OK |
| A5 | Shortcodes destinatario | Hecho | Media/Alta | `{customer_name}`, `{first_name}`, `{last_name}`, `{email}`, `{current_date}` con fallback por email. | `includes/functions-email.php` | `php -l`; QA email real pendiente |
| A6 | Borrado por IDs seguro | Hecho | Media/Alta | Borrar completados/cancelados/fallidos y running sin acciones pendientes. | `includes/ajax-handlers.php`, `includes/functions-scheduled.php` | Técnica OK; QA funcional pendiente |
| A7 | ZIP/release sin internos | Hecho | Alta | ZIP limpio de release. | `.github/workflows/release.yml` | Confirmado en releases previas |

## Plan UI — Editor de mensaje

| Código | Nombre | Estado | Prioridad | Qué contiene | Archivos/áreas | Validación |
|---|---|---|---|---|---|---|
| UI1 | Plantillas asunto + body | Hecho | Alta | Guardar y cargar asunto junto al cuerpo del mensaje. | `includes/ajax-handlers.php`, `src/admin/App.js`, `build/` | Build OK; QA manual pendiente |
| UI2 | TinyMCE avanzado | Hecho | Media | Selector de fuente, tamaño, color de texto y color de fondo. | `woo-broadcast-mailer.php` | `php -l`; QA visual pendiente |
| UI3 | Toolbar sin duplicado | Hecho | Media | Mantener color texto/fondo juntos y eliminar segunda toolbar separada. | `woo-broadcast-mailer.php` | `php -l`; QA visual pendiente |
| UI4 | H1/H2 inline | Hecho | Media | Estilos inline para `h1` y `h2` en emails. | `includes/functions-email.php` | `php -l`; QA email real pendiente |

## Urgente

- Completar release `2.4.0`: versión, changelog, readme, validación, commit, push y tag.
- Confirmar GitHub Release/ZIP tras push de tag.
- QA funcional controlado si el plugin se va a usar en producción.

## Recomendado

- Probar updater en staging.
- QA visual del editor TinyMCE tras limpiar caché.
- QA de shortcodes con usuario existente y email manual.

## Futuro

- Sistema real de baja/exclusión permanente antes de añadir `{unsubscribe_note}` automático.
- Mail Mint fase 2 con API/documentación real si se amplía integración.
- Separar archivos grandes cuando haya una fase de mantenimiento.

## Bloqueado

- QA funcional con efectos hasta permiso explícito.
- Deploy/producción hasta confirmar entorno, backup/staging y rollback.
- Borrado de `_dev/_md/` hasta decisión explícita.

## Descartado

- Añadir otro editor Node para el mensaje: se mantiene `wp_editor`/TinyMCE nativo.
- Reabrir migración React como pendiente.
- Cambiar text domain `wc-pbm`.
