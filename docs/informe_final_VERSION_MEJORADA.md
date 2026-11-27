# Informe final del proyecto integrador — Floristería Darcy

**Seminario:** Inteligencia Artificial Aplicada al Ciclo de Vida del Software  
**Proyecto:** Sistema administrativo web para una floristería  
**Estudiante:** Identidad anonimizada para cumplir la regla de privacidad  
**Fecha:** 27 de noviembre de 2026  
**Repositorio:** https://github.com/NeoCoder29/floristeria_darcy

> Se declaran las limitaciones de evidencia; no se inventan prompts, entrevistas, commits ni resultados.

## 1. Resumen
Este informe documenta el desarrollo de Floristería Darcy en el seminario Inteligencia Artificial Aplicada al Ciclo de Vida del Software. El resultado es una aplicación web administrativa para gestionar productos, categorías, inventario, clientes y ventas.

La evidencia específica del proyecto comienza en el Módulo 2 (Semana 3). El Módulo 1 correspondió a actividades generales de fundamentos de IA y no se encontró un entregable de Floristería Darcy asociado a esa etapa. Esta limitación se declara sin atribuir actividades inexistentes.

ChatGPT se utilizó como apoyo en requisitos, diseño, código, pruebas y documentación. Sus resultados se contrastaron con documentos, código, pruebas automatizadas, análisis estático y ejecuciones de GitHub Actions.

Entre los resultados documentados se encuentran correcciones de validación en HU-01, pruebas PHPUnit y pytest, cobertura de reglas de dominio, cinco mutaciones seleccionadas detectadas, auditorías de dependencias, CI/CD y validación local de Docker.

La entrevista a Darcy fue simulada; no hubo validación de campo con una propietaria real. Los pedidos personalizados aparecen en requisitos iniciales, pero no se declaran como funcionalidad terminada en la versión final observada.

## 2. Problema y usuario

El problema abordado es la dispersión de la información operativa de una floristería: consultar flores e insumos disponibles, registrar entradas y salidas, controlar existencias, asociar clientes a ventas y revisar el historial de operaciones.

El documento de requisitos de Semana 3 identifica a Darcy como propietaria ficticia y encargada del negocio. La entrevista se simuló con ChatGPT; por tanto, sus citas y necesidades constituyen un ejercicio académico, no declaraciones reales ni investigación con una usuaria externa.

La solución final observada centraliza seis áreas: panel, productos, inventario, categorías, clientes y ventas. Los pedidos personalizados, fechas de entrega, facturación electrónica, pagos en línea y geolocalización no se presentan como funcionalidades implementadas.

## 3. La IA en el ciclo de vida

| Módulo / fase | Uso de IA | Herramienta | Evidencia | Verificación |
|---|---|---|---|---|
| Módulo 1 – Fundamentos | Semana 1 abordó conceptos de IA y un laboratorio comparativo general. No se encontró evidencia específica de Floristería Darcy en este módulo. | No se atribuye una interacción de IA al proyecto. | Tarea_Semana1.pdf (actividad general, no evidencia del sistema). | Se declara el límite; la trazabilidad del proyecto inicia en Semana 3. |
| Módulo 2 – Requisitos | Estructurar actores, necesidades, historias y criterios. La entrevista a Darcy se simuló con ChatGPT. | ChatGPT | Documento de requisitos: https://github.com/NeoCoder29/floristeria_darcy/blob/main/Documento_Requisitos_Floristeria_Darcy_Final.docx | Se revisaron las 10 historias y se declaró que Darcy y la entrevista son ficticias. |
| Módulo 3 – Diseño | Explorar alternativas, C4, modelo de datos y propuesta REST; revisar sobreingeniería y alcance. | ChatGPT | Entregable local: Cabrera_Samuel_S4_Diseño.pdf; repo: https://github.com/NeoCoder29/floristeria_darcy | Se descartaron opciones desproporcionadas y se priorizó monolito local. El PDF debe quedar también en docs/. |
| Módulo 4 – Código | Apoyo en HU-01 y revisión de registrarProducto; corrección de precios no finitos y espacios Unicode. | ChatGPT, PHP, PHPUnit, PHPCS | Revisión: https://github.com/NeoCoder29/floristeria_darcy/blob/main/revision_seguridad.md; commit: https://github.com/NeoCoder29/floristeria_darcy/commit/84de46e94916ee143e429f0d429842f01e771c46 | Se reprodujeron los casos y se ejecutaron 10 pruebas / 14 aserciones en esa revisión. |
| Módulo 5 – Pruebas | Particiones, límites, tablas de decisión, datos sintéticos, pruebas parametrizadas, mutaciones e integración. | ChatGPT, PHPUnit, pytest, Xdebug | Bitácora: https://github.com/NeoCoder29/floristeria_darcy/blob/main/bitacora_ia.md; registro: https://github.com/NeoCoder29/floristeria_darcy/blob/main/registro_pruebas.md; plan: https://github.com/NeoCoder29/floristeria_darcy/blob/main/docs/semana8-plan-pruebas.md; ejecución: https://github.com/NeoCoder29/floristeria_darcy/actions/runs/45915401359 | Esperados derivados de reglas; cinco mutaciones seleccionadas detectadas; PostgreSQL aislado en CI. |
| Módulo 6 – Operación | Documentación de operación, seguridad, CI/CD, Docker, auditorías y propuesta de monitoreo. | ChatGPT, GitHub Actions, Docker | Revisión: https://github.com/NeoCoder29/floristeria_darcy/blob/main/revision_semana9.md; workflow: https://github.com/NeoCoder29/floristeria_darcy/blob/main/.github/workflows/semana9-cicd.yml; acciones: https://github.com/NeoCoder29/floristeria_darcy/actions | Pipeline y construcción Docker; después validación local y PostgreSQL: https://github.com/NeoCoder29/floristeria_darcy/commit/dc2b5980ffeeb69ea809ff97346d4e60aa20c191 |

**Módulo 1:** la actividad de Semana 1 fue general y no se encontró propuesta específica de Floristería Darcy. El proyecto documentado inicia en Semana 3 (Módulo 2).

## 4. Verificación
### Caso 1 — Requisitos: entrevista simulada que no puede tratarse como evidencia de campo

- **Fase:** Requisitos (Módulo 2).
- **Salida revisada:** ChatGPT se utilizó para simular una entrevista con un personaje llamado Darcy y generar necesidades y frases con apariencia de respuestas de una propietaria.
- **Problema/riesgo:** El contenido no es evidencia empírica. Presentarlo como entrevista real atribuiría declaraciones a una persona que no participó.
- **Detección:** Al revisar el documento fuente de Semana 3, que identifica a Darcy como ficticia y la entrevista como simulada.
- **Corrección:** Se mantuvo la etiqueta de entrevista simulada y se aclaró que las necesidades deben validarse con una floristería real. No se conserva un SHA individual para esta aclaración.
- **Evidencia:** https://github.com/NeoCoder29/floristeria_darcy/blob/main/Documento_Requisitos_Floristeria_Darcy_Final.docx

### Caso 2 — Diseño: propuestas que podían exceder el alcance

- **Fase:** Diseño (Módulo 3).
- **Salida revisada:** La evaluación crítica del diseño registra propuestas de IA como microservicios, serverless, nube, accesos para clientes/proveedores y endpoints para funciones no priorizadas.
- **Problema/riesgo:** Estas opciones podían introducir sobreingeniería o convertir historias Should/Could y supuestos en alcance obligatorio para una floristería pequeña.
- **Detección:** Al comparar las alternativas con las historias Must, la usuaria directa definida y los atributos de calidad; el documento de diseño incluye una matriz de evaluación crítica.
- **Corrección:** Se descartó la arquitectura desproporcionada, se eligió monolito local para la primera versión, se mantuvo a Darcy como usuaria directa y se limitaron los endpoints a historias priorizadas. No se conserva un commit individual del PDF de diseño.
- **Evidencia:** Entregable local: Cabrera_Samuel_S4_Diseño.pdf; repositorio: https://github.com/NeoCoder29/floristeria_darcy

### Caso 3 — Operación/documentación: la segunda revisión con IA no detectó un README incompleto

- **Fase:** Operación y documentación (Módulo 6 / Semana 6).
- **Salida revisada:** Durante la segunda revisión, la IA no señaló que el README anterior todavía no cumplía la estructura de ocho apartados exigida en la actividad.
- **Problema/riesgo:** El README era incompleto frente a la lista explícita de la tarea, aunque la revisión asistida no lo había identificado.
- **Detección:** Se comparó manualmente el README con las instrucciones de Semana 6, que enumeraban las secciones requeridas.
- **Corrección:** Se reorganizó el README y se documentaron instalación, ejecución, pruebas, estructura, uso de IA y demás apartados. El commit 84de46e incluye la actualización del README y la revisión de calidad.
- **Evidencia:** https://github.com/NeoCoder29/floristeria_darcy/blob/main/revision_seguridad.md; commit: https://github.com/NeoCoder29/floristeria_darcy/commit/84de46e94916ee143e429f0d429842f01e771c46

**Límite de trazabilidad:** no se conservan todos los prompts/respuestas literales. Los casos 1 y 2 no tienen un commit individual exclusivo. Los problemas NAN/INF y espacios Unicode fueron defectos del código que la IA ayudó a encontrar; no se presentan como errores introducidos por IA.

## 5. Estado final del sistema

Arquitectura y tecnologías. El README vigente describe una aplicación web administrativa basada en PHP 8.2 o superior y PostgreSQL 14 o superior, con PDO PostgreSQL, PHPUnit y pytest. El diseño de Semana 4 fue una propuesta inicial; la implementación evolucionó y el README/código describen el estado final.

Funcionalidades observadas: panel con indicadores; registro y consulta de productos y categorías; entradas y salidas de inventario con motivo y trazabilidad; directorio de clientes; registro de ventas con descuento, notas e historial; descuento automático de existencias al confirmar una venta.

Pruebas. En la ejecución 45915401359 se documentan 42 pruebas PHPUnit y 89 aserciones aprobadas; 35 pruebas pytest; cobertura de 88/89 líneas (98,88 %) y 5/6 métodos (83,33 %); cinco mutantes seleccionados detectados (5/5). Estos resultados corresponden a esa ejecución.

Semana 9 registra otra ejecución con 42 pruebas PHPUnit, 66 aserciones y 5 omitidas; 35 pytest; PHPCS, Composer Audit y pip-audit sin hallazgos reportados en esa ejecución. No se mezclan los resultados de ambas ejecuciones.

CI/CD y Docker. El workflow de Semana 9 ejecutó los trabajos de pruebas/seguridad y construcción Docker. Posteriormente, Docker Compose se validó localmente: la aplicación estuvo disponible en localhost:8000 y PostgreSQL respondió accepting connections. Esto no equivale a un despliegue público.

Pendientes. Los pedidos personalizados no se muestran como módulo final; tampoco se incluyen pagos en línea, facturación electrónica, geolocalización ni portal de clientes. Las señales doradas y alertas se documentan como propuesta, no como plataforma de observabilidad activa.

## 6. Reflexión ética y legal

| Principio | Decisión | Evidencia / límite |
|---|---|---|
| Datos personales / Ley 1581 de 2012 | El sistema admite nombre, teléfono, correo y dirección. Las pruebas usan datos sintéticos y las capturas se anonimizan. No se acredita un programa integral de autorización, aviso, retención y atención de derechos; no se afirma cumplimiento legal total. | README y capturas; política de tratamiento pendiente. |
| Sesgo | Darcy y la entrevista son ficticias; las necesidades no representan necesariamente a todas las floristerías. | Documento de requisitos Semana 3. |
| Transparencia | Se declara el uso de ChatGPT y se distingue entrevista simulada, supuestos y funciones realmente terminadas. | bitacora_ia.md y documentos de requisitos/diseño. |
| Responsabilidad | La responsabilidad por código, decisiones, seguridad y tratamiento de datos recae en quien desarrolla y opera el sistema; la IA no sustituye la revisión humana. | Pruebas, revisión de seguridad y pipeline. |
| Propiedad intelectual | Se deben respetar licencias y avisos de dependencias. No se acredita auditoría exhaustiva de licencias ni licencia de distribución definida. | composer.lock y repositorio; revisión exhaustiva pendiente. |

**Dilema ético principal:** una entrevista ficticia generada con IA podía confundirse con evidencia de campo. Se declaró su carácter simulado y no se atribuyeron las frases a una persona real. También se anonimizaron las capturas y no se presentaron funciones propuestas como terminadas.

## 7. Aprendizajes

Aprendí que el desarrollo no termina cuando la pantalla funciona: también debo comprobar reglas de negocio, persistencia, entradas inválidas, seguridad, calidad del código y comportamiento ante fallos.

Aprendí a derivar pruebas de reglas explícitas y no de lo que el programa devuelve. La cobertura indica qué se ejecutó; las mutaciones ayudan a comprobar si las pruebas detectan cambios incorrectos; ninguna métrica demuestra por sí sola que el sistema esté libre de defectos.

Aprendí a tratar la IA como apoyo, no como autoridad. Puede generar requisitos simulados, sugerir arquitecturas demasiado complejas o pasar por alto requisitos documentales. La decisión final debe contrastarse con las fuentes, el código y las pruebas.

Si repitiera el proyecto, mantendría desde el primer módulo una bitácora completa con prompt real, fragmento de respuesta, decisión, archivo, prueba y commit; validaría requisitos con una floristería real; y subiría al repositorio los entregables académicos de cada módulo.

## 8. Anexos

- Capturas anonimizadas de panel, productos, inventario, categorías, clientes y ventas.
- Documentos fuente: requisitos (Semana 3), diseño (Semana 4), revisión de seguridad (Semana 6), registro y plan de pruebas (Semanas 7–8), revisión Semana 9.
- Commits de corrección HU-01 y validación Docker.

**Cierre documental pendiente:** subir este PDF al repositorio; incorporar el diseño de Semana 4 y entregables de cada módulo a `docs/`; verificar la bitácora completa y la última ejecución verde del pipeline.