# Estado del plugin

## Última actualización

2026-06-29

## Resumen humano

Woo Broadcast Mailer tiene publicada la release estable `2.5.0` en rama `devWooBM` con tag `v2.5.0`. El ciclo consolida audiencia programada fija/dinámica y LOG2/LOG3 MVP.

## Estado general

Release `2.5.0` commiteada, pusheada y tageada. Pendiente confirmar GitHub Actions/GitHub Release/ZIP porque `gh` no está instalado. AD4 y LOG2/LOG3 tienen QA confirmado por usuario; no fue ejecutado por el agente.

## Hecho

- Release `2.4.0` publicada en rama `devWooBM` con tag `v2.4.0`.
- Release `2.5.0` publicada en rama `devWooBM` con tag `v2.5.0`.
- Mejoras 2.3.0: conservación visual del HTML, limpieza de saltos entre bloques/listas, estado `failed`, borrado de envíos atascados sin acciones pendientes y plantillas con asunto + body.
- Editor TinyMCE ampliado con selector de fuente, tamaño, color de texto y color de fondo.
- Corregida la toolbar TinyMCE para evitar selector de color duplicado/separado.
- Añadidos shortcodes de broadcast: `{customer_name}`, `{first_name}`, `{last_name}`, `{email}`, `{current_date}`.
- `{customer_name}` ahora busca datos de usuario por email si el destinatario no trae nombre.
- `h1` y `h2` reciben estilos inline mínimos al enviar para conservar formato en clientes de email.
- AD3 implementado: modo fijo/dinámico para envíos programados, persistencia de configuración dinámica y snapshot final recalculado al ejecutar.
- AD4 hecho: QA funcional confirmado por usuario.
- LOG1 hecho: análisis funcional de resumen de audiencia legible y logs informativos.
- LOG2 hecho: snapshot descriptivo de audiencia en `pbm_delivery_meta_{id}` con fallback histórico; QA confirmado por usuario.
- LOG3 hecho: eventos básicos por destinatario en `pbm_delivery_events_{id}` sin nuevas tablas; QA confirmado por usuario.

## En curso

- Confirmación externa de GitHub Actions/GitHub Release/ZIP para `v2.5.0`.

## Bloqueado

- Prueba del updater en staging pendiente.
- Confirmación de GitHub Release `v2.5.0` pendiente por falta de `gh` local.

## Riesgos visibles

### Alta prioridad

- Confirmar que el ZIP de GitHub Actions para `v2.5.0` se genera correctamente.

### Media prioridad

- La toolbar TinyMCE puede depender de caché del navegador/admin; si no se ve el cambio, limpiar caché.
- La entregabilidad depende de SMTP/configuración real.
- Plan LOG: aperturas/clics quedan como futuro/recomendado; rebotes y entrega real dependen de proveedor SMTP/webhook.

### Baja prioridad / mantenimiento

- `package.json` y `package-lock.json` siguen en versión interna `1.1.0`; no entran en ZIP.
- `_dev/_md/` sigue como histórico heredado; no borrar sin permiso explícito.

## Próximo paso recomendado

- Confirmar GitHub Actions/GitHub Release/ZIP generado para `v2.5.0` desde GitHub.
- Probar updater en staging antes de usarlo como canal crítico.

## Pendiente de validar

- Updater desde GitHub Release en staging.
- GitHub Release/ZIP tras push de tag `v2.5.0`.

## No volver a investigar

- Ruta real del plugin: `app/public/wp-content/plugins/Woo-Broadcast-Mailer/`.
- Rama de trabajo del plugin: `devWooBM`.
- Text domain correcto: `wc-pbm`.
- GitHub Releases es el canal de distribución.
- ZIP release excluye `_dev/`, `node_modules/`, `src/`, `.git`, `.github/`, paquetes Node, cachés y archivos locales.
- Node local disponible en `/Users/22mw/.local/node-install/node-v22.11.0-darwin-arm64/bin`.
- Plan LOG MVP recomendado: resumen de audiencia legible + log básico por destinatario antes de clics/aperturas.
- LOG2/LOG3 arquitectura: sin migración obligatoria; usar `audience_snapshot` en `pbm_delivery_meta_{id}` y `pbm_delivery_events_{id}` como option no autoload.
- QA de AD4/LOG2/LOG3 confirmado por usuario el 2026-06-29; no ejecutado por el agente.
