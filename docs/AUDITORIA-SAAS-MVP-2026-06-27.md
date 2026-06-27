# Auditoria SaaS MVP - 2026-06-27

Fuente revisada:

```text
stable-1.5.1/captacion-app
```

## Estado actual

La base actual ya permite evolucionar hacia un SaaS sobre WordPress sin rehacer el proyecto desde cero.

Elementos ya presentes:

- Tema WordPress instalable con cabecera valida en `style.css`.
- Panel de ajustes `Captacion.app` para textos, Stripe y Mailchimp.
- Registro, login, logout y verificacion mediante REST API propia.
- Endpoints REST `captacion/v1` para registros, contacto, reportes, recursos, tareas, marketplace access y territorios.
- Usuarios nativos de WordPress como base de identidad.
- Tablas propias para registros, eventos de email, recursos, accesos y territorios.
- Payment Links de Stripe configurables desde WordPress.
- Funciones preparadas para asignar plan desde webhook.
- Integracion Mailchimp desde servidor, no desde navegador.
- Datos territoriales INE como base/fallback local.

## Riesgos antes de produccion

- Hay referencias documentadas a `localStorage` como apoyo de preproduccion; no debe considerarse persistencia final.
- El desbloqueo comercial depende de confirmar correctamente el webhook de Stripe antes de conceder accesos.
- Los enlaces Stripe pueden estar vacios en configuracion; el producto debe fallar de forma segura si no estan definidos.
- Las claves de Mailchimp y servicios externos deben configurarse solo en WordPress/Hostinger.
- Google Calendar OAuth aparece pendiente de infraestructura real; ICS puede mantenerse como alternativa sin credenciales.
- Antes de publicar, hay que validar que visitantes no registrados no ejecuten acciones protegidas.

## Prioridad tecnica recomendada

1. Validar flujo publico/privado completo: registro, login, marketplace, demanda, captacion, recursos y panel privado.
2. Revisar todas las rutas que todavia usen datos demo o `localStorage` y clasificarlas por impacto.
3. Confirmar la persistencia real de captaciones, demandas, accesos, favoritos, notificaciones y reportes.
4. Especificar en `captacion-os/spec/` cualquier migracion de base de datos antes de implementarla.
5. Configurar Stripe en modo prueba y probar webhook antes de activar cobros reales.
6. Validar Mailchimp con una audiencia de prueba y etiquetas por origen.
7. Generar ZIP instalable sin versionarlo y probar activacion en WordPress.

## Decisiones de arquitectura

- Mantener WordPress como MVP SaaS inicial.
- Mantener la fuente activa en `stable-1.5.1/captacion-app`.
- No crear por ahora una app separada en `app.dominio.com` hasta validar usuarios reales y necesidad de escalado.
- No modificar login, pagos, datos territoriales ni reglas de acceso sin plan especifico y validacion.
