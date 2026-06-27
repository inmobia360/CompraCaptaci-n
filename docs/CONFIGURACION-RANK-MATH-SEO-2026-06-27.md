# Configuracion Rank Math SEO

## Plugin descargado

El ZIP oficial de Rank Math SEO se ha descargado en:

`dist/seo-by-rank-math.latest-stable.zip`

El archivo esta dentro de `dist/`, carpeta ignorada por Git.

## Instalacion en WordPress

1. Entrar en `WP Admin > Plugins > Anadir nuevo > Subir plugin`.
2. Subir `dist/seo-by-rank-math.latest-stable.zip`.
3. Activar `Rank Math SEO`.
4. Ejecutar el asistente de configuracion en modo sencillo o avanzado.

## Ajustes recomendados

- Tipo de sitio: `Small Business Site` o `Community Blog`, segun la opcion disponible mas cercana.
- Nombre del sitio: `Compra Captacion`.
- Separador de titulo: `|`.
- Indexacion: permitir indexacion solo en produccion. Mantener `noindex` en staging o demo.
- Sitemap: activar sitemap XML.
- Sitemap de paginas: activado.
- Sitemap de entradas: desactivado si no se usa blog.
- Sitemap de medios: desactivado.
- Redirecciones: activar si se van a cambiar slugs.
- 404 Monitor: activar en staging/produccion para detectar URLs rotas.

## Metadatos aplicados por el tema

El tema aplica metadatos Rank Math al restaurar paginas base desde:

`WP Admin > Captacion.app > Restaurar paginas base`

El proceso actualiza:

- `rank_math_focus_keyword`
- `rank_math_title`
- `rank_math_description`
- `rank_math_pillar_content`

## SEO on-page aplicado

El contenido base de las paginas se ha optimizado para cubrir criterios habituales de Rank Math:

- Keyword principal en titulo SEO, encabezado principal del contenido y primer parrafo.
- Meta description orientada a intencion de busqueda.
- Encabezados H2/H3 alineados con la consulta objetivo.
- Enlaces internos entre paginas principales.
- Textos alternativos descriptivos en imagenes del contenido base.
- FAQs o listas de apoyo en paginas comerciales.
- Mayor profundidad semantica en paginas principales.

Despues de subir el tema actualizado, ejecutar `WP Admin > Captacion.app > Restaurar paginas base` para que WordPress reciba el contenido optimizado y Rank Math actualice los metadatos.

## Paginas cubiertas

El mapa SEO cubre `inicio`, `marketplace`, `buscar-captaciones`, `ofrecer-captacion`, `como-funciona`, `recursos`, `planes`, `contacto`, `area-privada`, `aviso-legal`, `privacidad`, `cookies`, `normas-publicacion`, `condiciones-de-contratacion` y `canal-de-denuncias`.
