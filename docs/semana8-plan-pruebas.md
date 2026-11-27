# Semana 8 – Cobertura, pruebas de aceptación y análisis de defectos

**Proyecto:** Floristería Darcy  
**Rama:** `feature/semanas-8-9-floristeria`  
**Entorno de validación:** GitHub Actions, Ubuntu, PHP 8.2, PostgreSQL 16 y Python 3.12  
**Herramientas:** PHPUnit 11.5, pytest, Xdebug, PHPCS y GitHub Actions

## 1. Objetivo de la actividad

Aplicar los contenidos de Semana 8 al proyecto real: medir cobertura y sus límites, interpretar líneas no cubiertas, aplicar pruebas de mutación, convertir reglas en criterios de aceptación automatizados y analizar/documentar defectos de manera reproducible.

Se mantiene la arquitectura PHP/PostgreSQL del proyecto. No se inventan endpoints HTTP ni se modifica la base de datos local del estudiante.

## 2. Alcance técnico

| Módulo | Responsabilidad | Archivo |
|---|---|---|
| Productos | Validar y normalizar datos de producto. | `src/Domain/ProductRules.php` |
| Inventario | Calcular existencias y alertas de stock mínimo. | `src/Domain/InventoryRules.php` |
| Ventas | Validar artículos, cantidades, stock, descuentos y totales. | `src/Domain/SaleRules.php` |
| Persistencia | Operaciones transaccionales y consultas. | `src/Infrastructure/Repository.php` |

Las pruebas pytest invocan las reglas PHP reales por medio de `tests/php_bridge.php`; no duplican la lógica de negocio en Python.

## 3. Cobertura y sus límites

### 3.1 Configuración

Se configuró PHPUnit para medir cobertura del código incluido en `src/Domain`. GitHub Actions instala Xdebug y ejecuta:

```bash
XDEBUG_MODE=coverage
composer test -- --coverage-text --coverage-clover=coverage/clover.xml
```

El archivo Clover se publica como artefacto `cobertura-phpunit`.

### 3.2 Resultado verificado

Ejecución completada: [GitHub Actions – 45915401359](https://github.com/NeoCoder29/floristeria_darcy/actions/runs/45915401359).

| Indicador | Resultado |
|---|---:|
| Clases | 2/3 – 66,67 % |
| Métodos | 5/6 – 83,33 % |
| Líneas | 88/89 – 98,88 % |

Detalle por clase:

| Clase | Métodos | Líneas |
|---|---:|---:|
| `InventoryRules` | 2/2 – 100 % | 11/11 – 100 % |
| `ProductRules` | 2/3 – 66,67 % | 24/25 – 96 % |
| `SaleRules` | 1/1 – 100 % | 53/53 – 100 % |

### 3.3 Interpretación

En esa medición quedó una línea sin ejecutar en `ProductRules`. La cobertura de líneas fue alta, pero no llegó al 100 %; además, la cobertura de métodos fue 83,33 % y la cobertura de clases 66,67 %.

La cobertura indica qué líneas/métodos se ejecutaron, pero no garantiza que las aserciones sean correctas, que todos los requisitos estén cubiertos ni que no existan defectos en interfaz o configuración. Por eso se complementó con mutaciones y pruebas de integración.

## 4. Pruebas de mutación

### 4.1 Método

El script `tests/mutation_runner.py` introduce cinco cambios deliberadamente incorrectos, ejecuta PHPUnit y restaura el archivo original después de cada mutación. Un mutante muere cuando la suite falla al introducir el cambio; sobrevive si la suite continúa pasando.

### 4.2 Mutaciones y resultado

| N.º | Cambio temporal | Qué verifica | Resultado |
|---|---|---|---|
| 1 | Rechazar cantidad cero de producto. | Límite de cantidad válido. | Muerto |
| 2 | Alterar validación de movimiento cero. | Rechazo de movimiento nulo. | Muerto |
| 3 | Alterar límite que impide stock negativo. | Protección de inventario. | Muerto |
| 4 | Rechazar venta con cantidad exactamente igual al stock. | Límite inclusivo de disponibilidad. | Muerto |
| 5 | Alterar cálculo del subtotal. | Cálculo de venta. | Muerto |

Resultado verificado: **5 mutantes muertos de 5 ejecutados; 0 sobrevivientes; puntuación 100 %**.

`(mutantes muertos / mutantes ejecutados) × 100 = (5 / 5) × 100 = 100 %`.

Este porcentaje se limita a las cinco mutaciones seleccionadas; no demuestra que cualquier defecto posible sea detectado.

## 5. Criterios de aceptación (Dado/Cuando/Entonces)

| ID | Dado | Cuando | Entonces | Evidencia |
|---|---|---|---|---|
| AC-PRO-01 | Datos de producto válidos. | Se validan los datos. | Se acepta y normaliza el texto. | PHPUnit y pytest |
| AC-PRO-02 | Nombre vacío o datos fuera de límites. | Se valida el producto. | Se rechaza con excepción. | PHPUnit y pytest |
| AC-PRO-03 | Texto con bytes UTF-8 inválidos. | Se valida el producto. | Se rechaza. | Caso PHPUnit añadido |
| AC-INV-01 | Producto con 10 unidades. | Se registra entrada de 5. | Stock 15 y movimiento registrado. | Integración PostgreSQL |
| AC-INV-02 | Producto con 10 unidades. | Se intenta salida de 11. | Se rechaza y no quedan cambios parciales. | Integración PostgreSQL |
| AC-INV-03 | Producto con 10 unidades. | Se registra salida de 10. | El stock queda en cero. | PHPUnit y pytest |
| AC-SAL-01 | Producto con stock suficiente. | Se venden 2 unidades con descuento 10 %. | Se guarda la venta, se calcula el total y el stock queda en 8. | Integración PostgreSQL |
| AC-SAL-02 | Producto con 10 unidades. | Se intenta vender 11. | Se rechaza y no quedan venta, detalle ni movimiento parcial. | Integración PostgreSQL |

## 6. Pruebas API y alternativa de capa de servicio

El sistema revisado no expone endpoints REST en el alcance actual. Por ello no se inventaron URLs ni respuestas HTTP. Se aplicó la alternativa indicada para sistemas sin API: pruebas de la capa de repositorio/servicio y comprobación de efectos persistidos.

Se comprueban:
- Resultado de la operación (retorno o excepción).
- Estado de los datos en PostgreSQL.
- Efectos secundarios esperados (movimiento de inventario, detalle de venta y actualización de existencias).
- Ausencia de registros parciales cuando una transacción falla.

## 7. Pruebas de integración PostgreSQL

Archivo: `tests/Integration/RepositoryPostgresTest.php`.

La suite utiliza exclusivamente `DB_TEST_DSN`, `DB_TEST_USER` y `DB_TEST_PASSWORD`. El workflow crea un servicio PostgreSQL 16 con base `floristeria_test`, carga `database/schema.sql` y prepara datos ficticios. No lee `.env` ni utiliza la base local o de producción.

Escenarios:
1. Entrada de inventario y trazabilidad de stock anterior/nuevo.
2. Salida superior al stock: excepción, stock sin cambios y transacción revertida.
3. Venta correcta: cabecera, detalle, descuento, stock actualizado y movimiento de salida.
4. Venta sin stock: no deja venta, detalle ni movimiento parcial.
5. Rechazo de nombres de tabla no permitidos.

## 8. Análisis y reporte de defectos

Se utiliza el formato: identificador, tipo, archivo, precondición, pasos reproducibles, resultado esperado, resultado observado, severidad, prioridad, estado y corrección.

| ID | Hallazgo | Severidad | Prioridad | Estado |
|---|---|---|---|---|
| QA-08-01 | PHPCS detectó línea de 132 caracteres en prueba de integración. | Baja | Baja | Corregido dividiendo la cadena SQL. |
| QA-08-02 | No había caso explícito de texto con UTF-8 inválido. | Baja | Media | Prueba PHPUnit añadida. |

Los detalles reproducibles están en [Reporte de defectos de Semana 8](reporte_defectos_semana8.md).

Las mutaciones no son defectos permanentes del producto: fueron errores simulados para comprobar la suite y se restauraron después de cada ejecución. No se registran defectos funcionales de producción confirmados por esta evidencia.

## 9. Ejecución y evidencias

Workflow: `.github/workflows/semana8-tests.yml`.

Ejecución completada y verde: [45915401359](https://github.com/NeoCoder29/floristeria_darcy/actions/runs/45915401359).

| Paso | Resultado |
|---|---|
| PHPUnit con cobertura | 42 pruebas, 89 aserciones, aprobadas |
| pytest | 35 pruebas aprobadas |
| Mutación | 5/5 detectadas (100 %) |
| PHPCS | Paso completado correctamente con `--warning-severity=0` |
| Publicación Clover | Artefacto publicado |

El paso PHPCS usa severidad de advertencias cero para no bloquearse por advertencias heredadas. Adicionalmente, se dividió la línea extensa identificada para mejorar legibilidad. La última ejecución de los cambios documentales y de esa corrección debe confirmarse en GitHub Actions antes de la entrega final.

## 10. Comandos de ejecución local

Desde la raíz del repositorio en PowerShell:

```powershell
composer install
py -m pip install -r requirements.txt
vendor\bin\phpunit
py -m pytest -v
py tests\mutation_runner.py
vendor\bin\phpcs --warning-severity=0
```

Para generar cobertura se necesita Xdebug con `XDEBUG_MODE=coverage`. Para integración se necesita una base PostgreSQL exclusiva de pruebas y las tres variables `DB_TEST_DSN`, `DB_TEST_USER` y `DB_TEST_PASSWORD`. No apuntar a producción.

## 11. Conclusión

La actividad no se limitó a comprobar que las pruebas pasaran. Se midió cobertura y se interpretaron sus límites; las mutaciones comprobaron si las pruebas detectaban cambios incorrectos; los criterios de aceptación se expresaron como escenarios verificables; y las pruebas de integración comprobaron persistencia y reversión transaccional en PostgreSQL.

Los resultados observados fueron 98,88 % de cobertura de líneas, 83,33 % de cobertura de métodos, 100 % de mutación, 42 pruebas PHPUnit aprobadas y 35 pruebas pytest aprobadas. Estos indicadores aplican al alcance medido y no constituyen una garantía de calidad total.
