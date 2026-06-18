# Release notes internas

## 2.0.1.3 — Dev checkpoint — 2026-06-18

- Email String Editor E1-E3.2 + E2.3 añadido como módulo admin bajo WooCommerce.
- Búsqueda global en plantillas permitidas y búsqueda multiidioma por original, traducciones y personalizaciones.
- Edición multiidioma desde una misma pantalla y edición directa desde Cambios guardados.
- Guardado en `pbm_email_string_overrides` con lectura compatible de `wc_custom_email_strings`.
- E4 sigue pendiente: no se aplican overrides reales en emails hasta confirmar hook seguro.

## 2.0.1.2 — Dev checkpoint — 2026-06-18

- A2: estados/logs reales. `completed` queda ligado a logs acumulados y no solo a lotes programados.
- A3: snapshot seguro de destinatarios. El snapshot se conserva hasta completar o eliminar.
- A4: preview no obsoleto. Si cambia audiencia/configuración, el envío queda bloqueado hasta recalcular preview.
- A7: workflow release excluye `_dev/` del ZIP.
- Build React ejecutado con Node local y `build/` actualizado.
- QA funcional confirmado por usuario para A4.

## 2.0.1.1 — Dev checkpoint — 2026-06-18

- A1: Action Scheduler obligatorio con aviso en admin y bloqueo de envíos si no está disponible.
- A5: escape de `{customer_name}` y validación de email destino.
- A6: borrado individual y por IDs limitado a envíos completados o cancelados.
- Memoria `_dev/` consolidada con Plan A, visual interno y análisis Email String Editor.

## Pendiente antes de release estable

- QA funcional completo con envíos/logs reales.
- ZIP de prueba y revisión de exclusiones.
- Decidir `CHANGELOG.md`/`README.md` para release pública.
