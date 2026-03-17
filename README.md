# WOO Broadcast Mailer

Sistema de envío masivo de emails para WooCommerce: envía a compradores de productos específicos o programa envíos por rol de usuario.

## Requisitos

- WordPress 6.5 o superior
- PHP 8.1 o superior
- WooCommerce 10.0 o superior
- Action Scheduler (incluido con WooCommerce)

## Características

- Envío masivo a compradores de productos específicos
- Envío programado por rol de usuario
- Sistema de lotes configurable
- Control de emails por hora
- Compatible con HPOS (High-Performance Order Storage)
- Compatible con WooCommerce Subscriptions
- Logs detallados de envíos
- Integración con Action Scheduler

## Instalación

1. Sube la carpeta `woo-broadcast-mailer` a `/wp-content/plugins/`
2. Activa el plugin desde el menú Plugins en WordPress
3. Las tablas de base de datos se crearán automáticamente al activar


## Uso

### Envío por Producto

1. Ve a WooCommerce > Broadcast Mailer
2. Selecciona la pestaña "Envío por Producto"
3. Elige un producto del selector
4. Haz clic en "Vista Previa de Destinatarios" para ver a quién se enviará
5. Configura el asunto y mensaje (usa {customer_name} para personalizar)
6. Ajusta el tamaño de lote y emails por hora
7. Haz clic en "Enviar Emails"

### Envíos Programados

1. Ve a WooCommerce > Broadcast Mailer
2. Selecciona la pestaña "Envíos Programados"
3. Elige un rol de usuario
4. Configura el asunto y mensaje
5. Selecciona la fecha y hora de envío
6. Ajusta configuración de lotes
7. Haz clic en "Programar Envío"

### Monitoreo

- Todos los envíos se ejecutan en segundo plano vía Action Scheduler
- Monitoriza el progreso en WooCommerce > Estado > Acciones programadas
- Los envíos programados guardan logs detallados por lote

## Estructura del Plugin

```
woo-broadcast-mailer/
├── woo-broadcast-mailer.php    # Archivo principal con hooks de activación
├── includes/
│   ├── functions-products.php  # Funciones de productos y destinatarios
│   ├── functions-email.php     # Funciones de envío de emails
│   ├── functions-scheduled.php # Funciones de envíos programados
│   └── ajax-handlers.php       # Handlers AJAX
└── README.md                   # Este archivo
```

## Tablas de Base de Datos

### wp_pbm_scheduled_emails
Almacena la configuración de envíos programados.

### wp_pbm_scheduled_logs
Almacena logs individuales de cada lote ejecutado.

## Desinstalación

Al desactivar el plugin:
- Los datos en base de datos se mantienen
- Las acciones programadas se pueden limpiar manualmente desde Action Scheduler

Para eliminar completamente:
1. Desactiva el plugin
2. Si quieres borrado automático al desinstalar, activa una de estas opciones:
   - Define `PBM_DELETE_DATA_ON_UNINSTALL` en `wp-config.php` y ponlo en `true`
   - O crea la opción `pbm_delete_data_on_uninstall` con valor truthy
3. Elimina las tablas manualmente si lo deseas:
   - wp_pbm_scheduled_emails
   - wp_pbm_scheduled_logs

## Soporte

Este plugin fue desarrollado como proyecto personalizado. Para modificaciones o consultas, contacta al autor.

## Changelog

### 1.0.7
- Release de prueba para asset automático

### 1.0.6
- ZIP de asset en release para actualizaciones

### 1.0.5
- Descarga del ZIP por tag para actualizaciones

### 1.0.4
- Mostrar versión en el título del admin

### 1.0.3
- Updater con GitHub Releases

### 1.0.2
- Borrado opcional al desinstalar

### 1.0.1
- Preparación para publicación pública
- Requisitos y licencia alineados
- Carga de textdomain añadida
- Saltos de línea preservados en emails

## Licencia

GPLv2 o posterior.
