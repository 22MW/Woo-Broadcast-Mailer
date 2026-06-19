# Changelog

Todos los cambios relevantes del proyecto se documentan en este archivo.

## 2.0.3

- Consolidada la versión dev `2.0.2.1` como release estable.
- Mejorado el Email String Editor para localizar strings de emails WooCommerce en plantillas block y clases de email.
- Añadido soporte para overrides de `additional_content` en emails WooCommerce, incluyendo textos con `{store_email}` y `%s`.
- Corregida la aplicación del texto dinámico del footer de emails WooCommerce.
- Añadido modo ocultar texto en overrides del editor de emails.
- Mejorada la UI de resultados del editor con tarjetas por string y build actualizado.

## 2.0.2

- Consolidada la serie dev `2.0.1.1` a `2.0.1.9` en una release estable.
- Añadidas comprobaciones obligatorias de Action Scheduler y aviso de estado en admin.
- Mejorada la gestión real de estados/logs de envíos programados.
- Conservado el snapshot de destinatarios hasta completar o eliminar el envío.
- Añadida protección de preview obsoleta cuando cambia audiencia o configuración relevante.
- Corregida la invalidación de preview al activar programación o cambiar fecha/hora.
- Escapado seguro de `{customer_name}` y validación de email destino.
- Borrado seguro de envíos limitado a estados completados o cancelados.
- Añadido fallback HPOS para destinatarios por producto cuando la tabla lookup no devuelve resultados.
- Añadido Email String Editor bajo WooCommerce con interfaz React, búsqueda multiidioma, edición por idioma y compatibilidad de lectura legacy.
- Activada aplicación real de overrides del Email String Editor solo durante render de emails WooCommerce.
- Ajustada la UI admin: layout de audiencia, acceso al Editor de emails, headers y coherencia visual.
- Endurecido el workflow de release para generar un ZIP limpio sin `_dev/`, `node_modules/`, `src/`, `package.json`, `package-lock.json`, cachés ni archivos locales.

## 2.0.0

- Migración principal del panel admin a React para el flujo operativo completo.
- Limpieza del bloque legacy de selector/preview/envío en el admin.
- Flujo de audiencia global combinada: producto, rol, Mail Mint y emails manuales.
- Selector por fuente con multi-selección y búsqueda AJAX.
- Lista global de audiencias con recuentos por origen.
- Resumen dinámico de audiencia: bruto, únicos y duplicados.
- Vista previa React de destinatarios únicos mediante endpoint AJAX.
- Flujo React de envío instantáneo y programado.
- Integración estable del editor clásico `wp_editor` dentro del panel React.
- Validaciones backend para audiencia global sin exigir selectores legacy.
- Gestión de envíos y logs en cards React.
- Ordenación, selección por página, borrado masivo y paginación de logs.
- Badges de estado para `pending`, `running`, `completed` y `cancelled`.
- Total de mensajes por card con fallback para históricos.
- Ajustes visuales alineados con estilo 22MW.

## 1.1.0

- Flujo unificado de envío instantáneo/programado en una sola pantalla.
- Capa extensible de fuentes de destinatarios.
- Integración Mail Mint fase 1.
- Vista previa por fuente con emails únicos.
- Gestión básica de envíos y logs.
- Refuerzo de seguridad y validaciones.

## 1.0.9

- Filtrado de destinatarios por idioma de pedido usando WPML.

## 1.0.8.4

- Filtro de compradores por idioma WPML.

## 1.0.8.3

- Reversión de vista previa sin AJAX.

## 1.0.8.2

- Vista previa sin AJAX para evitar límites del servidor.

## 1.0.8.1

- Soporte WPML para IDs traducidos y etiqueta de idioma.

## 1.0.8

- Prueba de flujo de actualización automática.

## 1.0.7

- Release de prueba para asset automático.

## 1.0.6

- Uso del ZIP de release para conservar el nombre de carpeta.

## 1.0.5

- Descarga por ZIP de tag GitHub para actualizaciones.

## 1.0.4

- Versión visible en el título del admin.

## 1.0.3

- Updater mediante GitHub Releases.

## 1.0.2

- Limpieza opcional al desinstalar.

## 1.0.1

- Preparación de publicación pública.
- Requisitos, licencia y text domain alineados.
- Saltos de línea preservados en emails.
