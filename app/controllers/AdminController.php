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

        // Estimación de Ingresos (Premium a 5 USD/mes)
        $revenue = $premiumUsers * 5;

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
}
