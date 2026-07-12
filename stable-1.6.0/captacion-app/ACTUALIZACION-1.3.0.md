# Compra Captación 1.3.0

Fecha: 20 de junio de 2026.

## Planes y accesos

- Plan Basico: 0 accesos incluidos y acceso individual de 10 EUR.
- Los cupos de Professional Plus y Premium fueron actualizados posteriormente.
- Consultar `ACTUALIZACION-1.3.1.md` para los valores vigentes.
- El saldo, el consumo y los desbloqueos se controlan en servidor mediante metadatos de usuario.
- La tabla `wp_captacion_marketplace_access_log` registra usuario, oportunidad, plan, tipo de acceso, importe y fecha.
- Una oportunidad previamente desbloqueada no vuelve a consumir saldo.

## Autenticacion

- El boton Acceder abre login y registro profesional.
- Login y registro utilizan endpoints WordPress con nonce, rate limit y mensajes de error diferenciados.
- El telefono es opcional; la contrasena exige al menos 8 caracteres.
- La tarjeta de suscripcion de portada contiene el formulario compacto completo.
- El fallback local queda identificado exclusivamente como preproduccion.

## Calendario Premium

- Formulario para crear tareas con fecha, hora, descripcion, relacion, recordatorio y canal.
- Las tareas se guardan por usuario en WordPress y se muestran en calendario/listado.
- Exportacion ICS disponible.
- Google Calendar OAuth y envios externos quedan marcados como TODO hasta configurar infraestructura real.

## Seguridad y pagos

- Los endpoints de registros, creditos y tareas requieren usuario autenticado, nonce y capacidad `read`.
- Los listados privados se limitan al `user_id` actual salvo administradores.
- El frontend no puede conceder planes ni creditos.
- Los Payment Links solo crean intenciones; las funciones `captacion_app_set_user_plan_from_webhook`, `captacion_app_grant_marketplace_accesses` y `captacion_app_confirm_single_marketplace_access` son los puntos de integracion para webhooks confirmados.
