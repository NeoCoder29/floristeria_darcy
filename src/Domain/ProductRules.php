<?php

declare(strict_types=1);

namespace Floristeria\Domain;

use InvalidArgumentException;

final class ProductRules
{
    /** @return array{name:string, category:string, quantity:int, price:float, min_stock:int} */
    public static function validate(
        string $name,
        string $category,
        int $quantity,
        float $price,
        int $minStock = 5
    ): array {
        $name = self::normalize($name);
        $category = self::normalize($category);

        if ($name === '' || $category === '') {
            throw new InvalidArgumentException('El nombre y la categoría son obligatorios.');
        }
        if (self::unicodeLength($name) > 150 || self::unicodeLength($category) > 100) {
            throw new InvalidArgumentException('El nombre o la categoría exceden la longitud permitida.');
        }
        if ($quantity < 0 || $minStock < 0) {
            throw new InvalidArgumentException('Las existencias y el stock mínimo no pueden ser negativos.');
        }
        if (!is_finite($price) || $price <= 0) {
            throw new InvalidArgumentException('El precio debe ser un número finito mayor que cero.');
        }

        return [
            'name' => $name,
            'category' => $category,
            'quantity' => $quantity,
            'price' => round($price, 2),
            'min_stock' => $minStock,
        ];
    }

    private static function unicodeLength(string $value): int
    {
        $count = preg_match_all('/./us', $value);
        if ($count === false) {
            throw new InvalidArgumentException('El texto contiene una codificación inválida.');
        }
        return $count;
    }

    private static function normalize(string $value): string
    {
        $normalized = preg_replace('/^[\s\p{Z}]+|[\s\p{Z}]+$/u', '', $value);
        if ($normalized === null) {
            throw new InvalidArgumentException('El texto contiene una codificación inválida.');
        }
        return $normalized;
    }
}
