# Performance Phase A - 2026-06-29

## Alcance

Primera fase de optimizacion de rendimiento aplicada al tema activo `stable-1.5.1/captacion-app`.

## Cambios realizados

- Se anadio cache por request a `captacion_app_settings()` para evitar lecturas repetidas de opciones en la misma carga.
- Se anadio cache por request a `captacion_app_resource_catalog()` para evitar reconstruir el catalogo y consultar adjuntos varias veces.
- Se retiraron hooks de `init` que verificaban tablas o ejecutaban tareas one-shot en cada carga frontend.
- La preparacion del catalogo territorial queda en `after_switch_theme` y `admin_init`, no en cada carga publica.
- Se desactivo el renderizador PDF antiguo `captacion_app_render_create_pdf_page`, dejando activa la version v2.
- Se redujo Google Fonts a pesos 400, 500, 600 y 700.
- Se agregaron `preconnect` para Google Fonts, Tailwind CDN y Unpkg.
- Se difirio la carga de Leaflet y Leaflet Draw con `defer`.
- Se agregaron `width`, `height` y `decoding` a las imagenes principales para reducir CLS.
- Se recomprimieron assets locales: logo, favicon y placeholders de propiedades.

## Pesos despues de optimizacion

- `logo-compra-captacion.png`: 360x120, 47.4 KB.
- `favicon-compra-captacion.png`: 64x64, 6.1 KB.
- Placeholders JPG: 640x666, entre 38.2 KB y 82 KB aprox.

## Pendientes para Fase B

- Extraer el JS inline a archivos cacheables y encolados con WordPress.
- Sustituir Tailwind CDN por build estatico.
- Sacar el JSON territorial inline y consumirlo desde endpoint/cache.

## Fase B iniciada

- Leaflet y Leaflet Draw ya no se cargan en el `head` ni antes del script principal.
- Los CSS y JS de mapas se cargan bajo demanda al abrir/inicializar un mapa.
- Esto elimina del render inicial los recursos de Leaflet y reduce bloqueo de FCP/TBT sin cambiar el flujo funcional de mapas.
