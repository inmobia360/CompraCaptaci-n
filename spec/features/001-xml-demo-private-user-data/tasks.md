# Tasks: XML demo y datos privados por usuario

## Checklist Tecnico Paso A Paso

### 0. Preparacion

- [ ] Revisar cambios pendientes del worktree antes de implementar.
- [ ] Confirmar estrategia de indice unico para `captacion_app_records`.
- [ ] Confirmar si demo global usa `owner_user_id = 0`.
- [ ] Confirmar si borrado sera fisico o soft delete.
- [ ] Confirmar limite maximo de XML.

### 1. Migraciones De Datos

- [ ] Crear tabla `captacion_import_batches`.
- [ ] Versionar migracion con option WordPress.
- [ ] Anadir columnas de ownership/origen a `captacion_app_records`.
- [ ] Anadir indices necesarios.
- [ ] Revisar impacto del indice unico actual.
- [ ] Probar activacion/migracion en staging.

### 2. Infraestructura De Lotes

- [ ] Crear funcion `captacion_app_import_batches_table_name()`.
- [ ] Crear funcion `captacion_app_install_import_batches_table()`.
- [ ] Crear funcion `captacion_app_create_import_batch()`.
- [ ] Crear funcion `captacion_app_update_import_batch_status()`.
- [ ] Crear funcion `captacion_app_get_import_batch()`.
- [ ] Crear funcion `captacion_app_user_can_manage_import_batch()`.

### 3. Validacion XML

- [ ] Crear validador de tamano maximo.
- [ ] Rechazar XML vacio.
- [ ] Rechazar XML con `DOCTYPE`.
- [ ] Parsear con `LIBXML_NONET`.
- [ ] Validar root `captacionData`.
- [ ] Validar `schemaVersion`.
- [ ] Validar `dataOrigin`.
- [ ] Validar `privacyScope`.
- [ ] Validar numero maximo de registros.
- [ ] Validar `record_type` permitido.
- [ ] Sanitizar payload por tipo.
- [ ] No aceptar `owner_user_id` del XML para usuarios normales.

### 4. Exportacion XML Demo

- [ ] Definir fuente demo oficial.
- [ ] Crear `src/data/demo-user-records.xml` con datos sinteticos.
- [ ] Crear funcion generadora XML demo desde fuente oficial.
- [ ] Incluir `schemaVersion`.
- [ ] Incluir `generatedAt`.
- [ ] Incluir resumen.
- [ ] Incluir hash.
- [ ] Excluir datos reales.

### 5. Importacion XML Demo

- [ ] Crear endpoint `POST /captacion/v1/xml/demo/import`.
- [ ] Requerir `manage_options`.
- [ ] Crear lote `data_origin = demo_xml`.
- [ ] Insertar registros `is_demo = 1`.
- [ ] Insertar `privacy_scope = global_demo`.
- [ ] Insertar `owner_user_id = 0` si se aprueba.
- [ ] Devolver resumen.
- [ ] Registrar evento sin datos sensibles.

### 6. Importacion XML Privada

- [ ] Crear endpoint `POST /captacion/v1/xml/user/import`.
- [ ] Requerir `captacion_app_rest_private_permission`.
- [ ] Crear lote `data_origin = user_xml`.
- [ ] Insertar registros `owner_user_id = current_user_id`.
- [ ] Insertar `privacy_scope = private_user`.
- [ ] Insertar `is_demo = 0`.
- [ ] Impedir acceso cruzado.
- [ ] Devolver resumen.

### 7. Datos Manuales Privados

- [ ] Modificar `captacion_app_upsert_record()` para defaults server-side.
- [ ] Modificar `captacion_app_rest_save_record()` para no confiar en ownership del cliente.
- [ ] Marcar manuales con `data_origin = manual`.
- [ ] Marcar manuales con `privacy_scope = private_user`.
- [ ] Marcar manuales con `owner_user_id = current_user_id`.

### 8. Listado Y Lectura

- [ ] Modificar `captacion_app_rest_list_records()` para filtrar por `owner_user_id`.
- [ ] Mantener compatibilidad legacy con `user_id` durante migracion.
- [ ] Evitar devolver registros `deleted_at IS NOT NULL`.
- [ ] Definir si demo global se devuelve a visitantes o solo usuarios/admin.

### 9. Exportacion XML Privada

- [ ] Crear endpoint `GET /captacion/v1/xml/user/export`.
- [ ] Exportar solo `owner_user_id = current_user_id`.
- [ ] Excluir datos de terceros.
- [ ] Incluir schema y resumen.
- [ ] Registrar evento sin payload sensible.
- [ ] Probar descarga.

### 10. Eliminacion Por Lote

- [ ] Crear endpoint `DELETE /captacion/v1/import-batches/{import_batch_id}`.
- [ ] Requerir confirmacion textual.
- [ ] Usuario normal solo puede borrar lotes propios.
- [ ] Admin solo puede borrar demo global o lotes permitidos.
- [ ] Borrar/marcar registros por `import_batch_id`.
- [ ] Marcar lote como eliminado.
- [ ] Registrar evento.

### 11. Baja Y Supresion De Datos Privados

- [ ] Crear endpoint `DELETE /captacion/v1/my-data`.
- [ ] Requerir confirmacion textual.
- [ ] Borrar registros propios.
- [ ] Borrar lotes propios.
- [ ] Borrar o anonimizar access logs propios segun politica.
- [ ] Borrar o anonimizar resource events propios segun politica.
- [ ] Borrar archivos `uploads/captacion-private/{user_id}`.
- [ ] Eliminar conexion IA del usuario.
- [ ] Mantener trazabilidad minima no sensible.

### 12. Integracion RGPD WordPress

- [ ] Registrar exporter con `wp_privacy_personal_data_exporters`.
- [ ] Registrar eraser con `wp_privacy_personal_data_erasers`.
- [ ] Probar exportador nativo WP.
- [ ] Probar borrador nativo WP.

### 13. UI Frontend/Admin

- [ ] Admin: importar XML demo.
- [ ] Admin: listar lotes demo.
- [ ] Admin: eliminar lote demo.
- [ ] Usuario: importar XML privado.
- [ ] Usuario: listar sus lotes.
- [ ] Usuario: exportar XML privado.
- [ ] Usuario: eliminar lote privado.
- [ ] Usuario: eliminar base privada completa.
- [ ] Mostrar advertencias de irreversibilidad.

### 14. Documentacion

- [ ] Documentar XML schema.
- [ ] Documentar importacion demo.
- [ ] Documentar importacion usuario.
- [ ] Documentar borrado por lote.
- [ ] Documentar baja/supresion.
- [ ] Actualizar checklist despliegue.

## Orden Recomendado

1. Migracion de tablas.
2. Registro de lotes.
3. Validador XML.
4. XML demo sintetico.
5. Importacion demo admin.
6. Eliminacion lote demo.
7. Defaults ownership en manuales.
8. Importacion XML privada usuario.
9. Exportacion privada usuario.
10. Borrado privado usuario.
11. Integracion RGPD WP.
12. UI.
13. QA completo.

## Pruebas Manuales

- [ ] Admin importa XML demo.
- [ ] Admin ve lote importado.
- [ ] Marketplace muestra demo si corresponde.
- [ ] Admin elimina lote demo.
- [ ] Datos demo desaparecen.
- [ ] Paginas siguen existiendo.
- [ ] Menus siguen existiendo.
- [ ] Configuracion global sigue existiendo.
- [ ] Usuario A importa XML privado.
- [ ] Usuario B no ve XML de A.
- [ ] Usuario A exporta sus datos.
- [ ] Usuario B no puede exportar datos de A.
- [ ] Usuario A elimina lote propio.
- [ ] Usuario B conserva sus datos.
- [ ] Usuario A crea captacion manual.
- [ ] Captacion manual tiene `owner_user_id = A`.
- [ ] Usuario A elimina base privada.
- [ ] Datos privados A desaparecen.
- [ ] Cuenta A puede conservarse si esa opcion esta activa.

## Pruebas De Seguridad XML

- [ ] XML vacio se rechaza.
- [ ] XML mal formado se rechaza.
- [ ] XML con `DOCTYPE` se rechaza.
- [ ] XML con entidad externa se rechaza.
- [ ] XML mayor al limite se rechaza.
- [ ] XML con `record_type` no permitido se rechaza.
- [ ] XML con `owner_user_id` de otro usuario se ignora/rechaza.
- [ ] XML con emails/telefonos en campos publicos se sanitiza si aplica.
- [ ] Errores no muestran payload sensible.

## Pruebas Automaticas Si Existen

Actualmente no se ha identificado framework de tests automatizados.

Si se anade en el futuro:

- [ ] Tests unitarios de validador XML.
- [ ] Tests de permisos REST.
- [ ] Tests de borrado por lote.
- [ ] Tests de borrado por usuario.
- [ ] Tests de exportacion XML.
- [ ] Tests de rechazo XXE.

## Validaciones Antes De Produccion

- [ ] `git status --short --branch`.
- [ ] `git diff --check`.
- [ ] `php -l functions.php` si PHP CLI esta disponible.
- [ ] Validar en staging con dos usuarios.
- [ ] Validar con Rank Math activo.
- [ ] Validar con LiteSpeed Cache purgado.
- [ ] Validar que no se suben XML con datos reales a Git.
- [ ] Validar `.gitignore` para dumps, uploads y XML privados.
- [ ] Revisar logs de Hostinger.
- [ ] Revisar consola navegador.
- [ ] Documentar resultado de QA.
