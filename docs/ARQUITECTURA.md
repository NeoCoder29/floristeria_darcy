# Arquitectura de Floristería Darcy

La aplicación conserva una arquitectura sencilla por responsabilidades, implementada en PHP 8.2 y PostgreSQL.

- **Presentación:** `public/index.php` gestiona las rutas, formularios, sesiones, validación de solicitudes y renderizado HTML. `public/assets/app.css` contiene el sistema visual adaptable.
- **Dominio:** las clases de `src/Domain` contienen reglas de productos, inventario y ventas sin depender de la base de datos.
- **Infraestructura:** `Database` configura PDO PostgreSQL y `Repository` concentra las operaciones de persistencia.
- **Datos:** `database/schema.sql` define usuarios, categorías, productos, clientes, ventas, detalle de ventas y movimientos de inventario.
- **Pruebas PHP:** `tests/`.
- **Pruebas pytest:** `tests_py/`; llaman al código PHP real mediante `tests/php_bridge.php`.

## Flujo de ventas

1. La presentación valida el formato de los datos recibidos.
2. `Repository::createSale()` abre una transacción y bloquea los productos seleccionados mediante `SELECT ... FOR UPDATE`.
3. Los precios y existencias se leen nuevamente desde PostgreSQL; no se confía en los valores mostrados previamente en el navegador.
4. `SaleRules` calcula las líneas, el subtotal y el total.
5. Se registra la venta y su detalle, se descuentan existencias y se genera un movimiento de salida por cada producto.
6. Se confirma la transacción. Si cualquier paso falla, se revierte todo.

## Flujo de inventario

Las entradas y salidas manuales se procesan en una transacción. El producto se bloquea, se calcula la nueva existencia mediante `InventoryRules`, se actualiza el producto y se guarda el movimiento con el stock anterior, el nuevo stock, el motivo y el usuario responsable.

## Seguridad

La aplicación utiliza consultas preparadas, tokens CSRF, sesiones endurecidas, regeneración del ID de sesión al autenticar, escape HTML y encabezados HTTP de seguridad. Los errores técnicos se registran en el servidor y no se muestran al usuario final.
