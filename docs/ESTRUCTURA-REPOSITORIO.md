# Estructura del repositorio

## Resumen

El repositorio contiene la version activa del tema WordPress de Captacion.app y documentacion operativa para trabajar mediante GitHub.

## Fuente activa

```text
stable-1.5.1/captacion-app
```

Aunque la carpeta se llama `stable-1.5.1`, el tema puede tener una version superior indicada en `style.css`. Esta carpeta es la fuente que debe modificarse.

## Carpetas principales

```text
.github/
```

Configuracion de GitHub: plantilla de pull request y validaciones automaticas.

```text
docs/
```

Documentacion del proyecto, criterios de analisis, seguridad, flujo de trabajo y hoja de ruta.

```text
stable-1.5.1/captacion-app/
```

Tema WordPress activo.

```text
stable-1.5.1/captacion-app/media/
```

Logos, favicon, video y recursos visuales por defecto.

```text
stable-1.5.1/captacion-app/recursos/
```

PDFs legales y plantillas descargables.

```text
stable-1.5.1/captacion-app/src/data/
```

Datos locales, como la base territorial de Espana.

```text
stable-1.5.1/captacion-app/tools/
```

Scripts de apoyo para importacion o generacion de datos.

## Elementos no versionados

La carpeta raiz `captacion-app/` existe localmente como copia antigua y esta ignorada por Git. No debe usarse como fuente principal.

Los ZIPs de despliegue tambien estan ignorados por Git. Se generan localmente para subir a WordPress, pero no forman parte del repositorio.

## Duplicados detectados

- `captacion-app/`: copia local antigua no versionada.
- `stable-1.5.1/captacion-app/`: fuente activa versionada.

No se elimina ningun duplicado sin aprobacion expresa.
