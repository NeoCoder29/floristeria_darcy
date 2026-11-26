# Floristería Darcy

Sistema administrativo web para gestionar productos, categorías, inventario, clientes y ventas de una floristería. Está construido con PHP y PostgreSQL, sin cambiar el stack original del proyecto.

## Tecnologías

- PHP 8.2 o superior.
- PostgreSQL 14 o superior.
- PDO PostgreSQL.
- HTML5 y CSS3 adaptable a escritorio, tabletas y móviles.
- PHPUnit 11.5 para pruebas PHP.
- Python 3.10 o superior y pytest para las pruebas de reglas de negocio.

## Funcionalidades

- Inicio de sesión con contraseñas protegidas mediante `password_hash` y `password_verify`.
- Panel administrativo con indicadores de productos, clientes, alertas de stock, ventas del día, productos por reponer y ventas recientes.
- Registro y consulta de categorías.
- Registro y consulta de productos asociados a categorías existentes.
- Control de stock mínimo y alertas de existencias bajas.
- Registro de entradas y salidas manuales de inventario con motivo, responsable y trazabilidad de existencias antes y después.
- Descuento automático de existencias al registrar una venta; la salida queda registrada en el historial de inventario.
- Registro y consulta de clientes.
- Registro de ventas con productos, cantidades, cliente opcional, descuento y notas.
- Cálculo de totales en el servidor y validación del stock dentro de una transacción con bloqueo de filas para evitar ventas simultáneas con existencias desactualizadas.
- Historial de ventas con subtotal, descuento, total, cliente y responsable.
- Mensajes de confirmación y error mediante notificaciones de sesión.

## Seguridad aplicada

- Consultas SQL preparadas y parámetros enlazados.
- Token CSRF en formularios POST.
- Regeneración del identificador de sesión después del inicio de sesión.
- Cookies de sesión `HttpOnly` y `SameSite=Lax`; atributo `Secure` cuando se utiliza HTTPS.
- Encabezados de seguridad HTTP y política CSP.
- Escape de salida HTML con `htmlspecialchars`.
- Errores técnicos enviados al registro del servidor; no se exponen detalles de PDO al usuario.
- Validación de datos en el servidor, incluso cuando existen restricciones HTML en los formularios.
- Restricciones e índices en PostgreSQL.

## Requisitos previos

- PHP 8.2+ con `pdo` y `pdo_pgsql` habilitados.
- PostgreSQL 14+.
- Composer.
- Python 3.10+ y pip (para ejecutar pytest).

## Instalación local en Windows (PowerShell)

1. Crea una base de datos llamada `floristeria_darcy` en PostgreSQL.
2. Copia `.env.example` como `.env`.
3. Edita `.env` y configura los datos de conexión y una contraseña administrativa propia. No uses la contraseña de ejemplo en un entorno real.
4. Importa el esquema completo:

   ```powershell
   psql -U postgres -d floristeria_darcy -f database/schema.sql
   ```

   Si ya tienes una base de datos de una versión anterior, vuelve a ejecutar `database/schema.sql`: las tablas se crean con `IF NOT EXISTS` y se agrega la columna de descuento y la tabla de movimientos de inventario si aún no existen. Haz primero una copia de seguridad de tu base de datos.

5. Instala dependencias:

   ```powershell
   composer install
   py -m pip install -r requirements.txt
   ```

6. Inicia el servidor desde la carpeta raíz del proyecto:

   ```powershell
   php -S 127.0.0.1:8000 -t public
   ```

7. Abre `http://127.0.0.1:8000`.

La aplicación crea el usuario administrador definido por `ADMIN_NAME`, `ADMIN_EMAIL` y `ADMIN_PASSWORD` si no existe. Si cambias la contraseña en `.env` después de haber creado el usuario, el cambio no se aplica automáticamente al registro existente; actualiza la contraseña desde un procedimiento administrativo seguro o directamente mediante un hash generado con PHP.

## Pruebas

Pruebas PHP:

```powershell
vendor\bin\phpunit
```

Pruebas de reglas PHP ejecutadas mediante pytest:

```powershell
py -m pytest -v
```

Revisión de estilo (si están instaladas las dependencias de desarrollo):

```powershell
vendor\bin\phpcs
```

Las pruebas pytest invocan las reglas reales de PHP por CLI mediante `tests/php_bridge.php`; no duplican la lógica de negocio en Python.

## Estructura principal

```text
database/schema.sql
public/index.php
public/assets/app.css
src/Domain/ProductRules.php
src/Domain/InventoryRules.php
src/Domain/SaleRules.php
src/Infrastructure/Database.php
src/Infrastructure/Repository.php
src/registrarProducto.php
tests/
tests_py/
bootstrap.php
composer.json
pytest.ini
```

## Alcance

El sistema se mantiene como una herramienta administrativa interna de Floristería Darcy. No incluye pagos en línea, facturación electrónica, geolocalización ni seguimiento GPS.
