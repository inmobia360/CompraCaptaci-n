# Spec: XML demo y datos privados por usuario

## Problema

Captacion.app usa actualmente varias fuentes de datos demo y operativos:

- Datos iniciales hardcodeados en `template-app-interactiva.php`.
- Demo nacional generada en navegador y persistida en `localStorage`.
- Importaciones XML privadas parseadas en frontend y persistidas en `localStorage`.
- Registros reales guardados en `wp_captacion_app_records` mediante `/wp-json/captacion/v1/records`.

Esta mezcla permite probar la plataforma, pero no ofrece separacion suficiente entre datos demo, datos reales, datos importados por XML y datos creados manualmente por usuarios. Tampoco existe todavia un mecanismo formal de portabilidad XML, eliminacion por lote, baja de usuario o supresion completa de datos privados conforme a principios RGPD.

## Objetivo

Crear una arquitectura de XML portable para importacion/exportacion, manteniendo WordPress/MySQL como base operativa del SaaS.

El sistema debe permitir:

- Generar un XML con la base demo actual.
- Importar ese XML como demo controlada.
- Eliminar todos los registros insertados desde un lote XML demo mediante `import_batch_id`.
- Importar XML privado de usuario registrado.
- Asociar datos importados y manuales al propietario real mediante `owner_user_id`.
- Exportar datos privados del usuario en XML.
- Eliminar la base privada de un usuario al darse de baja o solicitar supresion.
- Evitar mezcla entre datos demo y datos reales.
- Garantizar trazabilidad minima sin conservar datos personales innecesarios.

## Alcance

Incluye:

- Esquema XML versionado para datos demo y privados.
- Registro de lotes de importacion.
- Metadatos de origen, privacidad, demo y propietario.
- Importacion XML demo solo para administradores.
- Importacion XML privada solo para usuarios autenticados.
- Exportacion XML privada del usuario.
- Eliminacion por lote XML.
- Eliminacion de datos privados por usuario.
- Validacion XML segura.
- Reglas de permisos y trazabilidad.
- Documentacion tecnica y QA.

## Exclusiones

No incluye en esta primera especificacion:

- Sustituir MySQL por XML como base de datos viva.
- Borrar paginas publicas, landings, legales, menus, planes o ajustes globales.
- Modificar `wp-config.php`.
- Instalar plugins o dependencias externas.
- Exportar datos privados de usuarios por administradores sin accion explicita y justificada.
- Crear un DMS documental completo.
- Crear un sistema avanzado de retencion legal para pagos/facturacion, salvo dejar el punto preparado.

## Entidades Afectadas

### Registros SaaS

Tabla actual:

`wp_captacion_app_records`

Tipos existentes:

- `property`
- `need`
- `smart_match`
- `report`
- `notification`
- `access_request`
- `activity`
- `user_preferences`
- `dashboard_state`
- `task`
- `generated_pdf`

Metadatos requeridos para evolucionar el modelo:

- `owner_user_id`
- `import_batch_id`
- `data_origin`
- `is_demo`
- `created_by`
- `created_at`
- `updated_at`
- `deleted_at`
- `privacy_scope`
- `consent_status`
- `source_file_name`
- `source_hash`
- `record_type`
- `record_id` / `record_key`

### Lotes XML

Nueva entidad propuesta:

`wp_captacion_import_batches`

Campos minimos:

- `id`
- `import_batch_id`
- `owner_user_id`
- `created_by`
- `data_origin`
- `is_demo`
- `privacy_scope`
- `source_file_name`
- `source_hash`
- `schema_version`
- `status`
- `records_total`
- `records_imported`
- `records_rejected`
- `summary_json`
- `created_at`
- `updated_at`
- `deleted_at`

### Usuarios

Usuarios WordPress con rol actual:

- `captacion_agent`

Capacidades actuales:

- `read`
- `upload_files`

Metadatos relevantes:

- consentimiento privacidad,
- consentimiento comercial,
- telefono,
- plan,
- accesos marketplace,
- estado de verificacion email.

### Archivos Privados

Rutas actuales a considerar:

- `uploads/captacion-private/{user_id}`

## Reglas De Negocio

1. XML es fichero portable de importacion/exportacion, no base operativa.
2. WordPress/MySQL es la fuente operativa del SaaS.
3. Todo registro operativo debe tener propietario o alcance claro.
4. Los datos demo globales deben guardarse como:
   - `is_demo = true`
   - `data_origin = demo_xml` o `system_seed`
   - `privacy_scope = global_demo`
   - `owner_user_id = 0` recomendado
   - `created_by = admin_id`
5. Datos privados importados por XML deben guardarse como:
   - `is_demo = false`
   - `data_origin = user_xml`
   - `owner_user_id = current_user_id`
   - `created_by = current_user_id`
   - `privacy_scope = private_user`
6. Datos manuales creados por usuario deben guardarse como:
   - `is_demo = false`
   - `data_origin = manual`
   - `owner_user_id = current_user_id`
   - `created_by = current_user_id`
   - `privacy_scope = private_user`
7. Un usuario no administrador nunca puede ver, editar, exportar ni eliminar datos privados de otro usuario.
8. Un administrador puede gestionar demo global.
9. Un administrador no debe exportar datos privados de usuarios salvo accion explicita, justificada y trazada.
10. Eliminar un lote XML debe borrar o marcar eliminado solo registros con ese `import_batch_id`.
11. Eliminar datos privados de usuario debe afectar solo registros con `owner_user_id = current_user_id` o `user_id = current_user_id` en tablas legacy.
12. No se deben borrar paginas, menus, legales, planes, configuracion global, roles, permisos ni estructura territorial.
13. Toda eliminacion irreversible requiere confirmacion explicita.
14. Las acciones destructivas deben registrar evento minimo sin payload sensible.

## Casos De Uso

### UC-01: Generar XML demo actual

Como administrador, quiero exportar los datos demo actuales a XML para poder reinstalar o limpiar la demo de forma controlada.

Criterios:

- Solo `manage_options`.
- XML marcado como demo.
- Sin datos reales privados.
- Incluye `schema_version`, fecha y resumen.

### UC-02: Importar XML demo global

Como administrador, quiero importar un XML demo para poblar el SaaS con datos ficticios.

Criterios:

- Solo `manage_options`.
- Genera `import_batch_id`.
- Marca `is_demo = true`.
- Marca `privacy_scope = global_demo`.

### UC-03: Eliminar lote demo

Como administrador, quiero eliminar todos los datos de un lote demo para dejar el SaaS limpio.

Criterios:

- Borra solo registros del lote.
- No borra datos reales.
- No borra paginas ni configuracion.
- Requiere confirmacion.

### UC-04: Importar XML privado de usuario

Como usuario registrado, quiero importar mi XML privado para trabajar mis captaciones y demandas dentro del SaaS.

Criterios:

- Requiere login, nonce REST y email verificado.
- Todo queda asociado al usuario actual.
- No se aceptan IDs de propietario procedentes del XML.
- Otros usuarios no pueden ver esos datos.

### UC-05: Crear datos manuales privados

Como usuario, quiero crear captaciones y demandas manualmente y que queden asociadas a mi cuenta.

Criterios:

- `data_origin = manual`.
- `privacy_scope = private_user`.
- `owner_user_id = current_user_id`.

### UC-06: Exportar XML privado

Como usuario, quiero exportar mis datos privados para ejercer portabilidad.

Criterios:

- Exporta solo datos propios.
- No incluye datos de terceros.
- Incluye metadatos de exportacion.

### UC-07: Eliminar base privada de usuario

Como usuario, quiero eliminar mis datos privados si decido darme de baja o limpiar mi cuenta.

Criterios:

- Requiere confirmacion explicita.
- Borra/importa solo datos propios.
- Permite conservar cuenta sin datos privados.
- Permite preparar futura eliminacion de cuenta si el flujo lo permite.

## Criterios De Aceptacion

1. Se puede generar XML demo desde datos demo actuales.
2. Se puede importar XML demo como demo global.
3. Se puede eliminar un lote demo por `import_batch_id`.
4. El SaaS queda limpio al eliminar ese lote demo.
5. Un usuario registrado puede importar XML privado.
6. Datos importados por usuario quedan asociados solo a ese usuario.
7. Datos manuales quedan asociados al usuario.
8. Usuario puede eliminar sus datos privados importados y manuales.
9. Usuario no puede acceder a datos privados de otro usuario.
10. Demo y datos reales no se mezclan.
11. No se borran paginas, menus, legales, planes ni configuracion global.
12. XML invalido se rechaza.
13. XML con `DOCTYPE` o entidades externas se rechaza.
14. Errores no exponen datos sensibles.
15. Queda documentacion tecnica de uso.

## Riesgos

- Colision de `record_key` entre usuarios por el indice unico actual `(record_type, record_key)`.
- Datos demo actuales viven en JS/localStorage y no tienen batch persistente servidor.
- XML privado actual se importa en navegador y no queda bajo control server-side.
- Baja de usuario no existe todavia.
- Eventos de mail, recursos y accesos contienen datos personales que deben considerarse en supresion.
- Archivos privados generados requieren borrado fisico controlado.
- Borrado fisico irreversible puede crear problemas si hay obligaciones legales de conservacion minima.

## Requisitos RGPD

- Derecho de supresion: borrar o anonimizar datos privados del usuario.
- Derecho de portabilidad: exportar datos propios en XML.
- Minimizacion: no guardar campos no necesarios.
- Limitacion de finalidad: distinguir `data_origin` y `privacy_scope`.
- Trazabilidad: registrar importaciones/exportaciones/borrados sin payload sensible.
- Consentimiento: registrar `consent_status` cuando aplique.
- Seguridad: impedir acceso cruzado entre usuarios.

## Requisitos De Privacidad

- No transferir datos privados a terceros sin autorizacion.
- No usar proxies publicos para XML privado en produccion.
- No incluir datos reales en XML demo versionado.
- No exponer direccion exacta, propietario, telefono, email privado, documentos o datos fiscales en vistas publicas.
- No permitir exportacion admin de datos privados sin flujo explicito.

## Requisitos De Seguridad XML

- Tamano maximo inicial recomendado: 5 MB.
- Rechazar XML vacio.
- Rechazar XML mal formado.
- Rechazar `DOCTYPE`.
- Usar `LIBXML_NONET`.
- Deshabilitar entidades externas si aplica por version PHP.
- Validar root y `schemaVersion`.
- Validar numero maximo de registros por lote.
- Validar `record_type` contra lista permitida.
- Sanitizar todos los campos.
- No permitir rutas locales ni lectura de archivos internos.
- No aceptar `owner_user_id` del XML para usuarios normales.
- Registrar errores tecnicos sin incluir datos personales.
