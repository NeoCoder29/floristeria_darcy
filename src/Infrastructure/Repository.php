<?php

declare(strict_types=1);

namespace Floristeria\Infrastructure;

use PDO;

final class Repository
{
    public function __construct(private PDO $db) {}

    public function all(string $table, string $order = 'id DESC'): array
    {
        $allowed = ['products', 'categories', 'customers', 'sales'];
        if (!in_array($table, $allowed, true)) {
            throw new \InvalidArgumentException('Tabla no permitida.');
        }
        return $this->db->query("SELECT * FROM {$table} ORDER BY {$order}")->fetchAll();
    }

    public function dashboard(): array
    {
        return [
            'products' => (int) $this->db->query('SELECT COUNT(*) FROM products WHERE active = TRUE')->fetchColumn(),
            'customers' => (int) $this->db->query('SELECT COUNT(*) FROM customers')->fetchColumn(),
            'low_stock' => (int) $this->db->query('SELECT COUNT(*) FROM products WHERE active = TRUE AND quantity <= min_stock')->fetchColumn(),
            'sales_today' => (float) $this->db->query("SELECT COALESCE(SUM(total), 0) FROM sales WHERE status = 'completed' AND created_at::date = CURRENT_DATE")->fetchColumn(),
        ];
    }

    public function createCategory(string $name, string $description = ''): void
    {
        $stmt = $this->db->prepare('INSERT INTO categories (name, description) VALUES (:name, :description)');
        $stmt->execute(['name' => trim($name), 'description' => trim($description)]);
    }

    public function createProduct(array $data): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO products (category_id, name, description, quantity, price, min_stock)
             VALUES (:category_id, :name, :description, :quantity, :price, :min_stock)'
        );
        $stmt->execute($data);
    }

    public function createCustomer(array $data): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO customers (name, phone, email, address)
             VALUES (:name, :phone, :email, :address)'
        );
        $stmt->execute($data);
    }

    public function findProductForSale(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT id, name, quantity AS stock, price FROM products WHERE id = :id AND active = TRUE FOR UPDATE');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function createSale(?int $customerId, int $userId, array $calculation, array $productIds, string $notes = ''): int
    {
        $this->db->beginTransaction();
        try {
            $sale = $this->db->prepare(
                "INSERT INTO sales (customer_id, user_id, subtotal, total, notes)
                 VALUES (:customer_id, :user_id, :subtotal, :total, :notes) RETURNING id"
            );
            $sale->execute([
                'customer_id' => $customerId,
                'user_id' => $userId,
                'subtotal' => $calculation['subtotal'],
                'total' => $calculation['total'],
                'notes' => $notes,
            ]);
            $saleId = (int) $sale->fetchColumn();

            foreach ($calculation['lines'] as $index => $line) {
                $item = $this->db->prepare(
                    'INSERT INTO sale_items (sale_id, product_id, quantity, unit_price, line_total)
                     VALUES (:sale_id, :product_id, :quantity, :unit_price, :line_total)'
                );
                $item->execute([
                    'sale_id' => $saleId,
                    'product_id' => $productIds[$index],
                    'quantity' => $line['quantity'],
                    'unit_price' => $line['unit_price'],
                    'line_total' => $line['line_total'],
                ]);
                $update = $this->db->prepare('UPDATE products SET quantity = quantity - :quantity, updated_at = CURRENT_TIMESTAMP WHERE id = :id AND quantity >= :quantity');
                $update->execute(['quantity' => $line['quantity'], 'id' => $productIds[$index]]);
                if ($update->rowCount() !== 1) {
                    throw new \RuntimeException('No fue posible actualizar el inventario.');
                }
            }

            $this->db->commit();
            return $saleId;
        } catch (\Throwable $error) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $error;
        }
    }

    public function findUserByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = :email');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function createAdmin(string $name, string $email, string $password): void
    {
        $stmt = $this->db->prepare('INSERT INTO users (name, email, password_hash) VALUES (:name, :email, :hash) ON CONFLICT (email) DO NOTHING');
        $stmt->execute(['name' => $name, 'email' => $email, 'hash' => password_hash($password, PASSWORD_DEFAULT)]);
    }
}
