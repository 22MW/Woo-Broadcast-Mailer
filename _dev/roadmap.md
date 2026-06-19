# Roadmap

## Estado release

| Código | Nombre | Estado | Prioridad | Qué contiene | Archivos/áreas | Validación |
|---|---|---|---|---|---|---|
| RLS-2.0.2 | Release estable 2.0.2 | Hecho | Alta | Consolidación de checkpoints dev, versión estable, changelog, readme, tag y GitHub Release. | `woo-broadcast-mailer.php`, `readme.txt`, `CHANGELOG.md`, GitHub Release | `php -l`, `npm run build`, ZIP limpio, tag `v2.0.2` |
| ZIP-2.0.2 | ZIP runtime limpio | Hecho | Alta | Excluir `_dev/`, `node_modules/`, `src/`, paquetes Node, `.git`, `.github`, cachés y locales. | `.github/workflows/release.yml` | ZIP temporal limpio + asset GitHub publicado |

## Plan A — Bloque técnico mínimo

| Código | Nombre | Estado | Prioridad | Qué contiene | Archivos/áreas | Validación |
|---|---|---|---|---|---|---|
| A1 | Action Scheduler obligatorio + aviso admin | Hecho | Alta | Comprobar disponibilidad, mostrar estado y bloquear envío/programación si falta. | `woo-broadcast-mailer.php`, `includes/functions-email.php`, `includes/ajax-handlers.php` | `php -l`, revisión admin |
| A2 | Estados/logs reales | Hecho | Alta | Mantener envíos en `running` hasta cubrir logs esperados. | `includes/functions-email.php`, `includes/functions-scheduled.php` | Técnica OK; QA funcional pendiente |
| A3 | Snapshot seguro de destinatarios | Hecho | Alta | Conservar snapshot hasta completar/eliminar. | `includes/functions-scheduled.php` | Técnica OK; QA funcional pendiente |
| A4 | Preview no obsoleto | Hecho | Alta | Bloquear envío si cambia audiencia/configuración relevante; programación no invalida preview. | `src/admin/App.js`, `build/index.js` | Build OK; QA A4 usuario OK |
| A5 | Escape de `{customer_name}` | Hecho | Media/Alta | Escapar nombre y validar email destino. | `includes/functions-email.php` | `php -l`; prueba caracteres HTML pendiente |
| A6 | Borrado por IDs seguro | Hecho | Media/Alta | Borrar solo completados/cancelados. | `includes/ajax-handlers.php`, `includes/functions-scheduled.php` | Técnica OK; QA funcional pendiente |
| A7 | ZIP/release sin internos | Hecho | Alta | ZIP limpio de release. | `.github/workflows/release.yml` | ZIP limpio confirmado |

## Plan E — Email String Editor

| Código | Nombre | Estado | Prioridad | Qué contiene | Archivos/áreas | Validación |
|---|---|---|---|---|---|---|
| E1 | Módulo base | Hecho | Alta | Cargador, clases base y submenú WooCommerce. | `includes/email-string-editor.php`, `includes/email-string-editor/*` | `php -l` |
| E2 | Admin MVP | Hecho | Alta | Selector de plantilla, búsqueda, listado y cambios guardados. | Email String Editor | QA usuario OK |
| E2.1 | Búsqueda global | Hecho | Alta | Buscar en todas las plantillas permitidas si no se elige plantilla. | React/PHP editor | QA usuario OK |
| E2.2 | Edición multiidioma | Hecho | Alta | Editar todos los idiomas disponibles desde una pantalla. | Editor emails | QA usuario OK |
| E2.3 | Búsqueda multiidioma | Hecho | Alta | Buscar original, traducciones y personalizaciones. | Editor emails | QA usuario OK |
| E3 | Guardado/borrado | Hecho | Alta | Guardar en `pbm_email_string_overrides` y borrar personalizaciones. | Repositorio editor | QA usuario OK |
| E3.1 | Guardado multiidioma | Hecho | Alta | Guardar personalizaciones por idioma. | Editor emails | QA usuario OK |
| E3.2 | Edición desde cambios guardados | Hecho | Alta | Editar desde la pestaña de cambios. | Editor emails | QA usuario OK |
| E4 | Aplicación en emails | Hecho | Alta | Aplicar overrides solo durante emails WooCommerce. | `class-gettext-filter.php` | QA usuario OK |
| E5 | QA Email String Editor | Hecho | Alta | Validación funcional reportada por usuario. | Admin/email WooCommerce | OK usuario |
| E6 | Títulos/H1/subjects | Hecho | Alta | Listar título, heading/H1 y subject desde `WC_Email` y aplicar overrides a headings/subjects dinámicos. | `class-template-scanner.php`, `class-email-string-editor.php`, `class-gettext-filter.php` | `php -l`; QA email real pendiente |
| E7 | Ocultar strings | Hecho | Media/Alta | Checkbox para ocultar un texto y renderizarlo vacío mediante marcador interno. | `class-string-repository.php`, `class-gettext-filter.php`, `src/admin/email-editor/*`, `build/` | Build OK; QA email real pendiente |

## Plan R — React Editor de emails

| Código | Nombre | Estado | Prioridad | Qué contiene | Archivos/áreas | Validación |
|---|---|---|---|---|---|---|
| R1 | React Editor de emails | Hecho | Alta | Interfaz React con AJAX seguro para búsqueda, guardado, edición y borrado. | `src/admin/email-editor/*`, `includes/email-string-editor/*` | Build OK; QA usuario OK |

## Urgente

- QA funcional controlado de envíos reales si el plugin se va a usar en producción.
- Probar updater en staging.
- Confirmar transporte real de emails (`wp_mail`).
- Validar búsqueda y override real de headings/subjects en email controlado.
- Validar checkbox `Ocultar este texto` en email controlado.

## Recomendado

- Preparar documentación pública breve.
- Preparar resumen comercial interno.
- Decidir si `_dev/_md/` se conserva como histórico o se borra en una limpieza posterior.
- Revisar `dangerouslySetInnerHTML` en logs si se aborda hardening de seguridad.
- Añadir límites server-side a `batch_size` y `emails_per_hour` si se abre fase de robustez.

## Futuro

- 22MW-BACK como piloto visual, no rediseño completo automático.
- Mail Mint fase 2 con API/documentación real si se amplía integración.
- Mejorar logs con desglose de audiencia compuesta.
- Separar archivos grandes cuando haya una fase de mantenimiento.

## Bloqueado

- QA funcional con efectos hasta permiso explícito.
- Borrado de `_dev/_md/` hasta decisión explícita.
- Deploy/producción hasta confirmar entorno, backup/staging y rollback.

## Descartado

- Reabrir migración React como pendiente.
- Reabrir flujo multi-fuente como pendiente.
- Cambiar text domain `wc-pbm`.
- Copiar `WooEmailStringEditor.php` tal cual sin adaptación.
