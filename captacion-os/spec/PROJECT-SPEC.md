# Especificacion de Captacion.app

## Producto

Captacion.app es una plataforma inmobiliaria B2B para profesionales que permite:

- publicar captaciones;
- publicar demandas activas;
- cruzar oferta y demanda;
- colaborar con trazabilidad;
- gestionar accesos y membresias;
- usar recursos profesionales;
- recibir notificaciones;
- operar desde panel privado.

## Arquitectura actual

- WordPress Theme: `stable-1.5.1/captacion-app`.
- Backend: `functions.php`.
- Frontend SPA: `template-app-interactiva.php`.
- Estilos: `style.css` y CSS embebido.
- Datos territoriales: `src/data/territorios-espana.json`.
- Persistencia mixta: tablas SQL, `user_meta`, opciones WP y `localStorage`.

## Integraciones

- Rank Math SEO.
- Complianz.
- Mailchimp.
- Stripe Payment Links.
- Leaflet / OpenStreetMap.
- IA BYO-AI por usuario.
- Hostinger / WordPress.

## Reglas de producto

- Usuarios no registrados solo visualizan informacion publica.
- Datos sensibles no se muestran publicamente.
- Acciones operativas requieren usuario registrado.
- Matches oferta-demanda respetan criterios territoriales, presupuesto, superficie, habitaciones y banos.
