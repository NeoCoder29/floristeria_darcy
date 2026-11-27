<?php

/**
 * Funciones de validación para productos de Floristería Darcy.
 *
 * PHP version 8.2
 *
 * @category Domain
 * @package  Floristeria
 */

declare(strict_types=1);

/**
 * Valida y normaliza los datos de un producto de la floristería.
 *
 * Elimina los espacios en blanco al inicio y al final del nombre
 * y de la categoría. Rechaza datos vacíos, cantidades negativas
 * y precios que no sean finitos o mayores que cero.
 *
 * @param string $nombre Nombre del producto.
 * @param string $categoria Categoría del producto.
 * @param int $cantidad Cantidad disponible en inventario.
 * @param float $precio Precio unitario del producto.
 *
 * @return array{
 *     nombre: string,
 *     categoria: string,
 *     cantidad: int,
 *     precio: float
 * }
 *
 * @throws InvalidArgumentException Si alguno de los datos no es válido.
 */
function registrarProducto(
    string $nombre,
    string $categoria,
    int $cantidad,
    float $precio
): array {
    $nombreNormalizado = preg_replace(
        '/^[\s\p{Z}]+|[\s\p{Z}]+$/u',
        '',
        $nombre
    );

    $categoriaNormalizada = preg_replace(
        '/^[\s\p{Z}]+|[\s\p{Z}]+$/u',
        '',
        $categoria
    );

    if (
        $nombreNormalizado === null
        || $categoriaNormalizada === null
    ) {
        throw new InvalidArgumentException(
            'El nombre y la categoría deben tener una codificación válida.'
        );
    }

    $nombre = $nombreNormalizado;
    $categoria = $categoriaNormalizada;

    $datosInvalidos =
        $nombre === ''
        || $categoria === ''
        || $cantidad < 0
        || !is_finite($precio)
        || $precio <= 0;

    if ($datosInvalidos) {
        throw new InvalidArgumentException(
            'Los datos del producto no son válidos.'
        );
    }

    return [
        'nombre' => $nombre,
        'categoria' => $categoria,
        'cantidad' => $cantidad,
        'precio' => $precio,
    ];
}
