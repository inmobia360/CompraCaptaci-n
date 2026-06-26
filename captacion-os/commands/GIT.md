# Comandos Git seguros

## Estado

```powershell
git status --short --branch
git log --oneline -5
```

## Crear rama

```powershell
git checkout main
git pull
git checkout -b feature/nombre-cambio
```

## Commit

```powershell
git diff --check
git add .
git commit -m "Descripcion clara del cambio"
```

## Subir

```powershell
git push
```

Para primera subida de rama:

```powershell
git push -u origin feature/nombre-cambio
```

## Prohibido sin aprobacion

```powershell
git reset --hard
git clean -fd
git checkout -- .
```
