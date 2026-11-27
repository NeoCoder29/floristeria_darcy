import json
import os
import subprocess
from pathlib import Path

import pytest

PROJECT_ROOT = Path(__file__).resolve().parents[1]
PHP = os.environ.get("PHP_BINARY", "php")
BRIDGE = PROJECT_ROOT / "tests" / "php_bridge.php"


class InvalidArgumentException(Exception):
    """Representa una InvalidArgumentException lanzada por las reglas PHP."""


@pytest.fixture
def call_php():
    """Invoca las reglas PHP reales por CLI y devuelve su respuesta JSON."""

    def _call(payload):
        process = subprocess.run(
            [PHP, str(BRIDGE)],
            input=json.dumps(payload, ensure_ascii=False),
            text=True,
            encoding="utf-8",
            capture_output=True,
            cwd=PROJECT_ROOT,
            check=False,
        )

        assert process.stdout, (
            f"PHP no devolvió respuesta. STDERR: {process.stderr}"
        )

        response = json.loads(process.stdout)

        if not response["ok"]:
            if response["error"] == "InvalidArgumentException":
                raise InvalidArgumentException(response.get("message", ""))

            raise RuntimeError(
                f'{response["error"]}: {response.get("message", "")}'
            )

        return response

    return _call


@pytest.fixture
def synthetic_product_cases():
    """Datos ficticios para comprobar caracteres y entradas especiales."""
    return [
        {
            "name": "Orqu\u00eddea \u00f1and\u00fa",
            "category": "Plantas",
            "quantity": 2,
            "price": 12500.50,
            "expected_name": "Orqu\u00eddea \u00f1and\u00fa",
        },
        {
            "name": "Ramo d'abril",
            "category": "Arreglos",
            "quantity": 1,
            "price": 30000,
            "expected_name": "Ramo d'abril",
        },
        {
            "name": "  Claveles  ",
            "category": "  Flores  ",
            "quantity": 0,
            "price": 2500,
            "expected_name": "Claveles",
        },
    ]
