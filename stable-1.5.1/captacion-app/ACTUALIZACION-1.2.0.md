# Compra Captación 1.2.0

Fecha: 20 de junio de 2026.

## Formularios inmobiliarios

- Unifica los tipos de inmueble de "Ofrecer captacion" y "Busco captacion".
- Muestra habitaciones solo para vivienda y banos para vivienda, local, nave y oficina.
- Normaliza superficie, precio o presupuesto, comision, condicion, encargo, urgencia y documentacion.
- Exige titulos de al menos 8 caracteres y descripciones de al menos 30 caracteres.
- Evita repetir la explicacion de privacidad debajo del municipio.

## Persistencia y compatibilidad

- Valida y sanea en REST los registros `property` y `need` antes de guardarlos.
- Rechaza valores ajenos a los enums y responde con errores HTTP 422.
- Guarda claves canonicas y alias heredados para no romper marketplace, paneles ni edicion.
- Migra en lectura tipos antiguos, `superficie_construida` y reforma integral.
- Actualiza el matching con territorio, minimos de superficie y estancias, presupuesto, condicion, encargo y documentacion.

## Instalacion

El ZIP distribuible contiene la carpeta `captacion-app` y el archivo `style.css` en la raiz del tema. Debe instalarse desde Apariencia > Temas > Anadir tema > Subir tema.
