# Infraestructura y Despliegue

## Entornos
- **Desarrollo (Local)**: Configurado en Laragon. Errores visibles para depuración.
- **Producción**: Configuración restrictiva. Logs activos pero errores ocultos al usuario.

## Requisitos
- Servidor Web (Apache/Nginx).
- PHP 8.x+ con extensiones PDO, JSON, MBString.
- Base de Datos MariaDB/MySQL.

## Gestión de Configuración
- Uso de archivos `.env` para variables de entorno (Base de datos, claves de API, etc.).
- Las credenciales **NUNCA** se suben al repositorio.

## Logs
- Ubicación: `/storage/logs/`.
- Rotación de logs recomendada.
- Registro de errores críticos y accesos no autorizados.
