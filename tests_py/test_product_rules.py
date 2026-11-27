import pytest

from conftest import InvalidArgumentException


@pytest.mark.parametrize(
    "name,category,quantity,price,expected_name",
    [
        ("Rosas rojas", "Flores", 10, 5000, "Rosas rojas"),  # producto normal
        ("  Claveles  ", "  Flores  ", 0, 2500, "Claveles"),  # espacios y stock cero
        ("Orquídea ñandú", "Plantas", 2, 12500.50, "Orquídea ñandú"),  # tildes y ñ
        ("Ramo d'abril", "Arreglos", 1, 30000, "Ramo d'abril"),  # apóstrofo
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
    with pytest.raises(InvalidArgumentException):
        call_php({"function": "product", **payload})


def test_product_price_boundary_positive(call_php):
    response = call_php({
        "function": "product", "name": "Rosa", "category": "Flores",
        "quantity": 0, "price": 0.01
    })
    assert response["ok"] is True
    assert response["result"]["price"] == 0.01

def test_synthetic_product_fixture(call_php, synthetic_product_cases):
    """Comprueba datos sintéticos mediante un fixture de pytest."""
    for case in synthetic_product_cases:
        response = call_php({
            "function": "product",
            "name": case["name"],
            "category": case["category"],
            "quantity": case["quantity"],
            "price": case["price"],
        })
        assert response["ok"] is True
        assert response["result"]["name"] == case["expected_name"]
