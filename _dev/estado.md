# Estado del plugin

## Última actualización

2026-06-19

## Resumen humano

Woo Broadcast Mailer está publicado como release estable `2.0.2`. El panel principal está en React, el flujo multi-fuente está implementado, Action Scheduler es obligatorio para envíos y el Email String Editor funciona como módulo propio bajo WooCommerce.

## Estado general

Release `2.0.2` publicada en GitHub Releases con ZIP limpio. Queda pendiente QA funcional completo de envíos reales en entorno controlado y preparar documentación/comercialización.

## Hecho

- Release estable `2.0.2` publicada.
- `main`, `devWooBM`, `origin/main` y `origin/devWooBM` apuntan al commit `dd51840`.
- Tag publicado: `v2.0.2`.
- GitHub Release creada con asset `woo-broadcast-mailer.zip`.
- Workflow de release empaqueta solo archivos runtime.
- ZIP de prueba validado como limpio antes de publicar.
- Panel admin principal migrado a React.
- Flujo unificado de envío instantáneo/programado.
- Fuentes de audiencia: Producto Woo, Rol WP, Lista Mail Mint y emails manuales.
- Audiencia global acumulada con deduplicación.
- Vista previa de destinatarios únicos.
- Gestión de envíos y logs en React.
- Action Scheduler obligatorio con aviso admin.
- Estados/logs reales: `completed` depende de logs acumulados.
- Snapshot de destinatarios conservado hasta completar o eliminar.
- Preview obsoleta bloquea envío si cambia audiencia/configuración relevante.
- Programación y fecha/hora no invalidan preview.
- Escape de `{customer_name}` y validación de email destino.
- Borrado seguro limitado a envíos completados o cancelados.
- Fallback HPOS para destinatarios por producto.
- Email String Editor E1-E5 implementado y con QA reportado OK.
- Email String Editor ampliado para encontrar títulos, headings/H1 y subjects de emails WooCommerce.
- Overrides de heading y subject conectados a filtros dinámicos WooCommerce.
- Email String Editor incluye opción `Ocultar este texto` mediante marcador interno `__pbm_hidden__`.
- Campo Buscar del Email String Editor lanza búsqueda con Enter.
- Resultados del Email String Editor pasan a bloques verticales para que la personalización por idioma tenga ancho suficiente.
- R1 React del Editor de emails implementado.
- Release ZIP excluye `_dev/`, `node_modules/`, `.git/`, `.github/`, `src/`, `package.json`, `package-lock.json`, `dist/`, cachés y archivos locales.
- Broadcast Lists implementado: guardar audiencia previa como lista reutilizable, usarla como fuente, renombrar, borrar emails y borrar listas.
- Vista previa permite excluir emails individualmente de forma temporal para el envío actual.

## En curso

- Consolidación de memoria `_dev/` post-release.
- Preparación de documentación pública y resumen comercial.

## Bloqueado

- QA funcional completo porque puede crear envíos, logs o acciones programadas.
- Prueba del updater en staging hasta confirmar entorno.
- Borrado de `_dev/_md/` hasta decisión explícita.

## Riesgos visibles

### Alta prioridad

- QA funcional completo de envíos reales pendiente.
- Probar updater en staging antes de depender de él en producción.

### Media prioridad

- `package.json` y `package-lock.json` siguen en versión interna `1.1.0`; decisión actual: no entran en ZIP y no bloquean release.
- `ScheduledLogsPanel.js` usa `dangerouslySetInnerHTML` para logs generados por AJAX; revisar si se aborda hardening posterior.
- `includes/ajax-handlers.php`, `includes/functions-products.php`, `src/admin/App.js` y `assets/css/admin.css` son archivos grandes.

### Baja prioridad / mantenimiento

- `functions-scheduled.php` conserva HTML/JS/CSS legacy; validar si sigue en uso antes de limpiar.
- Mail Mint está integrado en fase 1 por tablas internas, no por API pública confirmada.

## Próximo paso recomendado

- Cerrar limpieza de memoria `_dev/`.
- Preparar documentación pública breve.
- Preparar resumen comercial interno.
- Si se va a probar en cliente o producción: ejecutar QA funcional controlado en staging.

## Pendiente de validar

- Broadcast Lists: guardar, renombrar, borrar emails/lista, usar como fuente y enviar con exclusión temporal.
- Envío instantáneo real con logs.
- Envío programado real con ejecución por Action Scheduler.
- Borrado seguro con registros `completed`, `cancelled`, `pending` y `running`.
- Nombre de destinatario con caracteres HTML.
- Configuración real de `wp_mail()`.
- Presencia y tablas reales de Mail Mint si se usa esa fuente.
- Idioma de pedidos WPML en entorno real.
- Descarga/update vía updater desde GitHub Release.
- Búsqueda y aplicación real de headings/subjects en email WooCommerce controlado.
- Ocultar string con checkbox en email WooCommerce controlado.

## No volver a investigar

- Ruta real del plugin: `app/public/wp-content/plugins/Woo-Broadcast-Mailer/`.
- Rama de trabajo del plugin: `devWooBM`.
- Release actual: `2.0.2`.
- Text domain correcto: `wc-pbm`.
- `main` y `devWooBM` están alineadas en `dd51840` tras release `2.0.2`.
- Tag release: `v2.0.2`.
- Asset release: `woo-broadcast-mailer.zip`.
- React admin está implementado.
- Flujo multi-fuente está implementado.
- Action Scheduler está integrado y es obligatorio para envíos.
- Email String Editor E1-E5 está implementado.
- ZIP release mínimo confirmado: solo runtime, sin `_dev/`, `node_modules/` ni archivos de desarrollo.
- `_dev/_md/` es material histórico heredado; no borrar sin permiso explícito.
