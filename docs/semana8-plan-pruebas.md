# Plan de validación – Semana 8

## Objetivo

Comprobar las reglas de negocio, los efectos persistidos en PostgreSQL y la capacidad de los tests para detectar cambios defectuosos.

## Alcance de las pruebas

### 1. Pruebas unitarias

Las pruebas PHPUnit y pytest ejercitan las reglas PHP de productos, inventario y ventas. pytest invoca el código PHP mediante `tests/php_bridge.php`; no implementa una segunda versión de las reglas.

### 2. Pruebas de integración PostgreSQL

`tests/Integration/RepositoryPostgresTest.php` utiliza exclusivamente las variables `DB_TEST_DSN`, `DB_TEST_USER` y `DB_TEST_PASSWORD`. El flujo de CI crea un servicio PostgreSQL dedicado llamado `floristeria_test`.

Los escenarios comprueban:

- Entrada de inventario y registro de movimiento.
- Rechazo de una salida superior al stock y reversión de la transacción.
- Registro de venta, descuento de existencias y movimiento automático.
- Rechazo de venta sin stock suficiente, sin dejar registros parciales.
- Rechazo de nombres de tablas no permitidos.

La suite limpia las tablas antes de cada escenario. No lee `.env` ni se conecta a la base de datos de producción.

### 3. Pruebas de mutación

`tests/mutation_runner.py` introduce cinco cambios controlados en las reglas de dominio, ejecuta PHPUnit y restaura siempre el archivo original:

- Permitir stock cero inválido (producto).
- Alterar la validación de movimientos cero (inventario).
- Alterar el límite inferior del stock (inventario).
- Rechazar una venta cuyo pedido coincide exactamente con el stock.
- Alterar el cálculo del subtotal.

Un mutante se considera detectado cuando la suite falla con el cambio introducido. El porcentaje se calcula como mutantes detectados / mutantes ejecutados × 100.

## Cobertura

PHPUnit mide cobertura de líneas sobre `src/Domain` con Xdebug. El informe Clover se publica como artefacto `cobertura-phpunit` en GitHub Actions. La cobertura indica qué líneas se ejecutaron; no demuestra por sí sola que las aserciones sean suficientes.

## Ejecución automatizada

El workflow `.github/workflows/semana8-tests.yml` ejecuta, en orden:

1. PHPUnit con cobertura.
2. pytest.
3. Mutación controlada.
4. PHPCS.
5. Publicación del informe Clover.

## Registro de resultados

Los resultados numéricos y el estado final deben copiarse desde la ejecución real de GitHub Actions. No se consignan porcentajes ni resultados antes de que termine el workflow.
