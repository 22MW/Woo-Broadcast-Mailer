# Comercial

## Última actualización

2026-06-21

## Resumen humano

Woo Broadcast Mailer se posiciona como emailing sencillo para WooCommerce: permite enviar avisos puntuales a compradores, productos, listas que ya existen en WordPress y emails manuales, sin convertir cada aviso en una campaña completa. También permite adaptar textos genéricos de WooCommerce y plugins relacionados por idioma.

## Descubierto

- Valor principal: comunicación operativa segmentada dentro de WooCommerce.
- El plugin se comunica mejor en dos bloques separados:
  - Broadcast Mailer: enviar mensajes puntuales.
  - Email String Editor: adaptar textos WooCommerce/plugins por idioma.
- Diferenciador interno: audiencia global combinada, listas disponibles del WP, Mail Mint, Broadcast Lists, plantillas de body, bloques rápidos, programación por lotes y Editor de emails WooCommerce.
- Cliente ideal: tiendas WooCommerce con catálogo activo, cursos, eventos o proyectos con clientes segmentables por producto, lista o grupo.
- Riesgo comercial: QA funcional completo pendiente antes de presentarlo como listo para producción masiva.

## Hecho

- Creada landing comercial interna en `_dev/comercial/woo-broadcast-mailer-landing.html`.
- Copiada base visual 22MW y CSS específico en `_dev/comercial/22mw-base.css` y `_dev/comercial/woo-broadcast-mailer-landing.css`.
- Landing replanteada para separar Broadcast Mailer y Email String Editor sin mezclar mensajes.
- Mensaje comercial actualizado: emailing sencillo para WooCommerce, no newsletter ni automatización compleja.
- Actualizados `README.md` y `readme.txt` con enfoque 2.3.0.

### Propuesta de valor

Envía avisos puntuales desde WooCommerce a compradores, productos, roles, listas disponibles en WordPress, Mail Mint, Broadcast Lists o emails manuales, con vista previa, exclusión temporal, deduplicación, plantillas de body, bloques rápidos, programación y logs. Además, adapta textos genéricos de emails WooCommerce y plugins relacionados por idioma sin tocar plantillas.

### Problemas que resuelve

- Enviar avisos a compradores de un producto concreto.
- Comunicar cambios de cursos, eventos, productos o incidencias.
- Seleccionar listas que ya existen en WordPress, incluyendo Mail Mint y otros plugins de email si están disponibles.
- Guardar y reutilizar Broadcast Lists propias.
- Combinar varias fuentes sin duplicar destinatarios.
- Previsualizar audiencia antes de enviar y excluir destinatarios puntuales.
- Crear mensajes visuales simples con imagen, botón, bloque destacado y separador.
- Guardar plantillas de body sin guardar asunto, audiencia ni programación.
- Enviar HTML sin plantilla global cuando WP HTML Mail u otro wrapper no debe intervenir.
- Programar envíos por lotes para reducir riesgo de saturación.
- Adaptar textos genéricos de WooCommerce y plugins relacionados por idioma.

### Beneficios vendibles

- Menos exportaciones manuales.
- Más control antes de enviar.
- Menos duplicados.
- Reutilización de listas y bodies de mensaje.
- Segmentación por datos WooCommerce reales.
- Mensajes visuales simples sin constructor complejo.
- Logs para revisar qué pasó.
- Mejor control de textos WooCommerce sin tocar plantillas a mano.

### Mensaje corto

Woo Broadcast Mailer permite enviar avisos puntuales desde WooCommerce a compradores, productos, listas existentes, Broadcast Lists o emails manuales, reutilizando cuerpos de mensaje y adaptando textos genéricos de WooCommerce/plugins por idioma desde el admin.

## Pendiente

- Definir naming comercial final si se vende fuera de entorno interno.
- Definir pricing/licencia si se distribuye como producto.
- Confirmar y documentar alcance real de integraciones con AcyMailing, FluentCRM, MailPoet y Newsletter antes de prometer compatibilidad pública.
- Preparar capturas reales del admin, incluyendo Broadcast Lists, bloques rápidos, plantillas y Email String Editor.
- Ejecutar QA funcional antes de afirmaciones de producción.

## No volver a investigar

- Funcionalidades reales confirmadas: audiencia global, deduplicación, preview, exclusión temporal, Mail Mint, Broadcast Lists, plantillas de body, bloques rápidos, HTML sin plantilla global, programación, logs, Email String Editor, HPOS.
- No vender como plataforma de email marketing completa.
- No prometer automatizaciones avanzadas que no existen.
- No vender los bloques rápidos como constructor completo de newsletters.
- Las plantillas guardan asunto y body, no audiencia ni programación.

## Riesgos o bloqueos

- QA funcional completo pendiente.
- Entregabilidad depende de `wp_mail()`/SMTP del sitio.
- Mail Mint depende de sus datos internos.
- Otras listas de plugins dependen de integración disponible y datos reales.
- WPML depende de idioma guardado en pedidos.

## Próximo paso recomendado

- Sustituir placeholders por pantallazos reales del panel de broadcast y del editor de strings.
- Revisar titulares y CTA final con tono comercial definitivo.
- Confirmar públicamente solo integraciones verificadas.
- Si se va a usar fuera de entorno interno, crear una versión pública sin referencias a `_dev/`.
