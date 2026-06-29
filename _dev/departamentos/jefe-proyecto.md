# Nota de departamento — Jefe de Proyecto

## Última actualización

2026-06-29

## Resumen humano

Se prepara release `2.5.0` con memoria `_dev/` consolidada y documentación pública actualizada. El plugin sigue en `devWooBM` y el objetivo autorizado es commit, push y tag `v2.5.0`.

## Descubierto

- `2.4.0` ya existe como tag previo.
- Los cambios posteriores a `2.4.0` afectan audiencia programada fija/dinámica y logs informativos.
- QA AD4/LOG2/LOG3 fue confirmado por usuario; no ejecutado por el agente.

## Hecho

- Clasificada la tarea como release + Git + memoria `_dev`.
- Revisado estado Git, rama, tags y versión.
- Actualizados documentos públicos de release a `2.5.0`.
- Actualizados `_dev/estado.md`, `_dev/roadmap.md`, `_dev/decisiones.md`, `_dev/release-notes.md`.
- Actualizadas notas de Release Manager y Jefe de Proyecto.
- Actualizado `visual.html` para reflejar release `2.5.0` en preparación.

## Pendiente

- Ejecutar validaciones finales.
- Commit, push y tag `v2.5.0`.
- Confirmar GitHub Release/ZIP.

## No volver a investigar

- Plugin objetivo: Woo Broadcast Mailer.
- Ruta: `app/public/wp-content/plugins/Woo-Broadcast-Mailer/`.
- Rama: `devWooBM`.
- Release objetivo: `2.5.0`.
- `_dev/` no entra en ZIP/release.

## Riesgos o bloqueos

- QA real de envío crea acciones/logs; AD4/LOG2/LOG3 fue confirmado por usuario, no ejecutado por el agente.
- Producción requiere staging/backup/rollback si se despliega.

## Próximo paso recomendado

- Completar release `2.5.0` con validación, commit, push y tag.
