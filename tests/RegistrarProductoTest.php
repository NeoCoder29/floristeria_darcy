```php
<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../src/registrarProducto.php';

class RegistrarProductoTest extends TestCase
{
    // PRUEBA 1: Registrar rosas rojas correctamente
    public function testRegistrarRosasRojas(): void
    {
        $producto = registrarProducto(
            "Rosas rojas",
            "Flores",
            50,
            5000
        );

        $this->assertSame("Rosas rojas", $producto["nombre"]);
        $this->assertSame("Flores", $producto["categoria"]);
        $this->assertSame(50, $producto["cantidad"]);
        $this->assertSame(5000.0, $producto["precio"]);
    }

    // PRUEBA 2: Registrar papel coreano correctamente
    public function testRegistrarPapelCoreano(): void
    {
        $producto = registrarProducto(
            "Papel coreano",
            "Decoración",
            20,
            3500
        );

        $this->assertSame("Papel coreano", $producto["nombre"]);
        $this->assertSame("Decoración", $producto["categoria"]);
    }

    // PRUEBA 3: Permitir registrar productos sin existencias
    public function testPermitirRegistrarProductoSinExistencias(): void
    {
        $producto = registrarProducto(
            "Rosas blancas",
            "Flores",
            0,
            4500
        );

        $this->assertSame(0, $producto["cantidad"]);
    }

    // PRUEBA 4: Rechazar cantidades negativas
    public function testRechazarCantidadNegativa(): void
    {
        $this->expectException(InvalidArgumentException::class);

        registrarProducto(
            "Girasoles",
            "Flores",
            -5,
            6000
        );
    }

    // PRUEBA 5: Rechazar nombres vacíos
    public function testRechazarNombreVacio(): void
    {
        $this->expectException(InvalidArgumentException::class);

        registrarProducto(
            "",
            "Flores",
            10,
            5000
        );
    }

    // PRUEBA 6: Rechazar categorías vacías
    public function testRechazarCategoriaVacia(): void
    {
        $this->expectException(InvalidArgumentException::class);

        registrarProducto(
            "Rosas",
            "",
            10,
            5000
        );
    }

    // PRUEBA 7: Rechazar precios iguales a cero
    public function testRechazarPrecioCero(): void
    {
        $this->expectException(InvalidArgumentException::class);

        registrarProducto(
            "Rosas",
            "Flores",
            10,
            0
        );
    }

    // PRUEBA 8: Rechazar precios negativos
    public function testRechazarPrecioNegativo(): void
    {
        $this->expectException(InvalidArgumentException::class);

        registrarProducto(
            "Rosas",
            "Flores",
            10,
            -500
        );
    }
}
