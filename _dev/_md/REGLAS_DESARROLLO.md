# Reglas de Desarrollo (Interno)

Estas reglas aplican durante desarrollo en `dev`.
No deben incluirse en paquetes de release final.

## Antes de cada tarea

1. Leer los archivos afectados. Verificar que existen.
2. Decir qué entendí y qué archivos tocaré.
3. Esperar "ok", "ejecuta" o "adelante" — sin excepción.
4. Ejecutar solo la parte pedida. Un plan aprobado no autoriza el plan completo.

## Si aparece algo inesperado

- Parar.
- Explicar qué encontré.
- Esperar instrucción.
- Si el usuario pregunta algo durante desarrollo, responder antes de continuar.
- No avanzar ni ejecutar nada hasta recibir OK explícito.

## Prohibido sin permiso explícito

- Borrar o mover archivos.
- Refactors, limpiezas o mejoras no pedidas.
- Avanzar al siguiente paso de un plan.
- Activar/desactivar plugins o cambios en BD.
- Pruebas automáticas que alteren estado del sistema.

## Respuestas

- Breves.
- Sin introducciones ni resumen de lo ya hecho.
- El usuario ve cambios en VSCode, no listarlos.
- Reporte final: `HECHO/NO HECHO`, `php -l`, siguiente paso solo si el usuario lo pide.

## Calidad de Código

- Funciones pequeñas (20-30 líneas aprox.), una responsabilidad.
- Nombres claros y descriptivos.
- No duplicar lógica existente; reutilizar CSS/PHP/JS existente.
- Sin iconos salvo petición explícita.
- Seguridad WP obligatoria:
- sanitización
- escaping
- nonces
- capability checks
- `defined('ABSPATH') || exit;`
- WPCS.
- Preparado para i18n.
- Text domain: `vfwoo`.

## Documentación

- PHPDoc/JSDoc solo cuando aporte valor.
- Comentario inline solo si la lógica no es obvia.

## Validación

- Ejecutar `php -l` en cada archivo PHP tocado.
- Ruta PHP Local.app:
- `find ~/Library/Application\ Support/Local/lightning-services -name "php" -type f | grep "8.1" | head -1`
- Ejecutar `git diff --check`.
- En commits: `git add .` (incluye cambios manuales del usuario).

## Versionado

- Solo cuando el usuario lo pida explícitamente.
