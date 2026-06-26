# Skill: GitHub + Codex

## Responsabilidad

Mantener el repositorio preparado para trabajo asistido con Codex, Antigravity y GitHub.

## Reglas

- Leer `AGENTS.md`.
- Trabajar en plan antes de codigo importante.
- Crear commits claros.
- Mantener `.gitignore` actualizado.
- No subir artefactos generados.
- Documentar decisiones tecnicas.

## Validaciones

```powershell
git status --short --branch
git diff --check
```

Si se modifica PHP, validar sintaxis en local o GitHub Actions.
