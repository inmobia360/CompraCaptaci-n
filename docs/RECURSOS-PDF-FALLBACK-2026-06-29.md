# Recursos PDF fallback - 2026-06-29

## Problema

La seccion de herramientas/recursos dependia de PDFs estaticos configurados por URL. Varias plantillas apuntaban a un dominio anterior y solo dos PDFs existian fisicamente dentro del tema.

Ademas, la personalizacion de PDF estaba limitada a Professional/Premium aunque los recursos estuvieran marcados como `basic`.

## Cambios

- Los usuarios verificados pueden personalizar PDFs de cualquier recurso incluido en su plan.
- Las descargas de plantilla usan `admin-post.php?action=captacion_resource_template_pdf_download`.
- Si existe PDF local en el tema, se sirve ese archivo.
- Si no existe PDF local, se genera una plantilla base en PDF al vuelo.
- Si el registro de un PDF generado falla, la API devuelve un error claro en vez de entregar un enlace invalido.

## Notas

- Los PDFs locales actuales son `nda` y `collaboration`.
- El resto de recursos usa fallback generado automaticamente hasta que se suban plantillas finales.
