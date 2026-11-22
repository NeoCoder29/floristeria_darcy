# Arquitectura de Floristería Darcy

- **Presentación:** `public/index.php` y `public/assets/app.css`.
- **Dominio:** clases puras en `src/Domain`; contienen reglas comprobables sin conexión a base de datos.
- **Infraestructura:** `Database` y `Repository` implementan persistencia PostgreSQL mediante PDO.
- **Datos:** `database/schema.sql` define usuarios, categorías, productos, clientes, ventas y detalle de ventas.
- **Pruebas PHP:** `tests/`.
- **Pruebas pytest:** `tests_py/`; llaman al código PHP real mediante `tests/php_bridge.php`.

Las ventas se guardan dentro de una transacción para que la venta y el descuento de existencias se confirmen o reviertan juntos.
