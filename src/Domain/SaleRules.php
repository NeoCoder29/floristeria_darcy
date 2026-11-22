<?php

/**
 * Reglas de negocio para el procesamiento de ventas.
 *
 * PHP version 8.2
 *
 * @category Domain
 * @package  Floristeria
 * @author   Floristeria Darcy
 */

declare(strict_types=1);

namespace Floristeria\Domain;

use InvalidArgumentException;

final class SaleRules
{
    /**
     * Calcula el subtotal, descuento y total de una venta.
     *
     * @param array<int, array{
     *     price: float,
     *     stock: int,
     *     quantity: int
     * }> $items
     *
     * @return array{
     *     subtotal: float,
     *     total: float,
     *     lines: array<int, array{
     *         quantity: int,
     *         unit_price: float,
     *         line_total: float
     *     }>
     * }
     */
    public static function calculate(
        array $items,
        float $discountPercent = 0.0
    ): array {
        if ($items === []) {
            throw new InvalidArgumentException(
                'La venta debe contener al menos un producto.'
            );
        }

        if (
            !is_finite($discountPercent)
            || $discountPercent < 0
            || $discountPercent > 100
        ) {
            throw new InvalidArgumentException(
                'El descuento debe estar entre 0 y 100%.'
            );
        }

        $subtotal = 0.0;
        $lines = [];

        foreach ($items as $item) {
            $price = (float) ($item['price'] ?? 0);
            $stock = (int) ($item['stock'] ?? -1);
            $quantity = (int) ($item['quantity'] ?? 0);

            if (
                !is_finite($price)
                || $price <= 0
                || $quantity <= 0
                || $stock < 0
            ) {
                throw new InvalidArgumentException(
                    'Los datos de un producto de la venta no son válidos.'
                );
            }

            if ($quantity > $stock) {
                throw new InvalidArgumentException(
                    'La cantidad solicitada supera las existencias disponibles.'
                );
            }

            $lineTotal = round($price * $quantity, 2);
            $subtotal += $lineTotal;

            $lines[] = [
                'quantity' => $quantity,
                'unit_price' => round($price, 2),
                'line_total' => $lineTotal,
            ];
        }

        $subtotal = round($subtotal, 2);
        $total = round(
            $subtotal * (1 - ($discountPercent / 100)),
            2
        );

        return [
            'subtotal' => $subtotal,
            'total' => $total,
            'lines' => $lines,
        ];
    }
}
