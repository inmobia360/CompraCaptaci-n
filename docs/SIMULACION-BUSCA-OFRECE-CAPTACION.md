# Simulación funcional de `Busca Captación` y `Ofrece Captación`

## Objetivo

Validar el flujo real de la plataforma con dos usuarios distintos:

- `juansaint.af@gmail.com` como usuario que **busca captación**
- `companyandsolution@gmail.com` como usuario que **ofrece captación**

La simulación sigue la lógica que ya aplica el tema `captacion-app`:

- coincidencia por territorio
- margen de `±1` en habitaciones
- margen de `±1` en baños
- margen máximo del `10%` en superficie
- margen máximo del `20%` en presupuesto
- notificación interna cuando aparece una coincidencia
- contacto protegido dentro del panel privado

## Estado funcional actual

La revisión del código muestra que el tema ya dispone de:

- normalización de registros de oferta y demanda
- motor de compatibilidad entre captación y demanda
- listado de coincidencias en el marketplace
- suscripciones y alertas
- notificación protegida dentro del panel privado
- sala de comunicación interna sin exponer el contacto directo hasta completar el flujo

## Registro 1: `Busca Captación`

**Usuario:** `juansaint.af@gmail.com`

### Perfil simulado

- Tipo de usuario: profesional inmobiliario
- Acción principal: buscar una captación compatible
- Zona deseada: Ourense, Galicia
- Tipo de inmueble: Piso
- Operación: Venta
- Rango de precio objetivo: hasta `220.000 €`
- Habitaciones deseadas: `3`
- Baños deseados: `2`
- Superficie deseada: `100 m²` aprox.
- Estado: activo en el panel privado

### Necesidad registrada

La demanda se guarda como una búsqueda activa con estos criterios:

- `ccaa`: Galicia
- `province`: Ourense
- `municipality`: preferente, pero puede aceptar municipio vecino si el resto del match es fuerte
- `property_type`: Piso
- `budget`: `220.000 €`
- `min_rooms`: `3`
- `min_bathrooms`: `2`
- `desired_area_min_m2`: `100`

## Registro 2: `Ofrece Captación`

**Usuario:** `companyandsolution@gmail.com`

### Perfil simulado

- Tipo de usuario: captador / agencia colaboradora
- Acción principal: publicar una captación
- Zona: Ourense, Galicia
- Tipo de inmueble: Piso
- Precio indicativo: `179.000 €`
- Habitaciones: `3`
- Baños: `2`
- Superficie: `95 m²`
- Estado: publicado en Marketplace

### Oferta registrada

La captación entra con:

- `ccaa`: Galicia
- `province`: Ourense
- `municipality`: Ourense
- `property_type`: Piso
- `price`: `179.000 €`
- `rooms`: `3`
- `bathrooms`: `2`
- `surface`: `95 m²`

## Secuencia de la coincidencia

### Paso 1. Publicación de la captación

El usuario `companyandsolution@gmail.com` crea la oferta.

Resultado esperado:

- la ficha se publica en Marketplace
- la oferta queda visible en el panel privado
- el sistema recalcula posibles matches

### Paso 2. Evaluación automática

El motor compara la oferta con la demanda de `juansaint.af@gmail.com`.

Aplicación de reglas:

- territorio: coincide `Galicia / Ourense`
- habitaciones: `3` vs `3`, dentro del margen
- baños: `2` vs `2`, dentro del margen
- superficie: `95 m²` frente a `100 m²`, diferencia del `5%`
- presupuesto: `179.000 €` frente a `220.000 €`, dentro del margen

Resultado:

- coincidencia válida
- score alto
- oportunidad apta para comunicación protegida

### Paso 3. Notificación

La plataforma genera un aviso interno para ambos usuarios.

Para `juansaint.af@gmail.com`:

- aparece una notificación de nueva coincidencia
- se indica que la oportunidad puede revisarse sin exponer contactos

Para `companyandsolution@gmail.com`:

- aparece un aviso de demanda compatible
- se invita a revisar el expediente y continuar el flujo protegido

### Paso 4. Panel privado

En el panel privado de ambos perfiles:

- se registra la coincidencia
- se mantiene la trazabilidad
- se habilita la sala de comunicación interna
- el contacto directo sigue oculto hasta completar el flujo de validación

### Paso 5. Comunicación protegida

La plataforma abre un hilo interno asociado a la operación.

Lo que ve cada usuario:

- título de la oportunidad
- estado del flujo
- historial de acciones
- trazabilidad de avisos

Lo que no ve todavía:

- datos sensibles no necesarios
- contacto directo sin pasar por el flujo protegido

## Resultado funcional esperado

La simulación debe terminar con:

- una captación publicada correctamente
- una demanda activa correctamente registrada
- una coincidencia detectada
- notificaciones creadas
- trazabilidad guardada
- acceso a comunicación protegida

## Lectura práctica para pruebas

Si queremos validar el sistema como beta, este caso sirve como prueba mínima real:

1. crear la demanda de `juansaint.af@gmail.com`
2. crear la captación de `companyandsolution@gmail.com`
3. comprobar que el marketplace detecta el match
4. comprobar que el panel privado muestra la notificación
5. comprobar que la comunicación se abre de forma protegida

## Conclusión

Sí, el flujo es funcional a nivel de arquitectura y de UI interna.
La plataforma ya contempla el ciclo completo de:

- alta
- publicación
- búsqueda
- match
- aviso
- trazabilidad
- comunicación protegida

La siguiente mejora natural, si queremos llevarlo a producción fuerte, sería asegurar que estos estados no dependan solo de estado local de navegador y queden persistidos de forma central en WordPress/backend.
