# Informe de duplicados, codigo muerto y dependencias

Fecha: 2026-06-26

## Resultado

No se ha eliminado ningun archivo.

## Duplicados detectados

### Carpeta local antigua `captacion-app/`

Existe una carpeta `captacion-app/` en la raiz del proyecto. Esta carpeta contiene una version anterior del tema y no esta versionada en Git porque esta ignorada en `.gitignore`.

Fuente activa versionada:

```text
stable-1.5.1/captacion-app
```

Accion recomendada:

- Mantenerla fuera de Git.
- Eliminarla solo si se confirma que ya no se necesita como respaldo local.

Estado actual:

- No eliminada.
- Ignorada por Git.
- Documentada como duplicado local.

## ZIPs de despliegue

Existen varios ZIPs locales de despliegue WordPress en la raiz del proyecto. No se versionan en Git.

Accion recomendada:

- Mantener los ZIPs como artefactos locales o en Drive.
- No subirlos al repositorio.
- Generar un ZIP nuevo solo cuando haya una version lista para WordPress.

## Codigo muerto

No se ha eliminado codigo muerto.

Para detectar codigo muerto real hace falta una revision funcional del tema, especialmente en:

- `functions.php`
- `template-app-interactiva.php`
- flujos de login/registro
- Marketplace
- Busco captacion
- Ofrecer captacion
- panel privado
- integraciones externas

Cualquier eliminacion debe hacerse en una tarea separada, con informe previo y confirmacion.

## Dependencias sin uso

No se detectaron gestores de dependencias versionados como:

- `package.json`
- `composer.json`
- `vendor/`
- `node_modules/`

No se propone eliminar dependencias.
