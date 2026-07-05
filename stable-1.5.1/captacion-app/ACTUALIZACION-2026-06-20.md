# Actualizacion Compra Captación 1.1.0

Base utilizada: `captacion-app-ine-2026-wordpress-corregido.zip` del 19/06/2026.
El archivo de referencia original no se modifica.

## Territorios

- `wp_captacion_territories` usa una fila por municipio y columnas explicitas para CCAA, provincia y municipio INE.
- `wp_captacion_territory_postal_codes` admite varios codigos postales por municipio.
- El JSON incluido contiene 19 CCAA, 52 provincias y 8.132 municipios.
- `26codmun.xlsx` no contiene codigos postales: se guardan como `NULL` y nunca se inventan.
- La base de datos es la fuente principal; el JSON queda como fallback local.
- Nuevos endpoints para provincias, municipios, validacion de direccion e importacion administrativa.
- Importacion protegida con `manage_options` y nonce. CartoCiudad aplica rate limiting.

## Registro profesional

- Alta inicial reducida a nombre, correo, telefono, contrasena y privacidad.
- Alta conectada a usuarios nativos de WordPress mediante `POST /captacion/v1/register`.
- Email unico, contrasena de 8 caracteres y telefono internacional validados en frontend y backend.
- Agencia y territorio se completan despues en Perfil profesional.
- Fallback local marcado con `TODO/FIXME PREPRODUCCION` cuando el endpoint no esta disponible.

## Suscripcion de visitantes

- Popup a los 60 segundos solo para visitantes no registrados.
- Cierre persistido en `sessionStorage` durante la sesion.
- Ultimo aviso exit-intent una sola vez por sesion.
- Desktop usa salida por la parte superior; mobile usa scroll e inactividad.

## Contacto y mapa

- Contacto elimina agencia y anade telefono y preferencia de contacto.
- Llamada o WhatsApp exige telefono en frontend y backend.
- Contacto usa `POST /captacion/v1/contact` y mantiene el registro/notificacion por correo.
- El boton de cobertura usa `scrollIntoView` sobre `#mapa-cobertura` sin interferir con el router hash.

## Verificaciones

- JSON territorial: 19 CCAA, 52 provincias, 8.132 municipios y 0 CP.
- Seis bloques JavaScript embebidos: sintaxis valida mediante `vm.Script`.
- Llaves y parentesis equilibrados en `functions.php` y `template-app-interactiva.php`.
- No se ejecuto `php -l` porque el entorno local no incluye PHP CLI.
