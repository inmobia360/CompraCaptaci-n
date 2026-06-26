# Workflow GitHub

## Flujo normal

1. Revisar la tarea.
2. Si afecta codigo importante, trabajar primero en modo PLAN.
3. Confirmar archivos afectados.
4. Modificar solo lo necesario.
5. Validar localmente.
6. Crear commit descriptivo.
7. Subir a GitHub.

## Comandos base

```powershell
git status --short --branch
git add .
git commit -m "Descripcion clara del cambio"
git push
```

## Validaciones recomendadas

```powershell
git diff --check
```

Si PHP esta instalado:

```powershell
php -l stable-1.5.1/captacion-app/functions.php
php -l stable-1.5.1/captacion-app/template-app-interactiva.php
```

## Pull requests

Cada pull request debe completar la checklist de `.github/pull_request_template.md`.

No aprobar cambios que:

- eliminen funcionalidad sin informe previo;
- suban ZIPs o credenciales;
- modifiquen login, registro o panel privado sin validacion;
- rompan el formato de tema WordPress.

## Versionado de despliegues

Los ZIPs de WordPress se generan localmente y no se suben a Git.

Formato recomendado:

```text
captacion-app-X.Y.Z-descripcion-wp.zip
```

El ZIP instalable debe contener `captacion-app/style.css`.
