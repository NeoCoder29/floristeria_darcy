# Informe de revisión de calidad y seguridad del código

**Proyecto:** Floristería Darcy  
**Estudiante:** Samuel Santiago Cabrera Castro  
**Funcionalidad revisada:** HU-01 – Registrar productos e insumos  
**Fecha:** 14 de noviembre de 2026

## 1. Objetivo

Realizar una revisión de calidad y seguridad del código de la funcionalidad de registro de productos e insumos del proyecto Floristería Darcy, utilizando herramientas de análisis, pruebas automatizadas y una segunda revisión con inteligencia artificial.

## 2. Alcance de la revisión

La revisión se realizó sobre el repositorio entregado en la semana 5, específicamente sobre la función `registrarProducto`, ubicada en `src/registrarProducto.php`, y sus pruebas automatizadas en `tests/RegistrarProductoTest.php`.

La funcionalidad permite registrar los datos básicos de un producto: nombre, categoría, cantidad y precio. La revisión se concentró en las validaciones de entrada, el manejo de datos inválidos, la legibilidad, la documentación y la calidad del código.

## 3. Herramientas utilizadas

- **PHP 8.2:** lenguaje de programación del proyecto.
- **PHPUnit 11.5.57:** ejecución de pruebas automatizadas.
- **PHP_CodeSniffer (PHPCS):** revisión del estilo y las convenciones del código.
- **Composer Audit:** comprobación de avisos de vulnerabilidades conocidas en las dependencias.
- **ChatGPT:** apoyo para la segunda revisión del código y la identificación de oportunidades de mejora.

## 4. Lista de revisión de código y seguridad

| Criterio revisado | Resultado | Observación |
|---|---|---|
| Propósito claro de la función | Cumple | Registra y valida un producto. |
| Tipos de datos definidos | Cumple | Utiliza string, int y float. |
| Validación de campos obligatorios | Cumple | Comprueba nombre y categoría. |
| Rechazo de cantidades negativas | Cumple | Se genera una excepción. |
| Validación de precios | Cumple | Rechaza valores iguales o inferiores a cero y valores no finitos. |
| Tratamiento de espacios Unicode | Cumple | Normaliza los espacios al inicio y al final. |
| Documentación de la función | Cumple | Se agregó un bloque DocBlock. |
| Pruebas automatizadas | Cumple | Las 10 pruebas fueron aprobadas. |
| Análisis de estilo | Cumple | PHPCS no reportó errores ni advertencias. |
| Auditoría de dependencias | Cumple | Composer Audit no encontró avisos de vulnerabilidades conocidas. |
| Consultas SQL | No aplica | La función revisada no ejecuta consultas SQL. |
| Generación de HTML | No aplica | La función no genera contenido HTML. |

## 5. Hallazgos de la segunda revisión con IA

Se solicitó una segunda revisión independiente del código para identificar posibles errores y oportunidades de mejora.

La revisión identificó dos problemas de validación:

**Hallazgo 1. Precios no finitos**

La validación inicial permitía que valores como `NAN` e infinito superaran las condiciones existentes. Se comprobó el comportamiento ejecutando la función con estos valores.

**Corrección:** se incorporó la validación mediante `is_finite()` para rechazar precios no finitos.

**Hallazgo 2. Espacios Unicode**

La función utilizaba `trim()`, que no elimina todos los tipos de espacios Unicode. Se comprobó el comportamiento utilizando un espacio no separable Unicode (U+00A0).

**Corrección:** se incorporó una expresión regular que permite normalizar espacios Unicode al inicio y al final del nombre y la categoría.

## 6. Verificación de las correcciones

Después de aplicar las correcciones, se ampliaron las pruebas automatizadas para incluir los dos casos identificados.

Los resultados finales fueron:

| Herramienta | Resultado |
|---|---|
| PHPUnit 11.5.57 | 10 pruebas aprobadas, 14 aserciones. |
| PHP_CodeSniffer | Sin errores ni advertencias. |
| Composer Audit | No se encontraron avisos de vulnerabilidades conocidas. |

Las pruebas permitieron comprobar que la función rechaza los precios no finitos y los nombres compuestos únicamente por espacios Unicode.

## 7. Documentación del proyecto

Se actualizó el archivo `README.md` para describir las validaciones actuales, los casos límite, las tecnologías utilizadas y los comandos necesarios para ejecutar las pruebas y las herramientas de revisión.

También se agregó documentación DocBlock a la función principal `registrarProducto`, incluyendo su descripción, parámetros, valor de retorno y excepción.

La bitácora `bitacora_ia.md` conserva el registro de las sugerencias de inteligencia artificial, las decisiones tomadas y la verificación de los resultados.

## 8. Conclusiones

La revisión permitió mejorar la calidad de la funcionalidad de registro de productos e insumos mediante la identificación y corrección de dos problemas de validación.

La segunda revisión con inteligencia artificial fue útil para detectar situaciones que no estaban contempladas en las pruebas iniciales. Sin embargo, los hallazgos fueron comprobados antes de modificar el código.

Finalmente, las pruebas automatizadas, el análisis de estilo y la auditoría de dependencias permitieron verificar el estado del código después de las correcciones. Esta actividad permitió comprender la importancia de combinar herramientas automáticas, revisión manual y documentación para mejorar la calidad del software.

