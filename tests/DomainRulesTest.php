<?php

declare(strict_types=1);

namespace Tests;

use Floristeria\Domain\InventoryRules;
use Floristeria\Domain\ProductRules;
use Floristeria\Domain\SaleRules;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Pruebas unitarias y de aceptación de las reglas críticas del negocio.
 *
 * Los escenarios siguen el patrón Dado / Cuando / Entonces y no requieren
 * conexión a PostgreSQL porque prueban reglas puras del dominio.
 */
final class DomainRulesTest extends TestCase
{
    // PRODUCTOS

    public function testDadoProductoValidoCuandoSeValidaEntoncesNormalizaYRedondea(): void
    {
        // Dado: datos válidos con espacios externos y precio con tres decimales.
        // Cuando: se valida el producto.
        $result = ProductRules::validate('  Rosas rojas  ', '  Flores  ', 10, 1250.567, 3);

        // Entonces: se normalizan textos y el precio queda en dos decimales.
        self::assertSame('Rosas rojas', $result['name']);
        self::assertSame('Flores', $result['category']);
        self::assertSame(10, $result['quantity']);
        self::assertSame(1250.57, $result['price']);
        self::assertSame(3, $result['min_stock']);
    }

    public function testDadoProductoConStockCeroCuandoSeValidaEntoncesSeAcepta(): void
    {
        // Dado: producto con existencias en cero.
        // Cuando: se valida con precio positivo.
        $result = ProductRules::validate('Claveles', 'Flores', 0, 1.0, 0);

        // Entonces: se conserva el stock cero.
        self::assertSame(0, $result['quantity']);
    }

    public function testRechazaNombreVacio(): void
    {
        $this->expectException(InvalidArgumentException::class);
        ProductRules::validate('   ', 'Flores', 1, 100.0);
    }

    public function testRechazaCategoriaVacia(): void
    {
        $this->expectException(InvalidArgumentException::class);
        ProductRules::validate('Rosa', "\u{00A0}", 1, 100.0);
    }

    public function testAceptaLongitudesMaximasPermitidas(): void
    {
        $result = ProductRules::validate(str_repeat('a', 150), str_repeat('b', 100), 1, 1.0);
        self::assertSame(150, mb_strlen($result['name'], 'UTF-8'));
        self::assertSame(100, mb_strlen($result['category'], 'UTF-8'));
    }

    public function testRechazaNombreQueSuperaLongitudMaxima(): void
    {
        $this->expectException(InvalidArgumentException::class);
        ProductRules::validate(str_repeat('a', 151), 'Flores', 1, 1.0);
    }

    public function testRechazaCategoriaQueSuperaLongitudMaxima(): void
    {
        $this->expectException(InvalidArgumentException::class);
        ProductRules::validate('Rosa', str_repeat('b', 101), 1, 1.0);
    }

    public function testRechazaCantidadNegativa(): void
    {
        $this->expectException(InvalidArgumentException::class);
        ProductRules::validate('Rosa', 'Flores', -1, 1.0);
    }

    public function testRechazaStockMinimoNegativo(): void
    {
        $this->expectException(InvalidArgumentException::class);
        ProductRules::validate('Rosa', 'Flores', 1, 1.0, -1);
    }

    public function testRechazaPrecioCeroYNegativo(): void
    {
        foreach ([0.0, -0.01] as $price) {
            try {
                ProductRules::validate('Rosa', 'Flores', 1, $price);
                self::fail('Se esperaba InvalidArgumentException.');
            } catch (InvalidArgumentException) {
                self::assertTrue(true);
            }
        }
    }

    public function testRechazaPrecioNoFinito(): void
    {
        foreach ([NAN, INF, -INF] as $price) {
            try {
                ProductRules::validate('Rosa', 'Flores', 1, $price);
                self::fail('Se esperaba InvalidArgumentException.');
            } catch (InvalidArgumentException) {
                self::assertTrue(true);
            }
        }
    }

    // INVENTARIO

    public function testDadoInventarioCuandoEntraMercanciaEntoncesAumentaStock(): void
    {
        // Dado: 10 unidades disponibles.
        // Cuando: ingresan 5 unidades.
        $newStock = InventoryRules::calculateNewStock(10, 5);

        // Entonces: el inventario queda en 15.
        self::assertSame(15, $newStock);
    }

    public function testDadoInventarioCuandoSaleTodoElStockEntoncesQuedaEnCero(): void
    {
        // Dado: 10 unidades disponibles.
        // Cuando: salen exactamente 10 unidades.
        $newStock = InventoryRules::calculateNewStock(10, -10);

        // Entonces: el inventario puede quedar exactamente en cero.
        self::assertSame(0, $newStock);
    }

    public function testRechazaInventarioInicialNegativo(): void
    {
        $this->expectException(InvalidArgumentException::class);
        InventoryRules::calculateNewStock(-1, 2);
    }

    public function testRechazaMovimientoSinCambio(): void
    {
        $this->expectException(InvalidArgumentException::class);
        InventoryRules::calculateNewStock(5, 0);
    }

    public function testRechazaSalidaSuperiorAlStock(): void
    {
        $this->expectException(InvalidArgumentException::class);
        InventoryRules::calculateNewStock(5, -6);
    }

    public function testAlertaStockEnElLimiteYDebajoPeroNoEncima(): void
    {
        self::assertTrue(InventoryRules::isLowStock(5, 5));
        self::assertTrue(InventoryRules::isLowStock(4, 5));
        self::assertFalse(InventoryRules::isLowStock(6, 5));
    }

    public function testRechazaCantidadesNegativasEnAlertaDeStock(): void
    {
        foreach ([[-1, 5], [1, -1]] as [$quantity, $minimum]) {
            try {
                InventoryRules::isLowStock($quantity, $minimum);
                self::fail('Se esperaba InvalidArgumentException.');
            } catch (InvalidArgumentException) {
                self::assertTrue(true);
            }
        }
    }

    // VENTAS

    public function testDadoVentaValidaCuandoSeCalculaEntoncesDevuelveSubtotalYTotal(): void
    {
        // Dado: dos unidades a $10.000 con stock suficiente.
        // Cuando: se calcula una venta sin descuento.
        $result = SaleRules::calculate([
            ['price' => 10000, 'stock' => 5, 'quantity' => 2],
        ]);

        // Entonces: subtotal y total son $20.000.
        self::assertSame(20000.0, $result['subtotal']);
        self::assertSame(20000.0, $result['total']);
        self::assertSame(2, $result['lines'][0]['quantity']);
    }

    public function testAplicaDescuentoYRedondeaTotal(): void
    {
        $result = SaleRules::calculate([
            ['price' => 10000, 'stock' => 5, 'quantity' => 2],
        ], 10.0);

        self::assertSame(20000.0, $result['subtotal']);
        self::assertSame(18000.0, $result['total']);
    }

    public function testAceptaDescuentoEnLimitesCeroYCien(): void
    {
        $items = [['price' => 100, 'stock' => 1, 'quantity' => 1]];
        self::assertSame(100.0, SaleRules::calculate($items, 0.0)['total']);
        self::assertSame(0.0, SaleRules::calculate($items, 100.0)['total']);
    }

    public function testRechazaVentaSinProductos(): void
    {
        $this->expectException(InvalidArgumentException::class);
        SaleRules::calculate([]);
    }

    public function testRechazaDescuentoFueraDeRangoYNoFinito(): void
    {
        $items = [['price' => 100, 'stock' => 1, 'quantity' => 1]];
        foreach ([-0.01, 100.01, NAN, INF] as $discount) {
            try {
                SaleRules::calculate($items, $discount);
                self::fail('Se esperaba InvalidArgumentException.');
            } catch (InvalidArgumentException) {
                self::assertTrue(true);
            }
        }
    }

    public function testRechazaDatosDeProductoIncompletosONoNumericos(): void
    {
        $invalidItems = [
            [['price' => '100abc', 'stock' => 1, 'quantity' => 1]],
            [['price' => 100, 'stock' => '1stock', 'quantity' => 1]],
            [['price' => 100, 'stock' => 1, 'quantity' => '1unidad']],
            [['stock' => 1, 'quantity' => 1]],
        ];

        foreach ($invalidItems as $items) {
            try {
                SaleRules::calculate($items);
                self::fail('Se esperaba InvalidArgumentException.');
            } catch (InvalidArgumentException) {
                self::assertTrue(true);
            }
        }
    }

    public function testRechazaPrecioCantidadYStockInvalidos(): void
    {
        $invalidItems = [
            ['price' => 0, 'stock' => 1, 'quantity' => 1],
            ['price' => -1, 'stock' => 1, 'quantity' => 1],
            ['price' => INF, 'stock' => 1, 'quantity' => 1],
            ['price' => 100, 'stock' => -1, 'quantity' => 1],
            ['price' => 100, 'stock' => 1, 'quantity' => 0],
        ];

        foreach ($invalidItems as $item) {
            try {
                SaleRules::calculate([$item]);
                self::fail('Se esperaba InvalidArgumentException.');
            } catch (InvalidArgumentException) {
                self::assertTrue(true);
            }
        }
    }

    public function testRechazaCantidadSuperiorAlStock(): void
    {
        $this->expectException(InvalidArgumentException::class);
        SaleRules::calculate([
            ['price' => 100, 'stock' => 2, 'quantity' => 3],
        ]);
    }
}
