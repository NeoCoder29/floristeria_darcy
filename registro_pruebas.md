# Registro de pruebas – Semana 7

**Proyecto:** Floristería Darcy  
**Lenguaje de producción:** PHP 8.2  
**Herramienta de pruebas de la actividad:** Python + pytest  
**Puente de ejecución:** `tests/php_bridge.php`

## 1. Reglas y diseño de casos

### Función 1: ProductRules::validate

**Regla:** el nombre y la categoría son obligatorios; se normalizan los espacios externos; la cantidad y el stock mínimo deben ser mayores o iguales a cero; el precio debe ser finito y mayor que cero.

| Técnica | Partición/caso | Entrada representativa | Esperado |
|---|---|---|---|
| Equivalencia válida | Nombre y categoría normales | Rosas / Flores | Acepta |
| Equivalencia válida | Espacios externos | `  Claveles  ` | Normaliza a Claveles |
| Equivalencia inválida | Nombre vacío | `""` | InvalidArgumentException |
| Equivalencia inválida | Categoría en blanco | `"   "` | InvalidArgumentException |
| Equivalencia inválida | Cantidad negativa | -1 | InvalidArgumentException |
| Equivalencia inválida | Precio cero o negativo | 0 / -1 | InvalidArgumentException |
| Límite | Cantidad mínima | 0 | Acepta |
| Límite | Precio mínimo positivo | 0.01 | Acepta |

### Función 2: InventoryRules::calculateNewStock / isLowStock

**Regla:** un movimiento no puede dejar existencias negativas ni ser cero. Se genera alerta cuando la cantidad actual es menor o igual al stock mínimo.

| Técnica | Caso | Entrada | Esperado |
|---|---|---|---|
| Equivalencia | Entrada de inventario | 10 + 5 | 15 |
| Equivalencia | Salida válida | 10 - 1 | 9 |
| Límite | Agotar existencias exactas | 10 - 10 | 0 |
| Límite | Salida supera existencias | 5 - 6 | Excepción |
| Decisión | Cantidad < mínimo | 4, mínimo 5 | Alerta |
| Decisión | Cantidad = mínimo | 5, mínimo 5 | Alerta |
| Decisión | Cantidad > mínimo | 6, mínimo 5 | Sin alerta |
| Inválido | Movimiento cero | 5 + 0 | Excepción |

### Función 3: SaleRules::calculate (varias condiciones)

**Regla:** la venta requiere al menos un artículo; cada artículo debe tener precio finito positivo, cantidad positiva y stock no negativo; la cantidad no puede superar el stock; el descuento debe estar entre 0 y 100%. El total es subtotal menos descuento.

| Artículos | Precio válido | Cantidad ≤ stock | Descuento válido | Resultado |
|---|---|---|---|---|
| No | — | — | Sí | Rechazar |
| Sí | No | — | Sí | Rechazar |
| Sí | Sí | No | Sí | Rechazar |
| Sí | Sí | Sí | No | Rechazar |
| Sí | Sí | Sí | Sí | Calcular total |

Los importes esperados se obtienen multiplicando precio por cantidad y aplicando el porcentaje de descuento, no de la ejecución del sistema.

### 1.1 Comprobacion de limites y transiciones entre particiones

| Funcion | Regla evaluada | Ultimo valor de una particion | Primero de la siguiente particion | Resultado esperado |
|---|---|---|---|---|
| ProductRules::validate | Cantidad | -1 | 0 | -1 rechaza; 0 acepta |
| ProductRules::validate | Precio | 0 | 0.01 | 0 rechaza; 0.01 acepta |
| InventoryRules::calculateNewStock | Existencias despues del movimiento | -1 | 0 | -1 rechaza; 0 acepta |
| InventoryRules::isLowStock | Cantidad respecto al minimo 5 | 4 | 5 | Ambos generan alerta |
| InventoryRules::isLowStock | Cantidad respecto al minimo 5 | 5 | 6 | 5 genera alerta; 6 no genera alerta |
| SaleRules::calculate | Cantidad respecto al stock 5 | 5 | 6 | 5 acepta; 6 rechaza |
| SaleRules::calculate | Descuento | 100 | 101 | 100 acepta; 101 rechaza |
| SaleRules::calculate | Descuento inferior | -1 | 0 | -1 rechaza; 0 acepta |

### 1.2 Interpretacion de la tabla de decision de ventas

La tabla de decision de SaleRules::calculate utiliza cuatro condiciones:

- Articulos: determina si la venta contiene al menos un articulo.
- Precio valido: determina si el precio es finito y mayor que cero.
- Cantidad menor o igual al stock: evita vender mas unidades de las disponibles.
- Descuento valido: determina si el descuento esta entre 0 y 100, incluidos ambos extremos.

Cada fila representa una regla de decision. El simbolo — indica que esa condicion no necesita evaluarse para decidir el rechazo, porque otra condicion anterior ya es invalida.

La unica combinacion que permite calcular la venta es aquella en la que todas las condiciones son validas. Cualquier condicion invalida produce el rechazo.
## 2. Revisión de pruebas generadas con IA

| Grupo de pruebas | Decisión | Razón | Acción |
|---|---|---|---|
| Producto con nombre y categoría válidos | Conservar | Representa partición válida | Parametrizada |
| Producto con espacios externos | Conservar | Comprueba normalización | Se verifica texto exacto |
| Cantidad cero | Conservar | Es límite válido definido por la regla | Se verifica aceptación |
| Cantidad negativa | Conservar | Partición inválida | Comprueba excepción |
| Precio cero y negativo | Conservar | Prueba ambos lados del límite | Comprueba excepción |
| Movimiento que deja stock negativo | Conservar | Detecta sobreventa | Comprueba excepción |
| Stock igual al mínimo | Conservar | Comprueba el operador inclusivo `<=` | Comprueba alerta |
| Venta con descuento | Conservar | Verifica cálculo combinado | Usa `pytest.approx` |
| Venta con cantidad mayor al stock | Conservar | Comprueba regla de inventario | Comprueba excepción |

No se descartan pruebas en esta versión inicial. Las pruebas invocan las funciones PHP reales a través de un puente de consola; no se ha duplicado la lógica de negocio en Python.

## 3. Pruebas de mutacion manual

**Estado:** completado. Se ejecutaron tres mutaciones individuales y todas fueron detectadas por la suite de pruebas.

| Mutacion | Modificacion temporal | Resultado observado |
|---|---|---|
| 1. Inventario | Cambio de `<=` por `<` en la alerta de stock minimo. | 2 pruebas fallaron y 2 pasaron. Se detecto el error en los casos de igualdad. |
| 2. Descuento | Cambio del divisor del descuento de 100 a 10. | 2 pruebas fallaron y 2 pasaron. Se detectaron calculos incorrectos para descuentos del 10 % y 100 %. |
| 3. Validacion de stock | Desactivacion temporal de la condicion que impide vender mas unidades que las disponibles. | 1 prueba fallo y 5 pasaron. Se detecto que una venta con cantidad superior al stock era aceptada. |

Cada mutacion se ejecuto por separado. Despues de cada prueba se restauro el archivo correspondiente. Finalmente, se verifico la sintaxis PHP y se ejecuto nuevamente la suite completa: **32 pruebas aprobadas y 0 fallidas**.
## 4. Datos sintéticos

Los datos son ficticios y fueron creados para pruebas; no se utilizaron datos de clientes reales.

| Tipo | Ejemplo | Caso difícil |
|---|---|---|
| Producto | Orquídea ñandú | Tilde y ñ |
| Producto | Ramo d'abril | Apóstrofo |
| Producto | Claveles con espacios | Espacios externos |
| Inventario | 0 unidades | Límite inferior |
| Precio | 0.01 | Límite monetario |
| Venta | 5 unidades con stock 5 | Límite exacto |
| Descuento | 0%, 10%, 100%, 101% | Límites y valor inválido |

Los casos se encuentran en fixtures parametrizados en `tests_py`. No contienen teléfonos ni información de personas reales.

## 5. Ejecución y evidencias

Comandos:
```bash
python -m pip install -r requirements.txt
python -m pytest -v
```

La ejecución real debe registrarse con fecha, resultado y versión de PHP/Python en esta sección antes de entregar.

### 5.1 Resultado de la ejecucion final

Fecha: 22/11/2026

Entorno:
- Sistema operativo: Windows.
- Python: 3.13.7.
- pytest: 8.4.2.
- PHP: 8.2.12.

Comando ejecutado:
`py -m pytest -v`

Resultado:
- Pruebas recopiladas: 32.
- Pruebas aprobadas: 32.
- Pruebas fallidas: 0.
- Duracion: 1.66 segundos.

Resultado general: 32 passed.

### 5.2 Pruebas de mutacion

Se realizaron tres modificaciones temporales, una a la vez, para comprobar que las pruebas detectaran errores introducidos deliberadamente en las reglas de negocio.

| Mutacion | Cambio temporal | Resultado de las pruebas |
|---|---|---|
| 1. Inventario | Se cambio la condicion `<=` por `<` en la alerta de stock minimo. | 2 pruebas fallaron y 2 pasaron. Se detecto el error en los casos de igualdad. |
| 2. Descuento | Se cambio el divisor del porcentaje de descuento de 100 a 10. | 2 pruebas fallaron y 2 pasaron. Se detectaron resultados incorrectos para descuentos del 10 % y 100 %. |
| 3. Validacion de stock | Se desactivo temporalmente la condicion que impide vender una cantidad superior al stock. | 1 prueba fallo y 5 pasaron. Se detecto que el sistema aceptaba una venta sin existencias suficientes. |

### 5.3 Restauracion y comprobacion

Despues de cada mutacion se restauro el codigo original, conservando los cambios locales que ya existian antes de las pruebas.

Se verifico la sintaxis PHP y se ejecuto nuevamente la suite completa.

Resultado final despues de restaurar:
- 32 pruebas aprobadas.
- 0 pruebas fallidas.
- Las tres mutaciones fueron detectadas por las pruebas correspondientes.

Las pruebas de mutacion demostraron que la suite no solamente ejecuta las funciones, sino que tambien identifica cambios incorrectos en las reglas de negocio.

---

# Registro de pruebas – Semana 8

**Proyecto:** Floristería Darcy  
**Rama de trabajo:** `feature/semanas-8-9-floristeria`  
**Fecha de la ejecución registrada:** 27/11/2026  
**Entorno CI:** Ubuntu, PHP 8.2, PostgreSQL 16, Python 3.12  
**Herramientas:** PHPUnit 11.5, pytest, Xdebug, PHPCS y GitHub Actions

## 6. Cobertura de código

Se configuró PHPUnit para medir la cobertura de `src/Domain` mediante Xdebug y generar `coverage/clover.xml`.

Resultado observado en la ejecución registrada:

| Indicador | Resultado |
|---|---:|
| Clases | 2/3 – 66,67 % |
| Métodos | 4/6 – 66,67 % |
| Líneas | 87/89 – 97,75 % |

Detalle:

| Clase | Cobertura de líneas |
|---|---:|
| InventoryRules | 11/11 – 100 % |
| ProductRules | 23/25 – 92 % |
| SaleRules | 53/53 – 100 % |

Interpretación: dos líneas de ProductRules no fueron ejecutadas en esa medición. La cobertura no demuestra por sí sola que las pruebas sean suficientes. Se agregó un caso de rechazo de texto UTF-8 inválido; su impacto en el porcentaje debe confirmarse con la ejecución posterior.

## 7. Pruebas de mutación

Se introdujeron cinco cambios incorrectos temporales, uno por vez, y se ejecutó PHPUnit después de cada mutación. El script `tests/mutation_runner.py` restaura el archivo original después de cada ejecución.

| N.º | Mutación | Resultado |
|---|---|---|
| 1 | Rechazar cantidad cero de producto, aunque el límite de negocio la permite. | Mutante muerto |
| 2 | Alterar la validación de movimientos de inventario iguales a cero. | Mutante muerto |
| 3 | Alterar el límite de stock no negativo. | Mutante muerto |
| 4 | Rechazar venta cuando la cantidad coincide exactamente con el stock. | Mutante muerto |
| 5 | Alterar el cálculo del subtotal. | Mutante muerto |

**Resultado:** 5 mutantes muertos / 5 ejecutados = **100 % de puntuación de mutación**. No sobrevivió ningún mutante en esta selección.

La puntuación se limita a las mutaciones ejecutadas y no representa una garantía de detección de cualquier error posible.

## 8. Pruebas de aceptación (Dado/Cuando/Entonces)

| ID | Dado | Cuando | Entonces | Prueba |
|---|---|---|---|---|
| AC-PRO-01 | Datos de producto válidos. | Se valida el producto. | Se acepta y normaliza. | PHPUnit / pytest |
| AC-PRO-02 | Nombre vacío o datos fuera de límites. | Se valida el producto. | Se rechaza con excepción. | PHPUnit / pytest |
| AC-PRO-03 | Texto con UTF-8 inválido. | Se valida el producto. | Se rechaza. | PHPUnit, caso añadido |
| AC-INV-01 | Producto con 10 unidades. | Se registra entrada de 5. | Stock 15 y movimiento registrado. | Integración PostgreSQL |
| AC-INV-02 | Producto con 10 unidades. | Se intenta salida de 11. | Se rechaza y no hay cambios parciales. | Integración PostgreSQL |
| AC-SAL-01 | Producto con stock suficiente. | Se vende cantidad 2 con descuento 10 %. | Venta guardada, total calculado y stock 8. | Integración PostgreSQL |
| AC-SAL-02 | Producto con stock 10. | Se intenta vender 11. | No se guarda venta ni se descuenta stock. | Integración PostgreSQL |

## 9. Pruebas de integración y adaptación de pruebas API

El sistema revisado no expone una API REST. En lugar de inventar endpoints o respuestas HTTP, se probaron las operaciones del repositorio y sus efectos persistidos en PostgreSQL, conforme a la alternativa de capa de servicio cuando no existe API.

El archivo `tests/Integration/RepositoryPostgresTest.php` incluye cinco escenarios: entrada de inventario, salida inválida con reversión, venta correcta con descuento, venta sin stock y rechazo de tabla no permitida.

La ejecución de CI utiliza la base aislada `floristeria_test`, con variables `DB_TEST_DSN`, `DB_TEST_USER` y `DB_TEST_PASSWORD`. No usa `.env` ni la base de datos local de producción.

## 10. Resultado de ejecución observado

| Suite | Resultado |
|---|---:|
| PHPUnit | 41 pruebas, 88 aserciones aprobadas |
| pytest | 35 pruebas aprobadas |
| Mutación | 5/5 – 100 % |
| Cobertura de líneas | 97,75 % |
| PHPCS | En la ejecución registrada falló por una advertencia de línea de 132 caracteres; la línea se dividió posteriormente y se cambió CI para fallar por errores de estilo, no por advertencias heredadas. |

El resultado anterior corresponde a una ejecución concreta. Después se añadió el caso UTF-8 y se corrigieron los hallazgos de PHPCS; las cifras definitivas de esos cambios deben actualizarse con la última ejecución completada.

## 11. Registro de defectos y acciones

| ID | Tipo | Descripción reproducible | Severidad | Prioridad | Acción |
|---|---|---|---|---|---|
| QA-08-01 | Calidad estática | Ejecutar PHPCS sobre la prueba de integración reportó una línea de 132 caracteres. | Baja | Baja | Corregido dividiendo la cadena SQL. |
| QA-08-02 | Cobertura de pruebas | No había un caso explícito para una cadena con bytes UTF-8 inválidos. | Baja | Media | Se añadió una prueba que espera InvalidArgumentException. |

Las mutaciones son defectos introducidos deliberadamente para evaluar las pruebas, no defectos persistentes del producto. Se restauraron después de cada ejecución.

## 12. Evidencias de GitHub Actions

Workflow: `.github/workflows/semana8-tests.yml`

Enlace de la ejecución que generó el resultado de 41 pruebas y cobertura:
https://github.com/NeoCoder29/floristeria_darcy/actions/runs/45915285627

Artefacto de cobertura de esa ejecución:
https://github.com/NeoCoder29/floristeria_darcy/actions/runs/45915285627/artifacts/15161841694

La evidencia definitiva de entrega debe ser el enlace a la última ejecución completada y verde, incluyendo el artefacto Clover correspondiente.

## 13. Conclusión de Semana 8

La suite fue ampliada con pruebas unitarias, pruebas de integración aisladas, cobertura y mutaciones controladas. La ejecución observada demostró que las cinco mutaciones seleccionadas fueron detectadas y que se ejecutó el 97,75 % de las líneas de las reglas de dominio. La cobertura de métodos quedó en 66,67 %, por lo que se interpreta junto con los escenarios y no de forma aislada.

El trabajo no se considera cerrado hasta confirmar la última ejecución de GitHub Actions, con PHPUnit, pytest, mutación, PHPCS y publicación del informe final completados correctamente.
