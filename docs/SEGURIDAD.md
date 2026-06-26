# Seguridad

## Revision realizada

Se reviso el repositorio para detectar:

- passwords
- tokens
- API keys
- claves privadas
- credenciales Mailchimp/Stripe
- archivos `.env`
- `wp-config.php`
- certificados `.pem` o `.key`

## Resultado

No se detectaron credenciales reales versionadas.

Se encontraron referencias a nombres como `api_key`, `password`, `token` y `Authorization` dentro de `functions.php`, pero corresponden a codigo que gestiona credenciales introducidas desde WordPress, no a secretos hardcodeados.

## Reglas obligatorias

- No guardar claves reales en el repositorio.
- No subir `.env`, `wp-config.php`, certificados, tokens ni ZIPs generados.
- Usar campos de configuracion de WordPress para credenciales operativas.
- No publicar datos fiscales, telefonos, propietarios, documentos privados ni direcciones exactas.
- Mantener separadas las vistas publicas y privadas.

## Comando de revision recomendado

```powershell
rg -n --hidden --glob '!/.git/**' --glob '!*.zip' "password|secret|api[_-]?key|token|Authorization|Bearer|BEGIN PRIVATE KEY" .
```

Cada resultado debe revisarse antes de hacer commit.
