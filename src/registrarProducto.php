<?php

function registrarProducto(
    string $nombre,
    string $categoria,
    int $cantidad,
    float $precio
): array {

    $nombre = trim($nombre);
    $categoria = trim($categoria);

    $datosInvalidos =
        $nombre === '' ||
        $categoria === '' ||
        $cantidad < 0 ||
        $precio <= 0;

    if ($datosInvalidos) {
        throw new InvalidArgumentException(
            "Los datos del producto no son válidos."
        );
    }

    return [
        "nombre" => $nombre,
        "categoria" => $categoria,
        "cantidad" => $cantidad,
        "precio" => $precio
    ];
}