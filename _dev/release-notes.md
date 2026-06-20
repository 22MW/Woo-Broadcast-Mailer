# Release notes internas

## 2.1.0 — Release en preparación — 2026-06-21

- Consolidada la documentación comercial interna de Woo Broadcast Mailer.
- Incluye nueva landing visual en `_dev/comercial/`.
- Aclara el posicionamiento: broadcast puntual + editor de strings, sin venderlo como plugin de newsletter.
- Validaciones previstas: `php -l`, `npm run build`, `git diff --check`, tag `v2.1.0` y GitHub Release con ZIP limpio.

## 2.0.3 — Release en preparación — 2026-06-20

- Consolidada `2.0.2.1` como release estable `2.0.3`.
- Incluye mejoras del Email String Editor para plantillas block, clases de email y contenido adicional WooCommerce.
- Incluye fix de aplicación real para textos con `{store_email}` y `%s`.
- Incluye corrección del filtro de footer dinámico WooCommerce.
- Validaciones previstas: `php -l`, `npm run build`, `git diff --check`, tag `v2.0.3` y GitHub Release con ZIP limpio.

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
- Validaciones ejecutadas: `npm run build`, `php -l`, `git diff --check`, ZIP temporal limpio y GitHub Release confirmada por API.

## Pendiente post-release

- QA funcional completo de envíos reales en staging/local.
- Prueba del updater en staging.
- Documentación pública breve.
- Resumen comercial interno.
- Decidir conservación o borrado de `_dev/_md/`.

## Histórico dev consolidado

- `2.0.1.1`: Action Scheduler obligatorio, escape de nombre y borrado seguro.
- `2.0.1.2`: estados/logs reales, snapshot seguro, preview obsoleta y exclusión `_dev`.
- `2.0.1.3`: Email String Editor base, búsqueda global, multiidioma y guardado.
- `2.0.1.4`: fallback HPOS producto.
- `2.0.1.5`: aplicación real de overrides en emails WooCommerce.
- `2.0.1.6`: Editor de emails migrado a React.
- `2.0.1.7`: programación no invalida preview.
- `2.0.1.8`: ajustes visuales admin y enlace al Editor de emails.
- `2.0.1.9`: placeholder/búsqueda del Editor de emails y ZIP workflow limpio.
