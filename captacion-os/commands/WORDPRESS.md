# Comandos WordPress / tema

## Validar PHP

```powershell
php -l stable-1.5.1/captacion-app/functions.php
php -l stable-1.5.1/captacion-app/template-app-interactiva.php
```

## Validar estructura del tema

```powershell
Test-Path stable-1.5.1/captacion-app/style.css
Select-String -Path stable-1.5.1/captacion-app/style.css -Pattern "Theme Name:|Version:"
```

## Seguridad antes de paquete

```powershell
git status --short --branch
git diff --check
```

No crear paquetes desde carpetas que contengan `.git`, uploads, caches, logs o backups.

## Generar ZIP instalable

```powershell
.\scripts\build-theme-zip.ps1
```

Salida esperada:

```text
dist/captacion-app.zip
```

El ZIP debe contener `captacion-app/style.css` en la raiz interna. No versionar el ZIP generado.
