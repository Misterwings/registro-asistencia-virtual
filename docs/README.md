# Documentación de Attendance Record

Esta carpeta reúne la documentación funcional y técnica del sistema de registro de asistencia.

## Documentos

| Documento | Público objetivo | Contenido |
| --- | --- | --- |
| [Manual de usuario](MANUAL_DE_USUARIO.md) | Administradores, organizadores y asistentes | Operación diaria: crear eventos, compartir enlaces/QR, registrar asistentes, consultar firmas y descargar reportes. |
| [Documentación técnica](DOCUMENTACION_TECNICA.md) | Desarrollo, infraestructura y soporte | Arquitectura, rutas, modelo de datos, reglas de negocio, configuración, despliegue, pruebas y limitaciones conocidas. |

## Lectura recomendada

1. Para operar el sistema sin modificar código, comenzar por el [Manual de usuario](MANUAL_DE_USUARIO.md).
2. Para instalar o mantener la aplicación, consultar la [Documentación técnica](DOCUMENTACION_TECNICA.md).
3. Cuando una instrucción del manual dependa de Docker, revisar primero la sección de configuración del entorno local de la documentación técnica.

## Alcance y fuente

La documentación describe el comportamiento implementado en el código del proyecto. Cuando existe una diferencia entre una descripción histórica y el comportamiento actual, se indica expresamente en la sección de limitaciones conocidas de la documentación técnica.

La interfaz y los mensajes de la aplicación están en español. Los nombres de clases, variables, rutas internas y comandos conservan la nomenclatura del código fuente.
