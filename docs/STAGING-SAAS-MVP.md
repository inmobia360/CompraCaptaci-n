# Staging SaaS MVP

## Objetivo

Validar Captacion.app en un WordPress real antes de produccion, con dominio de pruebas, usuarios de prueba y Stripe en modo test.

## Entorno recomendado

- Subdominio: `staging.tudominio.com`.
- WordPress limpio o copia controlada de produccion.
- PHP compatible con el tema: 7.4 o superior.
- Plugins activos equivalentes al entorno objetivo cuando aplique: Rank Math, LiteSpeed Cache, Forminator, Pods, Complianz, Mailchimp, Stripe o Captacion Core.
- Indexacion desactivada en WordPress para evitar SEO duplicado.

## Generar ZIP instalable

Desde la raiz del repositorio:

```powershell
.\scripts\build-theme-zip.ps1
```

Salida esperada:

```text
dist/captacion-app.zip
```

El ZIP debe contener:

```text
captacion-app/style.css
captacion-app/functions.php
captacion-app/template-app-interactiva.php
```

No versionar el ZIP generado.

## Instalacion en WordPress

1. Ir a `Apariencia > Temas > Anadir nuevo > Subir tema`.
2. Subir `dist/captacion-app.zip`.
3. Activar el tema `Captacion.app`.
4. Entrar en el menu de administrador `Captacion.app`.
5. Configurar email, Stripe Payment Links de prueba y Mailchimp de prueba.
6. Crear/actualizar paginas editables si el entorno no las tiene.

## Validacion minima

- Registro profesional crea usuario WordPress.
- Login y logout funcionan.
- Panel privado muestra el usuario autenticado.
- Publicar captacion guarda en WordPress y aparece tras recargar.
- Publicar demanda guarda en WordPress y aparece tras recargar.
- Marketplace mantiene reglas de acceso.
- Recursos protegidos exigen login.
- Contacto y reportes envian email.
- Visitantes no pueden ejecutar acciones protegidas.
- No se muestran datos sensibles en publico.

## Pagos en modo test

- Configurar Stripe Payment Links de prueba.
- No activar cobros reales hasta validar webhook server-side.
- Ningun acceso privado debe concederse solo por retorno del navegador.
- El saldo/acceso debe depender de confirmacion en servidor.

## Cierre de pruebas

- Revisar consola del navegador.
- Revisar logs de WordPress/Hostinger.
- Probar escritorio, tablet y movil.
- Desactivar caches durante pruebas criticas.
- Documentar incidencias antes de pasar a produccion.
