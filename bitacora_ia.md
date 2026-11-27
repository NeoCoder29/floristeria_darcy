\# Bitácora de uso de inteligencia artificial



\## Semana 7



\### Entrada 10. Diseño de arquitectura y alcance



\*\*Prompt/solicitud:\*\* revisar el estado del proyecto y proponer una evolución desde el registro aislado de productos a un sistema administrativo.



\*\*Resultado:\*\* se propuso mantener PHP, incorporar PostgreSQL y separar reglas de negocio, infraestructura y presentación.



\*\*Verificación:\*\* se contrastó la propuesta con los archivos existentes y se conservó la funcionalidad original de registro como base.



\### Entrada 11. Diseño de reglas y pruebas con pytest



\*\*Prompt/solicitud:\*\* diseñar pruebas para productos, movimientos de inventario y ventas usando particiones, límites y combinaciones.



\*\*Resultado:\*\* se propusieron casos parametrizados para tres grupos de reglas; la venta combina precio, cantidad, existencias y descuento.



\*\*Verificación:\*\* cada esperado se deriva de las reglas definidas en las clases PHP. pytest invoca PHP real mediante un puente CLI.



\### Entrada 12. Implementación de datos y pruebas sintéticas



\*\*Prompt/solicitud:\*\* generar casos sintéticos con nombres que incluyan tildes, ñ, apóstrofos, espacios, ceros y límites.



\*\*Resultado:\*\* se añadieron fixtures parametrizados en `tests\_py`.



\*\*Verificación:\*\* los datos son ficticios y no corresponden a personas reales. Se incluyeron productos y clientes de ejemplo solo como datos de prueba.



\### Entrada 13. Revisión de seguridad y limitaciones



\*\*Prompt/solicitud:\*\* revisar riesgos habituales de un sistema administrativo.



\*\*Resultado:\*\* se incorporaron consultas preparadas, hash de contraseñas, protección CSRF, escape HTML y transacciones en ventas.



\*\*Verificación:\*\* estas medidas deben probarse en el entorno local con PostgreSQL y una revisión de despliegue antes de considerarlas listas para producción.



\### Entrada 14. Diseño de casos de prueba – Semana 7



\*\*Prompt/solicitud:\*\* identificar funciones con reglas de negocio y diseñar casos mediante particiones de equivalencia, valores límite y tablas de decisión.



\*\*Resultado:\*\* se organizaron casos para `ProductRules::validate`, `InventoryRules::calculateNewStock`, `InventoryRules::isLowStock` y `SaleRules::calculate`.



\*\*Verificación:\*\* se revisaron entradas válidas e inválidas, cantidad cero, precio mínimo positivo, igualdad con el stock mínimo y combinaciones de condiciones de venta. Los valores esperados se establecieron a partir de las reglas de negocio.



\### Entrada 15. Generación y revisión de pruebas automatizadas



\*\*Prompt/solicitud:\*\* generar y revisar pruebas parametrizadas con pytest que ejecuten las reglas PHP reales.



\*\*Resultado:\*\* se ejecutaron 32 pruebas automatizadas. Se utilizaron `pytest.mark.parametrize` y `pytest.approx` para verificar los cálculos decimales.



\*\*Verificación:\*\* la ejecución inicial obtuvo 32 pruebas aprobadas. Se realizaron tres mutaciones controladas: cambiar `<=` por `<` en la alerta de inventario, alterar el divisor del descuento de 100 a 10 y desactivar la validación de cantidad contra stock. Las tres fueron detectadas por las pruebas. Después de restaurar el código, se obtuvieron nuevamente 32 pruebas aprobadas.



\### Entrada 16. Generación y revisión de datos sintéticos



\*\*Prompt/solicitud:\*\* generar datos ficticios para probar entradas normales, inválidas y casos difíciles.



\*\*Resultado:\*\* se utilizaron nombres con tildes, ñ, apóstrofos y espacios externos; cantidades iguales a cero; precios de 0.01; existencias exactas y porcentajes de descuento de 0, 10 y 100.



\*\*Verificación:\*\* los datos se utilizaron en pruebas parametrizadas de `tests\_py`. No se emplearon datos personales ni registros de clientes reales.



## Evolución técnica posterior – 25 de noviembre de 2026

### Revisión e implementación sobre la rama de avance

**Solicitud:** mejorar la versión existente del sistema sin cambiar PHP/PostgreSQL ni ampliar el alcance a pagos en línea, facturación electrónica o geolocalización.

**Cambios realizados en esta versión de trabajo:**

- Modernización de la interfaz administrativa, navegación, tarjetas de indicadores, tablas, formularios y adaptación móvil.
- Endurecimiento de sesiones, encabezados HTTP, política CSP, manejo de errores y validación de formularios.
- Uso de categorías existentes mediante selector al registrar productos.
- Registro transaccional de entradas y salidas de inventario con motivo, responsable y existencias antes/después.
- Registro automático de salidas de inventario al confirmar ventas.
- Revalidación de precios y existencias dentro de la transacción de venta, con bloqueo de filas para reducir el riesgo de sobreventa concurrente.
- Actualización de la documentación de instalación, arquitectura y alcance.

**Verificación disponible en el entorno de trabajo:** `php -l` sin errores en todos los archivos PHP y `pytest` con 32 pruebas aprobadas antes de añadir tres casos de rechazo de valores parcialmente numéricos. La suite ampliada debe volver a ejecutarse. Este entorno no dispone de la extensión `pdo_pgsql`, por lo que no fue posible ejecutar una prueba de integración real contra PostgreSQL.

**Limitación:** la ejecución y verificación funcional completa de la interfaz requiere importar el esquema actualizado y probar la aplicación en el equipo local con PostgreSQL.


---

## Semana 8 – Cobertura, pruebas de aceptación y análisis de defectos

### Entrada 17. Revisión de los contenidos del profesor y adaptación al proyecto

**Prompt/solicitud:** revisar los contenidos de Semana 8 sobre cobertura y sus límites, pruebas de mutación, pruebas de aceptación/API y análisis de defectos; aplicarlos al repositorio real de Floristería Darcy sin inventar endpoints ni alterar la base de datos local.

**Resultado:** se definió medir cobertura de las reglas PHP de `src/Domain`, introducir mutaciones controladas, convertir reglas en escenarios Dado/Cuando/Entonces y comprobar las operaciones persistidas en PostgreSQL.

**Decisión tomada:** el proyecto no expone una API REST en el alcance revisado. Por eso, el componente de pruebas API se adaptó a pruebas de la capa de repositorio/servicio y sus efectos en la base de datos, sin crear endpoints ficticios.

**Verificación:** se revisaron los archivos reales del repositorio, el puente PHP utilizado por pytest y las clases de dominio. Se mantuvieron los datos sintéticos y se aisló la integración en una base PostgreSQL de CI llamada `floristeria_test`.

### Entrada 18. Configuración de cobertura con PHPUnit y Xdebug

**Prompt/solicitud:** configurar cobertura de líneas y métodos para las reglas PHP, interpretar líneas no cubiertas y conservar un informe verificable.

**Herramienta:** ChatGPT, PHPUnit 11.5, Xdebug y GitHub Actions.

**Cambios realizados:**
- Se configuró `phpunit.xml` para incluir `src/Domain` como fuente de cobertura.
- Se habilitó Xdebug en modo coverage en el workflow.
- Se generó el informe Clover en `coverage/clover.xml`.
- Se publicó el informe como artefacto `cobertura-phpunit`.

**Resultado observado en la ejecución registrada:** 88 de 89 líneas ejecutadas (98,88 %); 5 de 6 métodos (83,33 %) y 2 de 3 clases (66,67 %). InventoryRules y SaleRules tuvieron 100 % de líneas; ProductRules registró 24 de 25 líneas (96 %).

**Interpretación:** una cobertura de líneas alta no significa que todas las funciones estén probadas ni que las aserciones sean suficientes. Quedó una línea de ProductRules sin ejecutar en esa medición y la cobertura de métodos fue menor que la cobertura de líneas.

**Verificación posterior:** se añadió un caso explícito de texto con codificación UTF-8 inválida. No se afirma un porcentaje nuevo hasta que termine la ejecución posterior a ese cambio.

### Entrada 19. Diseño y ejecución de pruebas de mutación

**Prompt/solicitud:** crear mutaciones pequeñas y controladas sobre reglas de producto, inventario y ventas para comprobar si la suite detecta cambios incorrectos. Restaurar siempre el código original.

**Cambios realizados:** se añadió `tests/mutation_runner.py`, que introduce cinco mutaciones individuales, ejecuta PHPUnit y restaura los archivos originales en un bloque de limpieza.

**Mutaciones ejecutadas:**
1. Cambiar la aceptación del límite de cantidad cero para producto.
2. Alterar la validación de movimiento de inventario cero.
3. Alterar el límite que evita stock negativo.
4. Rechazar una venta que coincide exactamente con el stock.
5. Alterar el cálculo del subtotal.

**Resultado observado:** 5 mutantes muertos de 5 ejecutados; puntuación de mutación 100 %. No hubo mutantes sobrevivientes en el conjunto seleccionado.

**Verificación:** cada mutante provocó que PHPUnit detectara un comportamiento incorrecto. El ejecutor restauró los archivos originales después de cada intento. Las mutaciones son fallos simulados para medir la calidad de las pruebas, no defectos permanentes del sistema.

### Entrada 20. Pruebas de aceptación y persistencia PostgreSQL

**Prompt/solicitud:** traducir reglas del sistema a escenarios Given/When/Then y comprobar estado HTTP/respuesta/efecto cuando exista API; si no existe API, probar la capa de servicio.

**Resultado:** se documentaron criterios AC-PRO, AC-INV y AC-SAL para validación de productos, entradas/salidas de inventario y ventas. Se añadieron pruebas de integración para comprobar los efectos persistidos en PostgreSQL.

**Implementación:** `tests/Integration/RepositoryPostgresTest.php` comprueba cinco escenarios: entrada de inventario con trazabilidad, salida excesiva con reversión, venta correcta con descuento, venta fallida sin registros parciales y rechazo de identificadores de tabla no permitidos.

**Decisión de seguridad:** las pruebas usan `DB_TEST_DSN`, `DB_TEST_USER` y `DB_TEST_PASSWORD`; el workflow levanta PostgreSQL 16 con una base independiente `floristeria_test`. La suite no lee `.env` ni apunta a la base de datos local o de producción.

**Verificación observada:** PHPUnit registró 42 pruebas y 89 aserciones aprobadas en una ejecución de CI. El conteo incluye los escenarios de integración de esa versión del repositorio.

### Entrada 21. Análisis de defectos y revisión PHPCS

**Prompt/solicitud:** registrar defectos de forma reproducible, con severidad, prioridad, evidencia y corrección; no confundir mutaciones deliberadas con errores del producto.

**Hallazgos de calidad:**
- PHPCS reportó una línea de 132 caracteres en la preparación de la base PostgreSQL de pruebas. Se dividió la cadena SQL en dos líneas.
- Se identificó la ausencia de un caso explícito para texto con bytes UTF-8 inválidos. Se añadió una prueba PHPUnit que espera una excepción de validación.

**Clasificación:** ambos son hallazgos de baja severidad; el primero es de estilo y el segundo una brecha de cobertura. Las cinco mutaciones no se registran como defectos persistentes porque se introdujeron deliberadamente y se restauraron.

**Verificación:** se ajustó el workflow para que PHPCS falle por errores y no por advertencias heredadas. La ejecución posterior a estos cambios debe confirmarse antes de declarar verde el pipeline.

### Entrada 22. Documentación, revisión y límites de la evidencia

**Prompt/solicitud:** documentar la Semana 8 en el mismo estilo progresivo del proyecto, conservar el registro de Semana 7 y separar resultados observados de cambios pendientes de nueva ejecución.

**Archivos actualizados:**
- `docs/semana8-plan-pruebas.md`: metodología, cobertura, límites, mutaciones, criterios de aceptación, integración y defectos.
- `registro_pruebas.md`: anexo de Semana 8 con casos, resultados, evidencias y conclusión.
- `bitacora_ia.md`: esta bitácora con prompts, decisiones, resultados y verificación.
- `README.md`: comandos, flujo automatizado y enlaces a documentación.

**Resultado observado que se conserva como histórico:** 42 pruebas PHPUnit / 89 aserciones; 35 pruebas pytest; 98,88 % de cobertura de líneas; 5/5 mutantes detectados. PHPCS completó correctamente con advertencias configuradas como no bloqueantes; después también se corrigió la línea extensa.

**Limitaciones:** las cifras históricas no se presentan como resultado de los cambios posteriores. El caso UTF-8 y los ajustes de PHPCS requieren la confirmación de una ejecución posterior completada y verde. La cobertura de dominio no mide interfaz, seguridad integral ni todos los flujos de persistencia.

**Reflexión:** la actividad mostró que ejecutar pruebas no basta para valorar su calidad. La cobertura permitió encontrar líneas no ejecutadas; las mutaciones comprobaron si las aserciones detectaban errores intencionales; las pruebas de integración comprobaron efectos persistidos y reversión transaccional. La IA se utilizó como apoyo para diseñar, implementar y documentar, pero los resultados se contrastaron con el código y los logs de GitHub Actions.
