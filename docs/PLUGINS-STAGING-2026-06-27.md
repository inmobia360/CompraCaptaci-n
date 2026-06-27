# Plugins staging - 2026-06-27

Entorno:

```text
https://white-rabbit-626143.hostingersite.com/
```

## Plugins instalados y activos

- `WP Mail SMTP` 4.9.0: correo transaccional para verificacion, recuperacion, contacto y notificaciones.
- `Wordfence Security` 8.2.2: firewall, escaneo y proteccion de accesos.
- `Complianz | GDPR/CCPA Cookie Consent` 7.5.0: consentimiento cookies y RGPD.
- `Rank Math SEO` 1.0.272: SEO tecnico, metas y sitemap.
- `UpdraftPlus - Backup/Restore` 1.26.5: copias de seguridad.

## Plugins activos previos

- `Hostinger Tools` 3.0.70.
- `Hostinger Reach` 1.5.4.
- `LiteSpeed Cache` 7.8.1.

## Plugin desactivado

- `Hostinger Easy Onboarding` 2.1.28.

Motivo: es un plugin de onboarding inicial de Hostinger y no es necesario para la operativa del SaaS. Puede interferir con configuracion de tema o asistentes iniciales en staging.

## Incidencia detectada

Tras la instalacion de plugins, el tema activo detectado por API paso a ser `hostinger-ai-theme` y `Captacion.app` quedo instalado pero inactivo.

Estado detectado:

```text
captacion-app | inactive | Captacion.app | 1.5.3
hostinger-ai-theme | active | Tema de Hostinger con IA | 2.0.27
```

No se pudo reactivar el tema por REST API porque WordPress no expone activacion de temas en el endpoint estandar. El acceso programatico a `wp-admin` redirige a login y no permite completar la activacion desde consola.

## Accion manual requerida

Entrar en WordPress y reactivar:

```text
Apariencia > Temas > Captacion.app > Activar
```

Despues de reactivar, validar:

- Portada Captacion.app.
- Menu principal.
- Registro/login profesional.
- Captaciones y demandas.
- Panel privado.

## Configuracion pendiente

- `WP Mail SMTP`: configurar proveedor SMTP real y enviar email de prueba.
- `Wordfence`: completar asistente, activar firewall en modo learning y revisar alertas.
- `Complianz`: completar asistente RGPD/cookies y revisar textos legales.
- `Rank Math`: configurar titulo, descripcion, sitemap y evitar que el staging indexe.
- `UpdraftPlus`: configurar destino externo de backup y programacion.
- `LiteSpeed Cache`: purgar cache tras reactivar Captacion.app y excluir endpoints REST privados si fuese necesario.
