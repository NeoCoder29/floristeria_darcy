<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/src/Domain/ProductRules.php';
require_once dirname(__DIR__) . '/src/Domain/InventoryRules.php';
require_once dirname(__DIR__) . '/src/Domain/SaleRules.php';

use Floristeria\Domain\ProductRules;
use Floristeria\Domain\InventoryRules;
use Floristeria\Domain\SaleRules;

$input = json_decode(stream_get_contents(STDIN), true, 512, JSON_THROW_ON_ERROR);
try {
    $result = match ($input['function'] ?? '') {
        'product' => ProductRules::validate(
            (string)($input['name'] ?? ''),
            (string)($input['category'] ?? ''),
            (int)($input['quantity'] ?? -1),
            (float)($input['price'] ?? 0),
            (int)($input['min_stock'] ?? 5)
        ),
        'stock' => InventoryRules::calculateNewStock((int)$input['current'], (int)$input['change']),
        'low_stock' => InventoryRules::isLowStock((int)$input['quantity'], (int)$input['minimum']),
        'sale' => SaleRules::calculate($input['items'] ?? [], (float)($input['discount'] ?? 0)),
        default => throw new InvalidArgumentException('Función de prueba desconocida.'),
    };
    echo json_encode(['ok' => true, 'result' => $result], JSON_THROW_ON_ERROR);
} catch (Throwable $error) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'error' => get_class($error), 'message' => $error->getMessage()]);
}
