
import json
import os
import subprocess
from pathlib import Path

import pytest

PROJECT_ROOT = Path(__file__).resolve().parents[1]
PHP = os.environ.get("PHP_BINARY", "php")
BRIDGE = PROJECT_ROOT / "tests" / "php_bridge.php"


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

        return json.loads(process.stdout)

    return _call
