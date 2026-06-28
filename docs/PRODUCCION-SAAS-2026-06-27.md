# Preparacion SaaS Produccion 2026-06-27

## Administrador SaaS

- Email administrador SaaS configurado por defecto: `inmobia360@gmail.com`.
- El usuario con ese email recibe acceso SaaS premium total mediante `captacion_app_get_user_access_state()`.
- La contrasena no se guarda en el tema ni en el ZIP. Debe gestionarse desde Usuarios de WordPress o el flujo de login real.

## Limpieza De Produccion

- Se anade una accion manual en `Captacion.app > Preparar SaaS para produccion`.
- La accion marca como eliminados datos demo/sinteticos y lotes demo mediante `deleted_at`.
- No borra cuentas WordPress ni datos privados reales.

## Reset SaaS Dia 1

- Disponible en `Captacion.app > Reset SaaS dia 1`.
- Requiere escribir `RESET` y proporcionar la contrasena del administrador SaaS en el formulario.
- Vacia tablas propias de Captacion.app: registros, lotes XML, accesos marketplace, eventos de mail y eventos de recursos.
- Elimina usuarios SaaS detectados por metadatos `captacion_*`, excluyendo el usuario actual y el administrador SaaS configurado.
- Crea o actualiza el usuario SaaS administrador configurado en `saas_admin_email`.
- No guarda la contrasena en codigo, documentacion ni ZIP.

## Feeds XML

- Estados operativos: `active`, `paused`, `pending_deletion`, `deleted`.
- `paused` oculta las propiedades del Marketplace sin borrarlas.
- `pending_deletion` oculta las propiedades del Marketplace y conserva registros vinculados a procesos activos de compra de captacion.
- Cuando los procesos activos terminan con estado aprobado, rechazado, cerrado, cancelado o completado, la eliminacion pendiente se completa automaticamente mediante soft-delete.
- `deleted` usa `deleted_at` y no aparece en la lista de XML subidos.

## ZIP De Despliegue

- `dist/captacion-app.zip` se genera en formato flat con `style.css` en la raiz para Hostinger.
- `dist/captacion-app-flat.zip` queda como alias flat.
- `dist/captacion-app-folder.zip` conserva la estructura tradicional `captacion-app/style.css`.
