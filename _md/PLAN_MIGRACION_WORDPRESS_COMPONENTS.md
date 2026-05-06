# Plan de Migración: `@wordpress/components` en Woo Broadcast Mailer

## Objetivo

Migrar la UI del admin a stack WordPress moderno (`@wordpress/components` + `@wordpress/element`) de forma incremental, sin romper flujo actual ni backend de envíos.

## Principios

- Cero ruptura funcional en producción.
- Migración por fases pequeñas y reversibles.
- Mantener endpoints PHP/AJAX actuales al inicio.
- Reutilizar lógica de seguridad existente (nonces/capabilities/sanitización).

## Stack recomendado

- `@wordpress/components`
- `@wordpress/element`
- `@wordpress/i18n`
- `@wordpress/api-fetch` (opcional, luego)
- `@wordpress/scripts` para build

## Estructura propuesta

```text
woo-broadcast-mailer/
├── src/
│   └── admin/
│       ├── index.js
│       ├── App.js
│       ├── services/
│       │   └── api.js
│       ├── store/
│       │   └── useBroadcastState.js
│       └── components/
│           ├── SourceSelector.js
│           ├── AudienceBuilder.js
│           ├── ManualEmailsInput.js
│           ├── PreviewPanel.js
│           └── SendActions.js
├── build/
│   └── ... (generado)
└── woo-broadcast-mailer.php
```

## Fase 1: Base técnica (sin cambiar UX)

### Alcance

- Configurar build JS con `@wordpress/scripts`.
- Añadir entrypoint React en admin solo para esta página.
- Renderizar un contenedor app (`#pbm-admin-app`) junto al markup actual.
- Pasar config inicial desde PHP a JS.

### Criterios de aceptación

- Plugin carga sin errores.
- Pantalla actual sigue funcionando igual.
- App React se monta correctamente (aunque mínima).

## Fase 2: Migrar solo Fuente + Selector

### Alcance

- Reemplazar componente de `Fuente` por botones/radio usando componentes WP.
- Migrar selector dependiente (producto/rol/mailmint) dentro de React.
- Mantener preview y envío aún en flujo actual (compatibilidad).

### Criterios de aceptación

- Cambiar fuente actualiza selector correcto.
- Mail Mint deshabilitado se refleja en UI.
- No hay regresión de validaciones básicas.

## Fase 3: Construcción de audiencia global

### Alcance

- Implementar lista global de audiencias en estado React.
- Soportar múltiples selectores por fuente.
- Añadir entrada manual de emails.
- Dedupe cliente preliminar para UX inmediata.

### Criterios de aceptación

- El usuario puede añadir/quitar elementos por fuente.
- Puede añadir emails manuales válidos.
- Se evita duplicado visual en lista global.

## Fase 4: Preview consolidada en React

### Alcance

- Mover “Vista previa” a React.
- Crear servicio `api.js` para llamadas AJAX.
- Enviar payload global al backend y mostrar resumen consolidado.

### Criterios de aceptación

- Preview muestra bruto/duplicados/únicos.
- Errores de API muestran `Notice` claro.
- No se rompe seguridad (`nonce` vigente).

## Fase 5: Envío unificado desde React

### Alcance

- Mover submit completo a React.
- Mantener endpoint único instantáneo/programado.
- Enviar metadatos de audiencia global.

### Criterios de aceptación

- Instantáneo funciona.
- Programado funciona.
- Logs reflejan audiencia compuesta.

## Fase 6: Limpieza controlada

### Alcance

- Eliminar markup JS legado solo cuando la nueva UI cubra 100% del flujo.
- Mantener fallback temporal detrás de feature flag opcional.

### Criterios de aceptación

- Sin código duplicado crítico.
- Sin pérdida funcional.

## Diseño de estado recomendado

```text
state = {
  source: 'product|role|mailmint',
  selections: {
    product: [],
    role: [],
    mailmint: [],
    manual: []
  },
  globalAudience: [],
  preview: {
    totalRaw: 0,
    duplicates: 0,
    totalUnique: 0,
    emailsSample: []
  },
  sendConfig: {
    subject: '',
    message: '',
    batchSize: 30,
    emailsPerHour: 200,
    scheduleEnabled: false,
    scheduledDatetime: ''
  },
  ui: {
    loadingPreview: false,
    sending: false,
    notices: []
  }
}
```

## Contrato API (objetivo)

- `pbm_search_selectors` (nuevo): búsqueda por fuente con 3+ chars.
- `pbm_preview_recipients` (extensión): aceptar payload global.
- `pbm_send_broadcast` (extensión): aceptar payload global + manual.

Nota: mantener compatibilidad con parámetros actuales durante transición.

## Riesgos y mitigación

- Riesgo: mezcla React + jQuery durante transición.
- Mitigación: aislar responsabilidades por fase.

- Riesgo: regresión en envío.
- Mitigación: backend como fuente de verdad + dedupe server-side.

- Riesgo: deuda en estilos duplicados.
- Mitigación: tokens CSS compartidos y componentes reutilizables.

## Definición de Terminado

- UI principal en `@wordpress/components`.
- Flujo multifuente + manual operativo.
- Preview y envío consolidados.
- Dedupe garantizado server-side.
- Gestión/logs consistentes con nueva audiencia.
- Sin errores de sintaxis en PHP tocado.
- `git diff --check` limpio.

## Reglas de ejecución

Aplicar siempre `_md/REGLAS_DESARROLLO.md` antes y durante cada tarea.
