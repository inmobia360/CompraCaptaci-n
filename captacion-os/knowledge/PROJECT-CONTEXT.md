# Contexto del proyecto

## Fuente activa

`stable-1.5.1/captacion-app`

## Archivos clave

- `functions.php`: backend WordPress, REST, tablas, ajustes, Mailchimp, Stripe, IA, territorios.
- `template-app-interactiva.php`: SPA principal, Marketplace, Busco captacion, Ofrecer captacion, panel privado.
- `style.css`: cabecera del tema y estilos base.
- `src/data/territorios-espana.json`: datos territoriales.
- `recursos/`: PDFs profesionales.

## Persistencia actual

- Tablas SQL propias.
- `user_meta`.
- Opciones WordPress.
- `localStorage` para demo/preproduccion.

## Prioridad futura

Separar progresivamente logica de negocio en plugin/core y dejar el tema centrado en presentacion.
