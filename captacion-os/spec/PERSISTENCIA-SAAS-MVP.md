# Spec: persistencia SaaS MVP

## Contexto

Fuente activa:

```text
stable-1.5.1/captacion-app
```

La base actual ya incluye infraestructura WordPress para persistencia:

- Tabla generica `wp_captacion_app_records`.
- Tipos permitidos: `property`, `need`, `smart_match`, `report`, `notification`, `access_request`, `activity`, `user_preferences`, `dashboard_state`, `task`, `generated_pdf`.
- Endpoint privado `POST /wp-json/captacion/v1/records`.
- Endpoint privado `GET /wp-json/captacion/v1/records`.
- Sanitizacion especifica para `property` y `need` antes de guardar.
- Restriccion por `user_id` para usuarios no administradores.

Por tanto, la primera migracion SaaS no debe crear tablas nuevas para captaciones y demandas salvo que se demuestre una limitacion real de rendimiento o consulta.

## Objetivo

Sustituir progresivamente persistencia demo basada en `localStorage` por persistencia WordPress server-side, manteniendo el comportamiento publico/privado actual.

## Flujos afectados

### Captaciones

Estado actual:

- Array frontend `properties`.
- Clave local `captacion_properties_v3`.
- Datos demo iniciales en navegador.

Destino MVP:

- `record_type = property`.
- `record_key = id` estable de la captacion.
- `payload` con datos sanitizados.
- `status` para distinguir borrador, pendiente, activa, baja o rechazada.

### Demandas

Estado actual:

- Array frontend `needs`.
- Clave local `captacion_needs_v3`.
- Datos demo iniciales en navegador.

Destino MVP:

- `record_type = need`.
- `record_key = id` estable de la demanda.
- `payload` con criterios sanitizados.
- `status` para distinguir borrador, pendiente, activa, cerrada o caducada.

### Favoritos

Estado actual:

- Listas locales por tipo mediante `localStorage`.

Destino MVP:

- `record_type = user_preferences`.
- `record_key = favorites:{user_id}`.
- `payload` con IDs favoritos separados por tipo.

### Notificaciones

Estado actual:

- Parte de la experiencia se renderiza desde estado frontend.

Destino MVP:

- `record_type = notification`.
- `related_id` con captacion, demanda, match o reporte relacionado.
- `status` para pendiente, leida o archivada.

### Tareas y agenda

Estado actual:

- Ya existe endpoint dedicado `/captacion/v1/tasks` apoyado en `record_type = task`.

Destino MVP:

- Mantener endpoint actual.
- No duplicar tabla.

### XML privado

Estado actual:

- Feeds y URL se guardan localmente.
- Existen avisos de proxy publico de demostracion.

Destino MVP:

- `record_type = user_preferences` o `dashboard_state` para configuracion de feeds.
- Descarga XML siempre mediante proxy servidor propio, nunca proxy publico demo en produccion.

## Reglas de implementacion

- Mantener `localStorage` solo como cache temporal o fallback identificado de preproduccion.
- Nunca usar `localStorage` como fuente final de produccion para captaciones, demandas, accesos, favoritos, tareas o notificaciones.
- Cualquier escritura real debe requerir usuario autenticado y nonce REST valido.
- Usuarios no administradores solo pueden leer sus registros, salvo vistas publicas agregadas que oculten datos sensibles.
- No exponer direccion exacta, propietario, telefono, documentos privados ni datos fiscales en listados publicos.
- Las acciones de Marketplace protegidas deben seguir consumiendo accesos mediante la logica existente de `marketplace-access`.
- Mantener compatibilidad con el calculo de match definido en `AGENTS.md`.

## Plan tecnico minimo

1. Crear adaptador frontend `captacionRecordsApi` para `GET/POST /captacion/v1/records`.
2. Cargar captaciones y demandas del servidor cuando el usuario este autenticado.
3. Mantener datos demo iniciales solo para visitantes o modo preproduccion.
4. Al publicar captacion, guardar `property` en servidor y actualizar UI desde respuesta.
5. Al publicar demanda, guardar `need` en servidor y actualizar UI desde respuesta.
6. Migrar favoritos a `user_preferences`.
7. Relegar `captacion_properties_v3` y `captacion_needs_v3` a cache no autoritativa.
8. Documentar en `docs/` el cambio operativo antes de despliegue.

## Validacion requerida

- Usuario visitante no puede publicar ni ejecutar acciones protegidas.
- Usuario registrado puede crear y listar sus captaciones y demandas.
- Administrador puede listar registros por tipo y email.
- La UI sigue funcionando si el endpoint falla, mostrando aviso claro de preproduccion.
- No se guarda ninguna clave privada ni dato sensible en Git.
- `git diff --check` sin errores.
- `php -l` en archivos PHP modificados si PHP CLI esta disponible.
