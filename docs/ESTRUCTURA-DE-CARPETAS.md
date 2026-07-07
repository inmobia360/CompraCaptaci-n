# Estructura de carpetas recomendada

## Matriz oficial
- Repositorio: `https://github.com/inmobia360/CompraCaptaci-n`
- Rama base: `main`
- Ruta fuente activa: `stable-1.5.1/captacion-app`

## Estructura recomendada
```text
CompraCaptaci-n/
  stable-1.5.1/
    captacion-app/
      style.css
      functions.php
      template-app-interactiva.php
      media/
      assets/
  docs/
  AGENTS.md
```

## Regla por tipo de cambio
- UI/home/hero/vídeo/rutas hash: `stable-1.5.1/captacion-app/template-app-interactiva.php`
- lógica WordPress/hooks/REST/helpers: `stable-1.5.1/captacion-app/functions.php`
- medios del tema: `stable-1.5.1/captacion-app/media/`
- documentación operativa: `docs/` o la carpeta documental local definida por el proyecto

## Qué evitar
- carpetas duplicadas del tema fuera del repo
- ZIPs usados como fuente editable
- assets sueltos sin trazabilidad a commit
- documentación crítica dispersa en varias rutas sin una referencia clara

## Regla corta
> Una sola fuente editable, un solo repo matriz, una sola ruta activa del tema.
