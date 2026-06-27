# Correccion registro nonce - 2026-06-27

## Cambios

- Se elimina el texto tecnico visible bajo el formulario de alta inline.
- Los formularios publicos dejan de enviar `X-WP-Nonce` cacheado en registro, login, reenvio de verificacion, contacto, Mailchimp/notificaciones y validacion publica de direccion.
- Los endpoints publicos aceptan peticiones same-origin cuando no hay nonce valido, manteniendo rate limit propio.
- Los endpoints privados siguen exigiendo `X-WP-Nonce` valido, sesion autenticada y correo verificado.

## Motivo

En staging aparecia el error `Ha fallado la comprobacion de la cookie` al registrar usuario. La causa probable es una combinacion de pagina cacheada y nonce REST caducado para formularios publicos.

## Seguridad

- No se relajan endpoints privados.
- Las acciones privadas siguen requiriendo sesion real y nonce REST valido.
- Los formularios publicos mantienen validacion, rate limit y comprobacion same-origin.
