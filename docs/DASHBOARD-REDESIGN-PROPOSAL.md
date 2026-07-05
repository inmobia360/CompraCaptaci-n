# Propuesta de Rediseño — Dashboard / Resumen Ejecutivo (v3)

## Problema

Dos bloques casi duplicados + tres formas de representar lo mismo (donut, funnel, summary cards). El resultado es sobrecarga: el usuario ve la misma información repetida sin una jerarquía clara.

## Lo que se conserva

- **Donut chart** — visión gráfica de la distribución oferta/demanda
- **Embudo comercial (funnel)** — trazabilidad del pipeline (captación → solicitud → match → operación → cierre)

Ambos son ejemplos visuales valiosos para el día a día del agente.

## Propuesta: Dashboard en estándar internacional (Linear + HubSpot)

### Header con acceso rápido a XML

```
┌──────────────────────────────────────────────────────────────┐
│ Resumen ejecutivo                    [30 días] [⬆ Subir XML] │
│ Visión general de tu actividad               [Exportar PDF]  │
└──────────────────────────────────────────────────────────────┘
```

El botón **⬆ Subir XML** va en el header, junto a Exportar PDF, destacado en color (ámbar/naranja) para que el usuario lo vea siempre y sepa que ahí puede subir sus propiedades.

### Capa 1 — KPI bar (5 métricas cliqueables)

```
┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐
│Captaciones│ │ Demandas  │ │ Matches   │ │Solicitudes│ │Operaciones│
│   291     │ │    34     │ │    1      │ │    2      │ │    4      │
│↑12% vs ant│ │ ↑8% vs ant│ │ — 0%      │ │ Nuevas    │ │ ↑33%      │
│Acceder →  │ │Acceder →  │ │ Ver →     │ │ Revisar → │ │ Ver →     │
└──────────┘ └──────────┘ └──────────┘ └──────────┘ └──────────┘
```

Estándar Linear/HubSpot: cada métrica es una tarjeta con número grande, tendencia y CTA.

### Capa 2 — Donut + Funnel (2 columnas, igual que ahora)

```
┌──────────────────────────┐ ┌──────────────────────────────┐
│ Distribución general      │ │ Embudo comercial             │
│  ┌─────┐                 │ │ Captaciones    291 → 100%    │
│  │donut│  72% Captac.    │ │ Solicitudes      2 →  0,7%   │
│  │     │  18% Demandas   │ │ Coincidencias    1 →  0,3%   │
│  │     │   8% Solicitudes│ │ Operaciones      4 →  1,4%   │
│  │     │   2% Coinciden. │ │ Cerradas          6 →  2,1%   │
│  └─────┘  326 Total      │ │                              │
└──────────────────────────┘ └──────────────────────────────┘
```

Se mantiene exactamente como está ahora, sin cambios.

### Capa 3 — Pulse (Lo que necesita atención)

Inspirado en Linear Pulse. Reemplaza "Requiere tu atención" + "Próximas acciones".

```
┌────────────────────────────────────────────────────────────┐
│ Pulse · Lo que necesita atención                           │
├────────────────────────────────────────────────────────────┤
│ ● 2 solicitudes pendientes                     Revisar →  │
│ ● 1 XML sin publicar — Importado hace 2d       Publicar → │
│ ● 3 coincidencias nuevas                        Ver →     │
│ ● 1 operación sin actualizar — Desde hace 5d   Gestionar →│
└────────────────────────────────────────────────────────────┘
```

### Capa 4 — Actividad reciente (2 columnas)

```
┌──────────────────────────────┐ ┌───────────────────────────┐
│ Últimas solicitudes          │ │ Operaciones recientes     │
│ • Solicitud #REF-001 — 3h    │ │ OP-001 → En negociación   │
│ • Solicitud #REF-002 — 1d    │ │ OP-002 → Completada       │
│ Ver todas →                   │ │ Gestionar →               │
└──────────────────────────────┘ └───────────────────────────┘
```

### Capa 5 — Coincidencias recomendadas

Se mantiene, con diseño más compacto.

## Resumen de cambios

| Mantener | Eliminar (duplicado) |
|---|---|
| ✅ Donut chart | ❌ Summary cards (6 items) |
| ✅ Embudo comercial (funnel) | ❌ KPIs dinámicos duplicados |
| ✅ Coincidencias recomendadas | ❌ Filtros Vista general/Captaciones/Demandas |
| ✅ Operaciones recientes | ❌ Próximas acciones (fusionar con Pulse) |
| ✅ Últimas solicitudes | ❌ Almanaque operativo (mover a Calendario) |
| ✅ Actividad del mes | ❌ Historial de accesos (mover a Privacidad) |
| ✅ Mis accesos disponibles | ❌ Favoritos recientes (en su panel) |
| | ❌ Actividad reciente redundante |

## Nuevo / Cambiado

| Elemento | Cambio |
|---|---|
| **Header** | Nuevo botón "⬆ Subir XML" destacado (ámbar) |
| **KPI bar** | Rediseñada: cards más limpias, tendencias, CTA |
| **"Requiere tu atención"** | Renovado a "Pulse" con diseño Linear |
| **Sidebar Feeds XML** | Se mantiene en Configuración |
| **Resumen ejecutivo** | De ~15 secciones a ~8 organizadas |

## Estructura final

```
┌──────────────────────────────────────────────────────────────┐
│ Resumen ejecutivo                    [30 días] [⬆ Subir XML] │
│                                            [Exportar PDF]    │
├──────────────────────────────────────────────────────────────┤
│ ┌──────┐ ┌──────┐ ┌──────┐ ┌──────┐ ┌──────┐               │
│ │Captac│ │Demand│ │Match │ │Solic.│ │Oper. │               │
│ │ 291  │ │  34  │ │  1   │ │  2   │ │  4   │               │
│ │↑12%  │ │↑8%   │ │— 0%  │ │Nueva │ │↑33%  │               │
│ │Acceder│ │Acceder│ │Ver → │ │Revisar│ │Ver → │               │
│ └──────┘ └──────┘ └──────┘ └──────┘ └──────┘               │
├───────────────────────┬────────────────────────────────────┤
│ Donut distribución    │ Embudo comercial                    │
│ (sin cambios)         │ (sin cambios)                       │
├───────────────────────┴────────────────────────────────────┤
│ Pulse · Lo que necesita atención                           │
├──────────────────┬────────────────────────────────────────┤
│ Últimas          │ Operaciones recientes                   │
│ solicitudes      │                                         │
├──────────────────┴────────────────────────────────────────┤
│ Coincidencias recomendadas                                │
└───────────────────────────────────────────────────────────┘
```
