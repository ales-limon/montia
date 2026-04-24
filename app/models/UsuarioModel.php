<?php
/**
 * Modelo de Usuario - LinkViewer
 * Gestiona la autenticación y el aislamiento de id_tenant
 */

class UsuarioModel {
    private $db;

    public function __construct($pdo) {
        $this->db = $pdo;
    }

    /**
     * Registrar un nuevo usuario
     */
    public function registrar($nombre, $email, $password) {
        try {
            $this->db->beginTransaction();

            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $this->db->prepare("INSERT INTO usuarios (nombre, email, password) VALUES (?, ?, ?)");
            $stmt->execute([$nombre, $email, $hash]);
            
            $userId = $this->db->lastInsertId();

            // En este sistema, id_tenant es igual al id del usuario
            $stmt = $this->db->prepare("UPDATE usuarios SET id_tenant = ? WHERE id = ?");
            $stmt->execute([$userId, $userId]);

            // Poblar categorías iniciales por defecto
            $this->poblarCategoriasBase($userId);

            $this->db->commit();
            return $userId;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error en registro: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Login de usuario
     */
    public function login($email, $password) {
        $stmt = $this->db->prepare("SELECT id, nombre, email, password, rol, id_tenant, suscripcion FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Eliminar password del array antes de guardarlo en sesión
            unset($user['password']);
            return $user;
        }
        return false;
    }

    /**
     * Poblar categorías base para un nuevo usuario
     */
    private function poblarCategoriasBase($tenantId) {
        $categorias = [
            ['Tecnología', '#3498db', 'fa-laptop-code'],
            ['Gatitos', '#e67e22', 'fa-cat'],
            ['Risa', '#f1c40f', 'fa-face-laugh-beam'],
            ['Trabajo', '#2c3e50', 'fa-briefcase'],
            ['Educación', '#9b59b6', 'fa-book-open'],
            ['Comida', '#e74c3c', 'fa-utensils']
        ];

        $stmt = $this->db->prepare("INSERT INTO categorias (id_tenant, nombre, color, icono) VALUES (?, ?, ?, ?)");
        foreach ($categorias as $cat) {
            $stmt->execute([$tenantId, $cat[0], $cat[1], $cat[2]]);
        }
    }

    /**
     * Crear una solicitud de mejora de plan
     */
    public function solicitarMejoraPlan($userId, $plan) {
        $stmt = $this->db->prepare("INSERT INTO solicitudes_plan (id_usuario, plan_solicitado) VALUES (?, ?)");
        return $stmt->execute([$userId, $plan]);
    }
}
