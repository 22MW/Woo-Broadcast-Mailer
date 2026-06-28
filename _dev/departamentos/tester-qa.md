# Tester QA

## Última actualización

2026-06-28

## Resumen humano

QA funcional completo sigue pendiente. Para el ciclo `2.4.0` solo se han ejecutado validaciones técnicas previstas; queda pendiente validar en navegador y con envío real los shortcodes y controles TinyMCE.

## Descubierto

- Flujos QA nuevos del ciclo `2.4.0`: shortcodes de destinatario, TinyMCE con fuente/tamaño/color, toolbar sin duplicado y render de H1/H2 en email.
- QA real puede crear envíos, logs o acciones programadas.

## Hecho

- QA funcional no ejecutado todavía.
- Validación técnica prevista: `php -l`, build si aplica y `git diff --check`.

## Pendiente

- Validar `{customer_name}` con destinatario que tenga nombre en la fuente.
- Validar `{customer_name}`, `{first_name}`, `{last_name}` con usuario WordPress existente por email.
- Validar `{email}` y `{current_date}`.
- Validar editor TinyMCE en navegador: fuente, tamaño, color de texto y color de fondo.
- Validar que no aparece selector de color duplicado/separado tras limpiar caché.
- Validar email recibido con H1/H2, color, fondo y tamaño.

## No volver a investigar

- QA funcional completo requiere permiso porque puede crear envíos/logs/acciones.
- Email catcher recomendado: Mailpit/MailHog o SMTP controlado.

## Riesgos o bloqueos

- Clientes de email pueden modificar fuentes/colores.
- Caché del navegador puede mostrar toolbar antigua.
- Sin usuario asociado al email, first/last name pueden quedar vacíos.

## Próximo paso recomendado

- Después de release, ejecutar prueba controlada con un email manual y un usuario WordPress existente.
