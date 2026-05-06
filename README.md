# WOO Broadcast Mailer

Sistema de envío masivo de emails para WooCommerce con flujo unificado: permite envío instantáneo o programado por fuente de destinatarios.

## Descripción

Woo Broadcast Mailer permite comunicarte con clientes y audiencias segmentadas desde una única pantalla.
Puedes elegir la fuente de destinatarios, previsualizar emails únicos antes de enviar, configurar lotes, limitar envíos por hora y decidir si el envío se ejecuta ahora o queda programado para una fecha concreta.
Todo se procesa en segundo plano con Action Scheduler y mantiene logs de ejecución.

## Requisitos

- WordPress 6.5 o superior
- PHP 8.1 o superior
- WooCommerce 10.0 o superior
- Action Scheduler (incluido con WooCommerce)
- Mail Mint (opcional, solo para fuente "Lista Mail Mint")

## Características

- Flujo único de envío: instantáneo o programado desde el mismo formulario
- Fuentes de destinatarios:
- Producto Woo
- Rol WP
- Lista Mail Mint (si está disponible)
- Selector dependiente por fuente
- Vista previa de destinatarios únicos antes de enviar
- Personalización de asunto y mensaje (`{customer_name}`)
- Lotes configurables y límite de emails por hora
- Gestión de envíos y logs (ejecutar ahora, cancelar, borrar, borrado masivo)
- Compatible con HPOS
- Compatible con WooCommerce Subscriptions
- Integración con Action Scheduler

## Instalación

1. Sube la carpeta `woo-broadcast-mailer` a `/wp-content/plugins/`
2. Activa el plugin desde Plugins en WordPress
3. Las tablas de base de datos se crean automáticamente al activar

## Uso

### Broadcast (instantáneo o programado)

1. Ve a WooCommerce > Broadcast Mailer
2. En **Fuente**, elige: Producto Woo, Rol WP o Lista Mail Mint
3. En **Selector**, elige el producto/rol/lista según la fuente
4. Haz clic en **Vista Previa de Destinatarios**
5. Completa **Asunto** y **Mensaje**
6. Ajusta **Tamaño de lote** y **Emails por hora**
7. Opcional: activa **Programar envío** y define fecha/hora
8. Haz clic en **Enviar Emails**

### Gestión y logs

En la sección de gestión puedes:
- Ver envíos registrados (instantáneos y programados)
- Ejecutar ahora envíos pendientes
- Cancelar envíos pendientes
- Borrar envíos completados/cancelados
- Consultar logs por envío

### Monitoreo técnico

- Los envíos se ejecutan en segundo plano mediante Action Scheduler
- Puedes monitorizar tareas en WooCommerce > Estado > Acciones programadas

## Mail Mint (fase 1)

Cuando Mail Mint está activo y con tablas disponibles:
- Se cargan listas desde `mint_contact_groups` con `type=lists`
- Se obtienen contactos suscritos desde tablas de relación
- Se permite vista previa y envío a la lista seleccionada

Si Mail Mint no está activo o no hay tablas, la fuente queda deshabilitada y se muestra aviso en admin.

## Idiomas (WPML)

El plugin filtra compradores por idioma cuando WPML está activo.
El idioma se toma del pedido para mantener segmentación por idioma de compra.
Pedidos sin idioma guardado se incluyen para evitar pérdida de históricos.

## Estructura del Plugin

```text
woo-broadcast-mailer/
├── woo-broadcast-mailer.php    # Archivo principal, hooks y render admin
├── assets/
│   └── css/
│       └── admin.css           # Estilos del panel de administración
├── includes/
│   ├── functions-products.php  # Productos/fuentes y destinatarios
│   ├── functions-email.php     # Cola, lotes y envío
│   ├── functions-scheduled.php # Gestión/listado/logs de envíos
│   ├── ajax-handlers.php       # Endpoints AJAX
│   └── updater.php             # Actualizaciones por GitHub Releases
└── README.md
```

## Tablas de Base de Datos

### `wp_pbm_scheduled_emails`

Guarda envíos registrados (instantáneos y programados) y su configuración.

### `wp_pbm_scheduled_logs`

Guarda logs por ejecución (inicio, fin, enviados, fallidos y errores).

## Desinstalación

Al desactivar el plugin:
- Los datos se mantienen
- Las acciones programadas se pueden limpiar desde Action Scheduler

Para eliminar completamente:
1. Desactiva el plugin
2. Activa borrado de datos al desinstalar mediante:
- Constante `PBM_DELETE_DATA_ON_UNINSTALL` en `wp-config.php` con valor `true`, o
- Opción `pbm_delete_data_on_uninstall` con valor truthy
3. Desinstala el plugin

## Soporte

Proyecto personalizado. Para cambios o soporte, contacta al autor.

## Changelog

### 2.0.0
- Migración principal del panel admin a React para flujo operativo completo.
- Limpieza del bloque legacy de selector/preview/envío en el admin (markup y JS inline removidos).
- Flujo de audiencia global combinada (producto, rol, Mail Mint y emails manuales) con deduplicación previa al envío.
- Selector por fuente con modo multi-selección y búsqueda AJAX (3+ caracteres).
- Lista global de audiencias con añadir/quitar/limpiar y recuentos por origen.
- Resumen dinámico de audiencia (bruto, únicos y duplicados).
- Vista previa React de destinatarios únicos usando endpoint AJAX unificado.
- Flujo de envío React (instantáneo y programado) con validaciones de asunto/mensaje/fecha.
- Integración estable de `wp_editor` clásico para el campo mensaje dentro del panel React (híbrido controlado).
- Refuerzo backend para audiencias globales:
- Si hay `audience_items`/`manual_emails`, no exige selectores legacy (`product_id`, `role`, `mailmint_list_id`).
- Mantiene validación legacy cuando no existe audiencia global.
- Gestión de envíos y logs migrada a cards React:
- Ordenación por fecha/estado/asunto.
- Selección por página y borrado masivo por IDs.
- Paginación a 12 elementos por página.
- Badges de estado por color (`pending`, `running`, `completed`, `cancelled`).
- Total de mensajes por card con fallback para históricos:
- Primero desde destinatarios guardados.
- Si no existen, suma de enviados+fallidos en logs.
- Ajustes visuales unificados:
- Tokens de color y tipografía alineados con estilo 22MW.
- Botonera consistente, sin bordes, con fondo sólido y radio completo.
- Header con logo enlazado a `https://22mw.online/`.

### 1.1.0
- Flujo unificado de envío (instantáneo/programado) en una sola UI.
- Capa extensible de fuentes de destinatarios.
- Integración Mail Mint fase 1 (listas + suscritos + preview).
- Vista previa por fuente con resumen y emails únicos.
- Gestión de envíos/logs con acciones de ejecutar/cancelar/borrar.
- Validaciones de disponibilidad de Mail Mint y hardening de seguridad.
- Ajustes visuales del panel admin.

### 1.0.9
- Filtrado por idioma usando WPML en pedidos.

### 1.0.8.4
- Filtrar compradores por idioma WPML.

### 1.0.8.3
- Revertir vista previa sin AJAX.

### 1.0.8.2
- Vista previa sin AJAX para evitar límites.

### 1.0.8.1
- Soporte WPML para IDs traducidos y etiqueta de idioma.

### 1.0.8
- Prueba de actualización automática.

### 1.0.7
- Release de prueba para asset automático.

### 1.0.6
- ZIP de asset en release para actualizaciones.

### 1.0.5
- Descarga del ZIP por tag para actualizaciones.

### 1.0.4
- Mostrar versión en el título del admin.

### 1.0.3
- Updater con GitHub Releases.

### 1.0.2
- Borrado opcional al desinstalar.

### 1.0.1
- Preparación para publicación pública.
- Requisitos y licencia alineados.
- Carga de textdomain añadida.
- Saltos de línea preservados en emails.

## Licencia

GPLv2 o posterior.
