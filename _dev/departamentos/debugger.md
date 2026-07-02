# Debugger

## Última actualización

2026-07-02

## Resumen humano

Diagnóstico MailPoet: WooBM usa `API::MP('v1')->getLists()`, y esa API solo devuelve segmentos MailPoet de tipo `default`; por eso no aparecen las listas privadas/predeterminadas `wp_users` y `woocommerce_users`.

## Descubierto

- MailPoet `API\MP\v1\API::getLists()` delega en `Segments::getAll()`.
- `Segments::getAll()` filtra por `SegmentEntity::TYPE_DEFAULT`, excluyendo `wp_users` y `woocommerce_users`.
- En la BD local existen `Usuarios de WordPress` (`wp_users`, id 1) y `Clientes de WooCommerce` (`woocommerce_users`, id 2), ambos con `display_in_manage_subscription_page = 0` y `deleted_at IS NULL`.
- Esos segmentos tienen suscriptores `subscribed`: WordPress Users 5 y WooCommerce Customers 6.
- WooBM sí puede contar/resolver por ID si el ID llega al flujo, porque MailPoet `getSubscribers()` filtra por `listId` y acepta segmentos estáticos.
- WooCommerce/MCP devolvió pedidos de prueba `655`, `656`, `711` con emails de facturación.
- El producto `380` existe y aparece en el pedido `655`.
- HPOS lookup podía devolver `0 emails` aunque los pedidos tuvieran `line_items` válidos.
- A4 marcaba preview como obsoleta al activar programación o cambiar fecha/hora aunque no cambiaban destinatarios.
- El Email String Editor solo escaneaba strings estáticos dentro de funciones de traducción.
- Los H1 de WooCommerce se imprimen desde `$email_heading` en `email-header.php`, no como string traducible directo.
- WooCommerce expone filtros dinámicos `woocommerce_email_heading_{id}` y `woocommerce_email_subject_{id}`.

## Hecho

- Aplicado fallback HPOS: si lookup no devuelve destinatarios, se escanean pedidos por `line_items`.
- A4 corregido: programación y fecha/hora no invalidan preview.
- Email String Editor ampliado para listar `email_title`, `email_heading` y `email_subject` desde el objeto `WC_Email` asociado a cada plantilla.
- Añadidos filtros para aplicar overrides a headings y subjects dinámicos de WooCommerce.
- Añadido marcador interno `__pbm_hidden__` para ocultar strings sin usar espacios ni hacks visuales.

## Pendiente

- Aplicar fix mínimo en fase Desarrollador: ampliar obtención de selectores MailPoet para incluir segmentos estáticos privados/predeterminados sin romper uso de API pública para destinatarios.
- Validar selector/conteo/resolve con IDs 1 y 2 en entorno local.
- QA funcional completo de envíos reales.
- Confirmar updater en staging.
- Validar en navegador que el Editor de emails ya encuentra títulos/H1/subjects.
- Validar con email real que el override de heading/subject se aplica.
- Validar con email real que `Ocultar este texto` renderiza vacío y se puede revertir.

## Riesgos o bloqueos

- Si se mantiene solo `getLists()`, WooBM seguirá ocultando segmentos no `default` aunque existan y tengan suscriptores.
- Incluir segmentos privados implica decidir etiqueta/alcance visible en UI para evitar envíos accidentales a todos los usuarios/clientes.
- QA funcional puede crear envíos/logs/acciones programadas.
- Algunos headings/subjects pueden contener variables formateadas por WooCommerce; si cambia el texto final, el override debe coincidir con el original mostrado.

## Próximo paso recomendado

- Desarrollador: reemplazar o complementar `get_mailpoet_lists_for_selector()` con una lectura segura de segmentos MailPoet activos de tipo `default`, `wp_users` y `woocommerce_users`; mantener `getSubscribers()`/`getSubscribersCount()` para resolver y contar.
- Abrir `WooCommerce > Editor de emails`, buscar por título/H1 esperado y confirmar que aparece.
- Guardar un override de heading en entorno controlado y enviar email de prueba.

## No volver a investigar

- MailPoet `getLists()` solo devuelve segmentos `default`.
- Las listas privadas/predeterminadas MailPoet confirmadas son segmentos `wp_users` y `woocommerce_users`.
- En la BD local: `Usuarios de WordPress` id 1 y `Clientes de WooCommerce` id 2 están activos y ocultos de gestión pública (`display_in_manage_subscription_page = 0`).
- Los pedidos de prueba existen, están en `processing` y tienen emails de facturación.
- El producto `380` existe y aparece en el pedido `655`.
- El fallo HPOS estaba en lookup sin fallback.
- El H1 del email WooCommerce viene de `$email_heading`.
- Para headings/subjects hay que usar datos del objeto `WC_Email` y filtros dinámicos WooCommerce.

## Evidencia revisada

- `includes/functions-products.php`
- `includes/ajax-handlers.php`
- `includes/functions-scheduled.php`
- MailPoet `lib/API/MP/v1/API.php`
- MailPoet `lib/API/MP/v1/Segments.php`
- MailPoet `lib/API/MP/v1/Subscribers.php`
- MailPoet `lib/Entities/SegmentEntity.php`
- MailPoet `lib/Subscribers/SubscriberListingRepository.php`
- SELECT solo lectura: `m22w_mailpoet_segments` y conteo por `m22w_mailpoet_subscriber_segment`/`m22w_mailpoet_subscribers`
- `src/admin/App.js`
- `includes/email-string-editor/class-template-scanner.php`
- `includes/email-string-editor/class-email-string-editor.php`
- `includes/email-string-editor/class-gettext-filter.php`
- WooCommerce `templates/emails/email-header.php`
- WooCommerce `includes/emails/class-wc-email.php`
