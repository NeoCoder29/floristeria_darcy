<?php

declare(strict_types=1);

namespace Floristeria\Domain;

use InvalidArgumentException;

final class InventoryRules
{
    public static function calculateNewStock(int $currentStock, int $change): int
    {
        if ($currentStock < 0) {
            throw new InvalidArgumentException('El inventario actual no puede ser negativo.');
        }
        if ($change === 0) {
            throw new InvalidArgumentException('El movimiento debe modificar las existencias.');
        }
        $newStock = $currentStock + $change;
        if ($newStock < 0) {
            throw new InvalidArgumentException('No hay existencias suficientes para esa salida.');
        }
        return $newStock;
    }

    public static function isLowStock(int $quantity, int $minimum): bool
    {
        if ($quantity < 0 || $minimum < 0) {
            throw new InvalidArgumentException('Las cantidades no pueden ser negativas.');
        }
        return $quantity <= $minimum;
    }
}
