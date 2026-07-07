# Checklist de publicación y QA

## Antes del cambio
- [ ] Confirmar que se trabaja sobre la matriz oficial
- [ ] Confirmar ruta activa: `stable-1.5.1/captacion-app`
- [ ] Confirmar alcance exacto del cambio

## Antes del commit
- [ ] Revisar `git status --short --branch`
- [ ] Revisar `git diff --check`
- [ ] Confirmar que no hay cambios colaterales
- [ ] Confirmar que no se suben credenciales, logs o ZIPs

## Antes de generar ZIP
- [ ] `style.css` presente
- [ ] `functions.php` presente
- [ ] `template-app-interactiva.php` presente
- [ ] recursos multimedia necesarios presentes
- [ ] el cambio visible está en los archivos correctos

## Antes de subir a WordPress
- [ ] el ZIP contiene la carpeta `captacion-app/`
- [ ] el ZIP no contiene basura temporal
- [ ] se conoce el commit exacto del que sale el ZIP

## QA tras despliegue
- [ ] la home carga
- [ ] `#/inicio` no queda vacía
- [ ] el hero se ve correctamente
- [ ] el vídeo del hero aparece y reproduce si aplica
- [ ] CTAs visibles y correctos
- [ ] no hay errores visuales obvios en móvil y desktop
- [ ] el resultado en web coincide con el cambio del commit

## Criterio de cierre
- [ ] cambio validado en web
- [ ] cambio presente en GitHub
- [ ] ZIP trazable a commit
