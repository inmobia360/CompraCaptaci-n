# Plan SaaS sobre WordPress

Este documento fija la estructura recomendada para evolucionar Captacion.app como herramienta SaaS inicial desplegada en WordPress, usando la fuente activa del tema:

```text
stable-1.5.1/captacion-app
```

## Objetivo

Lanzar una primera version comercial sin rehacer la plataforma desde cero, aprovechando WordPress para web, usuarios, contenido, panel privado inicial, pagos y captacion de leads.

## Estructura de despliegue

- Dominio principal: WordPress con el tema `Captacion.app`.
- Tema activo: `stable-1.5.1/captacion-app`.
- ZIP instalable: debe contener `captacion-app/style.css` en la raiz interna.
- Pagos iniciales: Stripe Payment Links configurados desde el administrador.
- Contactos y leads: Mailchimp o CRM conectado desde servidor.
- Usuarios: usuarios nativos de WordPress para registro y login profesional.
- Datos sensibles: nunca versionados en Git; se configuran en WordPress o Hostinger.

## Fases

### Fase 1 - MVP WordPress

- Activar el tema en WordPress/Hostinger.
- Configurar textos principales, email, Stripe y Mailchimp desde el panel `Captacion.app`.
- Validar registro, login, contacto, marketplace, demandas, captaciones, recursos y panel privado.
- Revisar aviso legal, privacidad, cookies y cumplimiento de acceso a datos sensibles.

### Fase 2 - SaaS operativo

- Convertir captaciones, demandas, accesos, favoritos, notificaciones y reportes en datos persistentes de WordPress.
- Definir planes comerciales, limites de uso y permisos por perfil.
- Confirmar pagos con webhook seguro antes de desbloquear datos privados.
- Registrar actividad relevante para trazabilidad profesional.

### Fase 3 - Aplicacion separada

- Mantener WordPress como web comercial y SEO.
- Crear `app.dominio.com` solo cuando el producto necesite backend propio, mayor automatizacion o escalado multiusuario avanzado.
- Migrar progresivamente API, base de datos, pagos y panel SaaS sin romper la web existente.

## Reglas de seguridad

- No mostrar direccion exacta, propietario, telefono, documentos privados ni datos fiscales en zonas publicas.
- No permitir acciones protegidas a visitantes no registrados.
- No guardar claves de Stripe, Mailchimp ni otros servicios en Git.
- No depender de `localStorage` como persistencia final de produccion.
- Documentar cualquier migracion en `captacion-os/spec/` antes de implementarla.

## Siguiente trabajo recomendado

1. Auditar `stable-1.5.1/captacion-app` contra los criterios de `docs/CONDICIONES-ANALISIS-CAPTACION-APP.md`.
2. Preparar checklist de despliegue Hostinger y validacion WordPress.
3. Identificar que funciones siguen siendo demo/localStorage y priorizar persistencia real.
4. Definir los planes SaaS finales y su correspondencia con Stripe.
