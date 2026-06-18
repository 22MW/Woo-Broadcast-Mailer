# Nota de departamento — Jefe de Proyecto

## Última actualización

2026-06-18

## Resumen humano

Plan A completo aplicado y consolidado. A1-A7 están cerrados a nivel de código/workflow/build. Queda QA funcional y validación real de ZIP/release antes de publicar.

## Descubierto

- El usuario necesitaba que los códigos A1-A7 fueran consultables y no solo menciones sueltas.
- `roadmap.md` y `visual.html` no estaban suficientemente claros para consultar qué contenía A5/A6.
- A2 requería separar programación de lotes de finalización real por logs.
- A3 requería conservar snapshot hasta completar/eliminar.
- A4 requería build para que el cambio React se viera en admin.
- A7 requería excluir `_dev/` del ZIP/release.

## Hecho

- `roadmap.md` reorganizado con tabla Plan A: código, nombre, estado, prioridad, contenido, archivos probables y validación.
- `visual.html` reorganizado con tarjetas A1-A7.
- `estado.md` actualizado con resumen Plan A.
- A1 queda marcado como hecho.
- A2 queda marcado como hecho.
- A3 queda marcado como hecho.
- A4 queda marcado como hecho y compilado.
- A5 queda marcado como hecho.
- A6 queda marcado como hecho.
- A7 queda marcado como hecho.
- Entorno Node local preparado para build.
- `node_modules/` copiado desde el plugin anterior.
- `npm run build` ejecutado con éxito.

## Pendiente

- QA funcional del Plan A completo.
- Validación real de ZIP/release.
- Checklist release antes de publicar.
- Release, tag, push o deploy siguen bloqueados hasta permiso explícito.

## No volver a investigar

- Plan A significa bloque técnico mínimo antes de QA/release.
- A1: Action Scheduler obligatorio + aviso admin.
- A2: Estados/logs reales.
- A3: Snapshot seguro de destinatarios.
- A4: Preview no obsoleto.
- A5: Escape de `{customer_name}`.
- A6: Borrado por IDs seguro.
- A7: ZIP/release sin `_dev/`.
- Node local disponible en `/Users/22mw/.local/node-install/node-v22.11.0-darwin-arm64/bin`.

## Riesgos o bloqueos

- No ejecutar QA funcional sin permiso porque puede crear envíos, logs o acciones programadas.
- No preparar release sin validación de ZIP y checklist.
- No mezclar Plan A con Email String Editor.

## Próximo paso recomendado

- Ejecutar QA funcional controlado del Plan A completo.
