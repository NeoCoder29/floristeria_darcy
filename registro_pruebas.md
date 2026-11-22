# Registro de pruebas – Semana 7

**Proyecto:** Floristería Darcy  
**Lenguaje de producción:** PHP 8.2  
**Herramienta de pruebas de la actividad:** Python + pytest  
**Puente de ejecución:** `tests/php_bridge.php`

## 1. Reglas y diseño de casos

### Función 1: ProductRules::validate

**Regla:** el nombre y la categoría son obligatorios; se normalizan los espacios externos; la cantidad y el stock mínimo deben ser mayores o iguales a cero; el precio debe ser finito y mayor que cero.

| Técnica | Partición/caso | Entrada representativa | Esperado |
|---|---|---|---|
| Equivalencia válida | Nombre y categoría normales | Rosas / Flores | Acepta |
| Equivalencia válida | Espacios externos | `  Claveles  ` | Normaliza a Claveles |
| Equivalencia inválida | Nombre vacío | `""` | InvalidArgumentException |
| Equivalencia inválida | Categoría en blanco | `"   "` | InvalidArgumentException |
| Equivalencia inválida | Cantidad negativa | -1 | InvalidArgumentException |
| Equivalencia inválida | Precio cero o negativo | 0 / -1 | InvalidArgumentException |
| Límite | Cantidad mínima | 0 | Acepta |
| Límite | Precio mínimo positivo | 0.01 | Acepta |

### Función 2: InventoryRules::calculateNewStock / isLowStock

**Regla:** un movimiento no puede dejar existencias negativas ni ser cero. Se genera alerta cuando la cantidad actual es menor o igual al stock mínimo.

| Técnica | Caso | Entrada | Esperado |
|---|---|---|---|
| Equivalencia | Entrada de inventario | 10 + 5 | 15 |
| Equivalencia | Salida válida | 10 - 1 | 9 |
| Límite | Agotar existencias exactas | 10 - 10 | 0 |
| Límite | Salida supera existencias | 5 - 6 | Excepción |
| Decisión | Cantidad < mínimo | 4, mínimo 5 | Alerta |
| Decisión | Cantidad = mínimo | 5, mínimo 5 | Alerta |
| Decisión | Cantidad > mínimo | 6, mínimo 5 | Sin alerta |
| Inválido | Movimiento cero | 5 + 0 | Excepción |

### Función 3: SaleRules::calculate (varias condiciones)

**Regla:** la venta requiere al menos un artículo; cada artículo debe tener precio finito positivo, cantidad positiva y stock no negativo; la cantidad no puede superar el stock; el descuento debe estar entre 0 y 100%. El total es subtotal menos descuento.

| Artículos | Precio válido | Cantidad ≤ stock | Descuento válido | Resultado |
|---|---|---|---|---|
| No | — | — | Sí | Rechazar |
| Sí | No | — | Sí | Rechazar |
| Sí | Sí | No | Sí | Rechazar |
| Sí | Sí | Sí | No | Rechazar |
| Sí | Sí | Sí | Sí | Calcular total |

Los importes esperados se obtienen multiplicando precio por cantidad y aplicando el porcentaje de descuento, no de la ejecución del sistema.

## 2. Revisión de pruebas generadas con IA

| Grupo de pruebas | Decisión | Razón | Acción |
|---|---|---|---|
| Producto con nombre y categoría válidos | Conservar | Representa partición válida | Parametrizada |
| Producto con espacios externos | Conservar | Comprueba normalización | Se verifica texto exacto |
| Cantidad cero | Conservar | Es límite válido definido por la regla | Se verifica aceptación |
| Cantidad negativa | Conservar | Partición inválida | Comprueba excepción |
| Precio cero y negativo | Conservar | Prueba ambos lados del límite | Comprueba excepción |
| Movimiento que deja stock negativo | Conservar | Detecta sobreventa | Comprueba excepción |
| Stock igual al mínimo | Conservar | Comprueba el operador inclusivo `<=` | Comprueba alerta |
| Venta con descuento | Conservar | Verifica cálculo combinado | Usa `pytest.approx` |
| Venta con cantidad mayor al stock | Conservar | Comprueba regla de inventario | Comprueba excepción |

No se descartan pruebas en esta versión inicial. Las pruebas invocan las funciones PHP reales a través de un puente de consola; no se ha duplicado la lógica de negocio en Python.

## 3. Pruebas de mutación manual

**Estado:** pendiente de ejecutar en el entorno local antes de cerrar la entrega.

Registrar aquí, después de ejecutar cada mutación individual:
- Mutación 1: cambiar temporalmente `<=` por `<` en la alerta de stock. Prueba esperada: `test_low_stock_decision_table` con cantidad igual al mínimo.
- Mutación 2: alterar temporalmente el cálculo del descuento. Prueba esperada: `test_sale_calculation` con descuento del 10%.
- Mutación 3: retirar temporalmente la validación de cantidad contra stock. Prueba esperada: `test_sale_rejects_invalid_combinations` con cantidad superior al inventario.

Restaurar cada archivo inmediatamente después de la mutación y comprobar que el estado vuelve a pasar.

## 4. Datos sintéticos

Los datos son ficticios y fueron creados para pruebas; no se utilizaron datos de clientes reales.

| Tipo | Ejemplo | Caso difícil |
|---|---|---|
| Producto | Orquídea ñandú | Tilde y ñ |
| Producto | Ramo d'abril | Apóstrofo |
| Producto | Claveles con espacios | Espacios externos |
| Inventario | 0 unidades | Límite inferior |
| Precio | 0.01 | Límite monetario |
| Venta | 5 unidades con stock 5 | Límite exacto |
| Descuento | 0%, 10%, 100%, 101% | Límites y valor inválido |

Los casos se encuentran en fixtures parametrizados en `tests_py`. No contienen teléfonos ni información de personas reales.

## 5. Ejecución y evidencias

Comandos:
```bash
python -m pip install -r requirements.txt
python -m pytest -v
```

La ejecución real debe registrarse con fecha, resultado y versión de PHP/Python en esta sección antes de entregar.
