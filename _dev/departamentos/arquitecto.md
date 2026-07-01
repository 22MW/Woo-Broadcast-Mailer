# Arquitecto

## Última actualización

2026-07-01

## Relevo breve

Arquitectura para portar mensajes tipo toast desde AuthGate a Woo Broadcast Mailer. No se implementó código. Adaptación mínima recomendada: crear helper React local `showToast(message, type)` en `src/admin/App.js`, renderizar toasts dentro de `.pbm-react-shell` y copiar solo los estilos necesarios de `.mw22-back-toast` adaptados a `.pbm-admin .pbm-back-toast`.

## Hecho en esta tarea

- AuthGate confirmado:
  - `assets/js/22mw-back.js` define `showToast(root, message, type)` y lo usa para `updated=1`.
  - `assets/js/authgate-back.js` duplica `showToast()` y lo usa en formularios AJAX.
  - `assets/css/22mw-back.css` contiene la base visual `.mw22-back-toast`, `.is-error`, `.is-hiding` y `@keyframes mw22-back-toast-in`.
  - `assets/css/authgate-back.css` reposiciona el toast en admin con `.authgate-back > .mw22-back-toast` y oculta notices clásicas.
- Woo Broadcast Mailer confirmado:
  - React vive en `src/admin/App.js`, `src/admin/components/AudienceBuilder.js`, `src/admin/email-editor/EmailStringEditorApp.js` y build en `build/`.
  - Mensajes actuales: `setMessage()` + `<Notice>` en `AudienceBuilder`, `setBroadcastListMessage()` + `pbm-react-notice`, y `window.alert()` en preview/envío/logs.
  - CSS admin principal: `assets/css/admin.css`; `src/admin/styles.css` solo importa comentario y genera build.
- Recomendación técnica mínima:
  - No copiar `22mw-back.js` completo: trae tema, switches y subnav no usados por Woo Broadcast Mailer.
  - Copiar solo la idea de `showToast`: cola/estado React o helper local, timeout 2800/3200, tipos `success`, `error`, opcional `warning`.
  - Sustituir mensajes principales de `App.js` por toast; dejar el cambio del Email String Editor como opcional si el usuario quiere “todo React/admin”.

## Pendiente / riesgos

- Requiere build con `npm run build` porque afecta `src/admin/App.js` y probablemente `build/index.js`/`build/index.css`.
- Riesgo de conflicto si se reutiliza `.mw22-back-toast`: mejor namespace `.pbm-back-toast` o `.pbm-admin-toast`.
- Si se quitan todos los `<Notice>`, revisar accesibilidad: añadir `role="status"`/`aria-live="polite"` y `role="alert"` para errores.
- `window.alert()` bloquea flujo; sustituirlo por toast cambia comportamiento UX pero no backend.
- Text domain confirmado para textos nuevos: `wc-pbm`.

## No volver a investigar

- AuthGate `showToast` está en `assets/js/22mw-back.js` y `assets/js/authgate-back.js`.
- Estilos base del toast AuthGate están en `assets/css/22mw-back.css` líneas 388-426; override admin en `assets/css/authgate-back.css` líneas 157-162.
- Woo Broadcast Mailer usa React compilado con `@wordpress/scripts`; cambiar `src/admin/*` requiere regenerar `build/`.
- Text domain de Woo Broadcast Mailer: `wc-pbm`.

## Relevo para

→ Desarrollador: implementar adaptación mínima en `src/admin/App.js`, `src/admin/components/AudienceBuilder.js` si se elimina el Notice actual, `assets/css/admin.css` y regenerar `build/index.js`/`build/index.css` con `npm run build`. Opcional: extender el mismo patrón a `src/admin/email-editor/EmailStringEditorApp.js` y `src/admin/components/ScheduledLogsPanel.js` si el alcance aprobado incluye todas las pantallas React/admin.
