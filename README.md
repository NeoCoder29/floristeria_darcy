\# Floristería Darcy



\## Historia de usuario HU-01: Registrar productos e insumos



\*\*Como:\*\* propietaria de la Floristería Darcy.



\*\*Quiero:\*\* registrar productos e insumos con su información básica.



\*\*Para:\*\* mantener organizado el inventario y conocer las existencias disponibles.



\### Contrato de la función



\*\*Nombre:\*\* `registrarProducto`



\*\*Entradas:\*\*

\- `nombre`: string. Nombre del producto o insumo.

\- `categoria`: string. Categoría a la que pertenece.

\- `cantidad`: integer. Existencias iniciales.

\- `precio`: float. Precio unitario del producto.



\*\*Salida:\*\* un arreglo asociativo con la información del producto registrado.



\*\*Errores:\*\* se debe generar una excepción cuando alguno de los datos no cumpla las reglas de validación.



\### Reglas de validación



1\. El nombre es obligatorio.

2\. La categoría es obligatoria.

3\. La cantidad debe ser un número entero mayor o igual a cero.

4\. El precio debe ser numérico y mayor que cero.

5\. Los datos deben conservarse correctamente en el producto registrado.



\### Supuestos



\- Cada registro representa un producto o insumo.

\- Se permite registrar productos con cero existencias.

\- El precio corresponde al valor unitario.

\- No se contempla todavía la conexión con una base de datos.



\### Casos límite



\- Nombre compuesto por espacios.

\- Categoría vacía.

\- Cantidad igual a cero.

\- Cantidad negativa.

\- Precio igual a cero.

\- Precio negativo.



\### Ejemplos



\*\*Ejemplo 1: registro válido de rosas\*\*



Entrada: nombre "Rosas rojas", categoría "Flores", cantidad 50, precio 5000.



Resultado esperado: producto registrado correctamente.



\*\*Ejemplo 2: registro válido de papel decorativo\*\*



Entrada: nombre "Papel coreano", categoría "Decoración", cantidad 20, precio 3500.



Resultado esperado: producto registrado correctamente.



\*\*Ejemplo 3: nombre vacío\*\*



Entrada: nombre "", categoría "Flores", cantidad 10, precio 5000.



Resultado esperado: error de validación porque el nombre es obligatorio.



\### Tecnologías



\- PHP 8.2

\- Composer

\- PHPUnit 11

\- Git y GitHub



\### Metodología



Se utilizará desarrollo guiado por pruebas (TDD), comenzando con la creación y ejecución de pruebas automatizadas antes de implementar la funcionalidad.



