# Release Manager

## Última actualización

2026-06-18

## Resumen humano

Revisión release/versionado realizada en modo completo y solo lectura. El plugin ya tiene A7 aplicado: el workflow release excluye `_dev/`. Pendiente generar y revisar ZIP real antes de cualquier publicación.

## Descubierto

- Cabecera del plugin está en `2.0.1.2` dev.
- `readme.txt` está en versión pública base `2.0.1`.
- `readme.txt` incluye changelog y upgrade notice de `2.0.1`.
- `CHANGELOG.md` y `README.md` llegan a `2.0.0`, falta `2.0.1`.
- `package.json` y `package-lock.json` están en `1.1.0`.
- El updater busca asset `woo-broadcast-mailer.zip` y fallback a ZIP de tag GitHub.
- El workflow de release excluye `.git`, `.github`, `.DS_Store`, `LOCAL_*`, `_md` y ahora también `_dev/`.
- El workflow no ejecuta `npm ci`, `npm run build`, `php -l`, lint ni validación del ZIP.
- `node_modules/` fue copiado desde el plugin anterior y `npm run build` funciona con Node local.

## Hecho

- A7 aplicado: `rsync` excluye `_dev/` del ZIP/release.
- Build ejecutado con éxito:
  - `node v22.11.0`
  - `npm v10.9.0`
  - `npm run build`
- `build/index.js` y `build/index.asset.php` quedan sincronizados con `src/admin/App.js`.

## Hecho

- Preparado checkpoint dev `2.0.1.3` para Email String Editor E1-E3.2 + E2.3.
- `CHANGELOG.md` actualizado con entrada `2.0.1.3`.
- `_dev/release-notes.md` actualizado.
- Commit `1ec86c4` pusheado en `devWooBM`.
- Preparado checkpoint dev `2.0.1.5` para E4 Email String Editor con QA OK reportado por usuario.

## Pendiente

- Generar ZIP de prueba sin publicar.
- Revisar que ZIP excluya `_dev/`, `.kilo/` si existiera, `.git/`, `.github/`, secretos, logs y backups.
- Decidir si `src/`, `package.json` y `package-lock.json` entran en ZIP distribuible.
- Añadir entrada `2.0.1` en `CHANGELOG.md` y decidir actualización de `README.md`.
- Decidir versión de `package.json`/`package-lock.json`.
- Consolidar `2.0.1.1` y `2.0.1.2` en versión pública estable antes de release.
- Probar updater en staging.

## No volver a investigar

- Versión dev confirmada: `2.0.1.2` en cabecera.
- Versión pública base confirmada: `2.0.1` en `readme.txt`.
- Updater GitHub Releases confirmado.
- Riesgo original confirmado: workflow no excluía `_dev/`.
- A7 aplicado: workflow actual excluye `_dev/`.
- Node local disponible en `/Users/22mw/.local/node-install/node-v22.11.0-darwin-arm64/bin`.

## Riesgos o bloqueos

- No generar ZIP/tag/release/deploy sin permiso explícito.
- El workflow empaqueta lo que exista en `build/`; conviene ejecutar build antes de release.
- El workflow todavía no valida PHP ni genera ZIP en modo controlado.

## Próximo paso recomendado

- Si el usuario quiere release: hacer checklist release, generar ZIP de prueba, revisar contenido y solo entonces decidir publicación.
