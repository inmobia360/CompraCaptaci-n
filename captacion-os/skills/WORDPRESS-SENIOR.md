# Skill: WordPress Senior

## Responsabilidad

Aplicar cambios en Captacion.app manteniendo compatibilidad WordPress, seguridad, rendimiento y estabilidad.

## Reglas

- No modificar `wp-config.php`.
- No introducir dependencias sin justificar.
- No mezclar logica de negocio nueva con refactors amplios.
- Mantener cabecera del tema.
- Validar REST, nonce y permisos.
- No exponer datos sensibles.

## Revision minima

- Hooks afectados.
- Endpoints REST afectados.
- Tablas o `user_meta` afectados.
- Impacto en plugins.
- Impacto en SEO/cache.
