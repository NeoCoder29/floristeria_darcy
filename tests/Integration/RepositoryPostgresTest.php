<?php

declare(strict_types=1);

namespace Tests\Integration;

use Floristeria\Infrastructure\Repository;
use PDO;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * Pruebas de integración del repositorio contra una base PostgreSQL aislada.
 *
 * Requiere DB_TEST_DSN, DB_TEST_USER y DB_TEST_PASSWORD. Nunca usa .env.
 */
final class RepositoryPostgresTest extends TestCase
{
    private static PDO $db;
    private Repository $repository;
    private int $userId;
    private int $categoryId;
    private int $productId;

    public static function setUpBeforeClass(): void
    {
        $dsn = getenv('DB_TEST_DSN');
        if (!$dsn) {
            self::markTestSkipped('No se configuró DB_TEST_DSN para PostgreSQL de pruebas.');
        }

        self::$db = new PDO(
            $dsn,
            getenv('DB_TEST_USER') ?: 'postgres',
            getenv('DB_TEST_PASSWORD') ?: '',
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );

        $schema = file_get_contents(dirname(__DIR__, 2) . '/database/schema.sql');
        if ($schema === false) {
            throw new RuntimeException('No se pudo leer database/schema.sql.');
        }
        self::$db->exec($schema);
    }

    protected function setUp(): void
    {
        self::$db->exec(
            'TRUNCATE TABLE inventory_movements, sale_items, sales, products, customers, categories, users ' .
            'RESTART IDENTITY CASCADE'
        );

        $user = self::$db->prepare(
            'INSERT INTO users (name, email, password_hash) VALUES (:name, :email, :hash) RETURNING id'
        );
        $user->execute([
            'name' => 'Usuario de pruebas',
            'email' => 'test@example.invalid',
            'hash' => password_hash('test-password', PASSWORD_DEFAULT),
        ]);
        $this->userId = (int) $user->fetchColumn();

        $category = self::$db->prepare(
            'INSERT INTO categories (name) VALUES (:name) RETURNING id'
        );
        $category->execute(['name' => 'Flores de prueba']);
        $this->categoryId = (int) $category->fetchColumn();

        $this->productId = $this->insertProduct('Rosas de prueba', 10, 10000.00);
        $this->repository = new Repository(self::$db);
    }

    public function testRegistraEntradaDeInventarioYDejaTrazabilidad(): void
    {
        $this->repository->registerInventoryMovement(
            $this->productId,
            $this->userId,
            'entry',
            5,
            'Compra de prueba'
        );

        self::assertSame(15, $this->stock());
        $movement = self::$db->query('SELECT * FROM inventory_movements')->fetch();
        self::assertSame('entry', $movement['movement_type']);
        self::assertSame(10, (int) $movement['previous_stock']);
        self::assertSame(15, (int) $movement['new_stock']);
        self::assertSame('Compra de prueba', $movement['reason']);
    }

    public function testRechazaSalidaSinExistenciasYRevierteLaTransaccion(): void
    {
        try {
            $this->repository->registerInventoryMovement(
                $this->productId,
                $this->userId,
                'exit',
                11,
                'Salida inválida'
            );
            self::fail('Se esperaba una excepción por stock insuficiente.');
        } catch (\InvalidArgumentException) {
            self::assertSame(10, $this->stock());
            self::assertSame(0, (int) self::$db->query('SELECT COUNT(*) FROM inventory_movements')->fetchColumn());
            self::assertFalse(self::$db->inTransaction());
        }
    }

    public function testRegistraVentaDescuentaStockYGeneraMovimiento(): void
    {
        $saleId = $this->repository->createSale(
            null,
            $this->userId,
            [$this->productId => 2],
            10.0,
            'Venta de integración'
        );

        self::assertGreaterThan(0, $saleId);
        self::assertSame(8, $this->stock());

        $sale = self::$db->prepare('SELECT subtotal, total, discount_percent FROM sales WHERE id = :id');
        $sale->execute(['id' => $saleId]);
        $saleData = $sale->fetch();
        self::assertEquals(20000.00, (float) $saleData['subtotal']);
        self::assertEquals(18000.00, (float) $saleData['total']);
        self::assertEquals(10.00, (float) $saleData['discount_percent']);

        self::assertSame(1, (int) self::$db->query('SELECT COUNT(*) FROM sale_items')->fetchColumn());
        $movement = self::$db->query('SELECT * FROM inventory_movements')->fetch();
        self::assertSame('exit', $movement['movement_type']);
        self::assertSame(10, (int) $movement['previous_stock']);
        self::assertSame(8, (int) $movement['new_stock']);
    }

    public function testVentaConStockInsuficienteNoDejaVentaNiDescuentaInventario(): void
    {
        try {
            $this->repository->createSale(null, $this->userId, [$this->productId => 11]);
            self::fail('Se esperaba una excepción por stock insuficiente.');
        } catch (\InvalidArgumentException) {
            self::assertSame(10, $this->stock());
            self::assertSame(0, (int) self::$db->query('SELECT COUNT(*) FROM sales')->fetchColumn());
            self::assertSame(0, (int) self::$db->query('SELECT COUNT(*) FROM sale_items')->fetchColumn());
            self::assertSame(0, (int) self::$db->query('SELECT COUNT(*) FROM inventory_movements')->fetchColumn());
            self::assertFalse(self::$db->inTransaction());
        }
    }

    public function testRechazaTablasYOrdenamientosNoPermitidos(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->repository->all('users');
    }

    private function insertProduct(string $name, int $quantity, float $price): int
    {
        $statement = self::$db->prepare(
            'INSERT INTO products (category_id, name, quantity, price, min_stock)
             VALUES (:category, :name, :quantity, :price, 2) RETURNING id'
        );
        $statement->execute([
            'category' => $this->categoryId,
            'name' => $name,
            'quantity' => $quantity,
            'price' => $price,
        ]);
        return (int) $statement->fetchColumn();
    }

    private function stock(): int
    {
        $statement = self::$db->prepare('SELECT quantity FROM products WHERE id = :id');
        $statement->execute(['id' => $this->productId]);
        return (int) $statement->fetchColumn();
    }
}
