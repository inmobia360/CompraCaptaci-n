# Importaciones XML, CSV, JSON y webhook - 2026-06-29

## Alcance

Se amplio el sistema existente de feeds XML para que los usuarios registrados puedan importar captaciones desde archivos XML, CSV o JSON, mantener historial por lote, revisar propiedades incompletas y revertir importaciones.

## Backend

- `POST /captacion/v1/import/upload`: subida unificada de XML, CSV y JSON.
- `POST /captacion/v1/xml-feeds/import-file`: mantiene compatibilidad y usa el mismo handler unificado.
- `GET /captacion/v1/import/template`: descarga plantilla CSV de ejemplo.
- `POST /captacion/v1/import-batches/{import_batch_id}/rollback`: revierte un lote con soft-delete de sus registros.
- `POST /captacion/v1/webhook/receive`: recibe XML o JSON desde sistemas externos usando `X-Captacion-Webhook-Key`.

## Seguridad

- Archivos limitados a 10 MB y 1.000 registros.
- XML con `DOCTYPE` o `ENTITY` bloqueado para evitar XXE.
- Archivos guardados en `wp-content/uploads/captacion-imports/` mediante `wp_handle_upload`.
- Webhook protegido con API key configurada en el panel Captacion.app.
- Rollback solo permitido al propietario del lote o administradores.

## Formatos soportados

- XML nativo `captacionData`.
- XML externo con nodos `property`, `realty`, `offer`, `listing`, `item`, `ad`, `object`, `estate`.
- CSV con cabeceras flexibles y alias en espanol/ingles.
- JSON como array raiz o bajo `properties`, `propiedades`, `listings`, `items`, `data` o `records`.

## Notas operativas

- Si faltan campos de marketplace, la propiedad queda en `pending_review` para revisar antes de publicar.
- CSV y JSON usan el mismo modelo interno que XML: `captacion_app_records` + `captacion_import_batches`.
- El procesamiento sigue siendo sincronico con limite de 1.000 registros; para feeds superiores se recomienda dividir archivos o incorporar Action Scheduler en una fase posterior.
