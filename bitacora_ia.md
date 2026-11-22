# Bitácora de uso de inteligencia artificial

## Semana 7

### Entrada 10. Diseño de arquitectura y alcance
**Prompt/solicitud:** revisar el estado del proyecto y proponer una evolución desde el registro aislado de productos a un sistema administrativo.
**Resultado:** se propuso mantener PHP, incorporar PostgreSQL y separar reglas de negocio, infraestructura y presentación.
**Verificación:** se contrastó la propuesta con los archivos existentes y se conservó la funcionalidad original de registro como base.

### Entrada 11. Diseño de reglas y pruebas con pytest
**Prompt/solicitud:** diseñar pruebas para productos, movimientos de inventario y ventas usando particiones, límites y combinaciones.
**Resultado:** se propusieron casos parametrizados para tres grupos de reglas; la venta combina precio, cantidad, existencias y descuento.
**Verificación:** cada esperado se deriva de las reglas definidas en las clases PHP. pytest invoca PHP real mediante un puente CLI.

### Entrada 12. Implementación de datos y pruebas sintéticas
**Prompt/solicitud:** generar casos sintéticos con nombres que incluyan tildes, ñ, apóstrofos, espacios, ceros y límites.
**Resultado:** se añadieron fixtures parametrizados en `tests_py`.
**Verificación:** los datos son ficticios y no corresponden a personas reales. Se incluyeron productos y clientes de ejemplo solo como datos de prueba.

### Entrada 13. Revisión de seguridad y limitaciones
**Prompt/solicitud:** revisar riesgos habituales de un sistema administrativo.
**Resultado:** se incorporaron consultas preparadas, hash de contraseñas, protección CSRF, escape HTML y transacciones en ventas.
**Verificación:** estas medidas deben probarse en el entorno local con PostgreSQL y una revisión de despliegue antes de considerarlas listas para producción.
