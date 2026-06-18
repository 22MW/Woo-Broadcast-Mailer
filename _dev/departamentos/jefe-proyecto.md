# Nota de departamento — Jefe de Proyecto

## Última actualización

2026-06-19

## Resumen humano

Plan A completo aplicado y consolidado. Email String Editor E1-E5 y R1 React están implementados. A4 post-fix tiene QA OK del usuario. 22MW-BACK se evaluó como posible, pero debe entrar por piloto, no como rediseño entero de golpe.

## Descubierto

- El usuario necesitaba que los códigos A1-A7 fueran consultables y no solo menciones sueltas.
- `roadmap.md` y `visual.html` no estaban suficientemente claros para consultar qué contenía A5/A6.
- A2 requería separar programación de lotes de finalización real por logs.
- A3 requería conservar snapshot hasta completar/eliminar.
- A4 requería build para que el cambio React se viera en admin.
- A7 requería excluir `_dev/` del ZIP/release.
- Email String Editor debía entrar por fases: módulo propio, admin MVP y guardado/borrado antes de activar `gettext`.
- 22MW-BACK puede aportar identidad visual al admin, pero el alcance debe separarse en inventario, piloto y extensión.

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
- Email String Editor E1-E3 implementado.
- E4 implementado y QA reportado OK.
- R1 implementado y pusheado en `2.0.1.6`.
- A4 post-fix aplicado y QA reportado OK por usuario.
- Plan B 22MW-BACK añadido al roadmap como propuesta por fases.

## Pendiente

- Decidir alcance del piloto 22MW-BACK.
- QA funcional del Plan A completo.
- QA admin de Email String Editor E1-E3.
- E4 Email String Editor.
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
- No activar cambios 22MW-BACK sin arquitectura previa y piloto aprobado.

## Próximo paso recomendado

- Decidir B0/B1: inventario admin 22MW-BACK y piloto en Broadcast principal.
