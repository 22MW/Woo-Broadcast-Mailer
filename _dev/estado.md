# Estado del plugin

## Última actualización

2026-07-02

## Resumen humano

Woo Broadcast Mailer queda en rama `devWooBM` con integración MailPoet ajustada para mostrar listas API y segmentos predeterminados/globales activos. Release estable vigente documentada: `2.6.0`.

## Estado general

Integración MailPoet implementada y fix MP1B aplicado. QA funcional en WordPress queda pendiente porque probar preview/envío requiere entorno con MailPoet y acciones con efecto. Release `2.6.0` sigue como última release publicada.

## Hecho

- Release `2.4.0` publicada en rama `devWooBM` con tag `v2.4.0`.
- Release `2.5.0` publicada en rama `devWooBM` con tag `v2.5.0`.
- Release `2.6.0` consolida UI5: sistema toast React admin estilo AuthGate para mensajes principales del panel, con namespace propio y accesibilidad `aria-live`/`role="alert"`.
- AD3/AD4 hecho: modo fijo/dinámico para envíos programados; QA funcional confirmado por usuario.
- LOG2 hecho: snapshot descriptivo de audiencia en `pbm_delivery_meta_{id}`; QA confirmado por usuario.
- LOG3 hecho: eventos básicos por destinatario en `pbm_delivery_events_{id}`; QA confirmado por usuario.
- Cambio previo de arquitectura de toasts preservado en `_dev/departamentos/arquitecto.md`.
- MP1 hecho: fuente `mailpoet` mínima usando API pública MailPoet, sin SQL directo y solo estado `subscribed`.
- MP1B hecho técnicamente: selector complementa `getLists()` con segmentos internos activos `wp_users` y `woocommerce_users`, sin duplicar IDs.

## En curso

- QA funcional de MailPoet en entorno con listas reales.

## Bloqueado

- Prueba del updater en staging pendiente.
- Confirmación de GitHub Release `v2.6.0` pendiente por falta de `gh` local.
- Validación funcional de MailPoet pendiente si no hay MailPoet activo/listas en el entorno.

## Riesgos visibles

### Alta prioridad

- Confirmar que el ZIP de GitHub Actions para `v2.6.0` se genera correctamente.

### Media prioridad

- Limpiar caché del navegador/admin si no se ve el cambio visual de toasts tras actualizar.
- La entregabilidad depende de SMTP/configuración real.
- Plan LOG: aperturas/clics quedan como futuro/recomendado; rebotes y entrega real dependen de proveedor SMTP/webhook.

### Baja prioridad / mantenimiento

- `package.json` y `package-lock.json` siguen en versión interna `1.1.0`; no entran en ZIP.
- `_dev/_md/` sigue como histórico heredado; no borrar sin permiso explícito.

## Próximo paso recomendado

- Confirmar GitHub Actions/GitHub Release/ZIP generado para `v2.6.0` desde GitHub.
- Probar updater en staging antes de usarlo como canal crítico.
- QA controlado de fuente MailPoet: selector, conteo, preview, envío programado dinámico y MailPoet desactivado.

## Pendiente de validar

- Updater desde GitHub Release en staging.
- GitHub Release/ZIP tras push de tag `v2.6.0`.
- MailPoet con listas reales y segmentos privados/predeterminados: selector, conteo, preview, audiencia global, envío y dinámica programada.

## No volver a investigar

- Ruta real del plugin: `app/public/wp-content/plugins/Woo-Broadcast-Mailer/`.
- Rama de trabajo del plugin: `devWooBM`.
- Text domain correcto: `wc-pbm`.
- GitHub Releases es el canal de distribución.
- ZIP release excluye `_dev/`, `node_modules/`, `src/`, `.git`, `.github/`, paquetes Node, cachés y archivos locales.
- Node local disponible en `/Users/22mw/.local/node-install/node-v22.11.0-darwin-arm64/bin`.
- Toast admin usa namespace propio `.pbm-admin-toast` / `.pbm-admin-toasts`; no se copió `22mw-back.js` completo.
- QA de UI5 confirmado por usuario el 2026-07-02; no ejecutado por el agente.
- MailPoet se integra por API pública `\MailPoet\API\API::MP('v1')`; no usar SQL directo a tablas MailPoet.
- Fuente MailPoet incluye únicamente suscriptores `subscribed`.
- Descubrimiento interno MailPoet aprobado solo para segmentos activos `wp_users` y `woocommerce_users`; no resolver suscriptores por SQL.
