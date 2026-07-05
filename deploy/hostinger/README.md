# Hostinger VPS + Docker

Este directorio contiene una base de despliegue para ejecutar el tema **Captacion.app** dentro de un WordPress en VPS.

## Qué hace
- Levanta MySQL 8
- Levanta WordPress 6.6 + PHP 8.2 + Apache
- Monta el tema desde:
  `stable-1.5.1/captacion-app`
- Mantiene el tema en modo solo lectura dentro del contenedor

## Qué necesitas cambiar
1. Copia `.env.example` a `.env`
2. Rellena:
   - `WORDPRESS_DB_NAME`
   - `WORDPRESS_DB_USER`
   - `WORDPRESS_DB_PASSWORD`
   - `MYSQL_ROOT_PASSWORD`
   - `WORDPRESS_HOME_URL`
   - `WORDPRESS_SITE_URL`
3. Ajusta `HOST_HTTP_PORT` si el VPS ya usa el 80

## Arranque
Desde la raíz del repo:

```bash
docker compose --env-file deploy/hostinger/.env -f deploy/hostinger/docker-compose.yml up -d
```

## Primer acceso
- Abre la URL del VPS o el puerto que hayas elegido.
- Completa la instalación de WordPress.
- Activa el tema **Captacion.app** desde el panel.

## Importante
- Esto es una **base Docker para VPS**.
- No sustituye tu hosting actual si sigues en WordPress gestionado por Hostinger.
- Hermes debe ir **separado** del contenedor de WordPress.
