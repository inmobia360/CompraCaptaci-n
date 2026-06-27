# Implementacion persistencia SaaS MVP - 2026-06-27

Fuente modificada:

```text
stable-1.5.1/captacion-app/template-app-interactiva.php
```

## Cambio aplicado

- Se añade un adaptador frontend para leer registros WordPress desde `GET /wp-json/captacion/v1/records`.
- Se cargan captaciones (`property`) y demandas (`need`) guardadas en WordPress cuando existe sesion autenticada.
- Los registros de servidor se mezclan por `id` con la vista local, priorizando la version server-side.
- Las publicaciones de captacion y demanda siguen actualizando la UI al instante y muestran aviso si la sincronizacion WordPress falla.
- El fallback local de preproduccion se mantiene para no romper la experiencia si el endpoint no esta disponible.

## Alcance

- No se crean tablas nuevas.
- No se cambian reglas de acceso.
- No se modifica Stripe, Mailchimp, login, registro ni marketplace-access.
- No se elimina `localStorage`; queda como cache/fallback temporal.

## Validacion local

- `git diff --check` ejecutado sin errores.
- `php -v` no disponible en este entorno, por lo que no se pudo ejecutar `php -l`.
