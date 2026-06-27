# Validacion staging - 2026-06-27

Entorno validado:

```text
https://white-rabbit-626143.hostingersite.com/
```

## Resultado

- WordPress responde correctamente.
- El tema activo es `Captacion.app`.
- Version del tema detectada: `1.5.3`.
- La portada carga con titulo SEO de Captacion.app.
- El HTML de portada contiene la configuracion REST (`recordsEndpoint`).
- El HTML de portada contiene la carga de persistencia WordPress (`loadWordPressRealEstateRecords`).
- El endpoint publico de territorios responde.
- Los endpoints privados (`records`, `marketplace-access`, `tasks`) devuelven `403` fuera de una sesion web con nonce, comportamiento coherente con proteccion por usuario autenticado.

## Pendiente de validar en navegador autenticado

- Registro profesional desde la interfaz.
- Login/logout desde la interfaz.
- Publicar captacion y confirmar que aparece tras recargar.
- Publicar demanda y confirmar que aparece tras recargar.
- Acceso al panel privado.
- Marketplace access y consumo de accesos.
- Recursos protegidos.
- Contacto y reportes.

## Observaciones

- La Application Password permite consultar datos administrativos basicos de WordPress.
- Los endpoints privados del tema requieren nonce REST de sesion, no solo Basic Auth.
- No se detecto necesidad de modificar codigo para esta instalacion inicial.
