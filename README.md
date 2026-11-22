# Floristería Darcy

Sistema administrativo web para gestionar productos, categorías, inventario, clientes y ventas de una floristería.

## Tecnologías

- PHP 8.2+
- PostgreSQL 14+
- PDO PostgreSQL
- HTML, CSS y JavaScript
- PHPUnit para pruebas unitarias PHP
- Python 3.10+ y pytest para las pruebas de reglas de negocio exigidas en Semana 7

## Funcionalidades incluidas

- Inicio de sesión con contraseñas almacenadas mediante `password_hash`.
- Panel con indicadores de productos, clientes, alertas de stock y ventas del día.
- Registro de categorías.
- Registro y consulta de productos con validación y nivel mínimo de inventario.
- Registro y consulta de clientes.
- Registro de ventas con descuento, validación de stock y actualización transaccional de inventario.
- Consultas SQL preparadas, protección CSRF y escape de salida HTML.

## Requisitos

- PHP 8.2 con extensiones `pdo` y `pdo_pgsql`.
- PostgreSQL 14 o superior.
- Composer.
- Python 3.10 o superior y pip.
- Servidor web con `public/` como raíz documental (o servidor integrado de PHP para desarrollo).

## Instalación

1. Crear una base de datos PostgreSQL llamada `floristeria_darcy`.
2. Copiar `.env.example` como `.env` y configurar usuario, contraseña y credenciales administrativas.
3. Importar el esquema:

   ```bash
   psql -U postgres -d floristeria_darcy -f database/schema.sql
   ```

4. Instalar dependencias PHP:

   ```bash
   composer install
   ```

5. Instalar pytest:

   ```bash
   python -m pip install -r requirements.txt
   ```

6. Iniciar el servidor local desde la raíz:

   ```bash
   php -S 127.0.0.1:8000 -t public
   ```

7. Abrir `http://127.0.0.1:8000`.

La aplicación crea el usuario administrador indicado en `.env` si todavía no existe. Cambia la contraseña inicial antes de utilizar el sistema fuera de un entorno local.

## Pruebas

Pruebas PHP:

```bash
vendor/bin/phpunit
```

Pruebas de reglas PHP ejecutadas mediante pytest:

```bash
python -m pytest -v
```

Las pruebas de pytest llaman las reglas reales de PHP por CLI mediante `tests/php_bridge.php`; no duplican la lógica de negocio en Python.

## Estructura

```text
database/schema.sql
public/index.php
public/assets/app.css
src/Domain/ProductRules.php
src/Domain/InventoryRules.php
src/Domain/SaleRules.php
src/Infrastructure/Database.php
src/Infrastructure/Repository.php
tests/php_bridge.php
tests_py/
bootstrap.php
composer.json
pytest.ini
```

## Seguridad y alcance

- Consultas parametrizadas para las operaciones con datos.
- Contraseñas protegidas con `password_hash` y `password_verify`.
- Token CSRF en formularios POST.
- Escape de salida HTML.
- Restricciones de integridad en PostgreSQL.
- Las credenciales se configuran en `.env`, excluido de Git.

Antes de desplegar en producción se debe configurar HTTPS, cambiar credenciales, establecer `APP_DEBUG=false`, definir política de copias de seguridad, endurecer sesiones y realizar una auditoría integral.

## Autor

Samuel Santiago Cabrera Castro — Proyecto académico Floristería Darcy.
