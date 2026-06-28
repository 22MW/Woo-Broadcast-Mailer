# Estado del plugin

## Última actualización

2026-06-28

## Resumen humano

Woo Broadcast Mailer está preparado para release `2.4.0` en rama `devWooBM`. La release anterior publicada es `2.3.0`. El ciclo actual añade mejoras de editor TinyMCE, shortcodes de destinatario y ajustes de UI del editor de mensajes.

## Estado general

Release `2.4.0` en preparación para commit, push y tag. Pendiente confirmar GitHub Actions/GitHub Release tras pushear el tag.

## Hecho

- Release `2.3.0` publicada en rama `devWooBM` con tag `v2.3.0`.
- Mejoras 2.3.0: conservación visual del HTML, limpieza de saltos entre bloques/listas, estado `failed`, borrado de envíos atascados sin acciones pendientes y plantillas con asunto + body.
- Editor TinyMCE ampliado con selector de fuente, tamaño, color de texto y color de fondo.
- Corregida la toolbar TinyMCE para evitar selector de color duplicado/separado.
- Añadidos shortcodes de broadcast: `{customer_name}`, `{first_name}`, `{last_name}`, `{email}`, `{current_date}`.
- `{customer_name}` ahora busca datos de usuario por email si el destinatario no trae nombre.
- `h1` y `h2` reciben estilos inline mínimos al enviar para conservar formato en clientes de email.

## En curso

- Preparación de release `2.4.0`.
- Actualización de memoria operativa `_dev/`.

## Bloqueado

- QA funcional completo de envíos reales sigue pendiente porque crea envíos, logs y acciones programadas.
- Prueba del updater en staging pendiente.
- Confirmación de GitHub Release `v2.4.0` pendiente hasta pushear tag.

## Riesgos visibles

### Alta prioridad

- QA funcional completo de envíos reales pendiente.
- Confirmar que el ZIP de GitHub Actions para `v2.4.0` se genera correctamente.

### Media prioridad

- La toolbar TinyMCE puede depender de caché del navegador/admin; si no se ve el cambio, limpiar caché.
- La entregabilidad depende de SMTP/configuración real.

### Baja prioridad / mantenimiento

- `package.json` y `package-lock.json` siguen en versión interna `1.1.0`; no entran en ZIP.
- `_dev/_md/` sigue como histórico heredado; no borrar sin permiso explícito.

## Próximo paso recomendado

- Validar, commitear, pushear `devWooBM`, crear tag `v2.4.0` y confirmar GitHub Release/ZIP.

## Pendiente de validar

- QA real de shortcodes en envío a usuario existente y email manual.
- QA visual de TinyMCE en navegador tras limpiar caché si hace falta.
- QA real de email con color de texto/fondo y tamaños de fuente.
- Updater desde GitHub Release en staging.

## No volver a investigar

- Ruta real del plugin: `app/public/wp-content/plugins/Woo-Broadcast-Mailer/`.
- Rama de trabajo del plugin: `devWooBM`.
- Text domain correcto: `wc-pbm`.
- GitHub Releases es el canal de distribución.
- ZIP release excluye `_dev/`, `node_modules/`, `src/`, `.git`, `.github/`, paquetes Node, cachés y archivos locales.
- Node local disponible en `/Users/22mw/.local/node-install/node-v22.11.0-darwin-arm64/bin`.
