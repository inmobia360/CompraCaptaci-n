# Reglas de seguridad

## Nunca versionar

- `wp-config.php`
- `.env`
- claves privadas
- tokens
- API keys reales
- dumps SQL
- uploads
- backups
- caches
- logs
- ZIPs generados

## Datos sensibles

No mostrar publicamente:

- direccion exacta;
- propietario;
- telefono;
- documentos privados;
- datos fiscales;
- credenciales;
- datos internos de operaciones.

## Accesos

Usuarios no registrados solo pueden ver informacion publica. Acciones operativas requieren login.

## Revision

Antes de commit:

```powershell
rg -n --hidden --glob '!/.git/**' --glob '!*.zip' "password|secret|api[_-]?key|token|Authorization|Bearer|BEGIN PRIVATE KEY" .
```
