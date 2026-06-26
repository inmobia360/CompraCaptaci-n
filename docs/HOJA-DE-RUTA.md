# Hoja de ruta

## Corto plazo

- Consolidar la fuente activa en una estructura mas clara, previa aprobacion.
- Revisar codificacion de textos para evitar caracteres corruptos.
- Automatizar empaquetado del tema WordPress.
- Ampliar validaciones de GitHub Actions para revisar estructura del ZIP cuando se genere.
- Documentar configuracion exacta de Rank Math, Mailchimp, Stripe, Forminator, Pods y Captacion Core.

## Medio plazo

- Pasar datos demo restantes a tablas o tipos de contenido reales en WordPress.
- Reforzar persistencia de favoritos, notificaciones, actividad y preferencias por usuario.
- Completar sincronizacion territorial INE y validacion opcional CartoCiudad/CNIG.
- Mejorar auditoria de accesos a datos sensibles.
- Crear tests de flujos principales: registro, login, demanda, captacion, match, contacto y reporte.

## Largo plazo

- Separar logica de negocio en plugin propio y dejar el tema centrado en presentacion.
- Crear API interna versionada para captaciones, demandas, matches y notificaciones.
- Preparar despliegue continuo controlado para entorno staging.
- Implementar monitorizacion de errores y rendimiento.
- Crear sistema de roles y permisos mas granular para agencias, agentes y colaboradores.

## Pendiente de decision

- Confirmar si se elimina la copia local antigua `captacion-app/`.
- Confirmar estrategia definitiva para empaquetado ZIP.
- Confirmar si GitHub debe trabajar con ramas por funcionalidad o commits directos a `main`.
