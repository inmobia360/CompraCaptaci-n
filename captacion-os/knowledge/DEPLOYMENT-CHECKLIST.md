# Checklist de despliegue

## Antes de generar ZIP

- Rama correcta.
- `git status` limpio.
- Cambios commiteados.
- Sin secretos.
- Sin uploads/caches/logs/backups.
- PHP validado o pendiente documentado.

## ZIP WordPress

Debe contener:

```text
captacion-app/style.css
captacion-app/functions.php
captacion-app/template-app-interactiva.php
```

## Despues de subir

- Activar tema si aplica.
- Vaciar cache LiteSpeed/Hostinger.
- Revisar consola.
- Revisar Rank Math.
- Revisar login/registro.
- Revisar Marketplace, Busco captacion, Ofrecer captacion, Recursos y Panel privado.
