# Informe de revisión de calidad y seguridad del código

**Proyecto:** Floristería Darcy  
**Estudiante:** Samuel Santiago Cabrera Castro  
**Funcionalidad revisada:** HU-01 – Registrar productos e insumos  
**Fecha:** 14 de noviembre de 2026

## 1. Herramientas y criterios de revisión

### Objetivo y alcance

Se revisó la calidad y seguridad de la función `registrarProducto`, ubicada en `src/registrarProducto.php`, y sus pruebas automatizadas en `tests/RegistrarProductoTest.php`.

La revisión se concentró en las validaciones de entrada, el manejo de datos inválidos, la documentación y las dependencias del proyecto. No representa una auditoría de una aplicación completa.

### Herramientas utilizadas

- PHP 8.2.
- PHPUnit 11.5.57.
- PHP_CodeSniffer (PHPCS).
- PHP Code Beautifier and Fixer (PHPCBF).
- Composer Audit.
- Git y GitHub.
- ChatGPT como apoyo para la segunda revisión.

### Lista de revisión de seguridad

| Criterio | Resultado | Observación |
|---|---|---|
| Inyección | No aplica al alcance revisado | La función no ejecuta consultas SQL ni construye instrucciones de base de datos. |
| Protección de secretos | Cumple en los aspectos comprobados | `.env` está incluido en `.gitignore`. No se encontraron archivos `.env` registrados en Git ni en el historial consultado. El proyecto no utiliza variables de entorno actualmente. |
| Almacenamiento de contraseñas | No aplica | La funcionalidad revisada no almacena ni administra contraseñas. |
| Control de acceso | No aplica a esta función | La función no administra usuarios, roles ni autenticación. El control de acceso de una aplicación completa queda fuera del alcance. |
| Dependencias | Cumple en las comprobaciones realizadas | Existe `composer.lock` y Composer Audit no reportó avisos de vulnerabilidades conocidas. |

También se revisaron la legibilidad, los tipos de datos, las validaciones, el tratamiento de espacios Unicode y la documentación de la función.

## 2. Hallazgos y correcciones

La revisión identificó dos problemas reales de validación. Ambos fueron comprobados y corregidos.

| Hallazgo | Categoría | Severidad | Corrección aplicada | Commit |
|---|---|---|---|---|
| Se podían aceptar precios no finitos, como `NAN` e infinito. | Validación de entradas | Media | Se incorporó `is_finite()` para rechazar esos valores. | `84de46e` |
| La normalización inicial no eliminaba todos los espacios Unicode. | Validación de entradas | Media | Se incorporó una expresión regular con soporte Unicode para limpiar los extremos del texto. | `84de46e` |

### Ubicación de las correcciones en el código

- Normalización del nombre: línea 28.
- Normalización de la categoría: línea 34.
- Validación de datos: líneas 51 a 58, incluyendo `is_finite()` en la línea 55.

Las dos correcciones se encuentran en el mismo commit; no se realizaron commits independientes para cada hallazgo.

## 3. Segunda revisión con inteligencia artificial

Se utilizó ChatGPT para realizar una segunda revisión de la función y buscar situaciones que no estuvieran contempladas en las validaciones iniciales.

### Hallazgos confirmados

1. La aceptación de precios no finitos (`NAN` e infinito).
2. La normalización incompleta de espacios Unicode.

Ambos hallazgos se reprodujeron mediante pruebas y se corrigieron. No se clasificaron como falsos positivos.

### Aspectos no identificados por la revisión de IA

La revisión inicial no señaló que el README anterior todavía no cumplía las ocho secciones documentales exigidas en la actividad. Este aspecto se identificó al contrastar el documento con las instrucciones del profesor y posteriormente se corrigió.

### Verificación humana

Las recomendaciones de la IA se contrastaron con el código fuente, las pruebas automatizadas y las herramientas de revisión. Las modificaciones se conservaron únicamente después de comprobar su comportamiento.

## 4. Verificación, pendientes y conclusiones

### Resultados de las herramientas

| Herramienta | Resultado |
|---|---|
| PHPUnit 11.5.57 | 10 pruebas aprobadas y 14 aserciones. |
| PHP_CodeSniffer | Sin errores ni advertencias después de corregir los finales de línea. |
| PHPCBF | Corrigió automáticamente los finales de línea de `src/registrarProducto.php` y `tests/RegistrarProductoTest.php`. |
| Composer Audit | No se encontraron avisos de vulnerabilidades conocidas. |
| Instalación limpia | La clonación desde GitHub y `composer install` finalizaron correctamente. Se instalaron 28 paquetes desde `composer.lock`. |
| Git | El repositorio original quedó sincronizado con GitHub y sin cambios pendientes después de la verificación inicial. |

### Verificación de instalación limpia

Se realizó una prueba en una carpeta independiente llamada `floristeria_prueba`, clonando el repositorio público desde GitHub e instalando las dependencias mediante `composer install`.

Durante esta prueba, PHPCS identificó diferencias en los finales de línea de dos archivos: `src/registrarProducto.php` y `tests/RegistrarProductoTest.php`. Se utilizó PHPCBF para corregirlas automáticamente.

Posteriormente, se trasladaron los archivos corregidos al proyecto original y se ejecutaron nuevamente las pruebas. El resultado fue satisfactorio: PHPUnit aprobó los 10 tests y PHPCS finalizó sin errores ni advertencias.

Las pruebas comprobaron el rechazo de precios no finitos y el tratamiento de espacios Unicode, además de los demás casos contemplados en la suite.

### Pendientes y limitaciones

- No se ha realizado una auditoría de una aplicación completa, ya que el alcance corresponde a una función de registro.
- No se implementan actualmente persistencia en base de datos, autenticación ni administración de usuarios.
- No se ha definido una licencia de distribución para el proyecto.

### Conclusiones

La revisión permitió mejorar la validación de los datos de entrada mediante la corrección de dos problemas reales. Las pruebas automatizadas y las herramientas de análisis permitieron comprobar el estado del código después de los cambios.

La prueba de instalación limpia permitió verificar que el proyecto puede clonarse e instalar sus dependencias desde el archivo `composer.lock`. También permitió identificar y corregir un problema de formato en los finales de línea de dos archivos, logrando que PHPCS finalizara sin errores.

La segunda revisión con IA aportó sugerencias útiles, pero sus resultados fueron verificados manualmente. Finalmente, se actualizaron la documentación del proyecto y la bitácora para dejar evidencia del proceso de revisión y de las decisiones tomadas.