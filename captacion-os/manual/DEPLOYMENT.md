# Despliegue WordPress / Hostinger

## Objetivo

Subir una version estable del tema Captacion.app a WordPress sin romper instalacion, SEO, plugins ni datos.

## Reglas

- Desplegar solo desde una version validada.
- No desplegar cambios no commiteados.
- No incluir `.git`, `.env`, backups, caches ni uploads.
- El ZIP debe contener `captacion-app/style.css`.

## Checklist

```powershell
git status --short --branch
git diff --check
```

Validar que:

- `style.css` tiene cabecera WordPress.
- `functions.php` y `template-app-interactiva.php` no tienen errores de sintaxis.
- No hay credenciales en el paquete.
- No se incluyen carpetas de runtime.

## Hostinger

Subida recomendada:

- WordPress > Apariencia > Temas > Anadir tema > Subir tema.

Si falla por `style.css`, revisar estructura interna del ZIP.
