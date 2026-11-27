# Reporte de defectos y hallazgos – Semana 8

**Proyecto:** Floristería Darcy  
**Rama:** `feature/semanas-8-9-floristeria`  
**Fecha de registro:** 27/11/2026

## Criterios de clasificación

- **Severidad:** impacto técnico o funcional del hallazgo.
- **Prioridad:** urgencia de corrección.
- **Estado:** pendiente, corregido o no reproducido.
- Las mutaciones intencionales se documentan en el informe de pruebas, no como defectos persistentes del producto.

## QA-08-01 – Línea demasiado extensa en prueba de integración

| Campo | Detalle |
|---|---|
| Tipo | Calidad estática / mantenibilidad |
| Severidad | Baja |
| Prioridad | Baja |
| Archivo | `tests/Integration/RepositoryPostgresTest.php` |
| Estado | Corregido |

**Precondición:** instalar dependencias PHP del proyecto.

**Pasos para reproducir:**
1. Situarse en la raíz del repositorio.
2. Ejecutar `vendor/bin/phpcs`.
3. Revisar el reporte sobre `tests/Integration/RepositoryPostgresTest.php`.

**Resultado observado:** PHPCS reportó una línea de 132 caracteres, por encima del límite de 120.

**Resultado esperado:** las líneas deben respetar el estándar configurado para facilitar lectura y mantenimiento.

**Corrección:** se dividió la cadena SQL del comando `TRUNCATE` en dos fragmentos concatenados. No se modificó el contenido de la sentencia ni su comportamiento.

**Verificación pendiente de cierre:** volver a ejecutar PHPCS en la última ejecución de GitHub Actions y comprobar que el paso finalice correctamente.

## QA-08-02 – Falta de caso explícito para texto UTF-8 inválido

| Campo | Detalle |
|---|---|
| Tipo | Brecha de cobertura de pruebas |
| Severidad | Baja |
| Prioridad | Media |
| Archivo | `tests/DomainRulesTest.php` |
| Estado | Caso de prueba añadido; resultado posterior pendiente de confirmar |

**Precondición:** ejecutar la suite PHPUnit.

**Pasos para reproducir la brecha inicial:**
1. Revisar los casos de validación de producto.
2. Comprobar que no existe un caso con bytes UTF-8 inválidos.
3. Comparar con los límites de validación de texto del dominio.

**Resultado observado:** la suite inicial no contenía un caso explícito que documentara el rechazo de texto con codificación UTF-8 inválida.

**Resultado esperado:** la validación debe rechazar texto que no cumpla la codificación admitida y el comportamiento debe estar cubierto por una prueba automatizada.

**Corrección:** se añadió un test que entrega el byte inválido `\xFF` al validador de producto y espera `InvalidArgumentException`.

**Verificación pendiente de cierre:** confirmar que PHPUnit apruebe el caso nuevo en la última ejecución completada.

## Mutaciones deliberadas – No son defectos persistentes

Se introdujeron cinco modificaciones temporales para medir la capacidad de detección de las pruebas: límite de cantidad cero, movimiento de inventario cero, stock negativo, venta por cantidad exacta del stock y cálculo del subtotal. En la ejecución observada, las cinco fueron detectadas por PHPUnit (5/5, 100 %). Los archivos se restauraron después de cada mutación.

Estas mutaciones no se registran como defectos abiertos porque fueron introducidas deliberadamente como parte de la técnica de pruebas y no quedaron en la versión normal del código.

## Resumen

| ID | Severidad | Prioridad | Estado |
|---|---|---|---|
| QA-08-01 | Baja | Baja | Corrección aplicada; falta confirmar PHPCS en ejecución final |
| QA-08-02 | Baja | Media | Test añadido; falta confirmar ejecución posterior |

No se registran defectos funcionales de producción confirmados por la evidencia de esta actividad. Cualquier nuevo defecto debe agregarse con pasos reproducibles, evidencia y resultado de la corrección.
