# Nota de departamento — Jefe de Proyecto

## Última actualización

2026-06-18

## Resumen humano

Plan A ordenado y consolidado. Los códigos A1-A7 quedan documentados en `roadmap.md` y `visual.html` para consulta y ejecución por partes. A1 está hecho; A2-A7 siguen pendientes según prioridad.

## Descubierto

- El usuario necesitaba que los códigos A1-A7 fueran consultables y no solo menciones sueltas.
- `roadmap.md` y `visual.html` no estaban suficientemente claros para consultar qué contenía A5/A6.

## Hecho

- `roadmap.md` reorganizado con tabla Plan A: código, nombre, estado, prioridad, contenido, archivos probables y validación.
- `visual.html` reorganizado con tarjetas A1-A7.
- `estado.md` actualizado con resumen Plan A.
- A1 queda marcado como hecho.
- A2-A7 quedan marcados como pendientes.

## Pendiente

- Elegir siguiente punto del Plan A.
- Recomendado por bajo riesgo: A5 o A6.
- QA funcional sigue pendiente y bloqueado hasta permiso.
- Release sigue bloqueada hasta A7 y checklist release.

## No volver a investigar

- Plan A significa bloque técnico mínimo antes de QA/release.
- A1: Action Scheduler obligatorio + aviso admin.
- A2: Estados/logs reales.
- A3: Snapshot seguro de destinatarios.
- A4: Preview no obsoleto.
- A5: Escape de `{customer_name}`.
- A6: Borrado por IDs seguro.
- A7: ZIP/release sin `_dev/`.

## Riesgos o bloqueos

- No ejecutar QA funcional sin permiso porque puede crear envíos, logs o acciones programadas.
- No preparar release sin A7.
- No mezclar Plan A con Email String Editor.

## Próximo paso recomendado

- Ejecutar A5 o A6 como fix pequeño y validable.
