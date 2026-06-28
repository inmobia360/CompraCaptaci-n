# Preparacion SaaS Produccion 2026-06-27

## Limpieza de copy publico (Fase 1 y 2 — 2026-06-28)

- Textos legales con `TODO LEGAL`, `PREPRODUCCION` y datos societarios ficticios (`EMPRESA PENDIENTE DE DEFINIR, S.L.`, `B00000000`) sustituidos por copy productivo prudente sin inventar datos.
- Mensajes de checkout "en preproduccion" reemplazados por "no disponible temporalmente / contacta con soporte".
- Etiquetas "Demos interactivas", "Demo operativa", "Roadmap funcional", "Abrir demo" cambiadas por "Herramientas disponibles", "Disponible", "Planificado", "Abrir herramienta".
- `docs/demo-user-records.xml` eliminado del repositorio.
- Directorio `tools/` excluido del ZIP de despliegue.
- Version del tema: 1.5.4.

## Validacion en WordPress real (pendiente)

- [ ] Subir `dist/captacion-app.zip` via Apariencia > Temas > Añadir y activar.
- [ ] Verificar que las paginas editables (aviso legal, privacidad, cookies) muestran copy limpio sin preproduccion/TODO.
- [ ] Verificar pie legal global en footer con email de contacto configurado.
- [ ] Verificar checkout: mensaje "no disponible temporalmente" en lugar de "preproduccion".
- [ ] Verificar que el email del SaaS admin (`saas_admin_email`) permite acceso premium.
- [ ] Probar subida/importacion XML de captacion y revision de propiedades.
- [ ] Probar publicacion en Marketplace desde XML.
- [ ] Verificar que `tools/` no aparece en los archivos del tema instalado.
- [ ] Probar sesion de usuario registrado y no registrado.

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

- `dist/captacion-app.zip` se genera con carpeta `captacion-app/style.css` para subida via WordPress Admin (Apariencia > Temas > Añadir).
- `dist/captacion-app-folder.zip` alias con la misma estructura.
- `dist/captacion-app-flat.zip` plano (`style.css` en raiz) para extraccion manual en Hostinger/cPanel.
- El ZIP excluye `tools/` (scripts de desarrollo), `*.md`, `*.log` y archivos de sistema.
- `dist/backups/` conserva copias de seguridad de versiones anteriores.
