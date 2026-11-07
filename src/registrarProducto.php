<?php

function registrarProducto(
    string $nombre,
    string $categoria,
    int $cantidad,
    float $precio
): array {

    if (trim($nombre) === '') {
        throw new InvalidArgumentException(
            "El nombre del producto es obligatorio."
        );
    }

    if (trim($categoria) === '') {
        throw new InvalidArgumentException(
            "La categoría del producto es obligatoria."
        );
    }

    if ($cantidad < 0) {
        throw new InvalidArgumentException(
            "La cantidad no puede ser negativa."
        );
    }

    if ($precio <= 0) {
        throw new InvalidArgumentException(
            "El precio debe ser mayor que cero."
        );
    }

    return [
        "nombre" => trim($nombre),
        "categoria" => trim($categoria),
        "cantidad" => $cantidad,
        "precio" => $precio
    ];
}