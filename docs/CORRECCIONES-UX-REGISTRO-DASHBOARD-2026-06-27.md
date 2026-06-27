# Correcciones UX registro y dashboard - 2026-06-27

Fuente modificada:

```text
stable-1.5.1/captacion-app/template-app-interactiva.php
```

## Cambios aplicados

- Registro profesional con campo de pais separado del numero de contacto.
- Pais por defecto: Espana `+34`.
- Selector con codigos frecuentes de Europa, America y Marruecos.
- El frontend compone el telefono final en formato internacional antes de enviarlo al backend.
- Boton mostrar/ocultar contrasena en registro inline, modal de registro y login profesional.
- Checkboxes legales con mayor area tactil, borde, padding y tamano de control.
- Las captaciones nuevas guardan `userEmail` del usuario autenticado.
- Los registros cargados desde WordPress recuperan `user_email` como `userEmail` en frontend.
- El dashboard privado filtra captaciones y demandas para mostrar solo publicaciones del usuario actual.

## Alcance

- No se modifica la validacion backend de telefono.
- No se cambia login, verificacion de correo ni Stripe.
- No se elimina la vista publica del marketplace.
- El marketplace publico puede seguir mostrando oportunidades generales.
- El panel privado queda orientado a datos propios del usuario.

## Pendiente posterior

- Crear flujo propio de recuperacion de contrasena dentro de Captacion.app.
- Sustituir pantallas WordPress residuales de verificacion/restablecimiento por vistas propias de la app.
- Validar en navegador autenticado despues de reactivar el tema `Captacion.app` en staging.
