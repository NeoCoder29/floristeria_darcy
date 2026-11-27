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

**Fecha de ejecución observada:** 27/11/2026  
**Entorno CI:** Ubuntu, PHP 8.2, PostgreSQL 16, Python 3.12  
**Herramientas:** PHPUnit 11.5, pytest, Xdebug, PHPCS y GitHub Actions

## 6. Cobertura y límites

PHPUnit mide el código de `src/Domain` mediante Xdebug y genera `coverage/clover.xml`.

| Indicador | Resultado |
|---|---:|
| Clases | 2/3 – 66,67 % |
| Métodos | 5/6 – 83,33 % |
| Líneas | 88/89 – 98,88 % |

| Clase | Métodos | Líneas |
|---|---:|---:|
| InventoryRules | 2/2 – 100 % | 11/11 – 100 % |
| ProductRules | 2/3 – 66,67 % | 24/25 – 96 % |
| SaleRules | 1/1 – 100 % | 53/53 – 100 % |

Interpretación: quedó una línea sin ejecutar en ProductRules. La cobertura no demuestra por sí sola que las aserciones sean correctas o que todos los requisitos estén probados; por eso se complementó con mutaciones e integración.

## 7. Pruebas de mutación

Se ejecutaron cinco mutaciones deliberadas, una por vez. El script `tests/mutation_runner.py` ejecutó PHPUnit y restauró el código original después de cada mutación.

| Mutación | Resultado |
|---|---|
| Cambiar la aceptación de cantidad cero del producto. | Muerto |
| Alterar validación de movimiento cero. | Muerto |
| Alterar límite que evita stock negativo. | Muerto |
| Rechazar venta cuya cantidad coincide con stock. | Muerto |
| Alterar cálculo del subtotal. | Muerto |

Resultado: 5 mutantes muertos / 5 ejecutados = **100 %**. No sobrevivieron mutantes de la selección. Este porcentaje solo corresponde a los cinco cambios probados.

## 8. Criterios de aceptación (Dado/Cuando/Entonces)

| ID | Dado | Cuando | Entonces | Prueba |
|---|---|---|---|---|
| AC-PRO-01 | Producto con datos válidos. | Se validan los datos. | Se acepta y normaliza. | PHPUnit / pytest |
| AC-PRO-02 | Nombre vacío o datos fuera de límite. | Se valida. | Se rechaza con excepción. | PHPUnit / pytest |
| AC-PRO-03 | Texto UTF-8 inválido. | Se valida. | Se rechaza. | PHPUnit |
| AC-INV-01 | Stock inicial 10. | Se registra entrada 5. | Stock 15 y movimiento persistido. | PostgreSQL |
| AC-INV-02 | Stock inicial 10. | Se intenta salida 11. | Se rechaza y no hay cambios parciales. | PostgreSQL |
| AC-SAL-01 | Producto con stock suficiente. | Se venden 2 unidades con descuento 10 %. | Venta persistida, total calculado y stock 8. | PostgreSQL |
| AC-SAL-02 | Stock inicial 10. | Se intenta vender 11. | No se guarda venta ni movimiento parcial. | PostgreSQL |

## 9. Pruebas de integración / adaptación de pruebas API

El sistema revisado no expone una API REST; por ello no se inventaron endpoints ni estados HTTP. Se probaron las operaciones de la capa Repository y sus efectos persistidos, como permite la actividad cuando no existe API.

`tests/Integration/RepositoryPostgresTest.php` contiene cinco escenarios: entrada de inventario con trazabilidad, salida excesiva con reversión, venta correcta con descuento, venta fallida sin registros parciales y rechazo de nombres de tabla no permitidos.

CI levanta PostgreSQL 16 con la base aislada `floristeria_test`. La suite usa `DB_TEST_DSN`, `DB_TEST_USER` y `DB_TEST_PASSWORD`; no lee `.env` ni utiliza la base de producción.

## 10. Análisis de defectos

| ID | Hallazgo reproducible | Severidad | Prioridad | Estado |
|---|---|---|---|---|
| QA-08-01 | PHPCS detectó una línea de 132 caracteres en la preparación SQL de la prueba de integración. | Baja | Baja | Corregido dividiendo la cadena. |
| QA-08-02 | No existía caso explícito para texto con bytes UTF-8 inválidos. | Baja | Media | Prueba PHPUnit añadida. |

Las mutaciones no son defectos persistentes: son cambios deliberados restaurados tras cada ejecución. No se reportan defectos funcionales de producción confirmados por estas pruebas.

## 11. Ejecución verificada

Ejecución de GitHub Actions: https://github.com/NeoCoder29/floristeria_darcy/actions/runs/45915401359

| Suite / paso | Resultado |
|---|---|
| PHPUnit | 42 pruebas, 89 aserciones aprobadas |
| pytest | 35 pruebas aprobadas |
| Cobertura de líneas | 98,88 % (88/89) |
| Cobertura de métodos | 83,33 % (5/6) |
| Mutación | 5/5 detectadas (100 %) |
| PHPCS | Paso completado con `--warning-severity=0` |
| Informe Clover | Publicado como artefacto |

El resultado corresponde a la ejecución indicada. La ejecución más reciente tras los cambios de documentación y el ajuste de la línea extensa debe terminar correctamente antes de dar por cerrada la rama.

## 12. Conclusión

Semana 8 amplió la validación con cobertura, mutaciones, criterios de aceptación, pruebas de integración y análisis reproducible de defectos. Los resultados observados son favorables dentro del alcance de reglas de dominio y repositorio; no representan cobertura total de la interfaz ni garantía absoluta de ausencia de defectos.
