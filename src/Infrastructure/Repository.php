<?php

/**
 * Repositorio de acceso a datos de Floristería Darcy.
 *
 * PHP version 8.2
 *
 * @category Infrastructure
 * @package  Floristeria
 */

declare(strict_types=1);

namespace Floristeria\Infrastructure;

use InvalidArgumentException;
use PDO;
use RuntimeException;
use Throwable;

final class Repository
{
    public function __construct(private PDO $db)
    {
    }

    public function all(
        string $table,
        string $order = 'id DESC'
    ): array {
        $allowedTables = [
            'products',
            'categories',
            'customers',
            'sales',
        ];

        $allowedOrders = [
            'id DESC',
            'id ASC',
            'name ASC',
            'name DESC',
            'created_at DESC',
        ];

        if (!in_array($table, $allowedTables, true)) {
            throw new InvalidArgumentException(
                'Tabla no permitida.'
            );
        }

        if (!in_array($order, $allowedOrders, true)) {
            throw new InvalidArgumentException(
                'Ordenamiento no permitido.'
            );
        }

        $query = "SELECT * FROM {$table} ORDER BY {$order}";

        return $this->db->query($query)->fetchAll();
    }

    public function dashboard(): array
    {
        $products = $this->db->query(
            'SELECT COUNT(*) FROM products WHERE active = TRUE'
        )->fetchColumn();

        $customers = $this->db->query(
            'SELECT COUNT(*) FROM customers'
        )->fetchColumn();

        $lowStock = $this->db->query(
            'SELECT COUNT(*) FROM products
             WHERE active = TRUE AND quantity <= min_stock'
        )->fetchColumn();

        $salesToday = $this->db->query(
            "SELECT COALESCE(SUM(total), 0)
             FROM sales
             WHERE status = 'completed'
             AND created_at::date = CURRENT_DATE"
        )->fetchColumn();

        return [
            'products' => (int) $products,
            'customers' => (int) $customers,
            'low_stock' => (int) $lowStock,
            'sales_today' => (float) $salesToday,
        ];
    }

    public function createCategory(
        string $name,
        string $description = ''
    ): void {
        $stmt = $this->db->prepare(
            'INSERT INTO categories (name, description)
             VALUES (:name, :description)'
        );

        $stmt->execute([
            'name' => trim($name),
            'description' => trim($description),
        ]);
    }

    public function createProduct(array $data): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO products (
                category_id,
                name,
                description,
                quantity,
                price,
                min_stock
            )
            VALUES (
                :category_id,
                :name,
                :description,
                :quantity,
                :price,
                :min_stock
            )'
        );

        $stmt->execute($data);
    }

    public function createCustomer(array $data): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO customers (
                name,
                phone,
                email,
                address
            )
            VALUES (
                :name,
                :phone,
                :email,
                :address
            )'
        );

        $stmt->execute($data);
    }

    public function createSale(
        ?int $customerId,
        int $userId,
        array $items,
        float $discountPercent = 0.0,
        string $notes = ''
    ): int {
        if ($items === []) {
            throw new InvalidArgumentException('La venta debe contener al menos un producto.');
        }

        $this->db->beginTransaction();

        try {
            // Lock rows first, then calculate from current database values. This prevents
            // stale browser stock values from allowing overselling in concurrent sales.
            ksort($items);
            $lockedProducts = [];
            $find = $this->db->prepare(
                'SELECT id, name, quantity AS stock, price
                 FROM products WHERE id = :id AND active = TRUE FOR UPDATE'
            );

            foreach ($items as $productId => $quantity) {
                $productId = filter_var($productId, FILTER_VALIDATE_INT);
                $quantity = filter_var($quantity, FILTER_VALIDATE_INT);
                if ($productId === false || $productId < 1 || $quantity === false || $quantity < 1) {
                    throw new InvalidArgumentException('Hay productos o cantidades no válidos en la venta.');
                }
                $find->execute(['id' => $productId]);
                $product = $find->fetch();
                if (!$product) {
                    throw new RuntimeException('Uno de los productos ya no está disponible.');
                }
                $lockedProducts[] = [
                    'id' => (int) $product['id'],
                    'price' => (float) $product['price'],
                    'stock' => (int) $product['stock'],
                    'quantity' => (int) $quantity,
                ];
            }

            $calculation = \Floristeria\Domain\SaleRules::calculate($lockedProducts, $discountPercent);
            $sale = $this->db->prepare(
                'INSERT INTO sales (customer_id, user_id, subtotal, total, discount_percent, notes)
                 VALUES (:customer_id, :user_id, :subtotal, :total, :discount, :notes) RETURNING id'
            );
            $sale->execute([
                'customer_id' => $customerId,
                'user_id' => $userId,
                'subtotal' => $calculation['subtotal'],
                'total' => $calculation['total'],
                'discount' => round($discountPercent, 2),
                'notes' => trim($notes),
            ]);
            $saleId = (int) $sale->fetchColumn();

            $insertItem = $this->db->prepare(
                'INSERT INTO sale_items (sale_id, product_id, quantity, unit_price, line_total)
                 VALUES (:sale_id, :product_id, :quantity, :unit_price, :line_total)'
            );
            $decrement = $this->db->prepare(
                'UPDATE products SET quantity = quantity - :quantity, updated_at = CURRENT_TIMESTAMP
                 WHERE id = :id AND quantity >= :quantity AND active = TRUE'
            );
            $movement = $this->db->prepare(
                "INSERT INTO inventory_movements
                 (product_id, user_id, movement_type, quantity, previous_stock, new_stock, reason)
                 VALUES (:product_id, :user_id, 'exit', :quantity, :previous_stock, :new_stock, :reason)"
            );

            foreach ($lockedProducts as $index => $product) {
                $line = $calculation['lines'][$index];
                $insertItem->execute([
                    'sale_id' => $saleId,
                    'product_id' => $product['id'],
                    'quantity' => $line['quantity'],
                    'unit_price' => $line['unit_price'],
                    'line_total' => $line['line_total'],
                ]);
                $decrement->execute(['quantity' => $line['quantity'], 'id' => $product['id']]);
                if ($decrement->rowCount() !== 1) {
                    throw new RuntimeException('No fue posible actualizar las existencias. La venta fue cancelada.');
                }
                $movement->execute([
                    'product_id' => $product['id'],
                    'user_id' => $userId,
                    'quantity' => $line['quantity'],
                    'previous_stock' => $product['stock'],
                    'new_stock' => $product['stock'] - $line['quantity'],
                    'reason' => 'Salida automática por venta #' . $saleId,
                ]);
            }

            $this->db->commit();
            return $saleId;
        } catch (Throwable $error) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $error;
        }
    }

    public function registerInventoryMovement(
        int $productId,
        int $userId,
        string $type,
        int $quantity,
        string $reason
    ): void {
        if (!in_array($type, ['entry', 'exit'], true) || $quantity < 1 || trim($reason) === '') {
            throw new InvalidArgumentException('Completa correctamente el tipo, cantidad y motivo del movimiento.');
        }

        $this->db->beginTransaction();
        try {
            $find = $this->db->prepare(
                'SELECT quantity FROM products WHERE id = :id AND active = TRUE FOR UPDATE'
            );
            $find->execute(['id' => $productId]);
            $current = $find->fetchColumn();
            if ($current === false) {
                throw new RuntimeException('El producto seleccionado no existe o está inactivo.');
            }
            $previous = (int) $current;
            $change = $type === 'entry' ? $quantity : -$quantity;
            $new = \Floristeria\Domain\InventoryRules::calculateNewStock($previous, $change);

            $update = $this->db->prepare(
                'UPDATE products SET quantity = :quantity, updated_at = CURRENT_TIMESTAMP WHERE id = :id'
            );
            $update->execute(['quantity' => $new, 'id' => $productId]);

            $insert = $this->db->prepare(
                'INSERT INTO inventory_movements
                 (product_id, user_id, movement_type, quantity, previous_stock, new_stock, reason)
                 VALUES (:product_id, :user_id, :type, :quantity, :previous, :new, :reason)'
            );
            $insert->execute([
                'product_id' => $productId,
                'user_id' => $userId,
                'type' => $type,
                'quantity' => $quantity,
                'previous' => $previous,
                'new' => $new,
                'reason' => trim($reason),
            ]);
            $this->db->commit();
        } catch (Throwable $error) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $error;
        }
    }

    public function inventoryMovements(int $limit = 30): array
    {
        $limit = max(1, min($limit, 100));
        return $this->db->query(
            'SELECT m.*, p.name AS product_name, u.name AS user_name
             FROM inventory_movements m
             JOIN products p ON p.id = m.product_id
             JOIN users u ON u.id = m.user_id
             ORDER BY m.created_at DESC, m.id DESC LIMIT ' . $limit
        )->fetchAll();
    }

    public function findUserByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM users WHERE email = :email'
        );

        $stmt->execute(['email' => $email]);

        $user = $stmt->fetch();

        return $user ?: null;
    }

    public function createAdmin(
        string $name,
        string $email,
        string $password
    ): void {
        $stmt = $this->db->prepare(
            'INSERT INTO users (
                name,
                email,
                password_hash
            )
            VALUES (
                :name,
                :email,
                :hash
            )
            ON CONFLICT (email) DO NOTHING'
        );

        $stmt->execute([
            'name' => $name,
            'email' => $email,
            'hash' => password_hash($password, PASSWORD_DEFAULT),
        ]);
    }
}
