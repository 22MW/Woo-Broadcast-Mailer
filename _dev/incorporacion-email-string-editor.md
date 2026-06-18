# Incorporación Email String Editor en Woo Broadcast Mailer

## Última actualización

2026-06-18

## Objetivo

Evaluar la incorporación del archivo `_dev/_md/WooEmailStringEditor.php` dentro de Woo Broadcast Mailer como herramienta interna para editar strings de emails WooCommerce sin modificar plantillas core ni archivos del tema.

## Estado actual del archivo evaluado

El archivo funciona como plugin independiente llamado **WooCommerce Email String Editor**.

Hecho confirmado:

- Añade un submenú propio en WooCommerce: `Email String Editor`.
- Escanea plantillas PHP de emails en WooCommerce, algunos plugins WooCommerce y tema activo.
- Extrae strings traducibles mediante regex sobre funciones como `__()`, `_e()`, `esc_html__()`, `esc_html_e()`, `esc_attr__()` y `esc_attr_e()`.
- Permite editar textos por idioma.
- Guarda personalizaciones en la opción `wc_custom_email_strings`.
- Aplica personalizaciones con el filtro `gettext` cuando el dominio es `woocommerce`.
- Tiene pestaña de editor y pestaña de cambios guardados.
- Permite borrar personalizaciones.
- Mantiene compatibilidad con formato antiguo y nuevo de datos guardados.

## Funcionalidad existente aprovechable

- Escaneo de plantillas de email WooCommerce.
- Extracción de strings traducibles.
- Guardado por idioma.
- Vista de cambios guardados.
- Borrado de personalizaciones.
- Compatibilidad con datos existentes en `wc_custom_email_strings`.
- Uso de `manage_woocommerce`, nonces y `admin-post.php`.

## Problemas detectados

### Riesgo principal

El filtro `gettext` actual afecta a todo el dominio `woocommerce`, no solo a emails.

Impacto:

- Un cambio pensado para un email podría modificar el mismo string en checkout, admin u otras pantallas WooCommerce.

### Multiidioma limitado

El archivo usa `get_locale()`.

No puedo confirmarlo con seguridad con los datos actuales:

- Que esto coincida con el idioma real del pedido.
- Que sea compatible correctamente con WPML o Polylang.
- Que respete el idioma real usado por WooCommerce al enviar emails.

### UX poco intuitiva

- La selección se basa en plantilla/archivo técnico.
- No agrupa por categoría, plugin origen, tipo de email o idioma.
- La tabla con columnas por idioma escala mal si hay muchos idiomas.
- No hay buscador de strings.
- No hay vista contextual del email.
- No se avisa si un mismo string aparece en varias plantillas.

### Riesgos técnicos

- El patrón regex de `esc_html_e` parece tener un espacio en `(. +?)`, lo que puede impedir detecciones correctas.
- Usa clase global `WC_Email_String_Editor`; al integrarlo conviene namespacing/prefijo propio.
- Acciones `admin_post_save_email_strings` y `admin_post_delete_email_string` son genéricas; conviene prefijarlas.
- `gettext` puede tener coste de rendimiento si lee opciones repetidamente.
- Guarda por texto original, no por contexto completo de plantilla/origen.

## Recomendación general

Incorporar la funcionalidad, pero **no copiar el archivo tal cual**.

Primero debe convertirse en módulo propio de Woo Broadcast Mailer, con prefijos, seguridad reforzada, compatibilidad de datos y control para que los cambios afecten solo emails WooCommerce.

## Ubicación propuesta en admin

### Recomendación para MVP

Crear un submenú separado bajo WooCommerce, relacionado con Woo Broadcast Mailer.

Nombre sugerido:

- `Editor de emails WooCommerce`
- `Strings de emails`
- `Email String Editor`

Motivo:

- Menor riesgo.
- No mezcla el flujo de envíos broadcast con la edición de strings transaccionales.
- Permite estabilizar backend y multiidioma antes de integrarlo en React.

### Alternativa futura

Añadir una pestaña dentro del panel React de Woo Broadcast Mailer.

Pestañas posibles:

- Envíos
- Historial
- Editor de emails

Riesgo:

- Aumenta complejidad del panel principal.
- Requiere ampliar React, endpoints AJAX y build.

## MVP propuesto

### Incluido

- Módulo integrado en Woo Broadcast Mailer.
- Submenú admin separado.
- Permiso `manage_woocommerce`.
- Escaneo de plantillas email WooCommerce, tema y plugins compatibles detectados.
- Selector por idioma activo.
- Selector de plantilla con nombre humano, archivo y origen.
- Listado de strings detectados.
- Edición de string para el idioma seleccionado.
- Guardado de personalizaciones.
- Vista de cambios guardados.
- Borrado de personalizaciones.
- Compatibilidad de lectura con `wc_custom_email_strings`.
- No borrar ni migrar datos antiguos automáticamente.

### Excluido

- Constructor visual completo de emails.
- Edición de plantillas HTML completas.
- Traducción automática.
- Modificación de `.po/.mo`.
- Cambios en emails no WooCommerce.
- Cambios en el flujo de broadcast/envíos.
- Release, ZIP o deploy.
- Migración destructiva de datos antiguos.

## Selección mejorada de strings

### Por categoría

Agrupar emails por uso funcional:

- Pedidos cliente.
- Pedidos administración.
- Cuenta cliente.
- Suscripciones.
- Reservas.
- Membresías.
- Otros plugins detectados.

### Por plugin origen

Mostrar origen real:

- WooCommerce core.
- Tema hijo.
- Tema padre.
- WooCommerce Subscriptions.
- WooCommerce Bookings.
- WooCommerce Memberships.
- Otros plugins compatibles.

### Por plantilla

Cada plantilla debería mostrar:

- Nombre humano del email.
- Archivo técnico.
- Origen.
- Tipo: HTML/plain.
- Número de strings detectados.
- Estado: con cambios / sin cambios.
- Idiomas con personalización.

Ejemplo:

```text
Cliente · Pedido completado
customer-completed-order.php · WooCommerce core · 8 strings · ES/CA editados
```

### Por idioma

Recomendación:

- Elegir primero idioma activo.
- Editar solo ese idioma.
- Mostrar otros idiomas como comparación opcional, no como columnas obligatorias.

### Por estado

Filtros recomendados:

- Todos.
- Personalizados.
- Sin personalizar.
- Con cambios en este idioma.
- Strings repetidos en varias plantillas.
- Strings potencialmente globales.

### Buscador

Buscar por:

- Texto original.
- Traducción actual.
- Texto personalizado.
- Plantilla.
- Plugin origen.

## Edición visual recomendada

Sustituir la tabla ancha por cards o filas expandibles.

Cada string debería mostrar:

- Texto original.
- Traducción actual de WooCommerce.
- Campo personalizado.
- Plantilla origen.
- Función detectada.
- Aviso si aparece en más de una plantilla.
- Estado visual: sin cambios, modificado, pendiente de guardar, heredado.

## Compatibilidad multiidioma

### Mantener

- Guardado separado por idioma.
- Compatibilidad con datos actuales de `wc_custom_email_strings`.
- Compatibilidad con formato antiguo:

```php
'Original text' => 'Texto personalizado'
```

- Compatibilidad con formato nuevo:

```php
'Original text' => [
  'custom' => 'Texto personalizado',
  'template' => 'customer-processing-order.php',
]
```

### Mejorar

Crear un resolver de idioma.

Prioridad sugerida:

1. Idioma real del email/pedido si se confirma el hook correcto.
2. WPML/Polylang si están activos.
3. `get_locale()` como fallback.

Pendiente crítico:

No puedo confirmarlo con seguridad con los datos actuales: el hook exacto más seguro para limitar el reemplazo solo al render/envío de emails WooCommerce y resolver idioma real del email.

## Datos

### Opción actual

```text
wc_custom_email_strings
```

### Opción recomendada si se integra

```text
pbm_email_string_overrides
```

### Estrategia recomendada

- Leer `pbm_email_string_overrides` como opción principal.
- Si está vacía, detectar `wc_custom_email_strings`.
- Ofrecer migración manual no destructiva.
- No borrar la opción antigua automáticamente.

Estructura futura sugerida:

```php
[
  'version' => 1,
  'strings' => [
    'es_ES' => [
      'woocommerce' => [
        'Original text' => [
          'custom' => 'Texto personalizado',
          'template' => 'woocommerce/customer-processing-order.php',
          'source' => 'woocommerce',
          'context' => 'email',
          'updated_at' => '2026-06-18 08:00:00',
        ],
      ],
    ],
  ],
]
```

## Arquitectura recomendada por fases

### Fase 0 — Confirmación previa

- Confirmar si debe afectar solo emails o todo WooCommerce.
- Confirmar plugins multiidioma activos.
- Confirmar si existen personalizaciones actuales en `wc_custom_email_strings`.
- Confirmar si se quiere submenú o pestaña React.

### Fase 1 — Incorporación segura como submenú

Archivos sugeridos:

```text
includes/email-string-editor/class-email-string-editor.php
includes/email-string-editor/class-template-scanner.php
includes/email-string-editor/class-string-repository.php
includes/email-string-editor/class-language-resolver.php
includes/email-string-editor/class-gettext-filter.php
```

Versión simple posible:

```text
includes/email-string-editor.php
```

Hooks propuestos:

```text
admin_menu
admin_enqueue_scripts
admin_post_pbm_save_email_strings
admin_post_pbm_delete_email_string
gettext
```

### Fase 2 — UX mejorada

- Selector por categoría.
- Selector por plugin origen.
- Selector por plantilla.
- Idioma activo.
- Buscador.
- Filtros de estado.
- Cards visuales.

### Fase 3 — Limitar a contexto email

- Evitar que los overrides afecten checkout/admin/global WooCommerce.
- Confirmar hook seguro de WooCommerce email.
- Aplicar filtro solo durante render/envío de email.

### Fase 4 — Integración React

Solo cuando backend, datos y multiidioma estén estabilizados.

Componentes posibles:

```text
src/admin/components/EmailStringEditorPanel.js
src/admin/components/EmailStringTemplateSelector.js
src/admin/components/EmailStringChangesList.js
```

## Seguridad

Requisitos mínimos:

- Capability `manage_woocommerce`.
- Nonces en guardar, borrar y cargar plantillas si aplica.
- Acciones `admin-post` prefijadas con `pbm_`.
- Sanitización por tipo de campo.
- Escaping en toda salida admin.
- Rutas permitidas: no aceptar paths arbitrarios desde POST.
- No leer archivos fuera de plantillas email permitidas.
- No exponer datos sensibles por AJAX.

## QA necesario

### Funcional

- Cargar plantillas WooCommerce core.
- Cargar plantillas override del tema.
- Cargar plantillas de plugins compatibles si existen.
- Detectar strings de funciones soportadas.
- Guardar cambio.
- Borrar cambio.
- Confirmar que el cambio aparece en email.
- Confirmar que no afecta pantallas fuera de emails si se limita contexto.

### Multiidioma

- Sitio sin WPML/Polylang.
- Sitio con WPML.
- Sitio con Polylang si aplica.
- Pedido/email en idioma distinto al admin.
- Fallback si no hay custom string para idioma actual.
- Códigos tipo `es`, `es_ES`, `ca`, `ca_ES`.

### Seguridad

- Usuario sin permisos.
- Nonce inválido.
- Input incompleto.
- Intento de plantilla no permitida.
- Guardado repetido.
- Borrado repetido.

### Performance

- Medir impacto del filtro `gettext`.
- Cachear opción por request.
- Evitar escanear plantillas en cada carga innecesariamente.

### Release

- Confirmar que `_dev/_md/WooEmailStringEditor.php` no entra en ZIP.
- Si se incorpora, mover a `includes/` real.
- Actualizar changelog/release-notes.
- Validar PHP tocado con `php -l`.
- Ejecutar `git diff --check`.

## Criterios de aceptación

- El editor es accesible desde WooCommerce/Woo Broadcast Mailer.
- Solo usuarios con permisos pueden acceder y guardar.
- El usuario puede elegir idioma antes de editar.
- El usuario puede seleccionar plantilla sin conocer nombres técnicos.
- El usuario puede filtrar por categoría, origen y estado.
- El usuario puede buscar strings.
- El usuario puede guardar una personalización por idioma.
- El usuario puede borrar una personalización.
- Los datos existentes en `wc_custom_email_strings` siguen disponibles.
- No se pierden personalizaciones antiguas.
- Si no hay personalización para un idioma, se usa traducción normal de WooCommerce.
- Si se define alcance “solo emails”, no debe afectar checkout/admin/global WooCommerce.

## Riesgos funcionales

- Aplicación global por `gettext`.
- Idioma incorrecto en emails multiidioma.
- Duplicados de plantilla por basename.
- Strings dinámicos no detectados.
- Plurales/contextos no soportados inicialmente.
- Confusión entre plantilla HTML y plain text.
- Tabla actual inviable con muchos idiomas.

## Pendientes de decisión

1. ¿Submenú separado o pestaña React?
2. ¿Debe afectar solo emails WooCommerce o todo dominio `woocommerce`?
3. ¿Qué idioma manda en emails: pedido, usuario, WPML/Polylang, locale del sitio?
4. ¿Se mantiene `wc_custom_email_strings` como opción principal o se migra a `pbm_email_string_overrides`?
5. ¿Migración manual o automática?
6. ¿MVP con PHP clásico o directamente React?
7. ¿Primera fase solo WooCommerce core o también plugins detectados?

## Recomendación final

Incorporarlo en fases.

Orden recomendado:

1. Confirmar decisiones pendientes.
2. Crear módulo seguro como submenú separado.
3. Mantener compatibilidad de datos antiguos.
4. Limitar aplicación a emails WooCommerce.
5. Mejorar selección visual por categoría/origen/plantilla/idioma.
6. Ejecutar QA multiidioma.
7. Integrar en React solo después de estabilizar backend.

## Estado

Documento de planificación. No autoriza implementación.
