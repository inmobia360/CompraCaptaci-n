# Complianz — configuración inicial de Captacion.app

Esta guía forma parte de la versión 1.4.3. Complianz debe ser la única fuente de consentimiento, banner, preferencias, bloqueo preventivo y declaración de cookies.

## Wizard

- Región: Unión Europea / España.
- Marcos: RGPD y LOPDGDD como complemento nacional.
- Tipo: plataforma inmobiliaria B2B para profesionales, con registro/login, formularios, marketplace privado y recursos descargables.
- Categorías: necesarias siempre activas; preferencias cuando proceda; estadísticas y marketing desactivadas hasta consentimiento.
- Activar bloqueo automático de scripts y escaneo periódico de cookies.
- Activar WP Consent API cuando esté disponible.
- No activar Google Consent Mode mientras no existan IDs reales de Analytics, Ads o GTM.
- No añadir Analytics, GTM, Meta Pixel, Hotjar ni Clarity: no están configurados en el tema.

## Datos del sitio

- Proyecto: Captacion.app.
- Staging: https://lightblue-salamander-627943.hostingersite.com
- Dominio final: PENDIENTE.
- País: España.
- Descripción: plataforma inmobiliaria B2B para captaciones, demandas activas, colaboración profesional, marketplace, trazabilidad y recursos documentales.

Datos provisionales exclusivos de staging:

- Titular: EMPRESA PENDIENTE DE DEFINIR, S.L.
- NIF/CIF: B00000000.
- Domicilio: Domicilio social pendiente de completar.
- Privacidad: privacidad@captacion.app.
- Contacto: contacto@captacion.app.
- Teléfono: PENDIENTE.
- DPO: PENDIENTE / no designado salvo confirmación.

**TODO LEGAL — sustituir todos estos datos antes de producción.**

## Inventario y servicios a revisar

- WordPress y usuarios nativos: necesarios.
- Complianz: gestión de consentimiento.
- localStorage de preproducción: sesión demo, tema y datos operativos temporales; nunca consentimiento legal. Migrar la operativa a backend antes de producción.
- Leaflet/Leaflet Draw y teselas de OpenStreetMap: mapa técnico solicitado por el usuario; documentar proveedor, transferencias y base jurídica. Valorar autoalojar librerías.
- Google Fonts, Tailwind y librerías Leaflet desde CDN: inventariar y valorar autoalojamiento.
- Mailchimp: solo tras checkbox comercial separado y opcional. Double opt-in recomendado.
- Stripe: enlaces de pago previstos/configurables; sin scripts de seguimiento en el tema.
- Google Calendar, WhatsApp e IA BYO-AI: futuros/TODO; reevaluar antes de activar.

## Publicación y caché

1. Completar y publicar el wizard y la declaración de cookies de Complianz.
2. Ejecutar el escáner después de navegar por inicio, marketplace, mapas, login, registro, contacto, recursos y área privada.
3. Comprobar en incógnito que solo aparece el banner de Complianz y que estadísticas/marketing permanecen bloqueados antes del consentimiento.
4. Excluir los scripts esenciales de Complianz de retraso, combinación o minificación si el optimizador los altera.
5. Vaciar caché de WordPress, Hostinger y CDN después del despliegue.
6. Verificar que `/cookies/` muestra el shortcode `[cmplz-document type="cookie-statement" region="eu"]` y que “Configurar cookies” abre las preferencias.

