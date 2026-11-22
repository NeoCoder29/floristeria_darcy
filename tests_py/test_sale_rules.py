import pytest


@pytest.mark.parametrize(
    "items,discount,expected_subtotal,expected_total",
    [
        ([{"price": 10000, "stock": 5, "quantity": 2}], 0, 20000, 20000),  # venta normal
        ([{"price": 1250.50, "stock": 3, "quantity": 1}], 0, 1250.50, 1250.50),  # decimal
        ([{"price": 10000, "stock": 5, "quantity": 2}], 10, 20000, 18000),  # descuento
        ([{"price": 10000, "stock": 5, "quantity": 5}], 100, 50000, 0),  # límites combinados
    ],
)
def test_sale_calculation(call_php, items, discount, expected_subtotal, expected_total):
    response = call_php({
        "function": "sale", "items": items, "discount": discount
    })
    assert response["ok"] is True
    assert response["result"]["subtotal"] == pytest.approx(expected_subtotal)
    assert response["result"]["total"] == pytest.approx(expected_total)


@pytest.mark.parametrize(
    "items,discount",
    [
        ([], 0),  # no permite venta sin productos
        ([{"price": 100, "stock": 2, "quantity": 3}], 0),  # stock insuficiente
        ([{"price": 100, "stock": 2, "quantity": 1}], -1),  # descuento negativo
        ([{"price": 100, "stock": 2, "quantity": 1}], 101),  # descuento superior al máximo
        ([{"price": 0, "stock": 2, "quantity": 1}], 0),  # precio inválido
        ([{"price": 100, "stock": 2, "quantity": 0}], 0),  # cantidad cero
    ],
)
def test_sale_rejects_invalid_combinations(call_php, items, discount):
    response = call_php({
        "function": "sale", "items": items, "discount": discount
    })
    assert response["ok"] is False
    assert response["error"] == "InvalidArgumentException"
