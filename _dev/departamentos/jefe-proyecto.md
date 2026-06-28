# Nota de departamento — Jefe de Proyecto

## Última actualización

2026-06-28

## Resumen humano

Se corrige el flujo operativo: antes de cerrar release `2.4.0` se actualiza la memoria `_dev/` y se separan estado, roadmap, decisiones, desarrollo, QA pendiente y release. El plugin sigue en `devWooBM` y la release objetivo es `2.4.0`.

## Descubierto

- La memoria principal seguía desactualizada con referencias a `2.0.2` aunque ya existía release `2.3.0`.
- Los cambios pendientes actuales son de desarrollo menor: shortcodes y TinyMCE.
- No hay QA funcional completo ejecutado para estos cambios.

## Hecho

- Clasificada la tarea como release + Git + memoria `_dev`.
- Revisado estado Git y rama.
- Actualizados `_dev/estado.md`, `_dev/roadmap.md`, `_dev/decisiones.md`, `_dev/release-notes.md`.
- Actualizadas notas de Desarrollador, Release Manager, Tester QA y Jefe de Proyecto.
- Actualizado `visual.html` para reflejar release `2.4.0` en preparación.

## Pendiente

- Ejecutar validaciones finales.
- Actualizar versión/changelog/readme.
- Commit, push y tag `v2.4.0`.
- Confirmar GitHub Release/ZIP.

## No volver a investigar

- Plugin objetivo: Woo Broadcast Mailer.
- Ruta: `app/public/wp-content/plugins/Woo-Broadcast-Mailer/`.
- Rama: `devWooBM`.
- Release objetivo: `2.4.0`.
- `_dev/` no entra en ZIP/release.

## Riesgos o bloqueos

- QA real de envío crea acciones/logs; queda pendiente.
- Producción requiere staging/backup/rollback si se despliega.

## Próximo paso recomendado

- Completar release `2.4.0` con validación, commit, push y tag.
