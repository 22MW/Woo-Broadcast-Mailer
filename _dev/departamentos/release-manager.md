# Release Manager

## Última actualización

2026-07-02

## Resumen humano

Release `2.6.0` publicada sobre `devWooBM` con tag `v2.6.0`. Este ciclo consolida el sistema toast React admin estilo AuthGate.

## Descubierto

- `v2.5.0` ya fue tageada y pusheada.
- Existe un conjunto de cambios posterior a `v2.5.0` con sistema toast admin y build React.
- El workflow de release existente empaqueta solo runtime y excluye `_dev/`.
- `gh` no está instalado localmente; la comprobación de GitHub Release/ZIP queda externa.

## Hecho

- Versión pública actualizada a `2.6.0` en cabecera, `readme.txt`, `CHANGELOG.md` y `README.md`.
- Memoria `_dev/` actualizada para release `2.6.0`.
- `estado.md`, `roadmap.md`, `decisiones.md`, `release-notes.md`, notas departamentales y `visual.html` consolidados.
- QA UI5 registrado como confirmado por usuario.
- Cambio previo de arquitectura preservado e incluido en release.
- Commit `release(wcbm): 2.6.0`, push `devWooBM` y tag `v2.6.0` completados.

## Pendiente

- Confirmar GitHub Actions/GitHub Release/ZIP en GitHub tras push de tag; `gh` no está instalado localmente.
- Probar updater en staging.

## No volver a investigar

- Runtime release: `woo-broadcast-mailer.php`, `includes/`, `assets/`, `build/`, `readme.txt`, `LICENSE`, `uninstall.php`.
- Excluir siempre: `_dev/`, `node_modules/`, `src/`, `.git`, `.github`, paquetes Node, cachés y locales.
- Node local: `/Users/22mw/.local/node-install/node-v22.11.0-darwin-arm64/bin`.
- Versión estable objetivo de este ciclo: `2.6.0`.

## Riesgos o bloqueos

- QA UI5 confirmado por usuario; QA no ejecutado por el agente.
- Updater pendiente de probar en staging.
- GitHub Release depende del workflow al pushear tag y queda pendiente de comprobar externamente.

## Próximo paso recomendado

- Confirmar GitHub Release/ZIP de `v2.6.0` y probar updater en staging.
