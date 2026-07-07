# Matriz oficial — Compra Captación

## Decisión oficial
La matriz oficial del proyecto es:

- **Repositorio:** `https://github.com/inmobia360/CompraCaptaci-n`
- **Rama estable:** `main`
- **Ruta fuente activa:** `stable-1.5.1/captacion-app`

## Qué significa esto
La versión que debe considerarse editable, mantenible y reutilizable para el futuro sale de esa ruta dentro del repo.

## Qué tocar normalmente
Para cambios de interfaz, home, hero, vídeo, CTAs, copy o comportamiento de las páginas hash (`#/inicio`, etc.), revisar primero:

- `stable-1.5.1/captacion-app/template-app-interactiva.php`

Para cambios de comportamiento global o hooks WordPress, revisar:

- `stable-1.5.1/captacion-app/functions.php`

## Qué NO debe volver a pasar
- trabajar desde una copia suelta como fuente principal
- corregir en WordPress y no devolverlo a Git
- generar ZIPs desde carpetas no vinculadas al repo matriz
- mezclar varias fuentes sin saber cuál gobierna la web real

## Regla operativa corta
> Primero Git, después ZIP, después WordPress.
