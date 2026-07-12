# Compra Captación 1.4.3 — Complianz, RGPD y consentimiento

## Cambios funcionales

- Eliminados el banner y el modal propios de cookies.
- Eliminada la persistencia de consentimiento propio en localStorage y WordPress.
- Las claves heredadas `captacion_cookie_preferences_v1` y `captacion_cookies_v3_accepted` se borran al iniciar.
- `captacionOpenCookiePreferences()` abre el panel de Complianz con fallbacks compatibles.
- `openCookieSettings()` permanece únicamente como alias hacia Complianz.
- La ruta SPA de cookies y la página WordPress `/cookies/` incorporan la declaración dinámica de Complianz.
- El pop-up de alta se pospone mientras el banner de Complianz está visible.
- Complianz tiene prioridad de z-index sobre los modales del tema.

## Formularios y marketing

- Registro modal y registro inline mantienen privacidad obligatoria y añaden consentimiento comercial separado, opcional y revocable.
- Contacto mantiene privacidad obligatoria y añade consentimiento comercial separado.
- Mailchimp rechaza peticiones sin consentimiento comercial explícito.
- Las sincronizaciones operativas con Mailchimp se omiten cuando el usuario no ha otorgado ese consentimiento.
- Login, publicación, contacto, marketplace, recursos, mapas y dashboard conservan sus flujos existentes.

## Inventario auditado

- No existen Google Analytics, Google Tag Manager, Meta Pixel, Hotjar, Microsoft Clarity, reCAPTCHA, iframes de YouTube/Vimeo ni IDs reales de seguimiento en el tema.
- Leaflet, Leaflet Draw, OpenStreetMap, Google Fonts y Tailwind se cargan como recursos externos del flujo actual. Deben inventariarse mediante Complianz y revisarse para autoalojamiento antes de producción.
- Stripe se limita a enlaces configurables; no se añadió seguimiento.
- localStorage sigue usándose de forma técnica/demo para sesión y estado operativo de preproducción. No actúa como consentimiento legal.

## Pendientes de despliegue

- Completar el wizard según `COMPLIANZ-CONFIGURACION.md`.
- Sustituir todos los datos marcados `TODO LEGAL`.
- Ejecutar el escáner y comprobar el bloqueo previo en incógnito.
- Vaciar cachés de WordPress, Hostinger y CDN.
- Revisar que el optimizador no retrase ni combine incorrectamente los scripts de Complianz.

## Validaciones realizadas

- Sintaxis de los ocho bloques JavaScript embebidos: correcta.
- Búsqueda de referencias al banner/modal y funciones de consentimiento propias: sin referencias activas.
- Búsqueda de rastreadores conocidos: sin implementaciones activas.
- PHP CLI no estaba disponible en el entorno local; se verificaron el equilibrio de bloques PHP y las modificaciones por inspección estática. Debe ejecutarse `php -l` en staging o CI antes de producción.

