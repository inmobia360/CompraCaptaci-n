# Condiciones de analisis para Captacion.app

Este documento fija los criterios que deben revisarse antes de modificar, desplegar o aprobar cambios en Captacion.app.

## 1. Alcance de los cambios

- Modificar solo las secciones solicitadas.
- No refactorizar codigo estable si no es necesario para el cambio pedido.
- No alterar login, registro, panel privado, filtros, carruseles, compra/acceso ni navegacion salvo indicacion expresa.
- Mantener la fuente activa del tema en `stable-1.5.1/captacion-app`.
- No subir ZIPs generados al repositorio.

## 2. WordPress y despliegue

- El ZIP instalable debe contener `captacion-app/style.css` en la raiz interna del paquete.
- Validar que `style.css` tenga cabecera de tema WordPress y version correcta.
- Mantener compatibilidad con Rank Math SEO, LiteSpeed Cache, Forminator, Pods y Captacion Core.
- No depender de configuraciones locales no documentadas.
- Si se crea un paquete de despliegue, debe probarse que WordPress lo reconoce como tema.

## 3. SEO y CRO

- Cada pagina principal debe tener titulo, descripcion, palabra clave objetivo y contenido suficiente.
- Mantener textos orientados a busquedas inmobiliarias profesionales: captaciones, demandas, marketplace, colaboracion inmobiliaria, recursos IA, planes y contacto.
- Los CTA deben ser claros y coherentes:
  - Crear cuenta gratuita
  - Comenzar gratis
  - Publicar busqueda
  - Publicar captacion
  - Acceder al Marketplace
- No introducir contenido que contradiga la propuesta comercial principal: mas oportunidades de negocio inmobiliario mediante colaboracion profesional.

## 4. Acceso y privacidad

- Los usuarios no registrados solo pueden ver informacion visual e informativa.
- Usuarios no registrados no deben ejecutar acciones protegidas:
  - Ver ficha completa
  - Ver mas detalles
  - Publicar captacion
  - Publicar demanda
  - Acceder a recursos gratuitos o de pago
  - Usar acciones de Marketplace
  - Entrar al panel privado
- No revelar datos sensibles en zonas publicas:
  - direccion exacta
  - propietario
  - telefono
  - documentos privados
  - datos fiscales
- El panel privado debe mostrar el nombre del usuario autenticado.

## 5. Match oferta-demanda

Los matches entre demandas y captaciones deben considerar criterios reales de compatibilidad:

- Habitaciones: una por encima o una por debajo de lo deseado.
- Banos: uno por encima o uno por debajo de lo deseado.
- Superficie: margen maximo del 10% arriba o abajo.
- Presupuesto: margen maximo del 20% arriba o abajo.
- Geolocalizacion: al menos misma comunidad autonoma y provincia.

Cuando no exista coincidencia:

- Informar al usuario de que recibira una alerta cuando aparezca una compatibilidad.
- Asociar la alerta al panel privado, seccion Notificaciones.
- Aplicar el mismo criterio tanto al crear una demanda como al crear una oferta/captacion.

## 6. Base de datos real

Deben estar preparados para persistencia real en WordPress:

- Captaciones
- Demandas
- Smart matches
- Reportes
- Notificaciones
- Accesos
- Historial de actividad
- Preferencias de usuario
- Favoritos
- Datos profesionales y fiscales
- Datos territoriales

Si una funcionalidad usa demo/localStorage temporalmente, debe quedar identificada como transitoria.

## 7. Email y notificaciones

- Nuevo usuario: email de bienvenida personalizado.
- Contacto: email de confirmacion indicando que el mensaje sera revisado.
- Reporte/canal de denuncia: email de tramite asociado a usuario registrado.
- Enviar copia interna a `inmobia360@gmail.com` cuando aplique.
- Integracion Mailchimp debe respetar etiquetas por origen:
  - registro
  - contacto
  - reporte
  - demanda
  - captacion

## 8. Formularios y territorio

- Usar selectores dependientes:
  - Comunidad autonoma
  - Provincia
  - Municipio
  - Codigo postal
  - Direccion/zona
- Mantener valores existentes al editar.
- No mostrar direccion exacta ni codigo postal completo en fichas publicas si puede comprometer informacion sensible.
- La base territorial debe poder sincronizarse con fuentes oficiales INE y validacion opcional CartoCiudad/CNIG.

## 9. UI, accesibilidad y calidad visual

- Mantener estetica profesional, limpia y sin colores agresivos.
- Fondos oscuros deben tener textos claros.
- Fondos claros deben tener textos oscuros.
- Badges, categorias, botones y metricas deben ser legibles.
- Evitar textos corruptos por codificacion en acentos, enes y simbolos.
- Mantener responsive en movil, tablet y escritorio.

## 10. Validacion tecnica antes de aprobar

- Revisar consola del navegador sin errores visibles.
- Validar enlaces principales y CTAs.
- Validar formularios: registro, contacto, captacion, demanda, perfil profesional.
- Validar acceso publico vs usuario registrado.
- Validar favoritos y persistencia.
- Validar calculos EUR cuando existan datos.
- Validar que no se suban secretos, credenciales, ZIPs o archivos temporales a Git.

## 11. Regla de Git

- Cada cambio relevante debe tener commit propio con mensaje claro.
- Antes de `git push`, ejecutar:

```powershell
git status --short --branch
```

- No hacer `git reset --hard` ni revertir cambios sin confirmacion expresa.
