# Start here — Compra Captación

## Si vienes nuevo a este repo
Este es el punto de entrada recomendado para cualquier desarrollador o IA que vaya a tocar Compra Captación.

## Fuente oficial
- Repositorio matriz: `https://github.com/inmobia360/CompraCaptaci-n`
- Rama base: `main`
- Ruta activa del tema: `stable-1.5.1/captacion-app`

## Orden de lectura recomendado
1. `../AGENTS.md`
2. `MATRIZ-OFICIAL.md`
3. `PROTOCOLO-DE-CAMBIOS.md`
4. `CHECKLIST-PUBLICACION-QA.md`
5. `DESPLIEGUE-WORDPRESS.md`
6. `ESTRUCTURA-DE-CARPETAS.md`
7. `PLANTILLA-EJECUTIVA-DESARROLLADOR.md`
8. `INDICE-MAESTRO.md`
9. `SOP-MANUAL-INTERNO.md`

## Reglas rápidas
- trabaja solo sobre la matriz oficial
- modifica solo lo necesario
- no uses ZIPs como fuente editable
- no dejes cambios solo en WordPress
- si no está en GitHub, no forma parte de la matriz oficial

## Archivo que normalmente tocarás primero
Para cambios de home, hero, vídeo, CTAs o `#/inicio`:
- `stable-1.5.1/captacion-app/template-app-interactiva.php`

Para lógica global, hooks o REST:
- `stable-1.5.1/captacion-app/functions.php`

## Flujo corto
1. localizar archivo correcto
2. hacer cambio mínimo
3. verificar
4. revisar diff
5. commit
6. push
7. generar ZIP si aplica
8. validar en web

## Regla de oro
> Primero Git, después ZIP, después WordPress.
