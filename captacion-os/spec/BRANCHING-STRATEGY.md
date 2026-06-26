# Estrategia de ramas

## Ramas

### `main`

Rama estable. Debe representar codigo apto para despliegue o base de despliegue.

Reglas:

- No trabajar cambios grandes directamente aqui.
- Proteger con pull requests cuando sea posible.
- Exigir validacion GitHub Actions.

### `develop`

Rama de integracion previa a produccion.

Uso:

- Agrupar features aprobadas.
- Validar compatibilidad antes de pasar a `main`.

### `feature/nombre-cambio`

Rama para tareas concretas.

Ejemplos:

- `feature/seo-recursos`
- `feature/match-notificaciones`
- `feature/dashboard-eur`

## Flujo recomendado

```powershell
git checkout main
git pull
git checkout -b feature/nombre-cambio
```

Al terminar:

```powershell
git status --short --branch
git diff --check
git add .
git commit -m "Descripcion clara"
git push -u origin feature/nombre-cambio
```

Luego abrir pull request hacia `develop` o `main` segun criticidad.
