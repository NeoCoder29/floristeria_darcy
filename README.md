# Floristería Darcy

## 1. ¿Qué es este proyecto?

Floristería Darcy es un proyecto desarrollado en PHP que implementa la funcionalidad de registro y validación de productos e insumos para el inventario de una floristería.

La funcionalidad principal corresponde a la historia de usuario HU-01.

**Historia de usuario HU-01: Registrar productos e insumos**

**Como:** propietaria de la Floristería Darcy.

**Quiero:** registrar productos e insumos con su información básica.

**Para:** mantener organizado el inventario y conocer las existencias disponibles.

**Criterios de aceptación:**

1. **Registro correcto:** cuando se ingresan el nombre, la categoría, la cantidad y el precio con datos válidos, la función devuelve la información del producto.
2. **Cantidad inválida:** cuando se ingresa una cantidad negativa, el sistema rechaza el registro y genera una excepción indicando que los datos no son válidos.

## 2. Requisitos

Para utilizar y ejecutar las pruebas del proyecto se necesita:

- PHP 8.2 o superior.
- Composer.
- PHPUnit 11.
- PHP_CodeSniffer (PHPCS).
- Git (opcional, para clonar el repositorio).

## 3. Instalación

1. Clonar el repositorio:

   ```bash
   git clone https://github.com/NeoCoder29/floristeria_darcy.git
   ```

2. Ingresar a la carpeta:

   ```bash
   cd floristeria_darcy
   ```

3. Instalar las dependencias:

   ```bash
   composer install
   ```

El archivo `composer.lock` permite instalar las versiones de dependencias registradas para el proyecto.

## 4. Ejecución

La funcionalidad principal se encuentra en:

`src/registrarProducto.php`

Para utilizarla desde un archivo PHP, se debe incluir:

```php
require_once __DIR__ . '/src/registrarProducto.php';

$producto = registrarProducto(
    "Rosas rojas",
    "Flores",
    50,
    5000.0
);

print_r($producto);
```

La función recibe cuatro parámetros:

- `nombre`: nombre del producto o insumo.
- `categoria`: categoría a la que pertenece.
- `cantidad`: existencias iniciales.
- `precio`: precio unitario.

Devuelve un arreglo asociativo con los datos validados del producto.

**Contrato de la función**

Firma:

```php
function registrarProducto(
    string $nombre,
    string $categoria,
    int $cantidad,
    float $precio
): array
```

**Salida esperada:**

```php
[
    "nombre" => "Rosas rojas",
    "categoria" => "Flores",
    "cantidad" => 50,
    "precio" => 5000.0
]
```

**Excepciones:**

Se genera `InvalidArgumentException` cuando:

- El nombre está vacío o contiene únicamente espacios.
- La categoría está vacía o contiene únicamente espacios.
- El nombre o la categoría tienen una codificación UTF-8 inválida.
- La cantidad es negativa.
- El precio es igual o inferior a cero.
- El precio no es un número finito (`NAN` o infinito).

**Reglas de validación:**

1. El nombre es obligatorio.
2. La categoría es obligatoria.
3. La cantidad debe ser un número entero mayor o igual a cero.
4. El precio debe ser mayor que cero y finito.
5. El nombre y la categoría se limpian de espacios al inicio y al final, incluidos espacios Unicode.
6. Los datos válidos se devuelven en un arreglo asociativo.

**Supuestos:**

- Cada registro representa un producto o insumo.
- Se permite registrar productos con cero existencias.
- El precio corresponde al valor unitario.
- No se contempla todavía la conexión con una base de datos.
- La función valida y devuelve los datos, pero no realiza persistencia.

## 5. Pruebas

Las pruebas automatizadas se encuentran en:

`tests/RegistrarProductoTest.php`

Para ejecutar las pruebas desde la carpeta raíz del proyecto:

En Windows CMD:

```cmd
vendor\bin\phpunit
```

En Linux o macOS:

```bash
vendor/bin/phpunit
```

La última ejecución de la suite registró:

- 10 pruebas ejecutadas.
- 14 aserciones.
- Todas las pruebas aprobadas.

También se pueden ejecutar las herramientas de revisión de calidad y seguridad:

**Revisión de estilo:**

```bash
vendor/bin/phpcs
```

**Auditoría de dependencias:**

```bash
composer audit
```

La última auditoría ejecutada no reportó avisos de vulnerabilidades conocidas.

## 6. Estructura del proyecto

```text
floristeria_darcy/
│
├── src/
│   └── registrarProducto.php
│
├── tests/
│   └── RegistrarProductoTest.php
│
├── docs/
│   └── informe_revision_calidad.md
│
├── .gitignore
├── composer.json
├── composer.lock
├── phpcs.xml
├── phpunit.xml
├── README.md
├── revision_seguridad.md
└── bitacora_ia.md
```

**Descripción de los archivos principales:**

- `src/registrarProducto.php`: contiene la función de registro y validación de productos.
- `tests/RegistrarProductoTest.php`: contiene las pruebas automatizadas.
- `composer.json`: declara las dependencias del proyecto.
- `composer.lock`: registra las versiones resueltas de las dependencias.
- `phpcs.xml`: configura las reglas de revisión de estilo.
- `phpunit.xml`: configura la ejecución de las pruebas.
- `revision_seguridad.md`: documenta la revisión de seguridad.
- `bitacora_ia.md`: registra el uso de inteligencia artificial durante el desarrollo.

## 7. Uso de inteligencia artificial

Se utilizó ChatGPT como herramienta de apoyo para revisar la calidad y seguridad del código, analizar posibles errores, proponer mejoras de validación y apoyar la documentación del proyecto.

Las sugerencias de la IA fueron verificadas mediante la revisión del código y la ejecución de las pruebas automatizadas.

Durante la revisión se identificaron aspectos relacionados con la validación de precios no finitos (`NAN` e infinito) y la normalización de espacios Unicode.

Los hallazgos confirmados fueron corregidos y documentados en `revision_seguridad.md`.

Las interacciones y decisiones relacionadas con el uso de IA se encuentran registradas en `bitacora_ia.md`.

## 8. Autor y licencia

**Autor:** Samuel Santiago Cabrera Castro.

**Proyecto:** Floristería Darcy.

**Repositorio:** https://github.com/NeoCoder29/floristeria_darcy

**Licencia:** No se ha definido una licencia de distribución para este proyecto.