<?php
/**
 * Controlador Administrativo - LinkViewer
 * Gestiona métricas globales para el administrador
 */

class AdminController {
    private $db;

    public function __construct($pdo) {
        $this->db = $pdo;
    }

    /**
     * Obtener métricas generales
     */
    public function getMetrics() {
        if ($_SESSION['user_rol'] !== 'admin') {
            return ['success' => false, 'error' => 'No autorizado.'];
        }

        // Totales Básicos
        $totalUsers = $this->db->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
        $totalLinks = $this->db->query("SELECT COUNT(*) FROM enlaces")->fetchColumn();
        
        // Suscripciones
        $premiumUsers = $this->db->query("SELECT COUNT(*) FROM usuarios WHERE suscripcion = 'premium'")->fetchColumn();
        $gratisUsers = $totalUsers - $premiumUsers;

        // Usuarios con más links (Top 5)
        $topUsers = $this->db->query("
            SELECT u.nombre, u.email, COUNT(e.id) as total_links 
            FROM usuarios u 
            LEFT JOIN enlaces e ON u.id = e.id_tenant 
            GROUP BY u.id 
            ORDER BY total_links DESC 
            LIMIT 5
        ")->fetchAll();

        // Usuarios Recientes
        $recentUsers = $this->db->query("SELECT nombre, email, suscripcion, created_at FROM usuarios ORDER BY created_at DESC LIMIT 5")->fetchAll();

        // Estimación de Ingresos (Premium a 150 MXN/mes)
        $revenue = $premiumUsers * 150;

        return [
            'success' => true,
            'data' => [
                'total_usuarios' => $totalUsers,
                'total_enlaces' => $totalLinks,
                'usuarios_recientes' => $recentUsers,
                'premium_users' => $premiumUsers,
                'gratis_users' => $gratisUsers,
                'top_users' => $topUsers,
                'revenue_est' => $revenue
            ]
        ];
    }

    /**
     * Obtener lista de todos los usuarios para gestión
     */
    public function getUsers() {
        if ($_SESSION['user_rol'] !== 'admin') {
            return ['success' => false, 'error' => 'No autorizado.'];
        }

        $users = $this->db->query("SELECT id, nombre, email, suscripcion, rol, created_at FROM usuarios ORDER BY created_at DESC")->fetchAll();
        return ['success' => true, 'data' => $users];
    }

    /**
     * Actualizar plan de un usuario
     */
    public function updateUserPlan($userId, $newPlan) {
        if ($_SESSION['user_rol'] !== 'admin') {
            return ['success' => false, 'error' => 'No autorizado.'];
        }

        $stmt = $this->db->prepare("UPDATE usuarios SET suscripcion = ? WHERE id = ?");
        $success = $stmt->execute([$newPlan, $userId]);
        
        return ['success' => $success];
    }

    /**
     * Obtener logs de contenido bloqueado (Moderación)
     */
    public function getBlockedLogs() {
        if ($_SESSION['user_rol'] !== 'admin') {
            return ['success' => false, 'error' => 'No autorizado.'];
        }

        $logs = $this->db->query("
            SELECT l.*, u.nombre as usuario_nombre, u.email as usuario_email 
            FROM logs_actividad l 
            JOIN usuarios u ON l.id_tenant = u.id 
            WHERE l.accion = 'CONTENIDO_BLOQUEADO' 
            ORDER BY l.created_at DESC
        ")->fetchAll();
        return ['success' => true, 'data' => $logs];
    }

    /**
     * Obtener toda la configuración global
     */
    public function getConfig() {
        if ($_SESSION['user_rol'] !== 'admin') {
            return ['success' => false, 'error' => 'No autorizado.'];
        }

        $config = $this->db->query("SELECT * FROM configuracion")->fetchAll();
        return ['success' => true, 'data' => $config];
    }

    /**
     * Actualizar valores de configuración
     */
    public function updateConfig($settings) {
        if ($_SESSION['user_rol'] !== 'admin') {
            return ['success' => false, 'error' => 'No autorizado.'];
        }

        $stmt = $this->db->prepare("UPDATE configuracion SET c_value = ? WHERE c_key = ?");
        foreach ($settings as $key => $value) {
            $stmt->execute([$value, $key]);
        }
        
        return ['success' => true];
    }

    /**
     * Obtener solicitudes de mejora de plan (Pagos)
     */
    public function getUpgradeRequests() {
        if ($_SESSION['user_rol'] !== 'admin') {
            return ['success' => false, 'error' => 'No autorizado.'];
        }

        $requests = $this->db->query("
            SELECT s.*, u.nombre as usuario_nombre, u.email as usuario_email 
            FROM solicitudes_plan s 
            JOIN usuarios u ON s.id_usuario = u.id 
            WHERE s.estado = 'pendiente' 
            ORDER BY s.created_at DESC
        ")->fetchAll();
        return ['success' => true, 'data' => $requests];
    }

    /**
     * Procesar una solicitud (Aprobar/Cancelar)
     */
    public function handleUpgradeRequest($requestId, $action) {
        if ($_SESSION['user_rol'] !== 'admin') {
            return ['success' => false, 'error' => 'No autorizado.'];
        }

        if ($action === 'aprobar') {
            // Obtener datos de la solicitud
            $stmt = $this->db->prepare("SELECT id_usuario, plan_solicitado FROM solicitudes_plan WHERE id = ?");
            $stmt->execute([$requestId]);
            $req = $stmt->fetch();

            if ($req) {
                // Actualizar usuario
                $this->updateUserPlan($req['id_usuario'], $req['plan_solicitado']);
                // Marcar solicitud como completada
                $stmt = $this->db->prepare("UPDATE solicitudes_plan SET estado = 'completado' WHERE id = ?");
                $stmt->execute([$requestId]);
                return ['success' => true];
            }
        } elseif ($action === 'cancelar') {
            $stmt = $this->db->prepare("UPDATE solicitudes_plan SET estado = 'cancelado' WHERE id = ?");
            $stmt->execute([$requestId]);
            return ['success' => true];
        }

        return ['success' => false, 'error' => 'Acción no válida.'];
    }
}
