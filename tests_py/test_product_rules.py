import pytest


@pytest.mark.parametrize(
    "name,category,quantity,price,expected_name",
    [
        ("Rosas rojas", "Flores", 10, 5000, "Rosas rojas"),  # producto normal
        ("  Claveles  ", "  Flores  ", 0, 2500, "Claveles"),  # limpia espacios y admite stock cero
        ("Orquídea ñandú", "Plantas", 2, 12500.50, "Orquídea ñandú"),  # conserva tildes y ñ
        ("Ramo d'abril", "Arreglos", 1, 30000, "Ramo d'abril"),  # conserva apóstrofo
    ],
)
def test_product_valid_partitions(call_php, name, category, quantity, price, expected_name):
    response = call_php({
        "function": "product", "name": name, "category": category,
        "quantity": quantity, "price": price
    })
    assert response["ok"] is True
    assert response["result"]["name"] == expected_name


@pytest.mark.parametrize(
    "payload",
    [
        {"name": "", "category": "Flores", "quantity": 1, "price": 100},  # nombre vacío
        {"name": "Rosa", "category": "   ", "quantity": 1, "price": 100},  # categoría vacía
        {"name": "Rosa", "category": "Flores", "quantity": -1, "price": 100},  # cantidad negativa
        {"name": "Rosa", "category": "Flores", "quantity": 1, "price": 0},  # precio cero
        {"name": "Rosa", "category": "Flores", "quantity": 1, "price": -1},  # precio negativo
    ],
)
def test_product_rejects_invalid_partitions(call_php, payload):
    response = call_php({"function": "product", **payload})
    assert response["ok"] is False
    assert response["error"] == "InvalidArgumentException"


def test_product_price_boundary_positive(call_php):
    response = call_php({
        "function": "product", "name": "Rosa", "category": "Flores",
        "quantity": 0, "price": 0.01
    })
    assert response["ok"] is True
    assert response["result"]["price"] == 0.01
