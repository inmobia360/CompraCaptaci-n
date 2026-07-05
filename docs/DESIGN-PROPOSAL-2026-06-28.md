# Propuesta de Mejora UX/UI — Captacion.app

> Basada en análisis de Linear, Stripe, Framer, HubSpot, Airbnb, Zillow, Redfin, Idealista.
> Fecha: 2026-06-28

---

## Referencias analizadas

| Referencia | Patrón extraído | Adaptación a Captacion.app |
|---|---|---|
| **Linear** (2026) | Sidebar limpia como navegación principal; dashboard con métricas cliqueables; "Pulse" como sección de atención prioritaria | Dashboard con métricas accionables; navegación del panel privado agrupada por bloques operativos |
| **Stripe** (2026) | Hero con propuesta de valor única y CTA principal dominante; beneficios antes que funcionalidades; cifras de confianza | Landing con hero más directo; CTA "Entrar como profesional" dominante; bloque de confianza con datos del marketplace |
| **Framer** (2026) | Hero interactivo mostrando el producto en acción; secciones con narrativa clara; agentes como extensión del producto | Hero con preview visual del producto; bloques "cómo funciona" en 3 pasos |
| **HubSpot** (2026) | Dashboard accionable; taxonomía de navegación jerárquica; CRM como centro; IA operativa integrada | Panel privado con separación entre "operación diaria" y "configuración"; IA como herramienta operativa incrustada |
| **Airbnb/Zillow/Redfin/Idealista** | Fichas limpias con foto como elemento principal; filtros agrupados por intención; vista mapa/lista; estado y disponibilidad visibles | Marketplace con fichas orientadas a oportunidad B2B (tipo, provincia, precio, estado, demanda compatible); filtros agrupados por intención profesional |

---

## Diagnóstico general

Captacion.app tiene una propuesta potente: **red privada B2B inmobiliaria** que conecta captaciones, demanda activa y profesionales. El problema no es falta de funcionalidad, sino **exceso de complejidad visible** y **falta de jerarquía estratégica** en tres áreas clave: landing, marketplace y dashboard.

---

## Propuesta 1: Landing / Home

### Problemas detectados

- La propuesta de valor se diluye entre badge, headline, dos concept cards, dos CTAs, KPIs, valores, mapa, carruseles y registro.
- Dos CTAs al mismo nivel visual ("Comenzar gratis" y "Ver cómo funciona").
- El formulario de registro embebido en la landing crea fricción (el usuario no sabe si registrarse o explorar).
- La sección "Últimas publicaciones" compite con el hero.

### Cambios propuestos

| Componente | Cambio |
|---|---|
| **Hero** | Headline más directo: *"Captaciones inmobiliarias B2B con demanda activa y colaboración segura"*. CTA principal única: **"Entrar como profesional"**. CTA secundaria texto plano: *"Ver cómo funciona"* o *"Explorar marketplace"*. |
| **Preview visual** | Añadir mockup/imagen del producto (siguiente patrón Framer/Stripe) que muestre el dashboard o el marketplace en funcionamiento. |
| **"Cómo funciona"** | Bloque nuevo en 3 pasos: (1) Publica tu captación o demanda, (2) El sistema cruza oportunidades, (3) Colabora con control de acceso y trazabilidad. |
| **Bloque de confianza** | Reemplazar "Valores" actual con cifras concretas: *"X captaciones activas · Y profesionales · Z provincias con cobertura"*. |
| **Registro** | Mover a modal/tras CTA. El hero debe vender, no pedir datos. |

### Copy sugerido para el hero

> **Headline:** Captaciones inmobiliarias B2B con demanda activa y colaboración segura.
> **Subheadline:** Publica, cruza y colabora con otros profesionales manteniendo privacidad, trazabilidad y control de acceso.
> **CTA principal:** Entrar como profesional
> **CTA secundaria:** Explorar marketplace

### Archivos a modificar
- `stable-1.5.1/captacion-app/template-app-interactiva.php` (sections líneas 931-1216)

---

## Propuesta 2: Marketplace

### Problemas detectados

- Las fichas pueden parecer de portal inmobiliario tradicional en lugar de oportunidades profesionales B2B.
- Los filtros están funcionalmente completos pero visualmente densos.
- Falta énfasis en el contexto profesional de cada oportunidad.

### Cambios propuestos

| Componente | Cambio |
|---|---|
| **Fichas de propiedad** | Priorizar datos B2B: tipo de oportunidad (captación/demanda), provincia/municipio, precio, estado, demanda compatible, origen (XML/manual), nivel de datos completos. Badge de "acceso protegido". |
| **Filtros** | Agrupar por intención profesional: (1) Ubicación, (2) Tipo de oportunidad, (3) Precio, (4) Estado, (5) Origen. Reducir ruido visual. |
| **Vista por defecto** | Arrancar en vista "Bloques" (no mapa). El mapa como toggle secundario. |
| **Badges decorativos** | Eliminar badges sin valor operativo que añadan ruido visual. |
| **Encabezado** | Cambiar "Catálogo General de Captaciones" por "Oportunidades profesionales" o "Marketplace B2B". |

### Copy sugerido

> **Título:** Marketplace profesional
> **Subtítulo:** Captaciones y demandas activas de otros profesionales. Cada ficha representa una oportunidad de colaboración.

### Archivos a modificar
- `stable-1.5.1/captacion-app/template-app-interactiva.php` (sections líneas 1818-1922)
- Posiblemente CSS asociado

---

## Propuesta 3: Panel Privado / Dashboard

### Problemas detectados

- 14 items de navegación lateral mezclan operación diaria con configuración.
- El dashboard ejecutivo muestra muchas métricas sin jerarquía clara.
- "Zona de peligro" (borrado de datos) visible desde la navegación principal.

### Cambios propuestos

| Componente | Cambio |
|---|---|
| **Sidebar** | Agrupar en 3 secciones con separadores y headers: **Operación** (Inicio, Captaciones, Demandas, Solicitudes, Operaciones), **Seguimiento** (Favoritos, Calendario, Notificaciones), **Configuración** (Perfil, Suscripciones, Feeds XML, IA, Privacidad). |
| **Dashboard ejecutivo** | Reducir a 4-6 métricas principales cliqueables: Captaciones activas, Demandas activas, Matches detectados, Solicitudes pendientes, XML pendientes, Accesos disponibles. Cada una lleva a su panel correspondiente. |
| **"Pulse" (novedad)** | Añadir sección tipo Linear Pulse: "Lo que necesita atención" — solicitudes pendientes, XML sin publicar, operaciones sin actualizar. |
| **"Zona de peligro"** | Mover a subsección dentro de "Privacidad" o mantener pero fuera de la navegación principal (solo accesible desde Privacidad). |
| **IA** | Mantener "Bring your own AI" como está, es una propuesta diferencial. |

### Estructura de sidebar propuesta

```
OPERACIÓN
  └ Inicio (dashboard)
  └ Captaciones
  └ Demandas
  └ Solicitudes
  └ Operaciones

SEGUIMIENTO
  └ Favoritos
  └ Calendario
  └ Notificaciones

CONFIGURACIÓN
  └ Perfil profesional
  └ Suscripciones
  └ Feeds XML
  └ Conexión IA
  └ Privacidad y datos
```

### Archivos a modificar
- `stable-1.5.1/captacion-app/template-app-interactiva.php` (sidebar y dashboard, líneas 2406-2712)

---

## Priorización

| Prioridad | Propuesta | Esfuerzo | Impacto |
|---|---|---|---|
| P1 | Marketplace: fichas con enfoque B2B, filtros agrupados | Medio | Alto |
| P1 | Dashboard: sidebar agrupada, métricas accionables | Medio | Alto |
| P2 | Landing: hero más directo, CTA única | Bajo | Alto |
| P2 | Landing: bloque "cómo funciona" en 3 pasos | Bajo | Medio |
| P3 | Landing: mover registro a modal | Medio | Medio |
| P3 | Marketplace: vista por defecto bloques (no mapa) | Bajo | Bajo |

---

## Riesgos y conflictos

- Los cambios en landing pueden afectar a la tasa de registro actual si se mueve el formulario. Mitigación: mantener registro visible en footer y añadirlo en la CTA principal.
- La reagrupación del sidebar puede confundir a usuarios existentes. Mitigación: mantener la funcionalidad intacta, solo cambiar nomenclatura y agrupación visual.
- El marketplace actual es funcional; cambiar las fichas demasiado puede romper la experiencia. Mitigación: cambios iterativos, no rediseño completo.

---

## Criterios de aceptación

- Landing se entiende en 5 segundos: usuario sabe qué es Captacion.app y qué acción tomar.
- Marketplace comunica "oportunidad profesional", no "portal de pisos".
- Dashboard permite al profesional ver su estado operativo de un vistazo y actuar desde cada métrica.
- La navegación del panel privado es intuitiva incluso para un usuario nuevo.
- Todos los cambios mantienen la funcionalidad existente y no rompen flujos actuales.

---

## Siguientes pasos

1. Aprobación de esta propuesta.
2. Implementación por orden de prioridad (P1 primero).
3. Validación visual en navegador tras cada cambio.
4. Iteración basada en feedback.
