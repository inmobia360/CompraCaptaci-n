# Captacion.app

Repositorio del tema WordPress de Captacion.app, plataforma para colaboracion profesional inmobiliaria, gestion de captaciones, demandas, marketplace, recursos y panel privado.

## Estado del repositorio

- Repositorio GitHub: `inmobia360/captacion-app`
- Rama principal: `main`
- Fuente activa del tema: `stable-1.5.1/captacion-app`
- Version actual del tema: revisar `stable-1.5.1/captacion-app/style.css`
- Paquetes ZIP de despliegue: no se versionan en Git

## Estructura principal

```text
.
+-- AGENTS.md
+-- README.md
+-- .github/
|   +-- pull_request_template.md
|   +-- workflows/
|       +-- validate-theme.yml
+-- docs/
|   +-- CONDICIONES-ANALISIS-CAPTACION-APP.md
|   +-- ESTRUCTURA-REPOSITORIO.md
|   +-- HOJA-DE-RUTA.md
|   +-- SEGURIDAD.md
|   +-- WORKFLOW-GITHUB.md
+-- stable-1.5.1/
    +-- captacion-app/
        +-- functions.php
        +-- style.css
        +-- template-app-interactiva.php
        +-- media/
        +-- recursos/
        +-- src/data/
        +-- tools/
```

## Reglas de trabajo

Antes de modificar codigo importante, revisar `AGENTS.md`.

Principios obligatorios:

- No eliminar funcionalidad existente sin confirmacion.
- No refactorizar si el cambio solicitado no lo requiere.
- Trabajar cambios importantes primero en modo PLAN.
- Crear informe antes de eliminar duplicados, codigo muerto o dependencias sin uso.
- No subir claves, contrasenas, tokens, ZIPs generados ni archivos temporales.

## Validacion local recomendada

```powershell
git status --short --branch
git diff --check
```

Si PHP esta disponible:

```powershell
php -l stable-1.5.1/captacion-app/functions.php
php -l stable-1.5.1/captacion-app/template-app-interactiva.php
```

## Despliegue WordPress

El paquete instalable debe contener esta estructura interna:

```text
captacion-app/
+-- style.css
+-- functions.php
+-- ...
```

No subir a WordPress un ZIP que tenga `style.css` fuera de `captacion-app/` o dentro de una carpeta adicional.

## Documentacion

- [Reglas para agentes](AGENTS.md)
- [Condiciones de analisis](docs/CONDICIONES-ANALISIS-CAPTACION-APP.md)
- [Estructura del repositorio](docs/ESTRUCTURA-REPOSITORIO.md)
- [Seguridad](docs/SEGURIDAD.md)
- [Workflow GitHub](docs/WORKFLOW-GITHUB.md)
- [Hoja de ruta](docs/HOJA-DE-RUTA.md)
