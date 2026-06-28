# Release notes internas

## 2.4.0 — Release en preparación — 2026-06-28

- Añadidos shortcodes de broadcast: `{customer_name}`, `{first_name}`, `{last_name}`, `{email}`, `{current_date}`.
- `{customer_name}` usa nombre del destinatario y busca usuario por email como fallback.
- TinyMCE ampliado con selector de fuente, tamaño, color de texto y color de fondo.
- Corregida toolbar TinyMCE para evitar selector de color duplicado/separado.
- Añadidos estilos inline para `h1` y `h2` en emails.
- Validaciones previstas: `npm run build`, `php -l`, `git diff --check`, commit, push `devWooBM`, tag `v2.4.0` y GitHub Release con ZIP limpio.

## 2.3.0 — Release publicada — 2026-06-28

- Mejora de conservación visual del HTML en broadcasts.
- Limpieza de saltos automáticos entre bloques y listas.
- Conversión de espacios manuales del editor en espaciadores inline seguros.
- Estado `failed` para fallos de logs y borrado de envíos atascados sin acciones pendientes.
- Plantillas de mensaje con asunto + body.
- Tag publicado: `v2.3.0`.

## 2.1.0 — Release publicada — 2026-06-21

- Consolidada la documentación comercial interna de Woo Broadcast Mailer.
- Incluye nueva landing visual en `_dev/comercial/`.
- Aclara el posicionamiento: broadcast puntual + editor de strings, sin venderlo como plugin de newsletter.

## 2.0.3 — Release publicada — 2026-06-20

- Consolidada `2.0.2.1` como release estable `2.0.3`.
- Incluye mejoras del Email String Editor para plantillas block, clases de email y contenido adicional WooCommerce.
- Incluye fix de aplicación real para textos con `{store_email}` y `%s`.
- Incluye corrección del filtro de footer dinámico WooCommerce.

## 2.0.2 — Release publicada — 2026-06-19

- Release estable publicada en GitHub: `v2.0.2`.
- Asset publicado: `woo-broadcast-mailer.zip`.
- Consolidados checkpoints dev `2.0.1.1` a `2.0.1.9`.
- Action Scheduler obligatorio, estados/logs reales, snapshots seguros y preview obsoleta aplicados.
- Escape de `{customer_name}` y borrado seguro incluidos.
- Fallback HPOS para destinatarios por producto incluido.
- Email String Editor incluido con React admin, búsqueda multiidioma, edición por idioma y overrides limitados a emails WooCommerce.
- UI admin ajustada: layout de audiencia, acceso al Editor de emails y coherencia visual.
- Workflow de release genera ZIP limpio solo con runtime.
- ZIP excluye `_dev/`, `node_modules/`, `src/`, paquetes Node, `.git`, `.github`, cachés y archivos locales.

## Pendiente post-release

- QA funcional completo de envíos reales en staging/local.
- Prueba del updater en staging.
- Confirmar GitHub Release `v2.4.0` tras pushear tag.
- Decidir conservación o borrado de `_dev/_md/`.
