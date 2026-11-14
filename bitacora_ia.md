# Bitácora de uso de IA

**Proyecto:** Floristería Darcy  
**Estudiante:** Samuel Santiago Cabrera Castro  
**Funcionalidad:** HU-01 – Registrar productos e insumos  
**Asistente usado:** ChatGPT – GPT-6, modo conversación.

## Entradas

### Entrada 1. Selección de la historia de usuario

- **Fecha:** 2026-11-07
- **Prompt o sugerencia:** Seleccionar una historia de usuario Must del proyecto Floristería Darcy que permita desarrollar una funcionalidad pequeña y comprobarla mediante pruebas automatizadas.
- **Decisión:** Aceptada.
- **Por qué:** Se eligió HU-01, registrar productos e insumos, porque permite implementar una función concreta con validaciones para el nombre, la categoría, la cantidad y el precio.
- **Verificación:** Se revisaron los requisitos de la historia y se definieron sus entradas, salidas y condiciones de validación.
- **Commit relacionado:** `7f21b66` – test: agregar pruebas iniciales de HU-01.

### Entrada 2. Diseño de las pruebas automatizadas

- **Fecha:** 2026-11-07
- **Prompt o sugerencia:** Proponer pruebas automatizadas para comprobar el registro de productos, incluyendo casos normales, valores límite y datos inválidos.
- **Decisión:** Aceptada con cambios.
- **Por qué:** Se utilizaron los casos propuestos y se ampliaron las pruebas para incluir categorías vacías, precios iguales a cero y precios negativos.
- **Verificación:** Se ejecutaron las pruebas con PHPUnit antes de implementar la función y se comprobó que fallaban porque la función todavía no estaba definida.
- **Commit relacionado:** `7f21b66` – test: agregar pruebas iniciales de HU-01.

### Entrada 3. Implementación de la funcionalidad

- **Fecha:** 2026-11-07
- **Prompt o sugerencia:** Generar la función registrarProducto en PHP, utilizando el contrato definido y las pruebas automatizadas como referencia.
- **Decisión:** Aceptada.
- **Por qué:** La implementación utilizó los tipos de datos definidos, validó los campos obligatorios, rechazó cantidades negativas y precios iguales o inferiores a cero, y devolvió los datos del producto en un arreglo.
- **Verificación:** Se ejecutó PHPUnit y las ocho pruebas fueron aprobadas, con doce aserciones.
- **Commit relacionado:** `9f8b884` – feat: implementar registro de productos HU-01.

### Entrada 4. Refactorización del código

- **Fecha:** 2026-11-07
- **Prompt o sugerencia:** Mejorar la función implementada evitando repetir operaciones trim() y agrupando las condiciones de validación para simplificar el código.
- **Decisión:** Aceptada.
- **Por qué:** La propuesta permitió mejorar la organización y legibilidad del código sin cambiar las reglas de negocio ni modificar las pruebas.
- **Verificación:** Se ejecutaron nuevamente las ocho pruebas automatizadas y todas continuaron aprobadas, con doce aserciones.
- **Commit relacionado:** `01a82a6` – refactor: mejorar validaciones de HU-01.

### Entrada 5. Organización de la documentación

- **Fecha:** 2026-11-07
- **Prompt o sugerencia:** Inicialmente se indicó guardar la bitácora de IA dentro de la carpeta docs del repositorio.
- **Decisión:** Rechazada.
- **Por qué:** Al revisar la plantilla oficial entregada por el profesor, se comprobó que el archivo bitacora_ia.md debía estar ubicado en la raíz del repositorio y no dentro de la carpeta docs.
- **Verificación:** Se consultaron las instrucciones de la Semana 5 y se comprobó que el archivo quedó ubicado en la raíz del repositorio.
- **Commit relacionado:** `fb80921` – docs: agregar bitacora de uso de IA.

## Resumen

| Decisión | Cantidad |
|---|---:|
| Aceptadas | 3 |
| Aceptadas con cambios | 1 |
| Rechazadas | 1 |
| **Total** | **5** |

## Errores que atraparon las pruebas

Durante la fase roja, las pruebas detectaron que la función registrarProducto todavía no existía. PHPUnit mostró tres errores por llamadas a una función indefinida y cinco fallos porque no se generaba la excepción InvalidArgumentException esperada. Esto permitió comprobar que las pruebas identificaban la funcionalidad faltante antes de escribir la implementación. Después de implementar la función, las ocho pruebas pasaron correctamente.

## Reflexión final (5 a 8 líneas)

El asistente de IA me ayudó principalmente a organizar la historia de usuario, diseñar las pruebas y desarrollar la función de registro de productos en PHP.  
También me permitió comprender cómo aplicar la metodología TDD mediante las fases roja, verde y refactorización.  
Las pruebas detectaron inicialmente que la función no estaba implementada y permitieron comprobar posteriormente las validaciones de los datos.  
Aprendí la importancia de ejecutar las pruebas antes y después de modificar el código.  
La refactorización permitió mejorar la organización de la función sin alterar su comportamiento.  
En una próxima funcionalidad dedicaría más tiempo a revisar el contrato y los casos límite antes de comenzar a programar.  
Finalmente, comprendí que las sugerencias de la IA deben revisarse y verificarse antes de incorporarlas al proyecto.

## Entradas correspondientes a la semana 6

### Entrada 6. Revisión de calidad y seguridad del código

- **Fecha:** 2026-11-14
- **Prompt o sugerencia:** Revisar la función `registrarProducto` como parte de una evaluación profesional de calidad y seguridad del código.
- **Decisión:** Aceptada.
- **Por qué:** Se realizó una revisión de las validaciones, los tipos de datos, el manejo de entradas y la documentación de la función.
- **Verificación:** Se ejecutó PHP_CodeSniffer y se revisó manualmente el código fuente.
- **Resultado:** Se identificaron oportunidades de mejora en la validación de precios no finitos y espacios Unicode.

### Entrada 7. Segunda revisión con IA

- **Fecha:** 2026-11-14
- **Prompt o sugerencia:** Solicitar una segunda revisión independiente de la función para identificar posibles errores de validación y oportunidades de mejora.
- **Decisión:** Aceptada con cambios.
- **Por qué:** La revisión identificó que los valores `NAN` e infinito podían superar la validación del precio y que `trim()` no eliminaba determinados espacios Unicode.
- **Verificación:** Se reprodujeron ambos comportamientos mediante comandos PHP antes de modificar el código.
- **Resultado:** Los dos hallazgos fueron confirmados.

### Entrada 8. Corrección de los hallazgos

- **Fecha:** 2026-11-14
- **Prompt o sugerencia:** Corregir las validaciones detectadas y ampliar las pruebas automatizadas.
- **Decisión:** Aceptada.
- **Por qué:** Se incorporó la validación de precios finitos y la normalización de espacios Unicode en nombre y categoría.
- **Verificación:** Se ejecutó PHPUnit y se obtuvieron 10 pruebas aprobadas y 14 aserciones. PHPCS finalizó sin errores ni advertencias y Composer Audit no encontró avisos de vulnerabilidades conocidas.

### Entrada 9. Actualización del README y documentación

- **Fecha:** 2026-11-14
- **Prompt o sugerencia:** Actualizar el README para reflejar las validaciones actuales, las herramientas de calidad y los comandos de ejecución.
- **Decisión:** Aceptada.
- **Por qué:** La documentación anterior no contemplaba todos los casos límite encontrados durante la revisión.
- **Verificación:** Se actualizaron las instrucciones de ejecución de PHPUnit, PHPCS y Composer Audit.

## Reflexión sobre la revisión de calidad y seguridad

La revisión del código permitió identificar problemas que no estaban contemplados en las pruebas iniciales. La segunda revisión con IA señaló dos situaciones relacionadas con los valores no finitos y los espacios Unicode. Antes de aplicar las recomendaciones, reproduje los comportamientos para comprobar que realmente existían. Después corregí la función y amplié las pruebas automatizadas. Finalmente, ejecuté las herramientas de calidad y seguridad para comprobar el resultado de los cambios. Esta actividad me permitió comprender la importancia de revisar y verificar las sugerencias de la inteligencia artificial antes de aplicarlas.