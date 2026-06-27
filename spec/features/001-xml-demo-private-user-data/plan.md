# Plan: XML demo y datos privados por usuario

## Arquitectura Propuesta

La arquitectura se basa en tres capas:

1. XML portable.
2. MySQL/WordPress como base operativa.
3. Metadatos de ownership, origen, privacidad y lote para controlar visibilidad y borrado.

Principio clave:

`XML = importacion/exportacion`, no base viva.

## Modelo De Datos Propuesto

### Extender `wp_captacion_app_records`

Columnas nuevas recomendadas:

- `owner_user_id BIGINT UNSIGNED NOT NULL DEFAULT 0`
- `created_by BIGINT UNSIGNED NOT NULL DEFAULT 0`
- `import_batch_id VARCHAR(80) DEFAULT '' NOT NULL`
- `data_origin VARCHAR(40) DEFAULT 'manual' NOT NULL`
- `is_demo TINYINT(1) DEFAULT 0 NOT NULL`
- `privacy_scope VARCHAR(40) DEFAULT 'private_user' NOT NULL`
- `consent_status VARCHAR(40) DEFAULT '' NOT NULL`
- `source_file_name VARCHAR(190) DEFAULT '' NOT NULL`
- `source_hash CHAR(64) DEFAULT '' NOT NULL`
- `deleted_at DATETIME NULL`

Indices recomendados:

- `KEY owner_user_id (owner_user_id)`
- `KEY import_batch_id (import_batch_id)`
- `KEY data_origin (data_origin)`
- `KEY is_demo (is_demo)`
- `KEY privacy_scope (privacy_scope)`
- `KEY deleted_at (deleted_at)`

Decision pendiente:

- Revisar `UNIQUE KEY record_type_key (record_type, record_key)`.
- Riesgo: dos usuarios importando el mismo XML pueden colisionar.
- Opciones:
  - Cambiar a `UNIQUE(owner_user_id, record_type, record_key)`.
  - Mantener indice y generar `record_key` prefijado por usuario/lote.

Recomendacion tecnica:

- Migrar a `UNIQUE(owner_user_id, record_type, record_key)` si no hay dependencias externas.
- Si se quiere minimo riesgo, prefijar `record_key` con `u:{user_id}:` o `batch:{import_batch_id}:`.

### Nueva Tabla `wp_captacion_import_batches`

Campos:

- `id BIGINT UNSIGNED AUTO_INCREMENT`
- `import_batch_id VARCHAR(80) NOT NULL`
- `owner_user_id BIGINT UNSIGNED NOT NULL DEFAULT 0`
- `created_by BIGINT UNSIGNED NOT NULL DEFAULT 0`
- `data_origin VARCHAR(40) NOT NULL`
- `is_demo TINYINT(1) NOT NULL DEFAULT 0`
- `privacy_scope VARCHAR(40) NOT NULL`
- `source_file_name VARCHAR(190) DEFAULT ''`
- `source_hash CHAR(64) DEFAULT ''`
- `schema_version VARCHAR(20) DEFAULT '1.0'`
- `status VARCHAR(40) NOT NULL DEFAULT 'pending'`
- `records_total INT UNSIGNED NOT NULL DEFAULT 0`
- `records_imported INT UNSIGNED NOT NULL DEFAULT 0`
- `records_rejected INT UNSIGNED NOT NULL DEFAULT 0`
- `summary_json LONGTEXT NULL`
- `created_at DATETIME NOT NULL`
- `updated_at DATETIME NOT NULL`
- `deleted_at DATETIME NULL`

Indices:

- `UNIQUE(import_batch_id)`
- `KEY owner_user_id`
- `KEY data_origin`
- `KEY privacy_scope`
- `KEY status`
- `KEY deleted_at`

## Archivos A Crear

- `stable-1.5.1/captacion-app/src/data/demo-user-records.xml`
- Posible `stable-1.5.1/captacion-app/inc/xml-data.php`
- Posible `stable-1.5.1/captacion-app/inc/privacy-data.php`
- Documentacion en `docs/IMPLEMENTACION-XML-DEMO-DATOS-PRIVADOS-YYYY-MM-DD.md`

Nota: el tema actual esta concentrado en `functions.php`; separar en `inc/` es recomendable, pero debe hacerse con cuidado para no romper carga WordPress.

## Archivos A Modificar

- `stable-1.5.1/captacion-app/functions.php`
  - Migraciones de tabla.
  - Endpoints REST.
  - Import/export XML.
  - Borrado por lote.
  - Borrado datos privados.
  - Integracion privacy exporters/erasers.
- `stable-1.5.1/captacion-app/template-app-interactiva.php`
  - UI futura para import/export/borrado.
  - Adaptador frontend para nuevos endpoints.
  - Marcar datos manuales con origen y privacidad.
- `captacion-os/spec/PERSISTENCIA-SAAS-MVP.md`
  - Referenciar esta feature.
- `docs/CHECKLIST-DESPLIEGUE-SAAS-MVP.md`
  - Incluir pruebas de privacidad y XML.

## Estrategia De Exportacion XML Demo

1. Identificar fuente demo:
   - `initialProperties`.
   - `initialNeeds`.
   - demo nacional si se decide convertirla en XML oficial.
2. Generar XML con root `captacionData`.
3. Marcar:
   - `schemaVersion="1.0"`.
   - `dataOrigin="demo_xml"`.
   - `privacyScope="global_demo"`.
   - `isDemo="true"`.
4. No incluir datos reales ni datos de usuarios.
5. Incluir resumen:
   - total properties.
   - total needs.
   - fecha generacion.
   - hash.

## Estrategia De Importacion XML Demo

1. Endpoint admin: `POST /wp-json/captacion/v1/xml/demo/import`.
2. Requiere `manage_options`.
3. Validar XML.
4. Crear `import_batch_id`.
5. Insertar registros como:
   - `owner_user_id = 0`.
   - `created_by = current_admin_id`.
   - `is_demo = 1`.
   - `data_origin = demo_xml`.
   - `privacy_scope = global_demo`.
6. Registrar lote.
7. Devolver resumen.

## Estrategia De Importacion XML Privada De Usuario

1. Endpoint privado: `POST /wp-json/captacion/v1/xml/user/import`.
2. Requiere login, nonce, email verificado y `read`.
3. Validar XML.
4. Ignorar cualquier propietario declarado en XML.
5. Crear `import_batch_id`.
6. Insertar como:
   - `owner_user_id = current_user_id`.
   - `created_by = current_user_id`.
   - `is_demo = 0`.
   - `data_origin = user_xml`.
   - `privacy_scope = private_user`.
7. Devolver resumen.

## Estrategia De Creacion Manual De Datos Privados

Modificar `captacion_app_rest_save_record` y `captacion_app_upsert_record` para que, si no se indica otra cosa valida por servidor:

- `owner_user_id = current_user_id`.
- `created_by = current_user_id`.
- `data_origin = manual`.
- `is_demo = 0`.
- `privacy_scope = private_user`.

No confiar en valores enviados por frontend para ownership.

## Estrategia De Ownership Por Usuario

Lectura:

- Usuario normal: `owner_user_id = current_user_id OR user_id = current_user_id`.
- Demo global publica: solo si endpoint/vista lo permite y `privacy_scope = global_demo`.
- Admin: puede gestionar demo global y ver resumenes. Datos privados requieren flujo explicito.

Escritura:

- Usuario normal solo escribe sus datos.
- Admin solo puede crear demo global mediante endpoint admin.

## Estrategia De Eliminacion Por Lote

Endpoint:

- `DELETE /wp-json/captacion/v1/import-batches/{import_batch_id}`

Reglas:

- Usuario normal: lote debe pertenecer a `owner_user_id = current_user_id`.
- Admin: puede eliminar lote `global_demo`.
- Requiere confirmacion en payload.
- Borrar o marcar `deleted_at` en registros del lote.
- Marcar lote como deleted.

## Estrategia De Eliminacion Por Usuario

Endpoint:

- `DELETE /wp-json/captacion/v1/my-data`

Opciones:

- `private_data_only`.
- `private_data_and_generated_files`.
- futura `account_and_private_data`.

Debe incluir:

- Registros con `owner_user_id = current_user_id`.
- Registros legacy con `user_id = current_user_id`.
- Accesos `captacion_access_log`.
- Eventos recursos `captacion_resource_events` segun politica.
- Archivos privados en `uploads/captacion-private/{user_id}`.
- Conexion IA del usuario.

Debe excluir:

- Paginas.
- Menus.
- Planes.
- Ajustes globales.
- Roles.
- Usuarios de otros propietarios.
- Estructura territorial.

## Estrategia De Logs

Eventos minimos:

- `xml_import_started`.
- `xml_import_completed`.
- `xml_import_failed`.
- `xml_batch_deleted`.
- `user_private_data_deleted`.
- `user_private_data_exported`.

No guardar payload sensible en logs.

Datos permitidos:

- `event_type`.
- `actor_user_id`.
- `owner_user_id`.
- `import_batch_id`.
- contadores.
- timestamp.
- hash del archivo.

## Estrategia De Permisos

- Admin demo: `current_user_can('manage_options')`.
- Usuario privado: `captacion_app_rest_private_permission`.
- Export admin de privados: no implementar inicialmente o requerir endpoint separado con confirmacion y motivo.
- Borrado irreversible: confirmacion textual.

## Estrategia De Validacion XML

- Maximo 5 MB inicial.
- Maximo 500 registros por lote inicial.
- Rechazar `DOCTYPE`.
- Rechazar entidades externas.
- `LIBXML_NONET`.
- Root obligatorio: `captacionData`.
- `schemaVersion` obligatorio.
- Tipos permitidos segun `captacion_app_allowed_record_types()`.
- Sanitizacion especifica para `property` y `need` usando funciones existentes.
- Campos desconocidos permitidos solo dentro de `payload`, sanitizados.

## Estrategia De Pruebas

- Unitarias si existe framework futuro.
- Manuales obligatorias en staging.
- Pruebas con dos usuarios.
- Pruebas de XML invalido.
- Pruebas de XML con `DOCTYPE`.
- Pruebas de lote demo eliminado.
- Pruebas de baja usuario.
- Validar que paginas/menus/configuracion siguen intactos.
