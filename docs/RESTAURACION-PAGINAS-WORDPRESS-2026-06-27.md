# Restauracion de paginas WordPress

## Contexto

Las paginas publicas de Captacion.app se guardan en la base de datos de WordPress. El tema incluye un generador para crear la estructura base cuando una instalacion no contiene esas paginas.

## Cambio aplicado

El boton de administracion de Captacion.app crea las paginas faltantes y actualiza las existentes con el contenido base incluido en el tema. Este comportamiento permite devolver la estructura publica a la version base anterior.

## Uso operativo

Ruta en WordPress:

`WP Admin > Captacion.app > Restaurar paginas base`

Este proceso puede recrear o restaurar paginas como `marketplace`, `buscar-captaciones`, `ofrecer-captacion`, `como-funciona`, `recursos`, `planes`, `contacto`, `area-privada` o paginas legales.

## Limitaciones

El proceso sobrescribe el titulo, el contenido y los metadatos SEO de Rank Math de las paginas gestionadas por el tema. Antes de ejecutarlo en produccion, confirmar que no hay ediciones manuales que deban conservarse.
