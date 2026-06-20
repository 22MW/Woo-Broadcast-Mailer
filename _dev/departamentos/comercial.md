# Comercial

## Última actualización

2026-06-19

## Resumen humano

Woo Broadcast Mailer puede posicionarse como una herramienta para tiendas WooCommerce que necesitan comunicar campañas, avisos o mensajes post-compra a audiencias concretas sin exportar clientes ni usar plataformas externas para cada acción.

## Descubierto

- Valor principal: segmentación operativa dentro de WooCommerce.
- Diferenciador interno: audiencia global combinada, programación por lotes y Editor de emails WooCommerce.
- Cliente ideal: tiendas WooCommerce con catálogo activo, clientes segmentables por producto/rol/lista y necesidad de comunicación directa.
- Riesgo comercial: QA funcional completo pendiente antes de presentarlo como listo para producción masiva.

## Hecho

- Creada landing comercial interna en `_dev/comercial/woo-broadcast-mailer-landing.html`.
- Copiada base visual 22MW y CSS específico en `_dev/comercial/22mw-base.css` y `_dev/comercial/woo-broadcast-mailer-landing.css`.
- Mensaje comercial separado en dos bloques: broadcast puntual y editor de strings de emails WooCommerce.
- Posicionamiento aclarado: no vender como plugin de newsletter, sino como capa operativa para comunicaciones puntuales y control de textos.

### Propuesta de valor

Envía emails segmentados desde WooCommerce a compradores, roles, listas Mail Mint o emails manuales, con vista previa, deduplicación, programación, logs y edición de textos de emails WooCommerce.

### Problemas que resuelve

- Enviar avisos a compradores de un producto concreto.
- Comunicar a un rol de usuario específico.
- Reutilizar listas Mail Mint básicas.
- Combinar varias fuentes sin duplicar destinatarios.
- Previsualizar audiencia antes de enviar.
- Programar envíos por lotes para reducir riesgo de saturación.
- Ajustar textos de emails WooCommerce por idioma.

### Beneficios vendibles

- Menos exportaciones manuales.
- Más control antes de enviar.
- Menos duplicados.
- Segmentación por datos WooCommerce reales.
- Logs para revisar qué pasó.
- Mejor control de mensajes WooCommerce sin tocar plantillas a mano.

### Mensaje corto

Broadcast Mailer convierte WooCommerce en un centro de comunicación segmentada: crea audiencias desde productos, roles, listas o emails manuales, previsualiza destinatarios, programa envíos por lotes y personaliza textos de emails WooCommerce desde el admin.

## Pendiente

- Definir naming comercial final si se vende fuera de entorno interno.
- Definir pricing/licencia si se distribuye como producto.
- Definir si Mail Mint se comunica como integración básica o avanzada.
- Preparar capturas reales del admin.
- Ejecutar QA funcional antes de afirmaciones de producción.

## No volver a investigar

- Funcionalidades reales confirmadas: audiencia global, deduplicación, preview, programación, logs, Email String Editor, HPOS.
- No vender como plataforma de email marketing completa.
- No prometer automatizaciones avanzadas que no existen.

## Riesgos o bloqueos

- QA funcional completo pendiente.
- Entregabilidad depende de `wp_mail()`/SMTP del sitio.
- Mail Mint fase 1 depende de tablas internas.
- WPML depende de idioma guardado en pedidos.

## Próximo paso recomendado

- Sustituir placeholders por pantallazos reales del panel de broadcast y del editor de strings.
- Revisar titulares y CTA final con tono comercial definitivo.
- Si se va a usar fuera de entorno interno, crear una versión pública sin referencias a `_dev/`.
