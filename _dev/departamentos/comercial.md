# Comercial

## Última actualización

2026-07-02

## Resumen humano

Woo Broadcast Mailer se posiciona como una herramienta de avisos operativos para WooCommerce con dos usos claros: enviar broadcasts puntuales a audiencias reales y adaptar textos genéricos de emails WooCommerce/plugins por idioma. No compite con una suite de marketing automation ni con una newsletter completa.

## Descubierto

- Valor principal: comunicar cambios reales a clientes reales desde WooCommerce sin montar campañas complejas.
- El mensaje comercial debe separarse en dos productos:
  - Broadcast Mailer: avisos puntuales, deduplicación, preview, exclusión temporal, programación por lotes y logs básicos.
  - Email String Editor: adaptación de textos genéricos de emails WooCommerce/plugins por idioma.
- Diferenciador interno: audiencia global combinada, Broadcast Lists, listas disponibles del WP, plantillas de asunto/body, bloques rápidos, HTML sin plantilla global, resumen legible de audiencia, logs por destinatario y toasts admin claros.
- Cliente ideal: tiendas WooCommerce con catálogo vivo, cursos, eventos, membresías o proyectos que necesitan avisos operativos y control del copy de email.
- Riesgo comercial: no prometer tracking de aperturas/clics/rebotes ni automatización avanzada que todavía no existe.

## Hecho

- Creada landing comercial interna en `_dev/comercial/woo-broadcast-mailer-landing.html`.
- Mensaje comercial separado en dos bloques: Broadcast Mailer y Email String Editor.
- Posicionamiento ajustado a la versión 2.6.0: audiencia dinámica programada, resumen legible, logs básicos por destinatario y toasts admin React.
- Mensaje comercial actualizado para no vender tracking ni automatización completa.

### Propuesta de valor

Woo Broadcast Mailer permite resolver dos tareas que suelen estar separadas: avisar a grupos concretos desde WooCommerce con control de audiencia real, y ajustar textos automáticos de email para que hablen el idioma y el tono del negocio sin tocar plantillas a mano.

### Problemas que resuelve

- Avisar a compradores de un producto, curso, evento o grupo concreto.
- Comunicar cambios, incidencias o instrucciones sin crear una campaña completa.
- Reunir audiencias desde varias fuentes sin duplicar destinatarios.
- Reutilizar listas propias con Broadcast Lists.
- Revisar audiencia antes de enviar y excluir destinatarios puntuales para ese envío.
- Reutilizar mensajes con asunto/body y bloques rápidos sin un constructor de newsletters pesado.
- Controlar el HTML del envío sin que una plantilla global lo envuelva cuando no interesa.
- Adaptar textos genéricos de WooCommerce y plugins relacionados por idioma.

### Beneficios vendibles

- Menos trabajo manual para preparar envíos.
- Más control sobre quién recibe cada mensaje.
- Menos duplicados y menos errores de audiencia.
- Reutilización de listas, asunto y body.
- Mensajes operativos claros sin depender de otra plataforma.
- Copy de emails más coherente con el idioma y el tono de la tienda.
- Trazabilidad básica para revisar qué ocurrió con cada envío.

### Mensaje corto

Woo Broadcast Mailer permite enviar avisos operativos desde WooCommerce y ajustar textos de email automáticos por idioma desde el admin, sin convertir cada cambio en una campaña compleja.

## Pendiente

- Definir naming comercial final si se vende fuera de entorno interno.
- Definir pricing/licencia si se distribuye como producto.
- Confirmar y documentar el alcance real de cada integración antes de prometer compatibilidad pública.
- Preparar capturas reales del admin, incluyendo Broadcast Lists, bloques rápidos, plantillas, logs y Email String Editor.
- Ejecutar QA funcional antes de afirmaciones de producción.

## No volver a investigar

- Funcionalidades reales confirmadas: audiencia global, deduplicación, preview, exclusión temporal, Mail Mint, Broadcast Lists, plantillas de asunto/body, bloques rápidos, HTML sin plantilla global, programación por lotes, logs básicos por destinatario, resumen legible de audiencia, toasts admin React, Email String Editor y HPOS.
- No vender como plataforma de email marketing completa.
- No prometer automatizaciones avanzadas que no existen.
- No vender los bloques rápidos como constructor completo de newsletters.
- Las plantillas guardan asunto y body, no audiencia ni programación.
- No prometer aperturas, clics, rebotes ni entrega real como funcionalidad disponible.

## Riesgos o bloqueos

- QA funcional completo pendiente.
- Entregabilidad depende de `wp_mail()`/SMTP/proveedor del sitio.
- Mail Mint depende de sus datos internos.
- Otras listas de plugins dependen de integración disponible y datos reales.
- WPML depende de idioma guardado en pedidos.

## Próximo paso recomendado

- Sustituir placeholders por pantallazos reales del panel de broadcast y del editor de strings.
- Revisar titulares y CTA final con tono comercial definitivo.
- Confirmar públicamente solo integraciones verificadas.
- Si se va a usar fuera de entorno interno, crear una versión pública sin referencias a `_dev/`.
