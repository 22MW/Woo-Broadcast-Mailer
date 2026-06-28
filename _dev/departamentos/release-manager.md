# Release Manager

## Última actualización

2026-06-28

## Resumen humano

Release `2.4.0` en preparación sobre `devWooBM`. La release anterior publicada es `2.3.0`. Este ciclo es pequeño y concentra mejoras del editor TinyMCE y shortcodes de broadcast.

## Descubierto

- `v2.3.0` ya fue tageada y pusheada.
- Quedan cambios pendientes en `includes/functions-email.php` y `woo-broadcast-mailer.php` antes de consolidar `2.4.0`.
- El workflow de release existente empaqueta solo runtime y excluye `_dev/`.

## Hecho

- Memoria `_dev/` actualizada para release `2.4.0`.
- `estado.md`, `roadmap.md`, `decisiones.md`, `release-notes.md`, notas departamentales y `visual.html` preparados.
- Release notes internas de `2.4.0` creadas.

## Pendiente

- Actualizar versión pública a `2.4.0`.
- Actualizar `CHANGELOG.md`, `readme.txt` y `README.md`.
- Ejecutar build si aplica.
- Ejecutar `php -l` en PHP tocados.
- Ejecutar `git diff --check`.
- Commit `release(wcbm): 2.4.0`.
- Push `devWooBM`.
- Crear y pushear tag `v2.4.0`.
- Confirmar GitHub Actions/GitHub Release si la herramienta está disponible.

## No volver a investigar

- Runtime release: `woo-broadcast-mailer.php`, `includes/`, `assets/`, `build/`, `readme.txt`, `LICENSE`, `uninstall.php`.
- Excluir siempre: `_dev/`, `node_modules/`, `src/`, `.git`, `.github`, paquetes Node, cachés y locales.
- Node local: `/Users/22mw/.local/node-install/node-v22.11.0-darwin-arm64/bin`.

## Riesgos o bloqueos

- QA funcional completo pendiente.
- Updater pendiente de probar en staging.
- GitHub Release depende del workflow al pushear tag.

## Próximo paso recomendado

- Ejecutar validaciones, commit, push y tag `v2.4.0`.
