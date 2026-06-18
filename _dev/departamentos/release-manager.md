# Release Manager

## Última actualización

2026-06-18

## Resumen humano

Revisión release/versionado realizada en modo completo y solo lectura. El plugin no está listo para ZIP/release sin ajustes: el workflow puede incluir `_dev/`, no ejecuta build ni validaciones y hay documentación/versiones parcialmente desincronizadas.

## Descubierto

- Cabecera del plugin y `readme.txt` están en `2.0.1`.
- `readme.txt` incluye changelog y upgrade notice de `2.0.1`.
- `CHANGELOG.md` y `README.md` llegan a `2.0.0`, falta `2.0.1`.
- `package.json` y `package-lock.json` están en `1.1.0`.
- El updater busca asset `woo-broadcast-mailer.zip` y fallback a ZIP de tag GitHub.
- El workflow de release excluye `.git`, `.github`, `.DS_Store`, `LOCAL_*` y `_md`, pero no excluye `_dev/`.
- El workflow no ejecuta `npm ci`, `npm run build`, `php -l`, lint ni validación del ZIP.

## Hecho

- Revisión de versión, readme, changelog, package, updater, workflow y riesgos de ZIP completada en solo lectura.

## Pendiente

- Excluir `_dev/` del ZIP/release.
- Decidir si `src/`, `package.json` y `package-lock.json` entran en ZIP distribuible.
- Añadir entrada `2.0.1` en `CHANGELOG.md` y decidir actualización de `README.md`.
- Decidir versión de `package.json`/`package-lock.json`.
- Ejecutar build limpio y validar que `build/` está actualizado antes de release.
- Probar updater en staging.

## No volver a investigar

- Versión pública confirmada: `2.0.1` en cabecera y `readme.txt`.
- Updater GitHub Releases confirmado.
- Riesgo confirmado: workflow actual no excluye `_dev/`.

## Riesgos o bloqueos

- No generar ZIP, tag, push ni release sin permiso explícito.
- ZIP actual puede incluir memoria interna `_dev/` si se usa el workflow sin ajustes.
- Build puede estar desincronizado porque el workflow empaqueta lo que ya exista.

## Próximo paso recomendado

- Antes de cualquier release: corregir exclusiones, sincronizar documentación/versionado, ejecutar build y probar updater en staging.
