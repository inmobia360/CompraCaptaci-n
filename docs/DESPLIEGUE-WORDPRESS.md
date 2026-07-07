# Despliegue WordPress desde la matriz oficial

## Origen válido
Siempre partir de:
- `https://github.com/inmobia360/CompraCaptaci-n`
- carpeta fuente: `stable-1.5.1/captacion-app`

## Objetivo
Generar un ZIP de tema WordPress instalable sin depender de carpetas temporales o copias antiguas.

## Estructura esperada del ZIP
El archivo ZIP final debe contener:

```text
captacion-app/
  style.css
  functions.php
  template-app-interactiva.php
  ...
```

## Checklist antes de empaquetar
- cambios confirmados en Git
- `style.css` presente
- `functions.php` presente
- `template-app-interactiva.php` presente
- medios/referencias necesarios presentes
- sin `.env`, logs, ZIPs viejos ni artefactos temporales

## Checklist después de subir a WordPress
- el tema instala sin error de `style.css`
- la home carga correctamente
- el hero se ve bien
- el vídeo del hero aparece si aplica
- la ruta `#/inicio` no queda vacía
- el cambio validado coincide con el commit de GitHub

## Regla de trazabilidad
Cada despliegue válido debe poder responder a esta pregunta:

> ¿De qué commit sale este ZIP?

Si no puede responderse, el despliegue no está suficientemente controlado.
