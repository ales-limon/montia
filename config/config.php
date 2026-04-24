<?php
ob_start();
/**
 * Configuración Global - LinkViewer
 * Estándar FORJIATO
 */

// Configuración de errores según entorno
$envPath = __DIR__ . '/../.env';
$env = file_exists($envPath) ? parse_ini_file($envPath) : [];

if (($env['APP_ENV'] ?? 'prod') === 'dev') {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}

// Configuración de Sesión Básica (Más compatible con localhost)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Asegurar token CSRF para usuarios ya logueados
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Conexión a Base de Datos (PDO)
try {
    $dbPort = $env['DB_PORT'] ?? 3306;
    $dsn = "mysql:host={$env['DB_HOST']};port={$dbPort};dbname={$env['DB_NAME']};charset=utf8mb4";
    $pdo = new PDO($dsn, $env['DB_USER'], $env['DB_PASS'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    // Loguear error y mostrar mensaje controlado
    error_log("Error de conexión: " . $e->getMessage());
    die("Error de sistema. Por favor, intente más tarde.");
}

// Definición de constantes
define('APP_NAME', $env['APP_NAME'] ?? 'Montia');
define('APP_URL', $env['APP_URL'] ?? 'https://montia.mx');
define('CSRF_SECRET', $env['CSRF_SECRET'] ?? 'default_secret_forjiato_123');

/**
 * Función para obtener id_tenant del usuario actual
 */
function getTenantId() {
    return $_SESSION['id_tenant'] ?? null;
}

/**
 * Función para validar CSRF
 */
function validateCsrf($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
