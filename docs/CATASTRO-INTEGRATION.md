# Integración Catastro

## Alcance

Se ha incorporado una primera capa de integración de Catastro en Captacion.app para el flujo de `Ofrecer captación` y la ficha de propiedad.

## Qué hace

- Permite introducir una referencia catastral opcional en el alta de captación.
- Valida localmente que la referencia tenga formato correcto de 20 caracteres alfanuméricos.
- Guarda la referencia protegida en el payload interno de la propiedad.
- Expone una versión enmascarada para la interfaz privada y la ficha.
- Añade accesos directos a la sede electrónica oficial de Catastro.
- Mantiene el dato fuera de la ficha pública completa.

## Reglas de seguridad

- No se expone la referencia completa en la vista pública.
- La referencia completa queda protegida en el flujo privado y en el almacenamiento interno.
- No se implementa scraping ni acceso a datos protegidos del titular.
- La integración queda preparada para ampliar después con visor, WMS o validación oficial si se acuerda.

## Puntos tocados

- `stable-1.5.1/captacion-app/functions.php`
- `stable-1.5.1/captacion-app/template-app-interactiva.php`
