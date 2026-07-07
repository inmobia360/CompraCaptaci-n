# SOP / Manual interno — Compra Captación

## 1. Propósito
Establecer un procedimiento único para modificar, validar, versionar y desplegar Compra Captación sin perder trazabilidad.

## 2. Fuente de verdad
- Repositorio matriz: `https://github.com/inmobia360/CompraCaptaci-n`
- Rama base: `main`
- Ruta activa del tema: `stable-1.5.1/captacion-app`

## 3. Principio operativo
Todo cambio debe nacer en Git, verificarse en Git, quedar registrado en Git y solo después desplegarse en WordPress.

## 4. Flujo estándar
### 4.1 Preparación
- confirmar alcance del cambio
- abrir el repo correcto
- revisar archivos relevantes
- evitar tocar zonas no afectadas

### 4.2 Ejecución
- editar el archivo correcto
- mantener cambios mínimos
- no mezclar refactors no pedidos
- conservar coherencia visual y funcional

### 4.3 Verificación
- revisar diff
- revisar ausencia de cambios colaterales
- comprobar comportamiento esperado
- validar que el cambio está realmente en la fuente oficial

### 4.4 Versionado
- `git status --short --branch`
- `git diff --check`
- `git add ...`
- `git commit -m "mensaje claro"`
- `git push`

### 4.5 Empaquetado y despliegue
- generar ZIP desde `stable-1.5.1/captacion-app`
- confirmar estructura correcta del ZIP
- subir el ZIP a WordPress
- validar resultado online

## 5. Reglas de control
- no usar ZIPs como fuente editable
- no dejar cambios solo en WordPress
- no desplegar sin poder identificar el commit de origen
- no trabajar desde copias paralelas sin reflejo en GitHub

## 6. Reglas por tipo de cambio
- home / hero / vídeo / rutas hash: `template-app-interactiva.php`
- hooks / REST / lógica compartida: `functions.php`
- medios del tema: `media/`

## 7. Criterio de cierre
Un cambio se considera cerrado solo si:
- está validado visual o funcionalmente
- está en GitHub
- el despliegue puede trazarse al commit exacto

## 8. Regla ejecutiva
> Primero Git, después ZIP, después WordPress.
