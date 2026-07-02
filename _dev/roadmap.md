# Roadmap

## Estado release

| Código | Nombre | Estado | Prioridad | Qué contiene | Archivos/áreas | Validación |
|---|---|---|---|---|---|---|
| RLS-2.3.0 | Release estable 2.3.0 | Hecho | Alta | HTML email, estado `failed`, borrado de atascados, plantillas asunto + body. | `includes/functions-email.php`, `includes/functions-scheduled.php`, `includes/ajax-handlers.php`, `src/admin/App.js`, docs | Build OK, `php -l`, tag `v2.3.0` |
| RLS-2.4.0 | Release estable 2.4.0 | Hecho | Alta | Shortcodes de destinatario, fallback por email, TinyMCE con fuente/tamaño/color y toolbar corregida. | `woo-broadcast-mailer.php`, `includes/functions-email.php`, `src/`, `build/`, docs | Tag `v2.4.0` |
| RLS-2.5.0 | Release estable 2.5.0 | Hecho | Alta | Audiencia programada fija/dinámica, resumen legible de audiencia y LOG2/LOG3 MVP. | `includes/ajax-handlers.php`, `includes/functions-email.php`, `includes/functions-scheduled.php`, `src/`, `build/`, docs, `_dev/` | QA confirmado por usuario; commit/push/tag `v2.5.0` hecho |
| RLS-2.6.0 | Release estable 2.6.0 | Hecho | Alta | Sistema toast React admin estilo AuthGate para mensajes principales, errores y avisos. | `src/admin/App.js`, `src/admin/components/AudienceBuilder.js`, `src/admin/components/ScheduledLogsPanel.js`, `assets/css/admin.css`, `build/`, docs, `_dev/` | QA visual confirmado por usuario; commit/push/tag `v2.6.0` hecho |
| ZIP-2.6.0 | ZIP runtime limpio | Pendiente | Alta | GitHub Actions debe empaquetar solo runtime y excluir internos. | `.github/workflows/release.yml` | Pendiente comprobar release tras tag |

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
| UI5 | Toast admin React | Hecho | Media | Mensajes success/error/warning del panel admin con patrón AuthGate adaptado y namespace `.pbm-admin-toast`. | `src/admin/App.js`, `src/admin/components/AudienceBuilder.js`, `src/admin/components/ScheduledLogsPanel.js`, `assets/css/admin.css`, `build/` | Build OK; QA visual confirmado por usuario |

## Plan AD — Audiencia dinámica programada

| Código | Nombre | Estado | Prioridad | Qué contiene | Áreas/archivos probables | Validación prevista | Bloqueos |
|---|---|---|---|---|---|---|---|
| AD1 | Análisis funcional fija/dinámica | Hecho | Alta | Modo fijo vs dinámico, manuales fijos, exclusiones persistentes, UI al programar. | `_dev/departamentos/analista.md` | Revisión usuario | Ninguno |
| AD2 | Arquitectura técnica | Hecho | Alta | Guardar `audience_mode` y configuración de audiencia; recalcular en ejecución; crear snapshot final. | `_dev/departamentos/arquitecto.md` | Revisión técnica | Ninguno |
| AD3 | Implementación MVP | Hecho | Alta | UI fija/dinámica, persistencia de metadatos, recalcular al ejecutar. | `src/admin/App.js`, `includes/ajax-handlers.php`, `includes/functions-scheduled.php`, `build/` | Build OK; `php -l` OK; `git diff --check` OK | Ninguno |
| AD4 | QA funcional | Hecho | Alta | Probar rol dinámico, manuales, exclusiones, fuente no disponible y logs. | Admin + Action Scheduler | QA confirmado por usuario; no ejecutado por agente | Ninguno |

## Plan MP — Integración MailPoet

| Código | Nombre | Estado | Prioridad | Qué contiene | Áreas/archivos probables | Validación prevista | Bloqueos |
|---|---|---|---|---|---|---|---|
| MP1 | Fuente MailPoet mínima | Hecho | Alta | Fuente `mailpoet`, detección por API pública, listas, conteo y destinatarios solo `subscribed`, sin SQL directo. | `includes/functions-products.php`, `includes/ajax-handlers.php`, `includes/functions-scheduled.php`, `src/admin/`, `build/` | `php -l` OK; build OK; `git diff --check` OK | QA funcional con MailPoet real pendiente |
| MP1B | Segmentos privados MailPoet en selector | Hecho | Alta | Complementar `getLists()` con lectura interna mínima de segmentos activos `wp_users` y `woocommerce_users`, merge sin duplicados y labels claros; conteo/resolución siguen por API pública con `status = subscribed`. | `includes/functions-products.php` | `php -l` OK; `git diff --check` OK; QA funcional pendiente | Depende de estructura interna MailPoet para descubrimiento |
| MP2 | QA MailPoet real | Pendiente | Alta | Probar selector, búsqueda, conteo, preview, audiencia global, dinámica programada, segmentos privados/predeterminados y MailPoet desactivado. | Admin WordPress + MailPoet | Entorno con MailPoet y listas reales | Requiere pruebas con efecto controladas |

## Plan LOG — Audiencia legible y logs informativos

| Código | Nombre | Estado | Prioridad | Qué contiene | Áreas/archivos probables | Validación prevista | Bloqueos |
|---|---|---|---|---|---|---|---|
| LOG1 | Análisis funcional | Hecho | Alta | Define qué mostrar en resumen de audiencia, qué puede registrarse sin tracking y qué requiere tracking nuevo. | `_dev/departamentos/analista.md`, `_dev/roadmap.md`, `_dev/decisiones.md` | Revisión usuario | Ninguno |
| LOG2 | Resumen de audiencia legible | Hecho | Alta | Guardar `audience_snapshot` descriptivo en `pbm_delivery_meta_{id}` sin migración; modo `fixed`/`dynamic`; preview/final count; items con labels; manuales y exclusiones; fallback histórico actual si falta snapshot. | `includes/ajax-handlers.php`, `includes/functions-scheduled.php` | `php -l` OK; `git diff --check` OK; QA confirmado por usuario | Ninguno |
| LOG3 | Log básico por destinatario | Hecho | Alta | Guardar eventos por destinatario en option no autoload `pbm_delivery_events_{id}`: email, status, timestamp, error técnico básico y batch index opcional; borrar eventos al eliminar envío. | `includes/functions-email.php`, `includes/functions-scheduled.php`, `includes/ajax-handlers.php` | `php -l` OK; `git diff --check` OK; QA confirmado por usuario | Revisar privacidad/retención si se amplía uso o tracking |
| LOG4 | Tracking de aperturas/clics | Futuro/Recomendado | Media | Aperturas mediante pixel y clics mediante redirección/reescritura de enlaces. Debe advertir precisión limitada. No se implementa ahora. | Sistema de tracking, URLs, plantilla email | QA técnica + privacidad | Requiere decisión RGPD y arquitectura nueva |
| LOG5 | Rebotes, bajas y entrega real | Futuro | Media | Rebotes/entrega real vía proveedor SMTP/webhook; bajas con sistema real de unsubscribe. | Integración proveedor, sistema de bajas | QA con proveedor/staging | Depende de proveedor SMTP y decisión de privacidad |

## Urgente

- Confirmar GitHub Release/ZIP tras push de tag `v2.6.0`.

## Recomendado

- Probar updater en staging.
- MP2: validar integración MailPoet en entorno controlado con MailPoet activo y con MailPoet desactivado.
- LOG4: evaluar aperturas/clics como mejora futura si se acepta tracking y privacidad.

## Futuro

- Sistema real de baja/exclusión permanente antes de añadir `{unsubscribe_note}` automático.
- Mail Mint fase 2 con API/documentación real si se amplía integración.
- Separar archivos grandes cuando haya una fase de mantenimiento.
- Plan LOG4: aperturas y clics mediante tracking específico, no implementados ahora.
- Plan LOG5: rebotes y entrega real mediante proveedor SMTP/webhook; depende del proveedor y queda fuera del ciclo actual.

## Hecho

- Release `2.4.0` publicada.
- Release `2.5.0` publicada en `devWooBM` con tag `v2.5.0`.
- Release `2.6.0` publicada en `devWooBM` con tag `v2.6.0`.
- AD3/AD4 y LOG2/LOG3 consolidados para release `2.5.0` con QA confirmado por usuario.
- UI5: toast admin React implementado, build generado y QA visual confirmado por usuario.
- MP1B: selector MailPoet complementa API pública con segmentos internos activos `wp_users` y `woocommerce_users`, sin duplicar IDs.

## Bloqueado

- Deploy/producción hasta confirmar entorno, backup/staging y rollback.
- Borrado de `_dev/_md/` hasta decisión explícita.
- Tracking avanzado de aperturas/clics/rebotes hasta decisión de privacidad y proveedor.

## Descartado

- Añadir otro editor Node para el mensaje: se mantiene `wp_editor`/TinyMCE nativo.
- Reabrir migración React como pendiente.
- Cambiar text domain `wc-pbm`.
