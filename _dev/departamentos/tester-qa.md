# Tester QA

## Última actualización

2026-06-18

## Resumen humano

Revisión QA realizada en modo completo y solo lectura. No se ejecutaron pruebas con efecto. El flujo principal necesita QA funcional controlado antes de release o producción, especialmente Action Scheduler, preview, envíos, logs y borrado.

## Descubierto

- Flujos principales a probar: acceso admin, producto, rol, Mail Mint, emails manuales, audiencia global combinada, preview, envío instantáneo, envío programado, logs y borrado.
- QA requiere entorno local/staging con WooCommerce, Action Scheduler verificable y email catcher.
- Riesgos QA principales: éxito sin Action Scheduler real, estado `completed` prematuro, preview obsoleto si cambia audiencia y borrado por IDs sin restricción de estado.

## Hecho

- Checklist QA priorizado definido sin ejecutar acciones.
- No se crearon pedidos, envíos, logs, usuarios ni acciones programadas.

## Pendiente

- Ejecutar QA funcional con permiso explícito en entorno controlado.
- Confirmar build React contra `src/`.
- Validar Mail Mint disponible/no disponible.
- Validar WPML activo/inactivo y pedidos con/sin idioma.
- Validar usuario sin permisos WooCommerce.

## No volver a investigar

- QA funcional no ha sido ejecutado todavía.
- Action Scheduler, preview, envío instantáneo, envío programado, logs y borrado son el núcleo de QA.
- Email catcher recomendado para pruebas de envío: MailHog, Mailpit o similar.

## Riesgos o bloqueos

- QA funcional está bloqueado hasta permiso porque puede crear envíos, logs o acciones programadas.
- Producción no es entorno recomendado para estas pruebas.
- No se puede confirmar entrega real sin revisar `wp_mail()`/SMTP/email catcher.

## Próximo paso recomendado

- Ejecutar checklist P0 en local/staging: Action Scheduler, preview audiencia global, envío instantáneo, logs, programación futura y permisos AJAX.
