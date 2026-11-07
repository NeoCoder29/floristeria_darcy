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