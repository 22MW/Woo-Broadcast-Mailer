# Plan Completo: Broadcast Multi-Fuente + Lista Global

## Objetivo

Implementar un flujo de audiencia unificado donde el usuario pueda construir una lista global de destinatarios desde múltiples fuentes (Producto Woo, Rol WP, Lista Mail Mint y entrada manual), con deduplicación de emails y envío único (instantáneo o programado).

## Alcance Funcional

1. Reemplazar selector `Fuente` por botones visibles tipo radio con diseño botón.
2. Permitir selección múltiple de elementos por fuente.
3. Crear lista global acumulada de audiencias seleccionadas.
4. Añadir entrada manual de emails a la lista global.
5. Deduplicar emails antes de preview y antes de enviar.
6. Mostrar resumen final con conteos (bruto, duplicados, únicos, desglose por fuente).
7. Mantener envío unificado actual (instantáneo/programado) sin romper lógica existente.

## Reglas Operativas del Proyecto

Aplicar siempre reglas de `_md/REGLAS_DESARROLLO.md` durante ejecución del plan.

## Fases de Implementación

## Fase 1: UI de Fuente como botones

- Sustituir visualmente el `select` de fuente por grupo de botones/radio accesibles.
- Mantener valor sincronizado para compatibilidad con lógica JS actual.
- Estados: activo, hover, disabled (Mail Mint no disponible).

Criterios:
- Solo una fuente activa a la vez.
- No romper eventos existentes de cambio de fuente.

## Fase 2: Selector múltiple por fuente

- Añadir contenedor "Selección actual" por fuente (items seleccionados).
- Mostrar hasta 3 sugerencias iniciales por fuente.
- Añadir buscador AJAX por fuente con umbral 3+ caracteres.
- Permitir:
- seleccionar múltiples,
- deseleccionar,
- evitar duplicados de selector dentro de la misma fuente.

Criterios:
- UX clara para agregar/quitar.
- Respuesta rápida con debounce en búsqueda.

## Fase 3: Lista global de audiencias

- Crear bloque "Lista de envío" donde se agregan selecciones confirmadas.
- Cada entrada debe incluir:
- fuente,
- selector (id/label),
- tipo de origen.
- Acciones:
- quitar entrada individual,
- limpiar lista.

Criterios:
- Se puede mezclar contenido de distintas fuentes.
- Persistencia temporal en estado del formulario (JS).

## Fase 4: Entrada manual de emails

- Campo para añadir emails manuales (coma, punto y coma, salto de línea).
- Validación formato email al insertar.
- Normalización (trim, lowercase).
- Añadir como origen `manual` en lista global.
- Permitir quitar emails manuales individuales.

Criterios:
- Emails inválidos no se agregan y muestran aviso claro.
- No duplicar manuales ya existentes.

## Fase 5: Dedupe global + Preview consolidada

- Consolidar destinatarios desde:
- Producto(s) seleccionados,
- Rol(es) seleccionados,
- Lista(s) Mail Mint seleccionadas,
- Emails manuales.
- Ejecutar dedupe global por email.
- Mostrar resumen:
- total bruto,
- duplicados eliminados,
- total único final,
- desglose por fuente.
- Mostrar muestra/listado de emails únicos resultantes.

Criterios:
- Email repetido entre fuentes se envía una sola vez.

## Fase 6: Backend y endpoint unificado

- Extender payload del endpoint de envío para soportar selección global.
- Mantener ruta única:
- instantáneo => encolar lotes inmediatos,
- programado => guardar programación + encolar ejecución futura.
- Guardar metadatos de audiencia global por envío para auditoría/logs.

Criterios:
- Compatibilidad con estructura existente.
- Sin regresiones en envío actual.

## Fase 7: Compatibilidad/seguridad

- Mantener nonces/capability checks/sanitización/escaping.
- Si Mail Mint no está activo, deshabilitar su fuente con aviso.
- Validar server-side todos los IDs de selectores y emails manuales.

Criterios:
- No confiar en validación solo cliente.

## Fase 8: Gestión y logs

- Asegurar que gestión/logs reflejan audiencias mixtas.
- Mostrar origen compuesto en tabla/logs cuando aplique.

Criterios:
- Lectura clara para soporte y auditoría.

## Diseño de Datos (alto nivel)

- Modelo de selección global (en frontend):
- `source`: `product|role|mailmint|manual`
- `selector_id`: string/int
- `selector_label`: string
- `emails` (manual opcional)
- Metadatos por envío persistidos en opción/meta para reconstruir audiencia en logs.

## Endpoints AJAX previstos

- Buscar selectores por fuente (3+ chars).
- Cargar sugerencias iniciales por fuente.
- Preview consolidada global.
- Envío consolidado global.

## Riesgos y mitigaciones

- Riesgo: payload grande al mezclar muchas fuentes.
- Mitigación: enviar IDs + resolver server-side, no enviar listas masivas crudas.

- Riesgo: duplicados y conteos inconsistentes.
- Mitigación: dedupe centralizado en backend como fuente de verdad.

- Riesgo: regresión en flujo actual.
- Mitigación: mantener endpoint unificado y compatibilidad progresiva.

## Orden recomendado de ejecución

1. UI fuente botón (sin romper select lógico).
2. Selector múltiple + sugerencias/búsqueda.
3. Lista global acumulada.
4. Manual emails.
5. Preview consolidada + dedupe.
6. Envío consolidado backend.
7. Logs/gestión.
8. Pulido UI final.

## Definición de terminado (DoD)

- Se pueden combinar múltiples fuentes/selectores en una sola campaña.
- Se pueden añadir emails manuales válidos.
- Preview muestra resumen global correcto.
- Duplicados se eliminan antes de enviar.
- Envío instantáneo y programado siguen funcionando.
- Logs muestran contexto suficiente de audiencia.
- No hay errores de sintaxis (`php -l`).
- `git diff --check` sin problemas.
