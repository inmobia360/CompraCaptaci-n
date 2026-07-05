# Despliegue Hostinger + Docker + Hermes

## Objetivo
Tener el tema **Captacion.app** versionado en GitHub y desplegable en un **VPS de Hostinger** con Docker, dejando **Hermes** como capa separada de automatización.

## Arquitectura recomendada

| Capa | Rol |
|---|---|
| GitHub | Código fuente y control de versiones |
| VPS Hostinger | Ejecución del sitio |
| Docker / Docker Compose | Reproducibilidad del entorno |
| Hermes | Automatización, imports, mantenimiento, alertas |

## Reglas
- No subir secretos a GitHub.
- No versionar `wp-config.php`, `.env`, backups ni uploads.
- No mezclar Hermes dentro del core del WordPress.
- Mantener el tema montado desde `stable-1.5.1/captacion-app`.

## Comprobación mínima
1. Clonar el repo en el VPS.
2. Crear `deploy/hostinger/.env` desde `.env.example`.
3. Ejecutar `docker compose --env-file deploy/hostinger/.env -f deploy/hostinger/docker-compose.yml up -d`.
4. Abrir WordPress.
5. Activar el tema Captacion.app.
6. Validar login, marketplace e importación XML.

## Integración Hermes
Opciones recomendadas:

### Opción A — Hermes en el VPS como servicio aparte
- Instalar Hermes fuera del contenedor de WordPress.
- Usarlo para tareas programadas, logs, imports o checks.
- Conectarlo por HTTP/API o por archivos compartidos.

### Opción B — Hermes en tu máquina local
- Útil para desarrollo y revisión.
- El VPS solo ejecuta WordPress.

### Opción C — Hermes en un contenedor independiente
- Válido si quieres aislamiento.
- Debe usar su propia configuración y volumen persistente.

## Recomendación práctica
Para empezar:
- WordPress + MySQL en Docker
- Hermes fuera del stack de WordPress
- GitHub como fuente de verdad

## Siguientes pasos
- Añadir Nginx/Traefik si quieres HTTPS gestionado desde el VPS.
- Añadir un contenedor `wp-cli` si quieres automatizar activaciones y updates.
- Añadir un job de Hermes para verificar que el sitio responde y que el XML sigue importando bien.
