# Checklist despliegue SaaS MVP

Fuente activa:

```text
stable-1.5.1/captacion-app
```

## Antes de generar ZIP

- Confirmar que `stable-1.5.1/captacion-app/style.css` tiene cabecera WordPress valida.
- Ejecutar `git status --short --branch`.
- Ejecutar `git diff --check`.
- Si PHP CLI esta disponible, ejecutar:
  - `php -l stable-1.5.1/captacion-app/functions.php`
  - `php -l stable-1.5.1/captacion-app/template-app-interactiva.php`
- Confirmar que no hay ZIPs, dumps SQL, backups, logs, uploads ni credenciales preparados para commit.

## ZIP instalable

- El ZIP debe contener una carpeta raiz llamada `captacion-app`.
- Dentro debe existir `captacion-app/style.css`.
- No debe contener una carpeta intermedia como `stable-1.5.1/captacion-app`.
- No versionar el ZIP generado.
- Si Hostinger/WordPress indica que no encuentra `style.css`, usar `dist/captacion-app-flat.zip`, que contiene `style.css` directamente en la raiz del ZIP.

## Configuracion WordPress

- Activar el tema `Captacion.app`.
- Entrar en el menu de administrador `Captacion.app`.
- Configurar email de contacto.
- Configurar Stripe Payment Links en modo prueba antes de produccion.
- Configurar Mailchimp API Key y Audience ID solo en WordPress.
- Crear/actualizar paginas editables si el entorno esta vacio.
- Revisar enlaces de aviso legal, privacidad y cookies.

## Validacion publica

- Portada carga sin errores visibles.
- Menu movil funciona.
- Paginas principales cargan correctamente.
- Visitante puede navegar contenido publico.
- Visitante no puede acceder a acciones protegidas.
- No se muestran direccion exacta, propietario, telefono, documentos privados ni datos fiscales en publico.

## Validacion usuario registrado

- Registro profesional crea usuario WordPress.
- Login inicia sesion real.
- Logout cierra sesion.
- Panel privado muestra datos del usuario autenticado.
- Marketplace aplica reglas de acceso.
- Publicacion de captacion y demanda no depende de persistencia local como fuente final.
- Recursos protegidos requieren usuario autenticado.

## Validacion pagos

- Plan gratuito no redirige a cobro si no corresponde.
- Planes de pago usan enlaces Stripe configurados.
- Si Stripe no esta configurado, el flujo falla de forma segura hacia contacto/preproduccion.
- Ningun acceso privado se concede solo por accion del navegador.
- El desbloqueo definitivo debe depender de webhook confirmado en servidor.

## Validacion email y CRM

- Contacto envia correo interno.
- Registro envia bienvenida/verificacion si aplica.
- Reporte envia confirmacion al usuario registrado.
- Mailchimp respeta consentimiento comercial separado.
- Etiquetas Mailchimp por origen: registro, contacto, reporte, demanda y captacion.

## Cierre

- Revisar consola del navegador.
- Revisar logs de WordPress/Hostinger.
- Probar en movil, tablet y escritorio.
- Confirmar que caches no sirven version antigua.
- Documentar incidencias antes de pasar a produccion.
