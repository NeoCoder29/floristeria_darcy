#!/usr/bin/env python3
"""Ejecuta mutaciones controladas y verifica que PHPUnit las detecte."""
from pathlib import Path
import subprocess
import sys

ROOT = Path(__file__).resolve().parents[1]
MUTATIONS = [
    ("Producto: rechazar stock cero", "src/Domain/ProductRules.php",
     "if ($quantity < 0 || $minStock < 0)", "if ($quantity <= 0 || $minStock < 0)"),
    ("Inventario: permitir movimiento cero", "src/Domain/InventoryRules.php",
     "if ($change === 0)", "if ($change !== 0)"),
    ("Inventario: permitir stock negativo", "src/Domain/InventoryRules.php",
     "if ($newStock < 0)", "if ($newStock <= 0)"),
    ("Venta: rechazar compra con stock exacto", "src/Domain/SaleRules.php",
     "if ($quantity > $stock)", "if ($quantity >= $stock)"),
    ("Venta: alterar cálculo del subtotal", "src/Domain/SaleRules.php",
     "round($price * $quantity, 2)", "round($price + $quantity, 2)"),
]

def main() -> int:
    killed = 0
    survived = []
    for name, relative, original, mutant in MUTATIONS:
        path = ROOT / relative
        source = path.read_text(encoding="utf-8")
        if source.count(original) != 1:
            raise RuntimeError(f"No se encontró exactamente una ocurrencia para: {name}")
        try:
            path.write_text(source.replace(original, mutant, 1), encoding="utf-8")
            result = subprocess.run(
                ["php", "vendor/bin/phpunit", "--no-coverage"],
                cwd=ROOT, text=True, stdout=subprocess.PIPE,
                stderr=subprocess.STDOUT, check=False,
            )
            if result.returncode != 0:
                killed += 1
                print(f"MUERTO: {name}")
            else:
                survived.append(name)
                print(f"SOBREVIVIÓ: {name}")
        finally:
            path.write_text(source, encoding="utf-8")

    total = len(MUTATIONS)
    score = killed / total * 100
    print(f"\nResultado: {killed}/{total} mutantes detectados ({score:.1f}%).")
    if survived:
        print("Mutantes sobrevivientes:")
        for name in survived:
            print(f"- {name}")
        return 1
    return 0

if __name__ == "__main__":
    sys.exit(main())
