# Floristería Darcy

## Funcionalidad de la semana 5

### Historia de usuario HU-01: Registrar productos e insumos

**Como:** propietaria de la Floristería Darcy.

**Quiero:** registrar productos e insumos con su información básica.

**Para:** mantener organizado el inventario y conocer las existencias disponibles.

**Criterios de aceptación:**

1. **Registro correcto:** dado que un producto no está registrado, cuando se ingresan su nombre, categoría, cantidad y precio con datos válidos, entonces el sistema devuelve la información del producto registrado.
2. **Cantidad inválida:** dado que se está registrando un producto, cuando se ingresa una cantidad negativa, entonces el sistema rechaza el registro y genera una excepción indicando que los datos no son válidos.

### Contrato de la función

**Nombre:** `registrarProducto`

**Firma en PHP:**

```php
function registrarProducto(
    string $nombre,
    string $categoria,
    int $cantidad,
    float $precio
): array
```

**Entradas:**

- `nombre`: string. Nombre del producto o insumo.
- `categoria`: string. Categoría a la que pertenece.
- `cantidad`: int. Existencias iniciales.
- `precio`: float. Precio unitario del producto.

**Salida:**

Devuelve un arreglo asociativo con los datos del producto:

```php
[
    "nombre" => "Rosas rojas",
    "categoria" => "Flores",
    "cantidad" => 50,
    "precio" => 5000.0
]
```

**Excepciones:**

Se genera una excepción `InvalidArgumentException` cuando:

- El nombre está vacío o contiene únicamente espacios.
- La categoría está vacía o contiene únicamente espacios.
- La cantidad es negativa.
- El precio es igual o inferior a cero.

### Reglas de validación

1. El nombre es obligatorio.
2. La categoría es obligatoria.
3. La cantidad debe ser un número entero mayor o igual a cero.
4. El precio debe ser mayor que cero.
5. El nombre y la categoría se limpian de espacios al inicio y al final.
6. Los datos válidos se devuelven en un arreglo asociativo.

### Supuestos

- Cada registro representa un producto o insumo.
- Se permite registrar productos con cero existencias.
- El precio corresponde al valor unitario.
- No se contempla todavía la conexión con una base de datos.
- La función valida y devuelve los datos, pero no realiza persistencia.

### Casos límite

- Nombre compuesto únicamente por espacios.
- Categoría vacía.
- Cantidad igual a cero.
- Cantidad negativa.
- Precio igual a cero.
- Precio negativo.

### Ejemplos de entrada y salida

**Ejemplo 1: registro válido de rosas**

Entrada:

```php
registrarProducto("Rosas rojas", "Flores", 50, 5000.0);
```

Salida esperada:

```php
[
    "nombre" => "Rosas rojas",
    "categoria" => "Flores",
    "cantidad" => 50,
    "precio" => 5000.0
]
```

**Ejemplo 2: registro válido de papel decorativo**

Entrada:

```php
registrarProducto("Papel coreano", "Decoración", 20, 3500.0);
```

Salida esperada:

```php
[
    "nombre" => "Papel coreano",
    "categoria" => "Decoración",
    "cantidad" => 20,
    "precio" => 3500.0
]
```

**Ejemplo 3: cantidad negativa**

Entrada:

```php
registrarProducto("Rosa roja", "Flores", -5, 5000.0);
```

Salida esperada:

```text
InvalidArgumentException:
Los datos del producto no son válidos.
```

### Tecnologías

- PHP 8.2
- Composer
- PHPUnit 11
- Git y GitHub

### Ejecución de las pruebas

Desde la carpeta raíz del proyecto, ejecutar:

```bash
vendor/bin/phpunit
```

Las pruebas automatizadas se encuentran en:

```text
tests/RegistrarProductoTest.php
```

### Metodología

Se utiliza desarrollo guiado por pruebas (TDD), comenzando con la creación y ejecución de pruebas automatizadas antes de implementar la funcionalidad. El proceso incluye las fases roja, verde y refactorización.