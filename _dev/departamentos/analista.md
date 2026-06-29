# Analista

## Última actualización

2026-06-29

## Relevo breve

Funcionalidad nueva analizada: Plan LOG para mejorar el resumen de audiencia y los logs de envío. El problema funcional es que el admin no puede entender con claridad a quién se envía ni qué ocurrió con cada destinatario cuando el resumen muestra datos pobres como `Audiencia: Global: Roles: 1`.

## Hecho en esta tarea

- Definido MVP recomendado: **resumen de audiencia legible + log básico por destinatario** antes de aperturas/clics.
- Audiencia a mostrar cuando esté disponible:
  - Nombre real del rol, Broadcast List, producto, categoría o lista/fuente externa.
  - Tipo de fuente: rol, Broadcast List, producto, categoría, lista externa, email manual u otra fuente soportada.
  - Modo de audiencia: `fixed` o `dynamic`; si falta dato, tratar como `fixed` según memoria AD3.
  - Número previsto en preview y número final al ejecutar/enviar.
  - Emails manuales añadidos y exclusiones aplicadas.
- Separación funcional de datos:
  - Datos que pueden existir ya: identificadores de roles/fuentes, emails destinatarios, estados/logs de envío y `audience_mode` para programados desde AD3.
  - Datos a empezar a guardar como snapshot descriptivo: nombres legibles de fuentes, etiquetas visibles, conteos previstos/finales, modo aplicado y resumen de exclusiones/manuales.
- Logs posibles sin tracking extra:
  - Enviados, pendientes y fallidos por flujo de envío/Action Scheduler/wp_mail si el plugin ya guarda esos estados.
  - Estado por destinatario si ya existe log por email.
  - Fecha/hora e información técnica de error si ya se registra.
- Logs que requieren tracking nuevo:
  - Aperturas: pixel de tracking.
  - Clics: enlaces redirigidos o reescritura de URLs.
  - Bajas/unsubscribe: sistema real de baja.
  - Rebotes reales: integración con proveedor SMTP/webhook.
  - Entregado real: depende del proveedor; no equivale a `wp_mail` correcto.

## Plan funcional

### Objetivo

Dar al administrador una lectura clara de audiencia y resultado de envío sin prometer métricas que el plugin no puede saber todavía.

### Usuario afectado

Administrador o gestor que prepara, programa y revisa campañas desde Woo Broadcast Mailer.

### MVP

- Mostrar resumen de audiencia legible en preview, envío programado y detalle/log.
- Guardar snapshot descriptivo mínimo para que el resumen histórico no dependa de que el rol/lista/producto cambie de nombre o desaparezca.
- Mostrar log básico por destinatario con estado disponible: enviado, pendiente o fallido, fecha/hora y error técnico si existe.

### Alcance incluido

- Nombres legibles de fuentes si están disponibles.
- Conteos previsto/final.
- Modo `fixed`/`dynamic`.
- Manuales y exclusiones.
- Fallos técnicos y estados básicos de entrega interna del plugin.

### Alcance excluido del MVP

- Aperturas.
- Clics.
- Rebotes reales.
- Entrega real confirmada por proveedor.
- Baja/unsubscribe permanente.
- Dashboard analítico avanzado.

### Mejoras futuras

- Tracking de aperturas con aviso de precisión limitada.
- Tracking de clics con redirección controlada.
- Webhooks SMTP para rebotes/entregas si el proveedor lo permite.
- Métricas agregadas por campaña y fuente.

### Extras opcionales

- Exportar logs por campaña.
- Filtros por estado/fuente/destinatario.
- Resumen comparativo entre audiencia prevista y audiencia final.

## Reglas de negocio

- No presentar `wp_mail` correcto como entrega real al buzón.
- Si una fuente no tiene nombre disponible, mostrar identificador y tipo, no inventar etiqueta.
- En modo dinámico, la audiencia prevista es estimación; la final es la recalculada al ejecutar.
- Los emails manuales deben mostrarse separados de fuentes dinámicas.
- Las exclusiones deben quedar visibles como parte del resumen.
- Si hay datos incompletos o históricos sin snapshot descriptivo, mostrar “dato no disponible” o equivalente funcional.

## Casos límite

- Usuario sin permisos: no debe poder ver logs/audiencias si no tiene capacidad administrativa adecuada.
- Datos incompletos: fuente sin nombre, rol borrado, producto/categoría eliminado o lista externa no disponible.
- Datos duplicados: el resumen debe distinguir fuentes seleccionadas de destinatarios únicos finales.
- Acción repetida: reintentos o reprocesos no deben duplicar destinatarios ni inflar conteos sin indicarlo.
- Plugin dependiente desactivado: fuentes externas deben mostrarse como no disponibles si el plugin origen no responde.
- WooCommerce inactivo: fuentes basadas en productos/categorías/pedidos quedan no disponibles o bloqueadas según comportamiento existente pendiente de revisar.
- Multisitio: los roles/listas/fuentes deben interpretarse en el sitio correspondiente; pendiente de confirmar soporte real.

## Criterios de aceptación

- Un resumen nunca debe limitarse a `Roles: 1` si el nombre legible del rol está disponible.
- El admin puede distinguir qué fuentes componen la audiencia y cuántos destinatarios únicos resultan.
- En programados, el admin puede ver si la audiencia fue fija o dinámica.
- El detalle histórico conserva una descripción comprensible aunque la fuente cambie después, si se implementa snapshot descriptivo.
- El log básico permite identificar qué emails fallaron, cuáles quedaron pendientes y cuáles se procesaron como enviados según el plugin.
- Las métricas de aperturas/clics/rebotes no aparecen como disponibles hasta implementar tracking o integración específica.

## Pendiente / riesgos

- Pendiente revisar código para confirmar qué datos ya existen exactamente y dónde se guardan.
- Pendiente decidir si LOG1 y LOG2 entran antes o después de cerrar AD4/QA.
- Riesgo RGPD/privacidad: guardar eventos por email, aperturas, clics o bajas requiere criterio de minimización, retención y transparencia.
- Riesgo de precisión: aperturas pueden ser bloqueadas por clientes de email; clics requieren reescritura de URLs; rebotes/entregas dependen del proveedor SMTP.
- Riesgo UX: mostrar demasiados datos puede confundir si no se separa “estimado”, “procesado” y “entregado real”.

## No volver a investigar

- AD3 ya implementó modo fijo/dinámico para programados y `audience_mode=dynamic`; si falta, el comportamiento histórico se trata como `fixed`.
- El text domain correcto es `wc-pbm`.
- El MVP funcional recomendado para esta mejora es “Resumen de audiencia legible + log por destinatario básico”.

## Relevo para

→ Arquitecto: revisar datos reales existentes y proponer diseño mínimo para `LOG1`/`LOG2` sin incluir aperturas/clics en MVP.
