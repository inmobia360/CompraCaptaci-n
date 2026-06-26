# AGENTS.md

Instrucciones obligatorias para cualquier agente, desarrollador o automatizacion que trabaje en este repositorio.

## Objetivo del proyecto

Captacion.app es un tema WordPress para una plataforma inmobiliaria profesional. Gestiona captaciones, demandas, marketplace, recursos, panel privado, notificaciones, reportes, datos territoriales y flujos de colaboracion.

## Fuente activa

La fuente activa del tema esta en:

`stable-1.5.1/captacion-app`

No usar la carpeta raiz `captacion-app/` como fuente principal. Es una copia local antigua ignorada por Git.

## Modo de trabajo

- Antes de modificar codigo importante, trabajar en modo PLAN.
- Explicar el alcance antes de tocar archivos funcionales.
- Modificar solo lo necesario para cumplir la tarea.
- No hacer refactors preventivos.
- No eliminar archivos, funciones, datos ni dependencias sin confirmacion explicita.
- Si se detectan duplicados, codigo muerto o dependencias sin uso, crear primero un informe en `docs/` y esperar aprobacion antes de borrar.

## Proteccion de funcionalidades existentes

No romper ni alterar sin solicitud directa:

- Login y registro profesional.
- Panel privado.
- Filtros de Marketplace y Busco captacion.
- Carruseles.
- Formularios de captacion, demanda, contacto y reportes.
- Integraciones con Rank Math, Forminator, Pods, LiteSpeed Cache, Mailchimp, Stripe o Captacion Core.
- Reglas de acceso para usuarios registrados y no registrados.

## Seguridad y privacidad

- No subir claves privadas, passwords, tokens, API keys ni credenciales.
- No incluir datos reales de usuarios en commits.
- No mostrar publicamente direccion exacta, propietario, telefono, documentos privados ni datos fiscales.
- Mantener protegidas las acciones que requieren usuario registrado.
- Revisar `.gitignore` antes de anadir archivos nuevos.

## WordPress

- Mantener cabecera valida en `style.css`.
- El ZIP instalable debe contener `captacion-app/style.css`.
- No versionar ZIPs generados.
- No depender de configuraciones locales no documentadas.
- Validar que los cambios sean compatibles con WordPress y PHP.

## UI y contenido

- Mantener estilo profesional, limpio y responsive.
- No introducir textos con problemas de codificacion.
- Mantener la propuesta principal: mas oportunidades de negocio inmobiliario mediante colaboracion profesional.
- No cambiar copy SEO/CRO validado si no se solicita.

## Match oferta-demanda

Cualquier cambio en coincidencias debe respetar:

- Habitaciones: margen de una unidad arriba o abajo.
- Banos: margen de una unidad arriba o abajo.
- Superficie: margen maximo del 10%.
- Presupuesto: margen maximo del 20%.
- Territorio: al menos misma comunidad autonoma y provincia.

## Git

- Crear commits pequenos y descriptivos.
- Ejecutar antes de subir:

```powershell
git status --short --branch
git diff --check
```

- No usar `git reset --hard` ni comandos destructivos sin confirmacion expresa.
- Mantener la rama `main` estable.
