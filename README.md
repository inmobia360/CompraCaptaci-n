# Captacion.app

Repositorio oficial del tema WordPress de Captacion.app, plataforma inmobiliaria B2B para publicar captaciones, registrar demandas activas, cruzar oportunidades, gestionar colaboraciones profesionales y operar un panel privado con trazabilidad.

## Estado

- GitHub: `inmobia360/captacion-app`
- Rama estable: `main`
- Fuente activa: `stable-1.5.1/captacion-app`
- Tema WordPress: `Captacion.app`
- Version del tema: ver `stable-1.5.1/captacion-app/style.css`
- Despliegue objetivo: WordPress / Hostinger

## Estructura

```text
.
+-- AGENTS.md
+-- README.md
+-- .github/
|   +-- pull_request_template.md
|   +-- workflows/
|       +-- validate-theme.yml
+-- captacion-os/
|   +-- manual/
|   +-- spec/
|   +-- skills/
|   +-- commands/
|   +-- knowledge/
+-- docs/
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

## Regla principal

Antes de modificar codigo importante, leer:

- `AGENTS.md`
- `captacion-os/manual/WORKFLOW.md`
- `docs/CONDICIONES-ANALISIS-CAPTACION-APP.md`

No se eliminan archivos, funcionalidades, tablas ni dependencias sin informe previo y aprobacion.

## Flujo Git recomendado

- `main`: produccion/estable. Solo cambios revisados.
- `develop`: integracion previa a produccion.
- `feature/nombre-cambio`: cambios concretos.

Ejemplo:

```powershell
git checkout -b feature/seo-marketplace
git status --short --branch
git add .
git commit -m "Ajustar SEO Marketplace"
git push -u origin feature/seo-marketplace
```

## Seguridad

No se versiona:

- `wp-config.php`
- `.env`
- claves privadas
- tokens/API keys
- uploads
- backups
- caches
- logs
- dumps SQL
- ZIPs de despliegue

La configuracion sensible debe vivir en WordPress/Hostinger, no en Git.

## Validacion local

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

El ZIP instalable debe contener:

```text
captacion-app/
+-- style.css
+-- functions.php
+-- template-app-interactiva.php
+-- ...
```

No subir a WordPress un ZIP donde `style.css` quede dentro de una carpeta adicional.

## Documentacion

- `AGENTS.md`: reglas obligatorias para agentes y desarrolladores.
- `captacion-os/`: sistema operativo documental para Codex, Antigravity y GitHub.
- `docs/`: informes, seguridad, estructura, workflow y hoja de ruta.
- `.github/`: plantillas y validaciones automaticas.
