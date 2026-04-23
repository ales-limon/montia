# Seguridad y Protección de Datos

## Principios de Seguridad
1. **Defensa en Profundidad**: Aplicación de seguridad en múltiples niveles (Middleware, Controladores, Modelos).
2. **Mínimo Privilegio**: Acceso restringido a datos basado en el `id_tenant` del usuario autenticado.

## Medidas Implementadas
- **PDO Prepared Statements**: Prevención total contra SQL Injection.
- **CSRF Protection**: Uso de tokens únicos por sesión para validar peticiones POST/AJAX.
- **Saneamiento de Salida**: Uso de funciones de escape para prevenir XSS.
- **Configuración de Sesiones**:
    - `HttpOnly`: Protege contra el robo de cookies mediante JS.
    - `Secure`: Asegura que las cookies solo se envíen sobre HTTPS.
    - `SameSite`: Previene ataques CSRF de origen cruzado.
- **Validación de Tenant**: Verificación constante de que el usuario tiene acceso al id_tenant solicitado.
