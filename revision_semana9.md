# Revisión Semana 9 – Floristería Darcy

## 1. Integración continua y CI/CD

Se implementó el workflow `semana9-cicd.yml` mediante GitHub Actions.

El pipeline contempla:
- Descarga del repositorio.
- Configuración de PHP 8.2 y Python 3.12.
- Instalación de dependencias.
- Ejecución de PHPUnit y Pytest.
- Verificación de estándares con PHPCS.
- Auditoría de dependencias con Composer Audit y pip-audit.
- Construcción de la imagen Docker sin publicarla.

Se establecieron permisos mínimos mediante `contents: read`. No se almacenan claves ni contraseñas reales en el archivo YAML.

## 2. Contenedores Docker

Se crearon los siguientes archivos:
- `Dockerfile`: define el entorno PHP 8.2 e instala pdo_pgsql.
- `.dockerignore`: excluye archivos sensibles, pruebas y elementos innecesarios.
- `docker-compose.yml`: configura la aplicación PHP y PostgreSQL mediante servicios independientes.

La aplicación utiliza un usuario no privilegiado dentro del contenedor.

La construcción y ejecución local de Docker quedan pendientes de validación por inconvenientes de configuración de WSL 2 en Windows.

## 3. Monitoreo

Se proponen las cuatro señales doradas:

- Latencia: tiempo de respuesta de las operaciones.
- Tráfico: cantidad de solicitudes y operaciones por minuto.
- Errores: porcentaje de operaciones fallidas.
- Saturación: consumo de CPU, memoria y almacenamiento.

## 4. Alertas

Se proponen las siguientes alertas:

- Tiempo de respuesta superior a 2 segundos.
- Más de tres errores de venta en cinco minutos.
- Uso de disco superior al 90 %.
- Incremento sostenido de errores en las operaciones.

Las alertas deben considerar periodos de evaluación para evitar falsas alarmas.

## 5. Análisis de incidentes y postmortem

Se adopta un enfoque sin culpables, orientado a identificar causas técnicas y prevenir recurrencias.

Cada incidente debe documentar:
- Descripción y momento del incidente.
- Servicios afectados.
- Evidencias y registros.
- Causa identificada o hipótesis pendientes de comprobar.
- Acciones correctivas y preventivas.

## 6. Mantenimiento y seguridad

Se realizaron las siguientes comprobaciones:

- PHPUnit: 42 pruebas, 66 aserciones y 5 omitidas.
- Pytest: 35 pruebas aprobadas.
- PHPCS: sin errores reportados con `--warning-severity=0`.
- Composer Audit: sin avisos de vulnerabilidades.

Se incorpora `pip-audit` al pipeline para revisar dependencias Python.

Los registros utilizados para diagnósticos deben anonimizar datos personales y credenciales.

## 7. MLOps y deriva de datos

Aunque el sistema actual de Floristería Darcy no tiene un modelo predictivo integrado, se documenta el procedimiento aplicable si se incorpora uno:

1. Versionar los datos de entrenamiento.
2. Entrenar con conjuntos de datos identificados.
3. Evaluar con datos no utilizados durante el entrenamiento.
4. Registrar la versión del modelo.
5. Monitorear errores y cambios en los datos.
6. Reentrenar cuando se detecte una degradación justificada.

La deriva de datos ocurre cuando cambia la distribución de las entradas. La deriva de concepto ocurre cuando cambia la relación entre las entradas y los resultados esperados.

## 8. Conclusión

La Semana 9 incorpora medidas de automatización, seguridad, contenedorización, monitoreo y mantenimiento para Floristería Darcy.

Las pruebas locales de PHP y Python fueron ejecutadas satisfactoriamente. La validación real de la construcción Docker y la ejecución del workflow en GitHub quedan pendientes de realizarse.
