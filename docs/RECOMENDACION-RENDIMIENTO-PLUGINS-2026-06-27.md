# Recomendacion de rendimiento y plugins

## Contexto

El entorno objetivo usa Hostinger y ya se ha detectado `LiteSpeed Cache` en staging. La web incluye imagenes locales del tema y un video MP4 local en `media/video-explicativo-captacion-app.mp4`.

Pesos locales relevantes:

- `video-explicativo-captacion-app.mp4`: 11,36 MB.
- `logo-compra-captacion.png`: 1,09 MB.
- Imagenes inmobiliarias por defecto: entre 0,37 MB y 0,84 MB.

## Stack recomendado

### 1. Latencia y cache: LiteSpeed Cache

Recomendado como plugin principal porque Hostinger suele trabajar sobre LiteSpeed y el plugin ofrece cache de servidor, optimizacion CSS/JS, lazy load, WebP/AVIF, cache de navegador, crawler, object cache si Redis/Memcached esta disponible y limpieza de base de datos.

Ajustes recomendados:

- Activar cache publica.
- Activar cache de navegador.
- Activar lazy load de imagenes e iframes.
- Activar optimizacion de imagenes si QUIC.cloud queda correctamente conectado.
- Minificar CSS/JS con pruebas visuales despues de cada cambio.
- Evitar combinar JS agresivamente si rompe mapas, formularios, login o panel privado.
- Excluir de cache flujos privados, endpoints REST sensibles y paginas de cuenta si aparece contenido cacheado incorrectamente.

### 2. Imagenes: elegir una sola solucion

No instalar varios optimizadores de imagen a la vez. Pueden duplicar WebP, lazy load, reescrituras y reglas `.htaccess`.

Opcion recomendada si se usa LiteSpeed completo:

- Usar `LiteSpeed Cache > Image Optimization` con WebP/AVIF y lazy load.

Opcion alternativa si no se quiere depender de QUIC.cloud:

- `EWWW Image Optimizer`: buena opcion para compresion local o via API, WebP, redimensionado al subir, bulk optimize y lazy load. Adecuado si se busca control y optimizacion sin cambiar demasiado el flujo de WordPress.

Opcion alternativa simple solo para WebP/AVIF:

- `Converter for Media`: convierte imagenes a WebP/AVIF, conserva originales y sirve formatos modernos. Es simple, pero puede requerir validar reglas de servidor en Hostinger.

Opcion CDN gestionado:

- `Optimole`: optimiza, redimensiona por dispositivo y sirve desde CDN. Interesante si el trafico crece y se quiere descargar trabajo del hosting, pero implica servicio externo y limite por visitas en plan gratuito.

## Video

El video local de 11,36 MB no deberia cargarse de forma agresiva. En el tema se ha cambiado la carga a `preload="metadata"` y reproduccion diferida por viewport.

Opciones recomendadas:

- Si el video es propio y se quiere mantener control: `Presto Player`, preferiblemente con Bunny.net o HLS adaptativo en fase avanzada.
- Si el video se publica en YouTube: `WP YouTube Lyte`, porque carga un embed ligero y solo llama al reproductor pesado al hacer clic.
- Si se mantiene self-hosted: generar version `.webm`, comprimir el MP4 con HandBrake y crear poster WebP ligero.

## Decision propuesta para Captacion.app

Usar esta combinacion inicial:

- Mantener `LiteSpeed Cache` como plugin principal de rendimiento.
- No instalar otro plugin de cache.
- Usar la optimizacion de imagenes de LiteSpeed si QUIC.cloud queda operativo.
- Si QUIC.cloud no queda operativo o no conviene, instalar `EWWW Image Optimizer` como unica solucion de imagenes.
- Para video, mantener la carga diferida actual y evaluar `Presto Player` si se van a usar mas videos propios.

## Validacion despues de activar plugins

- Probar portada, marketplace, formularios, login, registro y panel privado.
- Purgar cache despues de subir tema o restaurar paginas base.
- Medir en PageSpeed/Lighthouse antes y despues.
- Revisar que no se rompan mapas Leaflet, Rank Math, Complianz ni formularios REST.
