# Reglas Globales FORJIATO

Este documento resume las reglas obligatorias para el proyecto **LinkViewer**.

## Estructura MVC
- **Controlador**: Orquesta (no lógica pesada).
- **Modelo**: Acceso a datos (sin lógica de presentación).
- **Vista**: Solo UI.
- **Services**: Lógica de negocio reutilizable.
- **Middleware**: Seguridad y validaciones globales.

## Multitenant (Obligatorio)
- **TODA** consulta lleva `id_tenant`.
- Nunca usar defaults tipo `id_tenant = 1`.
- Validar sesión antes de cualquier operación.
- Aislamiento lógico en DB.
- **Prohibido**: Queries sin filtro por tenant, confiar en datos del frontend.

## Seguridad
- **Sesiones**: `HttpOnly=true`, `Secure=true`, `SameSite=Strict/Lax`.
- **CSRF**: Token en formularios y endpoints sensibles. Validar siempre.
- **Input**: Validar y sanear TODO input. Usar Prepared Statements (PDO).
- **Output**: Escapar HTML en vistas. Headers correctos.
- **Brute Force**: Implementar limitación de intentos (Rate Limiting) por IP en endpoints de autenticación (Login). Bloquear tras 5 fallos en 15 minutos.

## API / AJAX
- SIEMPRE responder JSON limpio.
- Formato: `{"success": true, "data": {}, "error": null}`.
- **Prohibido**: Warnings PHP en salida, echo suelto, respuestas inconsistentes.

## Manejo de Errores
- `display_errors = OFF` en producción.
- Logs en `/storage/logs`.
- Errores visibles solo en dev.

## Base de Datos
- Usar PDO y Prepared Statements siempre.
- Transacciones cuando aplique.
- Tablas en plural, snake_case.

## Buenas Prácticas
- **Idioma**: Toda comunicación (conversaciones) y comentarios en el código deben ser estrictamente en **español**.
- Versionar TODO (Git).
- Commits claros.
- No subir credenciales. Usar `.env`.
- No lógica en vistas.
- No deploy manual en producción.
- **Interfaz**: Queda estrictamente prohibido el uso de emojis en la interfaz. Se deben utilizar únicamente iconos de **FontAwesome** para mantener la coherencia visual y profesional.
