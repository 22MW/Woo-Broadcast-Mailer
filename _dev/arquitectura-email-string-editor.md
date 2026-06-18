# Arquitectura Email String Editor

## Última actualización

2026-06-18

## Objetivo técnico

Incorporar la funcionalidad de `_dev/_md/WooEmailStringEditor.php` dentro de Woo Broadcast Mailer como módulo propio para editar strings de emails WooCommerce por idioma, manteniendo compatibilidad con datos existentes y evitando afectar textos WooCommerce fuera del contexto email.

## Decisiones cerradas para esta arquitectura

- Ubicación inicial: submenú separado bajo WooCommerce.
- Alcance funcional: edición de strings de emails WooCommerce.
- Idiomas: mantener detección de idiomas disponibles y edición multiidioma.
- Datos: nueva opción principal `pbm_email_string_overrides`, con lectura compatible de `wc_custom_email_strings`.
- MVP: PHP/admin clásico primero.
- React: futuro, solo cuando backend y multiidioma estén estabilizados.
- QA: se hará después de implementar el MVP.

## Alcance incluido

- Crear módulo interno de Email String Editor.
- Registrar submenú admin bajo WooCommerce.
- Escanear plantillas de emails WooCommerce, tema y plugins compatibles detectados.
- Agrupar plantillas por origen/categoría.
- Seleccionar idioma activo.
- Extraer strings traducibles de plantillas.
- Mostrar original, traducción actual y personalización del idioma activo.
- Guardar personalizaciones por idioma.
- Borrar personalizaciones.
- Listar cambios guardados.
- Leer datos antiguos de `wc_custom_email_strings`.
- Aplicar overrides en emails WooCommerce.
- Seguridad admin: capability, nonces, sanitización y escaping.

## Alcance excluido

- Constructor visual de emails.
- Edición de plantillas completas.
- Traducción automática.
- Modificación de archivos `.po/.mo`.
- Migración destructiva automática.
- Integración React en primera fase.
- Release, ZIP, tag, push o deploy.
- Cambios en el flujo de broadcast.

## Archivos propuestos

### Archivo cargador

```text
includes/email-string-editor.php
```

Responsabilidad:

- Cargar clases del módulo.
- Inicializar el módulo cuando WooCommerce esté disponible.
- Registrar hooks principales.

### Clases propuestas

```text
includes/email-string-editor/class-email-string-editor.php
includes/email-string-editor/class-template-scanner.php
includes/email-string-editor/class-string-repository.php
includes/email-string-editor/class-language-resolver.php
includes/email-string-editor/class-gettext-filter.php
includes/email-string-editor/class-admin-page.php
```

## Responsabilidad por clase

### `Email_String_Editor`

Coordinador del módulo.

Responsabilidades:

- Inicializar dependencias.
- Registrar hooks.
- Conectar admin, repositorio, scanner, resolver de idioma y filtro gettext.

### `Template_Scanner`

Responsabilidades:

- Detectar plantillas de email permitidas.
- Agrupar por origen:
  - WooCommerce core.
  - Tema hijo.
  - Tema padre.
  - WooCommerce Subscriptions.
  - WooCommerce Bookings.
  - WooCommerce Memberships.
  - Otros plugins compatibles si se confirman.
- Generar IDs únicos por origen + archivo, no solo por basename.
- Extraer strings traducibles.
- Evitar leer rutas arbitrarias desde POST.

### `String_Repository`

Responsabilidades:

- Leer opción nueva `pbm_email_string_overrides`.
- Leer opción antigua `wc_custom_email_strings` como compatibilidad.
- Guardar personalizaciones nuevas.
- Borrar personalizaciones.
- Normalizar formato antiguo y nuevo.
- Cachear datos por request.

### `Language_Resolver`

Responsabilidades:

- Listar idiomas disponibles.
- Resolver idioma activo en admin.
- Resolver idioma aplicable durante emails.
- Fallback seguro a `get_locale()`.

Prioridad prevista:

1. Idioma del email/pedido si se confirma hook/contexto seguro.
2. WPML/Polylang si están activos y se puede resolver idioma.
3. `get_locale()`.

Pendiente crítico:

No puedo confirmarlo con seguridad con los datos actuales: el hook exacto más seguro para obtener idioma real del email WooCommerce.

### `Gettext_Filter`

Responsabilidades:

- Registrar filtro `gettext`.
- Aplicar overrides solo al domain `woocommerce`.
- Aplicar overrides solo cuando el contexto sea email WooCommerce.
- No afectar checkout, carrito, admin general ni pantallas WooCommerce fuera de emails.
- Cachear mapa de strings por idioma.

### `Admin_Page`

Responsabilidades:

- Registrar submenú.
- Renderizar pantalla.
- Procesar selección de idioma, origen y plantilla.
- Mostrar strings.
- Mostrar cambios guardados.
- Gestionar formularios con nonces.
- Escapar toda salida.

## Hooks propuestos

```text
plugins_loaded
admin_menu
admin_enqueue_scripts
admin_post_pbm_save_email_strings
admin_post_pbm_delete_email_string
gettext
```

## Integración con archivo principal

En `woo-broadcast-mailer.php`, dentro de `init()`, añadir carga del módulo:

```php
require_once plugin_dir_path(__FILE__) . 'includes/email-string-editor.php';
```

El módulo debe inicializarse solo si WooCommerce está disponible.

## Menú admin propuesto

Submenú bajo WooCommerce:

```text
WooCommerce > Editor de emails
```

Slug sugerido:

```text
pbm-email-string-editor
```

Capability:

```text
manage_woocommerce
```

## Flujo admin MVP

1. Usuario entra en `WooCommerce > Editor de emails`.
2. Selecciona idioma activo.
3. Selecciona origen/categoría.
4. Selecciona plantilla.
5. El sistema muestra strings detectados.
6. Usuario edita personalizaciones para el idioma activo.
7. Guarda cambios.
8. Usuario puede ir a “Cambios guardados”.
9. Usuario puede borrar una personalización.

## UI MVP

### Secciones

- Cabecera.
- Selector de idioma.
- Selector de origen/categoría.
- Selector de plantilla.
- Buscador de strings.
- Lista editable.
- Cambios guardados.

### Datos visibles por string

- Texto original.
- Traducción actual WooCommerce.
- Campo personalizado.
- Función detectada.
- Plantilla origen.
- Estado: sin cambios, personalizado, heredado.

## Estrategia de plantillas

No usar solo `basename()` como ID porque puede haber duplicados entre core, tema y plugins.

ID recomendado:

```text
{source}:{relative_path}
```

Ejemplos:

```text
woocommerce:emails/customer-completed-order.php
theme-child:woocommerce/emails/customer-completed-order.php
woocommerce-subscriptions:emails/customer-renewal-invoice.php
```

## Extracción de strings

Funciones mínimas soportadas:

```text
__
_e
esc_html__
esc_html_e
esc_attr__
esc_attr_e
```

Corregir patrón heredado de `esc_html_e`.

Pendiente futuro:

- `_x`
- `_ex`
- `_n`
- `_nx`
- plurales y contexto.

## Datos

### Opción nueva

```text
pbm_email_string_overrides
```

### Opción antigua compatible

```text
wc_custom_email_strings
```

### Estructura recomendada

```php
array(
    'version' => 1,
    'strings' => array(
        'es_ES' => array(
            'woocommerce' => array(
                'Original text' => array(
                    'custom'     => 'Texto personalizado',
                    'template'   => 'woocommerce:emails/customer-completed-order.php',
                    'source'     => 'woocommerce',
                    'context'    => 'email',
                    'updated_at' => '2026-06-18 09:00:00',
                ),
            ),
        ),
    ),
);
```

## Compatibilidad con datos antiguos

Reglas:

- No borrar `wc_custom_email_strings` automáticamente.
- Leer datos antiguos si no hay dato nuevo equivalente.
- Mostrar aviso de compatibilidad si se detectan datos antiguos.
- Ofrecer migración manual futura.
- No ejecutar migración en MVP salvo permiso explícito.

## Multiidioma

### En admin

- Mostrar todos los idiomas disponibles.
- Mantener edición por idioma activo.
- Indicar qué idiomas tienen personalizaciones.

### En aplicación de emails

Objetivo:

- Aplicar el override del idioma correcto del email.

Fallback:

- Si no se puede resolver idioma real, usar `get_locale()`.

Riesgo:

- En sitios WPML/Polylang, `get_locale()` puede no representar idioma real del pedido/email.

Pendiente técnico:

- Confirmar hook/contexto real de WooCommerce para resolver idioma de email y limitar `gettext` solo a emails.

## Limitación a emails WooCommerce

Decisión funcional confirmada:

- La herramienta es para emails WooCommerce.
- No debe modificar checkout, carrito, admin general ni otras pantallas WooCommerce.

Arquitectura prevista:

- `Gettext_Filter` solo devuelve overrides si está activo un contexto interno `email_context = true`.
- Ese contexto debe activarse/desactivarse con hooks de WooCommerce email confirmados.

Pendiente:

No puedo confirmarlo con seguridad con los datos actuales: los hooks exactos más seguros para activar y desactivar ese contexto.

## Seguridad

### Obligatorio

- Capability `manage_woocommerce` para ver, guardar y borrar.
- Nonces separados:
  - selección/carga de plantilla;
  - guardado;
  - borrado.
- Acciones prefijadas:
  - `pbm_save_email_strings`;
  - `pbm_delete_email_string`.
- Sanitización:
  - idioma: `sanitize_text_field` + whitelist de idiomas disponibles;
  - plantilla: validar contra lista escaneada;
  - original: texto de plantilla detectada, no libre desde usuario;
  - custom: `sanitize_text_field` para MVP.
- Escaping:
  - `esc_html`, `esc_attr`, `esc_url` según contexto.
- No aceptar rutas completas desde POST.
- No usar paths arbitrarios.

## Performance

Riesgos:

- `gettext` se ejecuta muchas veces.
- Escanear plantillas en cada carga puede ser costoso.

Medidas:

- Cachear overrides por request.
- Escanear plantillas solo en pantalla admin del módulo.
- No escanear plantillas dentro de `gettext`.
- Considerar transient/cache futuro para índice de plantillas.

## QA posterior

### QA funcional

- Cargar plantilla WooCommerce core.
- Cargar plantilla override del tema.
- Cargar plantilla de plugin compatible si existe.
- Detectar strings esperados.
- Guardar personalización.
- Borrar personalización.
- Ver cambios guardados.

### QA multiidioma

- Sitio con un idioma.
- Sitio con varios idiomas instalados.
- WPML activo si aplica.
- Polylang activo si aplica.
- Pedido/email con idioma distinto al admin.
- Fallback si no hay custom string para idioma actual.

### QA seguridad

- Usuario sin permisos.
- Nonce inválido.
- Plantilla no permitida.
- Guardado repetido.
- Borrado repetido.
- Entrada vacía.

### QA no regresión

- Confirmar que checkout/admin WooCommerce no cambian strings personalizados para emails.
- Confirmar que Broadcast Mailer sigue funcionando igual.
- Confirmar que no se altera el flujo de envíos.

## Riesgos

### Alto

- No limitar `gettext` a emails puede afectar WooCommerce globalmente.
- Resolver mal idioma puede aplicar traducción incorrecta en emails multiidioma.

### Medio

- Duplicados de plantilla por basename.
- Regex puede no detectar todos los strings.
- Plurales/contextos quedan fuera del MVP.
- Performance si `gettext` no cachea datos.

### Bajo

- Submenú separado puede sentirse menos integrado que una pestaña React.

## Plan de implementación propuesto

### Paso 1 — Preparación técnica

- Crear carpeta `includes/email-string-editor/`.
- Crear cargador `includes/email-string-editor.php`.
- Crear clases base.
- Registrar módulo desde `woo-broadcast-mailer.php`.

### Paso 2 — Admin MVP

- Registrar submenú.
- Renderizar selector de idioma.
- Renderizar selector de origen/plantilla.
- Mostrar strings.
- Mostrar cambios guardados.

### Paso 3 — Guardado y borrado

- Guardar en `pbm_email_string_overrides`.
- Leer compatibilidad `wc_custom_email_strings`.
- Borrar personalizaciones.

### Paso 4 — Aplicación de overrides

- Implementar `gettext` cacheado.
- Limitar al dominio `woocommerce`.
- Limitar a contexto email cuando se confirme hook seguro.

### Paso 5 — QA técnico

- `php -l` en PHP tocado.
- `git diff --check`.
- QA manual posterior.

## Archivos que tocaría una implementación futura

```text
woo-broadcast-mailer.php
includes/email-string-editor.php
includes/email-string-editor/class-email-string-editor.php
includes/email-string-editor/class-template-scanner.php
includes/email-string-editor/class-string-repository.php
includes/email-string-editor/class-language-resolver.php
includes/email-string-editor/class-gettext-filter.php
includes/email-string-editor/class-admin-page.php
```

Opcional futuro:

```text
assets/css/admin.css
src/admin/components/EmailStringEditorPanel.js
```

## Pendientes antes de programar

- Confirmar hooks WooCommerce exactos para contexto email.
- Confirmar si el MVP cubre solo WooCommerce core o también plugins detectados.
- Confirmar si `custom` debe permitir HTML mínimo o solo texto plano.
- Confirmar si se quiere botón de migración manual desde `wc_custom_email_strings`.

## Estado

Arquitectura preparada. No autoriza implementación por sí sola.
