# Sincronización territorial

El tema guarda el catálogo oficial en la tabla `wp_captacion_territories` y mantiene
`src/data/territorios-espana.json` como alternativa local.

La tabla principal usa una fila por municipio con columnas explícitas de comunidad,
provincia y código INE. Los futuros códigos postales se almacenan en
`wp_captacion_territory_postal_codes`, permitiendo varios CP por municipio.

## Importar INE

```bash
wp captacion territory import /ruta/municipios.csv
wp captacion territory import /ruta/municipios.xlsx
```

El importador reconoce, entre otros, los encabezados oficiales `CPRO`, `CMUN`,
`CODIGO_INE` y `NOMBRE`.

`26codmun.xlsx` no contiene códigos postales. La importación INE 2026 deja
`codigo_postal` como `NULL` y no infiere ni inventa valores. La tabla postal solo
debe alimentarse con una fuente oficial adicional y cada CP debe tener 5 dígitos.

## Actualización anual

```bash
wp captacion territory update --source=/ruta/municipios-2027.csv
```

La fuente usada queda guardada en WordPress y puede reutilizarse omitiendo
`--source` en actualizaciones posteriores.

## Generar el JSON local

```bash
wp captacion territory export
wp captacion territory export /ruta/territorios-espana.json
```

El primer comando actualiza el JSON incluido en el tema. El segundo permite
generarlo en una ubicación alternativa.

## API

- `GET /wp-json/captacion/v1/territories`
- `GET /wp-json/captacion/v1/territories/provinces?community=13`
- `GET /wp-json/captacion/v1/territories/municipalities?province=28`
- `POST /wp-json/captacion/v1/address/validate`
- `POST /wp-json/captacion/v1/territories/import` (solo administradores y nonce)

La validación de dirección consulta CartoCiudad/CNIG mediante el servidor de
WordPress. Es orientativa y no publica la dirección exacta ni el código postal
completo en las fichas públicas. El endpoint aplica validación y rate limiting.
