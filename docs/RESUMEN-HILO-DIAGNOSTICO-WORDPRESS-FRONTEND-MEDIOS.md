# Resumen del hilo: diagnóstico y reparación de Captación.app

## Proyecto

Captación.app es una plataforma inmobiliaria B2B desarrollada como tema de WordPress. Su funcionalidad principal incluye:

- Publicar ofertas de captación inmobiliaria.
- Registrar demandas de compradores o inversores.
- Buscar captaciones por territorio y tipología.
- Marketplace de oportunidades.
- Matching entre oferta y demanda.
- Acceso progresivo a información sensible.
- Notificaciones y trazabilidad de colaboraciones.
- Formularios de contacto, registro, reportes e importación XML.
- Uso de imágenes predeterminadas desde la Biblioteca de Medios de WordPress.
- Mapa territorial y vídeo explicativo en la home.

La URL temporal actualmente revisada es:

`https://inmobia360-com-103379.hostingersite.com/`

La fuente activa del código local es:

`stable-1.5.1/captacion-app`

No incluir en el código, commits ni informes credenciales, contraseñas, cookies, nonces ni tokens de acceso.

## Incidencia visible

En la URL pública se muestra principalmente la home y el diseño visual, pero la aplicación no responde correctamente:

- Los enlaces del menú no cambian de sección.
- Los botones parecen congelados.
- No se inicializan correctamente las funciones de la aplicación.
- El mapa no se visualiza.
- El vídeo de cabecera no aparece.
- Algunas imágenes predeterminadas muestran el icono de imagen rota.
- La home muestra estados como `Cargando...` sin completar los datos.

El problema se reproduce especialmente en una ventana de incógnito, aunque también afecta a la sesión pública normal.

## Diagnóstico confirmado

El fallo principal no es de CSS ni de la maquetación HTML. El script principal de negocio está siendo bloqueado por Complianz.

En el DOM de producción se observó que el script que contiene el router, los formularios, el mapa, la lógica de imágenes y la interacción aparece así:

```html
<script type="text/plain"
        data-service="openstreetmaps"
        data-category="marketing">
  /* lógica completa de la aplicación */
</script>
```

Al tener `type="text/plain"`, el navegador no ejecuta el contenido. Complianz lo está tratando como un recurso de marketing relacionado con OpenStreetMap y lo deja bloqueado hasta obtener consentimiento.

Por eso el HTML estático se ve, pero no se ejecuta el JavaScript que activa la aplicación.

## Errores JavaScript observados

La consola del navegador mostró, entre otros, estos errores:

```text
ReferenceError: setResourceCategory is not defined
ReferenceError: openProfessionalSubscriptionModal is not defined
```

Estas funciones sí existen en el código fuente del tema, pero no existen en `window` cuando se pulsan los elementos porque el script principal nunca llegó a ejecutarse.

Localización comprobada en el código:

- `setResourceCategory()` está definida en `template-app-interactiva.php` alrededor de la línea 9259.
- `openProfessionalSubscriptionModal()` está definida en `template-app-interactiva.php` alrededor de la línea 5583.

Esto confirma que los errores son consecuencia de la carga bloqueada del script, no de que las funciones hayan sido eliminadas del código.

## Recursos multimedia observados

### Logo

El logo sí se está resolviendo desde la Biblioteca de Medios:

```text
/wp-content/uploads/2026/06/Logo-Compra-Captacion_transparente.png
```

### Imagen predeterminada rota

Una imagen se estaba solicitando con esta URL:

```text
/wp-content/uploads/2026/06/Piso-imagen-predeterminada.png
```

La imagen no cargó correctamente (`naturalWidth: 0`). Debe comprobarse el nombre real del adjunto en WordPress y resolverlo mediante la función de búsqueda de medios, no mediante una URL fija.

### Vídeo

En el código local, el vídeo se define en `template-app-interactiva.php` y se resuelve mediante `captacion_app_media_url()`:

- `media/video-explicativo-captacion-app.mp4`
- Alias previsto: `videowebdecaptacion.mp4`
- Poster: `media/poster-video-captacion-app.webp`

En la página pública inspeccionada no había elementos `<video>` presentes en el DOM final. Esto debe revisarse después de reactivar el script principal y comprobar que la URL del vídeo procede de un adjunto válido de WordPress.

### Mapa

El código usa Leaflet y Leaflet Draw desde `unpkg.com` y las teselas de OpenStreetMap. El mapa depende de funciones que están dentro del mismo script bloqueado, por lo que no se puede separar el fallo del mapa de la falta de ejecución de la aplicación.

## Archivos principales afectados

### Código funcional

`stable-1.5.1/captacion-app/template-app-interactiva.php`

Contiene:

- Router basado en hash.
- Enlaces y botones de navegación.
- Formularios de oferta y demanda.
- Matching.
- Mapa Leaflet.
- Carga del vídeo.
- Preview de imágenes predeterminadas.
- Funciones `setResourceCategory()` y `openProfessionalSubscriptionModal()`.

`stable-1.5.1/captacion-app/functions.php`

Contiene:

- Resolución de archivos multimedia de WordPress.
- Alias de nombres de imágenes y vídeo.
- Función `captacion_app_media_url()`.
- Búsqueda de adjuntos mediante `_wp_attached_file`, título, slug y URL.
- Endpoints REST.
- Gestión de usuarios, registros, XML y notificaciones.

## Corrección recomendada

### 1. Evitar que Complianz bloquee el script funcional

El bloque principal de lógica de negocio debe marcarse explícitamente como necesario y no bloqueable por consentimiento. La corrección mínima recomendada es añadir el atributo de exclusión de Complianz al script principal:

```html
<script data-cmplz="ignore">
  /* lógica de la aplicación */
</script>
```

No debe clasificarse el script completo como `marketing`. El código de la aplicación es funcional y necesario para prestar el servicio solicitado.

El mapa y las teselas de OpenStreetMap pueden documentarse en la política de cookies y gestionarse como recurso técnico, pero no se debe bloquear todo el router por contener una URL de OpenStreetMap.

### 2. Revisar la configuración de Complianz

En el panel de Complianz hay que comprobar que:

- El script principal del tema no está registrado como servicio de marketing.
- No existe una regla automática que transforme cualquier script con `openstreetmap` en `type="text/plain"`.
- Leaflet y el mapa se consideran recursos técnicos necesarios o se cargan de forma independiente.
- La carga de recursos opcionales sigue respetando el consentimiento.

### 3. Mantener la resolución de medios por adjuntos

`captacion_app_media_url()` debe seguir siendo la fuente principal para logo, favicon, vídeo, poster e imágenes predeterminadas.

El orden esperado es:

1. Buscar el adjunto por nombre de archivo.
2. Buscar por nombre sanitizado.
3. Buscar por slug.
4. Buscar por título del adjunto.
5. Usar los alias definidos para los nombres reales de la Biblioteca de Medios.
6. Si no existe, mostrar un fallback visual seguro y no una imagen rota.

No se deben dejar como fuente principal rutas antiguas del tipo:

```text
/wp-content/themes/captacion-app/media/...
```

cuando exista un adjunto equivalente en:

```text
/wp-content/uploads/...
```

### 4. Validar las imágenes predeterminadas

Comprobar en la Biblioteca de Medios que existan y que sus nombres se resuelvan correctamente:

- Piso imagen predeterminada.
- Casa Chalet imagen predeterminada.
- Comercial imagen predeterminada.
- Edificio imagen predeterminada.
- Nave imagen predeterminada.
- Oficina imagen predeterminada.
- Terreno imagen predeterminada.

Cada tipo de inmueble debe apuntar a su imagen correspondiente.

### 5. Validar el vídeo

Después de corregir la ejecución del script:

- Confirmar que el elemento `<video>` aparece en la home.
- Confirmar que el `src` apunta a la URL real del adjunto de WordPress.
- Mantener `autoplay`, `muted`, `loop` y `playsinline`.
- Mantener controles manuales o una opción visible para activar el audio.
- Confirmar que el poster también carga desde la Biblioteca de Medios.
- Probar Chrome, móvil y ventana de incógnito.

## Pruebas obligatorias después del cambio

### JavaScript

- La consola no debe mostrar `setResourceCategory is not defined`.
- La consola no debe mostrar `openProfessionalSubscriptionModal is not defined`.
- El script principal debe aparecer como JavaScript ejecutable, no como `text/plain`.
- El router debe cambiar entre `#/inicio`, `#/buscar-captaciones`, `#/ofrecer-captacion`, `#/marketplace`, `#/recursos` y `#/contacto`.

### Navegación y botones

- Menú superior.
- Botón `Comenzar`.
- Botón `Acceder`.
- Botón `Publicar captación`.
- Botones de registro.
- Filtros de recursos.
- Botón del mapa.
- Carruseles.

### Medios

- Logo transparente.
- Favicon.
- Vídeo de cabecera.
- Poster del vídeo.
- Imagen de Piso.
- Imagen de Casa/Chalet.
- Imagen Comercial.
- Imagen de Edificio.
- Imagen de Nave.
- Imagen de Oficina.
- Imagen de Terreno.

### Mapa

- El mapa aparece visualmente.
- Leaflet carga sin errores.
- Las teselas de OpenStreetMap cargan.
- Los filtros `Todas`, `Captaciones` y `Demandas` funcionan.
- La búsqueda por código postal funciona.
- Dibujar y limpiar zona funcionan.

### Responsive

- Escritorio.
- Móvil.
- Ventana de incógnito.
- Usuario no autenticado.
- Usuario autenticado.

## Importación XML y marketplace

En revisiones anteriores se detectó además un problema separado en el importador XML:

- El registro se crea, pero algunos campos pueden quedar incompletos.
- Precio, habitaciones, baños, superficie, municipio, código postal e imágenes deben conservarse en el payload.
- Las imágenes deben leerse primero desde `payload.images` y solo después intentar resolver adjuntos.
- No se debe sobrescribir un payload completo con una actualización vacía.

El importador debe mapear como mínimo:

| XML Kyero | Campo interno |
|---|---|
| `price` | `price` |
| `beds` | `rooms` |
| `baths` | `bathrooms` |
| `town` | `municipality` |
| `province` | `province` |
| `postcode` | `postal_code` |
| `url` de imagen | `images` / `image` |

Este problema XML es independiente del bloqueo de Complianz, aunque ambos afectan a la visualización final del marketplace.

## Criterio de aceptación

La reparación se considera correcta cuando:

1. La home sigue siendo visible sin consentimiento de marketing.
2. El router y todos los enlaces funcionan en incógnito.
3. No aparecen errores JavaScript de funciones inexistentes.
4. El mapa se muestra y responde.
5. El vídeo se muestra y se reproduce sin audio por defecto.
6. El usuario puede activar el audio y usar los controles.
7. Las imágenes predeterminadas cargan desde la Biblioteca de Medios.
8. Los formularios se pueden abrir y enviar según sus permisos.
9. El marketplace muestra correctamente los datos importados.
10. La corrección no expone credenciales ni datos sensibles.

## Nota de despliegue

El ZIP instalable debe contener directamente:

```text
captacion-app/
  style.css
  functions.php
  template-app-interactiva.php
  ...
```

WordPress debe instalarlo como actualización del tema activo `captacion-app`. No se debe subir un ZIP que contenga una carpeta intermedia adicional, porque WordPress mostrará el error de que falta `style.css`.

Antes de desplegar:

```powershell
git status --short --branch
git diff --check
```

Después del despliegue hay que purgar la caché de LiteSpeed/Hostinger y verificar la web en una ventana de incógnito.
