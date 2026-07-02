# Arquitecto

## Última actualización

2026-07-02

## Relevo breve

Arquitectura del fix mínimo MailPoet tras diagnóstico confirmado: WooBM debe seguir usando la API pública MailPoet para contar/resolver suscriptores, pero debe complementar el selector con una lectura interna mínima de segmentos activos `wp_users` y `woocommerce_users` porque `API::MP('v1')->getLists()` solo devuelve segmentos `default`.

## Hecho en esta tarea

- Revisado diagnóstico en `_dev/departamentos/debugger.md`.
- Revisado flujo real en `includes/functions-products.php`, `includes/ajax-handlers.php` y `includes/functions-scheduled.php`.
- Confirmado punto de cambio mínimo: `get_mailpoet_lists_for_selector()` alimenta disponibilidad, búsqueda AJAX y labels de snapshots.
- Confirmado que conteo y resolución ya pasan por API pública:
  - `get_mailpoet_subscribers_count()` usa `getSubscribersCount()` con `listId` y `status = subscribed`.
  - `get_recipients_from_mailpoet_list()` usa `getSubscribers()` con `listId` y `status = subscribed`.

## Scope exacto para Desarrollador

1. En `includes/functions-products.php`, mantener `get_mailpoet_api()` y la API pública como fuente principal.
2. Añadir helper mínimo para obtener segmentos internos MailPoet activos de tipo `wp_users` y `woocommerce_users`:
   - Opción preferida si existe en el entorno: usar clase/repositorio interno de MailPoet para listar segmentos, sin instanciar sistemas paralelos.
   - Fallback aceptado: lectura `$wpdb` solo de `{$wpdb->prefix}mailpoet_segments`, con `SHOW TABLES LIKE`, tipos permitidos cerrados, `deleted_at IS NULL`, `ORDER BY name ASC` y valores sanitizados.
   - No consultar ni contar suscriptores por SQL.
3. Modificar `get_mailpoet_lists_for_selector()` para:
   - Leer primero `$api->getLists()`.
   - Normalizar cada item a estructura común: `id`, `name`, opcional `type`.
   - Añadir segmentos internos privados/predeterminados solo si no existen ya por ID.
   - Evitar duplicados con mapa por ID.
   - Devolver nombres claros para UI, por ejemplo:
     - `Usuarios de WordPress (MailPoet predeterminada)`
     - `Clientes de WooCommerce (MailPoet predeterminada)`
     o incluir `type` para que el label se construya después.
4. Mantener intactos:
   - `get_recipients_from_mailpoet_list()`.
   - `get_mailpoet_subscribers_count()`.
   - Validación `absint()` de IDs en AJAX.
5. Ajustar labels si hace falta en:
   - `search_mailpoet_selectors()` para mostrar nombre claro + `#ID`.
   - `get_delivery_audience_selector_label()` si se decide no guardar el sufijo directamente en `name`.

## Pendiente / riesgos

- Riesgo principal: depender de estructura interna de MailPoet. Mitigación: limitar la lectura a descubrir segmentos, encapsularla en helper único y devolver `array()` si la tabla/clase no existe.
- Riesgo de envío accidental: labels deben dejar claro que son listas predeterminadas/globales de MailPoet, no listas públicas creadas manualmente.
- Si MailPoet cambia nombre de tabla o entidad interna, el selector perderá esas listas, pero conteo/resolución por API seguirá aislado.
- No cambiar el modelo de permisos: las acciones AJAX ya usan nonce y `manage_woocommerce`.

## No volver a investigar

- `get_mailpoet_lists_for_selector()` es el punto mínimo para ampliar el selector MailPoet.
- `search_mailpoet_selectors()` y `get_delivery_audience_selector_label()` consumen `get_mailpoet_lists_for_selector()`.
- Conteo y resolución MailPoet ya usan API pública con `status = subscribed`; no deben migrarse a SQL.
- Segmentos privados confirmados: ID 1 `Usuarios de WordPress` (`wp_users`) e ID 2 `Clientes de WooCommerce` (`woocommerce_users`).

## Relevo para

→ Desarrollador: implementar solo el helper interno mínimo + merge sin duplicados en `includes/functions-products.php`; tocar `ajax-handlers.php` y `functions-scheduled.php` solo si se decide separar `name` y `type` para labels. Validar `php -l`, `git diff --check` y prueba manual del selector/conteo con IDs 1 y 2.
