# Semana 8 – Cobertura, pruebas de aceptación y análisis de defectos

**Proyecto:** Floristería Darcy  
**Rama:** `feature/semanas-8-9-floristeria`  
**Lenguaje principal:** PHP 8.2  
**Base de datos:** PostgreSQL 16 en CI  
**Herramientas:** PHPUnit 11.5, pytest, Xdebug, PHPCS y GitHub Actions

## 1. Objetivo de la actividad

Aplicar los contenidos de la Semana 8 al sistema Floristería Darcy: medir cobertura de líneas y métodos, interpretar líneas no cubiertas, comprobar la calidad de las pruebas mediante mutación, traducir criterios de aceptación a pruebas automatizadas y registrar defectos de manera reproducible.

Se conserva la estructura tecnológica del proyecto. Las pruebas pytest ejecutan las reglas PHP reales a través de `tests/php_bridge.php`; Python no duplica las reglas de negocio.

## 2. Alcance y módulos revisados

| Módulo | Responsabilidad | Archivo |
|---|---|---|
| Productos | Validar y normalizar datos de productos. | `src/Domain/ProductRules.php` |
| Inventario | Calcular existencias y determinar alertas de stock mínimo. | `src/Domain/InventoryRules.php` |
| Ventas | Validar artículos, cantidades, stock, descuentos y totales. | `src/Domain/SaleRules.php` |
| Persistencia | Ejecutar operaciones transaccionales y consultas al repositorio. | `src/Infrastructure/Repository.php` |

La aplicación no expone una API REST en el alcance revisado. Por esa razón, el apartado de pruebas API se adapta a pruebas de la capa de servicio/repositorio y sus efectos persistidos, tal como permite el material de la actividad cuando no existe API.

## 3. Cobertura de código y sus límites

### 3.1 Configuración

Se configuró PHPUnit para incluir el código de `src/Domain` como fuente de cobertura. GitHub Actions instala Xdebug y ejecuta PHPUnit con:

```bash
XDEBUG_MODE=coverage
composer test -- --coverage-text --coverage-clover=coverage/clover.xml
```

El informe Clover se publica como artefacto de la ejecución con el nombre `cobertura-phpunit`.

### 3.2 Resultado observado

En la ejecución de CI del 27/11/2026 se obtuvo:

| Indicador | Resultado |
|---|---:|
| Clases cubiertas | 2 de 3 (66,67 %) |
| Métodos cubiertos | 4 de 6 (66,67 %) |
| Líneas ejecutadas | 87 de 89 (97,75 %) |

Detalle por clase:

| Clase | Métodos | Líneas |
|---|---:|---:|
| `InventoryRules` | 2/2 (100 %) | 11/11 (100 %) |
| `ProductRules` | 1/3 (33,33 %) | 23/25 (92 %) |
| `SaleRules` | 1/1 (100 %) | 53/53 (100 %) |

La cobertura de líneas fue alta, pero la cobertura de métodos muestra que no todos los métodos de `ProductRules` quedaron ejecutados en esa medición. Por tanto, no se interpreta el 97,75 % como garantía de ausencia de defectos.

**Límite de la medición:** la cobertura solo indica qué líneas se ejecutaron. No determina si las aserciones son correctas, si todos los requisitos están probados ni si existen errores en la interfaz o en el entorno real.

### 3.3 Líneas no cubiertas y acciones

En la medición citada quedaron dos líneas sin ejecutar en `ProductRules`. Se añadió un caso adicional para texto con codificación UTF-8 inválida con el fin de cubrir un escenario de rechazo. La ejecución posterior a ese cambio debe ser la fuente del porcentaje actualizado; no se reemplaza el resultado anterior hasta que termine el workflow nuevo.

## 4. Pruebas de mutación

### 4.1 Propósito y método

Se introdujeron cinco cambios deliberadamente incorrectos, uno por vez, en las reglas de dominio. Después de cada cambio se ejecutó PHPUnit. Un mutante se considera muerto cuando la suite falla al introducirlo; si la suite continúa aprobando, el mutante sobrevive y revela una posible debilidad de las pruebas.

El ejecutor `tests/mutation_runner.py` restaura el contenido original del archivo incluso si la ejecución falla.

### 4.2 Mutaciones realizadas

| N.º | Regla modificada temporalmente | Qué intenta comprobar |
|---|---|---|
| 1 | Rechazar cantidad cero de producto cuando el límite válido permite cero. | Prueba de frontera de cantidad. |
| 2 | Alterar la validación de movimientos de inventario iguales a cero. | Rechazo de movimiento nulo. |
| 3 | Alterar el límite que impide existencias negativas. | Protección contra stock negativo. |
| 4 | Rechazar una venta cuya cantidad coincide exactamente con el stock. | Límite inclusivo de disponibilidad. |
| 5 | Cambiar el cálculo del subtotal de precio por cantidad. | Exactitud del cálculo de venta. |

### 4.3 Resultado

En la ejecución observada, los cinco mutantes fueron detectados:

- Mutantes ejecutados: 5.
- Mutantes muertos: 5.
- Mutantes sobrevivientes: 0.
- Puntuación de mutación: **100 % (5/5)**.

Fórmula:

`Puntuación = (mutantes muertos / mutantes ejecutados) × 100`

`(5 / 5) × 100 = 100 %`

Este resultado aplica únicamente a las cinco mutaciones definidas; no representa una garantía de detección de cualquier defecto posible.

## 5. Criterios de aceptación y pruebas automatizadas

Los escenarios se expresan en formato Dado/Cuando/Entonces (Given/When/Then) y se implementan en PHPUnit y pytest. pytest invoca las funciones PHP de producción mediante el puente CLI.

| ID | Dado | Cuando | Entonces | Evidencia automatizada |
|---|---|---|---|---|
| AC-PRO-01 | Un producto tiene nombre y categoría válidos, cantidad no negativa y precio finito positivo. | Se validan sus datos. | Se acepta y normaliza el texto. | `DomainRulesTest` / `test_product_rules.py` |
| AC-PRO-02 | El nombre está vacío o excede los límites definidos. | Se validan los datos. | Se rechaza con excepción de validación. | PHPUnit y pytest |
| AC-PRO-03 | El texto contiene bytes UTF-8 inválidos. | Se valida el producto. | Se rechaza la entrada. | PHPUnit: caso de codificación inválida |
| AC-INV-01 | Un producto tiene 10 unidades. | Se registra una entrada de 5. | El stock queda en 15 y se registra el movimiento. | PHPUnit de integración PostgreSQL |
| AC-INV-02 | Un producto tiene 10 unidades. | Se intenta una salida de 11. | Se rechaza, el stock permanece en 10 y no queda movimiento parcial. | PHPUnit de integración PostgreSQL |
| AC-INV-03 | Un producto tiene 10 unidades. | Se intenta una salida válida de 10. | El stock puede quedar exactamente en cero. | PHPUnit / pytest |
| AC-SAL-01 | Existe un producto con stock suficiente y precio válido. | Se registra una venta de 2 unidades con descuento de 10 %. | Se guarda la venta, el total se calcula correctamente y el stock baja a 8. | PHPUnit de integración PostgreSQL |
| AC-SAL-02 | Existe un producto con 10 unidades. | Se intenta vender 11. | Se rechaza y no quedan venta, detalle ni movimiento parcial. | PHPUnit de integración PostgreSQL |
| AC-SAL-03 | Una venta tiene precio y cantidad válidos. | Se calcula subtotal y descuento. | El total corresponde al subtotal menos el descuento. | PHPUnit / pytest |

### 5.1 Pruebas de capa de servicio/repositorio

No se ejecutan pruebas HTTP porque el sistema revisado no ofrece endpoints REST. En sustitución, se comprueban las operaciones de `Repository` y los efectos observables en PostgreSQL: filas insertadas, existencias actualizadas, movimientos asociados y reversión de transacciones ante errores.

## 6. Pruebas de integración con PostgreSQL

El archivo `tests/Integration/RepositoryPostgresTest.php` utiliza exclusivamente las variables `DB_TEST_DSN`, `DB_TEST_USER` y `DB_TEST_PASSWORD`. El workflow levanta un servicio PostgreSQL 16 con base `floristeria_test`.

La prueba carga `database/schema.sql`, prepara datos ficticios y limpia las tablas de prueba antes de cada escenario. No lee `.env` ni utiliza la base de datos local o de producción.

Escenarios implementados:

1. Entrada de inventario con trazabilidad de stock anterior y nuevo.
2. Salida superior a las existencias: excepción y reversión.
3. Venta correcta: cabecera, detalle, descuento, actualización de stock y movimiento.
4. Venta sin stock suficiente: ausencia de registros parciales.
5. Rechazo de identificadores de tabla no permitidos por el repositorio.

## 7. Análisis y reporte de defectos

### 7.1 Criterio

Un defecto debe documentarse con identificador, título, entorno, precondiciones, pasos para reproducir, resultado esperado, resultado observado, evidencia, severidad, prioridad, estado y corrección.

### 7.2 Hallazgos de esta iteración

| ID | Hallazgo | Severidad | Prioridad | Estado |
|---|---|---|---|---|
| QA-08-01 | PHPCS reportó una línea de 132 caracteres en la preparación de la base de datos de integración. | Baja | Baja | Corregido: se dividió la cadena SQL en dos líneas. |
| QA-08-02 | La primera suite no tenía un caso explícito para texto con codificación UTF-8 inválida. | Baja (brecha de pruebas) | Media | Añadido caso de rechazo en PHPUnit. |

Las mutaciones intencionales no se registran como defectos del producto: son modificaciones temporales del código para medir la capacidad de detección de las pruebas y fueron restauradas.

No se afirma que existan defectos funcionales de producción confirmados por esta ejecución. Los resultados de las pruebas y las limitaciones del alcance se conservan por separado.

## 8. Ejecución automatizada y evidencias

Workflow: `.github/workflows/semana8-tests.yml`

Pasos:

1. Descargar la rama.
2. Preparar PHP 8.2 con `pdo_pgsql` y Xdebug.
3. Preparar Python 3.12.
4. Instalar dependencias Composer y pip.
5. Ejecutar PHPUnit y generar cobertura.
6. Ejecutar pytest.
7. Ejecutar mutación.
8. Ejecutar PHPCS.
9. Publicar el informe Clover como artefacto.

### 8.1 Resultados de una ejecución observada

| Actividad | Resultado registrado |
|---|---|
| PHPUnit | 41 pruebas, 88 aserciones, aprobadas |
| pytest | 35 pruebas aprobadas |
| Cobertura de líneas | 97,75 % (87/89) |
| Mutación | 5/5 detectadas (100 %) |
| PHPCS | En esa ejecución se detuvo por una advertencia de longitud de línea (132 caracteres); se corrigió después en el archivo de integración. |

**Importante:** estos números corresponden a la ejecución que produjo esos logs. Los cambios posteriores (caso UTF-8, ajuste de PHPCS y longitud de línea) requieren confirmar el resultado de la ejecución más reciente antes de actualizar las cifras finales.

### 8.2 Comandos locales

En PowerShell, desde la raíz del repositorio:

```powershell
composer install
py -m pip install -r requirements.txt
vendor\bin\phpunit
py -m pytest -v
vendor\bin\phpcs --warning-severity=0
```

Para cobertura, el entorno debe tener Xdebug habilitado y `XDEBUG_MODE=coverage`. Las pruebas de integración requieren una base PostgreSQL de pruebas separada y las variables `DB_TEST_DSN`, `DB_TEST_USER` y `DB_TEST_PASSWORD`. No se deben apuntar a la base de datos de producción.

## 9. Conclusión

La actividad amplió la validación del proyecto más allá de comprobar que los tests pasan. Se midió cobertura, se interpretaron las líneas no ejecutadas, se aplicaron mutaciones controladas y se prepararon pruebas de integración que verifican los efectos de las operaciones sobre PostgreSQL.

El resultado observado de 97,75 % de cobertura de líneas y 100 % de mutación es favorable para el alcance medido, pero no equivale a una garantía de calidad total. La cobertura de métodos y la ejecución final del workflow también deben considerarse. La evidencia final debe corresponder a una ejecución verde de todos los pasos.
