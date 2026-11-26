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
