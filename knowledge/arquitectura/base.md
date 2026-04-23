# Arquitectura del Sistema

## Descripción General
El sistema sigue un patrón de diseño Modelo-Vista-Controlador (MVC) estricto para asegurar la escalabilidad y mantenibilidad.

## Componentes
- **Capa de Controladores**: Encargada de recibir las peticiones HTTP, interactuar con los servicios y devolver respuestas (HTML o JSON).
- **Capa de Servicios**: Contiene la lógica de negocio. Es donde se procesan los datos antes de ser enviados al modelo o después de ser recuperados.
- **Capa de Modelos**: Interactúa directamente con la base de datos mediante PDO. Implementa el filtrado por id_tenant de forma obligatoria.
- **Middleware**: Capa intermedia para filtrado de seguridad, autenticación y validación de estados globales.
- **Vistas**: Plantillas PHP/HTML puras encargadas únicamente de la representación visual.

## Flujo de Datos
1. Petición -> `public/index.php` (Router)
2. Router -> Middleware (Validación/Sesión)
3. Middleware -> Controlador
4. Controlador -> Service (Lógica)
5. Service -> Model (DB)
6. Controlador -> Vista o JSON response
