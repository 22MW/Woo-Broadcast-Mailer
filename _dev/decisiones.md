# Decisiones

## Última actualización

2026-07-02

## Decisiones confirmadas

- **Release actual publicada**: `2.6.0` con tag `v2.6.0` en rama `devWooBM`.
- **Release anterior publicada**: `2.5.0` con tag `v2.5.0`.
- **Rama de trabajo**: `devWooBM`.
- **Canal de distribución**: GitHub Releases con ZIP generado por workflow.
- **ZIP release mínimo**: incluye solo runtime: `woo-broadcast-mailer.php`, `includes/`, `assets/`, `build/`, `readme.txt`, `LICENSE`, `uninstall.php`.
- **ZIP release excluye**: `_dev/`, `_md/`, `.git/`, `.github/`, `node_modules/`, `src/`, `package.json`, `package-lock.json`, `dist/`, cachés, logs y archivos locales.
- **React para admin**: panel principal y Editor de emails usan React con `@wordpress/components`, `@wordpress/element` y `@wordpress/i18n`.
- **Editor de mensaje**: se mantiene `wp_editor`/TinyMCE nativo; no se añade editor Node nuevo.
- **Toast admin UI5**: se usa patrón tipo AuthGate adaptado, con helper local React, namespace `.pbm-admin-toast` / `.pbm-admin-toasts` y sin copiar `22mw-back.js` completo.
- **TinyMCE**: se permiten fuente, tamaño, color de texto y color de fondo; formatos P/H1/H2/H3/H4 ya existen.
- **Shortcodes de broadcast aprobados**: `{customer_name}`, `{first_name}`, `{last_name}`, `{email}`, `{current_date}`.
- **`{unsubscribe_note}` descartado por ahora**: no se añade hasta tener sistema real de baja/exclusión permanente.
- **Plantillas de mensaje**: guardan asunto y body; no guardan destinatarios, programación ni configuración de envío.
- **Action Scheduler**: obligatorio para envío instantáneo y programado.
- **Estados de envío**: `failed` existe para fallos de logs; se pueden borrar `failed` y `running` sin acciones pendientes.
- **Audiencia programada**: AD3 está implementado; existe modo fijo/dinámico para programados, `audience_mode=dynamic` en `pbm_delivery_meta_{id}` y si falta se trata como `fixed`.
- **Plan LOG MVP recomendado**: primero “resumen de audiencia legible + log por destinatario básico”; aperturas/clics/rebotes/entrega real quedan fuera del MVP.
- **Arquitectura LOG2**: resumen de audiencia legible se resuelve con `audience_snapshot` descriptivo dentro de `pbm_delivery_meta_{id}` sin migración obligatoria y fallback histórico actual si falta snapshot.
- **Arquitectura LOG3**: log básico por destinatario se resuelve con option no autoload `pbm_delivery_events_{id}` para evitar migración; tabla/JSON queda como alternativa si el volumen exige escalar.
- **QA AD4/LOG2/LOG3**: confirmado por usuario el 2026-06-29; no fue ejecutado por el agente.
- **QA UI5**: confirmado por usuario el 2026-07-02 como “QA ok”; no fue ejecutado por el agente.
- **Versión estable consolidada**: `2.6.0`, por mejora visual/admin de toasts tras `2.5.0`.
- **Aperturas/clics**: no se implementan ahora; quedan en roadmap como LOG4 futuro/recomendado.
- **Rebotes/entrega real**: no se implementan ahora; quedan en roadmap como LOG5 futuro dependiente de proveedor SMTP/webhook.
- **Text domain correcto**: `wc-pbm`.
- **`_dev/_md/`**: queda como histórico heredado; no borrar sin permiso explícito.

## Decisiones operativas

- QA funcional debe hacerse en local/staging porque puede crear envíos, logs y acciones programadas.
- No hacer deploy a producción sin confirmar entorno, backup/staging y rollback.
- Documentación pública debe ser breve y orientada a uso real.
- Resumen comercial debe basarse solo en funcionalidades confirmadas.
- No presentar Mail Mint como integración profunda: actualmente es fase 1.
- No presentar WPML como traducción completa del plugin: se usa para segmentación por idioma de pedido cuando existe dato.
- No presentar `wp_mail` correcto como entrega real en buzón; solo como procesado/enviado por el sistema si el log lo confirma.
- No presentar aperturas, clics, rebotes ni entregas reales como disponibles sin implementar tracking o integración específica.

## Pendientes de decisión

- Ejecutar QA funcional completo antes de usar en producción.
- Probar updater en staging.
- Borrar o conservar definitivamente `_dev/_md/`.
- Definir si el plugin se venderá como herramienta interna, producto privado o plugin público.
- Definir si se implementará sistema real de baja/exclusión permanente.
- Decidir si el modo dinámico será global en MVP o configurable por fuente en una fase posterior.
- Plan LOG: decidir política de privacidad/retención antes de cualquier tracking de aperturas, clics o bajas.
- Plan LOG: decidir proveedor o integración si se quieren rebotes/entregas reales.

## No reabrir sin motivo

- El editor de mensaje seguirá usando TinyMCE nativo.
- La audiencia dinámica no sustituye el modo fijo actual; debe convivir con snapshot existente.
- La migración React está completada.
- El flujo multi-fuente está implementado.
- El text domain correcto es `wc-pbm`.
- El ZIP de release debe seguir excluyendo `_dev/` y archivos de desarrollo.
