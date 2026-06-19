# Release notes internas

## 2.0.2 — Release — 2026-06-19

- Consolidados los checkpoints dev `2.0.1.1` a `2.0.1.9` en una release estable.
- Endurecimiento de Action Scheduler, estados/logs reales, snapshots y preview obsoleta.
- Email String Editor con React admin, búsqueda multiidioma, edición por idioma y aplicación limitada a emails WooCommerce.
- Fallback HPOS para destinatarios por producto.
- Workflow de release ajustado para generar ZIP limpio sin `_dev/`, `node_modules/`, `src/`, `package.json`, `package-lock.json`, cachés ni archivos locales.
- ZIP de prueba en temporal validado como limpio.

## 2.0.1.8 — Dev checkpoint — 2026-06-19

- Ajustes visuales del admin Broadcast principal: fuentes a la izquierda; emails manuales, lista global y botón preview a la derecha.
- Resultado preview movido encima de `Asunto`.
- Añadido acceso directo `Editor de emails` en el header.
- Header del Editor de emails alineado visualmente con Broadcast e incluye acceso de vuelta a Broadcast.
- Editor de emails reutiliza clases visuales del Broadcast para filtros, tablas y tabs.
- Tabs del Editor sustituidos por botones estilo fuente y descripción movida al bloque principal.
- Build React actualizado.

## 2.0.1.7 — Dev checkpoint — 2026-06-19

- A4 ajustado: activar programación o cambiar fecha/hora ya no invalida la preview porque no cambia destinatarios.
- Build React actualizado tras el ajuste.
- QA A4 post-fix reportado OK por usuario.
- Plan B 22MW-BACK añadido como propuesta por fases.
- Ajuste visual posterior sin checkpoint: fuentes a la izquierda; emails manuales, lista global y botón preview a la derecha; resultado preview encima de `Asunto`.

## 2.0.1.6 — Dev checkpoint — 2026-06-19

- R1 aplicado: Editor de emails migrado a React.
- Añadido controlador AJAX para bootstrap, búsqueda, guardado, edición, borrado y cambios guardados.
- Build React actualizado.
- E4 se mantiene sin cambios funcionales.

## 2.0.1.5 — Dev checkpoint — 2026-06-18

- E4 aplicado: overrides reales del Email String Editor limitados a emails WooCommerce.
- `gettext`/`gettext_with_context` solo actúan entre `woocommerce_email_header` y `woocommerce_email_footer` y solo para dominio `woocommerce`.
- Idioma resuelto desde `wpml_language` del pedido con fallback a locale.
- QA reportado por usuario como OK.

## 2.0.1.4 — Dev checkpoint — 2026-06-18

- Bugfix HPOS destinatarios por producto: fallback a escaneo por `line_items` cuando `wp_wc_order_product_lookup` no devuelve resultados.
- Validado por QA con producto `380` y pedidos `655`, `656`, `711`.
- Mantiene deduplicación por email y filtro de idioma existente.

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
