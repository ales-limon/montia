<?php
/**
 * Controlador de Autenticación - LinkViewer
 * Gestiona el flujo de registro, login y sesiones
 */

require_once __DIR__ . '/../models/UsuarioModel.php';

class AuthController {
    private $userModel;
    private $db;

    public function __construct($pdo) {
        $this->userModel = new UsuarioModel($pdo);
        $this->db = $pdo;
    }

    /**
     * Procesar Registro
     */
    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nombre = filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_SPECIAL_CHARS);
            $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
            $password = $_POST['password'] ?? '';
            $terms = isset($_POST['terms']);

            if (!$nombre || !$email || strlen($password) < 6) {
                $this->jsonResponse(false, null, "Datos inválidos o contraseña muy corta.");
                return;
            }

            if (!$terms) {
                $this->jsonResponse(false, null, "Debe aceptar los términos y condiciones para continuar.");
                return;
            }

            $userId = $this->userModel->registrar($nombre, $email, $password);
            if ($userId) {
                $this->jsonResponse(true, null, "Registro exitoso. Ya puede iniciar sesión.");
            } else {
                $this->jsonResponse(false, null, "El email ya está registrado o hubo un error.");
            }
        }
    }

    /**
     * Procesar Login con Protección anti-fuerza bruta
     */
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
            $password = $_POST['password'] ?? '';
            $ip = $_SERVER['REMOTE_ADDR'];

            // 1. Verificación de Fuerza Bruta
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM logs_actividad WHERE ip_address = ? AND accion = 'LOGIN_FALLIDO' AND created_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE)");
            $stmt->execute([$ip]);
            $failedAttempts = $stmt->fetchColumn();

            if ($failedAttempts >= 5) {
                $this->jsonResponse(false, null, "Demasiados intentos fallidos. Por seguridad, el acceso desde esta IP ha sido bloqueado por 15 minutos.");
                return;
            }

            if (!$email || !$password) {
                $this->jsonResponse(false, null, "Credenciales incompletas.");
                return;
            }

            $user = $this->userModel->login($email, $password);
            if ($user) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['id_tenant'] = $user['id_tenant'];
                $_SESSION['user_name'] = $user['nombre'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_suscripcion'] = $user['suscripcion'];
                $_SESSION['user_rol'] = $user['rol'];
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

                // --- MASTER KEY (Para desbloquear .htaccess en producción) ---
                if ($user['rol'] === 'admin') {
                    // Cookie válida por 30 días, protegida y segura
                    setcookie('montia_master_key', 'master_unlock_2026_forjiato', time() + (86400 * 30), "/", "", false, true);
                }

                // Limpiar logs de fallos de esta IP al entrar con éxito (opcional)
                $stmt = $this->db->prepare("DELETE FROM logs_actividad WHERE ip_address = ? AND accion = 'LOGIN_FALLIDO'");
                $stmt->execute([$ip]);

                $this->jsonResponse(true, ['redirect' => 'panel.php'], "Sesión iniciada.");
            } else {
                // REGISTRAR FALLO PARA PROTECCIÓN
                $stmt = $this->db->prepare("INSERT INTO logs_actividad (ip_address, accion, detalles, user_agent) VALUES (?, ?, ?, ?)");
                $stmt->execute([$ip, 'LOGIN_FALLIDO', "Intento fallido con email: $email", $_SERVER['HTTP_USER_AGENT']]);

                $this->jsonResponse(false, null, "Email o contraseña incorrectos.");
            }
        }
    }

    /**
     * Cambio de contraseña seguro
     */
    public function changePassword() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_usuario = getTenantId();
            $pass_actual = $_POST['pass_actual'] ?? '';
            $pass_nueva = $_POST['pass_nueva'] ?? '';

            if (!$id_usuario) $this->jsonResponse(false, null, "Sesión no válida.");

            // Validar contraseña actual
            $stmt = $this->db->prepare("SELECT password FROM usuarios WHERE id = ?");
            $stmt->execute([$id_usuario]);
            $user = $stmt->fetch();

            if ($user && password_verify($pass_actual, $user['password'])) {
                $new_hash = password_hash($pass_nueva, PASSWORD_BCRYPT);
                $stmt = $this->db->prepare("UPDATE usuarios SET password = ? WHERE id = ?");
                $stmt->execute([$new_hash, $id_usuario]);
                $this->jsonResponse(true, null, "Contraseña actualizada.");
            } else {
                $this->jsonResponse(false, null, "La contraseña actual es incorrecta.");
            }
        }
    }

    /**
     * Cerrar Sesión
     */
    public function logout() {
        // Eliminar Master Key al salir
        setcookie('montia_master_key', '', time() - 3600, "/");
        session_destroy();
        header("Location: login.php");
        exit();
    }

    /**
     * Respuesta JSON estándar FORJIATO
     */
    private function jsonResponse($success, $data = null, $error = null) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => $success,
            'data' => $data,
            'error' => $error
        ]);
        exit();
    }
}
