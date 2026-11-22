import pytest


@pytest.mark.parametrize(
    "current,change,expected",
    [
        (10, 5, 15),   # entrada de unidades
        (10, -1, 9),   # salida sin agotar
        (10, -10, 0),  # límite: agotar exactamente
        (0, 4, 4),     # entrada desde inventario cero
    ],
)
def test_stock_movements(call_php, current, change, expected):
    response = call_php({"function": "stock", "current": current, "change": change})
    assert response["ok"] is True
    assert response["result"] == expected


@pytest.mark.parametrize(
    "current,change",
    [
        (5, -6),  # salida supera inventario
        (5, 0),   # movimiento sin cambio
        (-1, 2),  # inventario inicial inválido
    ],
)
def test_stock_rejects_invalid_movements(call_php, current, change):
    response = call_php({"function": "stock", "current": current, "change": change})
    assert response["ok"] is False
    assert response["error"] == "InvalidArgumentException"


@pytest.mark.parametrize(
    "quantity,minimum,expected",
    [
        (5, 5, True),   # límite exacto: alerta
        (4, 5, True),   # por debajo del mínimo
        (6, 5, False),  # por encima del mínimo
        (0, 0, True),   # cero con mínimo cero
    ],
)
def test_low_stock_decision_table(call_php, quantity, minimum, expected):
    response = call_php({
        "function": "low_stock", "quantity": quantity,
        "minimum": minimum
    })
    assert response["ok"] is True
    assert response["result"] is expected
