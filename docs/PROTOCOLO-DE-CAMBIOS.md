# Protocolo de cambios — Compra Captación

## Flujo obligatorio para futuros cambios

### 1. Localizar la fuente correcta
Trabajar en:
- repo: `CompraCaptaci-n`
- rama: `main` o una rama de trabajo
- ruta: `stable-1.5.1/captacion-app`

### 2. Hacer el cambio
Modificar solo lo necesario.

### 3. Verificar
Antes de publicar, comprobar como mínimo:
- que el archivo editado contiene exactamente el cambio esperado
- que no se han arrastrado cambios colaterales
- que el tema sigue teniendo estructura WordPress válida

### 4. Guardar en Git
Secuencia recomendada:
- `git status --short --branch`
- `git diff --check`
- `git add ...`
- `git commit -m "mensaje claro"`
- `git push`

### 5. Generar ZIP instalable
El ZIP debe contener dentro la carpeta:
- `captacion-app/`

Y dentro de ella, al menos:
- `style.css`
- `functions.php`
- `template-app-interactiva.php`

### 6. Subir a WordPress
Ruta habitual:
- **Apariencia → Temas → Añadir tema → Subir tema**

### 7. Validar online
Comprobar la web final y confirmar que el cambio visible coincide con el commit subido.

## Criterio de oro
Si un cambio no está en GitHub, **no forma parte de la matriz oficial**.
