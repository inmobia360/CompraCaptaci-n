# Workflow operativo

## Principio

Captacion.app se trabaja con plan previo, cambios acotados y validacion antes de commit.

## Secuencia obligatoria

1. Leer `AGENTS.md`.
2. Revisar estado Git:

```powershell
git status --short --branch
```

3. Analizar archivos afectados.
4. Proponer plan.
5. Esperar aprobacion.
6. Modificar solo lo necesario.
7. Validar:

```powershell
git diff --check
```

8. Documentar cambios relevantes.
9. Crear commit claro.
10. Subir a GitHub solo si procede.

## Prohibiciones

- No modificar `wp-config.php`.
- No subir `uploads`, caches, logs, backups, ZIPs ni dumps SQL.
- No eliminar archivos sin informe previo.
- No cambiar login, registro, panel privado, pagos o datos sensibles sin plan aprobado.

## Despliegue seguro

El despliegue hacia WordPress/Hostinger se realiza mediante ZIP de tema validado. El ZIP no se versiona en Git.
