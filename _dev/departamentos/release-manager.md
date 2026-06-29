# Release Manager

## Última actualización

2026-06-29

## Resumen humano

Release `2.5.0` en preparación sobre `devWooBM`. La release anterior publicada es `2.4.0`. Este ciclo consolida audiencia programada fija/dinámica y LOG2/LOG3 MVP.

## Descubierto

- `v2.4.0` ya fue tageada y pusheada.
- Existe un commit posterior a `v2.4.0` con mejoras de logs de audiencia programada.
- El workflow de release existente empaqueta solo runtime y excluye `_dev/`.

## Hecho

- Versión pública actualizada a `2.5.0` en cabecera, `readme.txt`, `CHANGELOG.md` y `README.md`.
- Memoria `_dev/` actualizada para release `2.5.0`.
- `estado.md`, `roadmap.md`, `decisiones.md`, `release-notes.md`, notas departamentales y `visual.html` preparados.

## Pendiente

- Ejecutar build si aplica.
- Ejecutar `php -l` en PHP tocados.
- Ejecutar `git diff --check`.
- Commit `release(wcbm): 2.5.0`.
- Push `devWooBM`.
- Crear y pushear tag `v2.5.0`.
- Confirmar GitHub Actions/GitHub Release si la herramienta está disponible.

## No volver a investigar

- Runtime release: `woo-broadcast-mailer.php`, `includes/`, `assets/`, `build/`, `readme.txt`, `LICENSE`, `uninstall.php`.
- Excluir siempre: `_dev/`, `node_modules/`, `src/`, `.git`, `.github`, paquetes Node, cachés y locales.
- Node local: `/Users/22mw/.local/node-install/node-v22.11.0-darwin-arm64/bin`.

## Riesgos o bloqueos

- QA funcional AD4/LOG2/LOG3 confirmado por usuario; QA no ejecutado por el agente.
- Updater pendiente de probar en staging.
- GitHub Release depende del workflow al pushear tag.

## Próximo paso recomendado

- Ejecutar validaciones, commit, push y tag `v2.5.0`.
