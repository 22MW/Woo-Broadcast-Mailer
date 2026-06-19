# Release Manager

## Última actualización

2026-06-19

## Resumen humano

Release `2.0.2` publicada correctamente. El flujo de release queda operativo con ZIP limpio generado por GitHub Actions. Queda pendiente probar updater en staging y QA funcional completo si el plugin va a producción.

## Descubierto

- El workflow inicial podía empaquetar material de desarrollo si se copiaba todo el workspace.
- El workflow final usa inclusión explícita de runtime y respeta `.gitignore`.
- GitHub Release `v2.0.2` se creó con asset `woo-broadcast-mailer.zip`.
- `package.json` y `package-lock.json` no entran en ZIP; su versión `1.1.0` no bloquea release.

## Hecho

- Versión pública actualizada a `2.0.2`.
- `readme.txt` actualizado con `Stable tag: 2.0.2`.
- `CHANGELOG.md` consolidado en `2.0.2`.
- `README.md` actualizado post-release.
- Workflow de release endurecido.
- ZIP temporal validado como limpio.
- Tag `v2.0.2` creado.
- `main` pusheada.
- Tag `v2.0.2` pusheado.
- GitHub Release publicada con asset ZIP.

## Pendiente

- Probar updater en staging.
- Ejecutar QA funcional completo si se va a producción.
- Decidir si se conserva o borra `_dev/_md/`.

## No volver a investigar

- Release `2.0.2` publicada.
- Asset release: `woo-broadcast-mailer.zip`.
- ZIP release limpio confirmado.
- Runtime incluido: `woo-broadcast-mailer.php`, `includes/`, `assets/`, `build/`, `readme.txt`, `LICENSE`, `uninstall.php`.
- Excluido del ZIP: `_dev/`, `node_modules/`, `src/`, paquetes Node, `.git`, `.github`, caches y locales.
- Node local disponible en `/Users/22mw/.local/node-install/node-v22.11.0-darwin-arm64/bin`.

## Riesgos o bloqueos

- Updater no probado todavía en staging.
- QA funcional completo pendiente.
- Producción requiere validación separada.

## Próximo paso recomendado

- Probar updater en staging o preparar paquete para instalación manual controlada.
