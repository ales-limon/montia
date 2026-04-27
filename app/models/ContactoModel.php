<?php
/**
 * Modelo de Contacto - LinkViewer
 * Gestiona las solicitudes de amistad y el intercambio de enlaces entre usuarios
 */

class ContactoModel {
    private $db;
    private $userId;

    public function __construct($pdo, $userId) {
        $this->db = $pdo;
        $this->userId = $userId;
    }

    /**
     * Buscar usuario por email (para enviar solicitud)
     */
    public function buscarUsuario($email) {
        $stmt = $this->db->prepare("SELECT id, nombre, email FROM usuarios WHERE email = ? AND id != ?");
        $stmt->execute([$email, $this->userId]);
        return $stmt->fetch();
    }

    /**
     * Enviar solicitud de contacto con validación mejorada
     */
    public function enviarSolicitud($idReceptor) {
        // Verificar si ya existe alguna relación en cualquier dirección
        $stmt = $this->db->prepare("SELECT id, estado, id_solicitante FROM contactos WHERE (id_solicitante = ? AND id_receptor = ?) OR (id_solicitante = ? AND id_receptor = ?)");
        $stmt->execute([$this->userId, $idReceptor, $idReceptor, $this->userId]);
        $existente = $stmt->fetch();

        if ($existente) {
            if ($existente['estado'] === 'aceptado') throw new Exception("Ya son contactos.");
            if ($existente['id_solicitante'] == $this->userId) throw new Exception("Ya enviaste una solicitud a este usuario.");
            throw new Exception("Este usuario ya te envió una solicitud. búscala en tus solicitudes pendientes.");
        }

        $stmt = $this->db->prepare("INSERT INTO contactos (id_solicitante, id_receptor, estado) VALUES (?, ?, 'pendiente')");
        return $stmt->execute([$this->userId, $idReceptor]);
    }

    /**
     * Listar solicitudes enviadas por el usuario
     */
    public function listarSolicitudesEnviadas() {
        $stmt = $this->db->prepare("SELECT c.id, u.nombre, u.email, c.estado, c.created_at 
                                   FROM contactos c 
                                   JOIN usuarios u ON c.id_receptor = u.id 
                                   WHERE c.id_solicitante = ?
                                   ORDER BY c.created_at DESC");
        $stmt->execute([$this->userId]);
        return $stmt->fetchAll();
    }

    /**
     * Listar solicitudes de contacto recibidas (pendientes)
     */
    public function listarSolicitudesPendientes() {
        $stmt = $this->db->prepare("SELECT c.id, u.nombre, u.email, c.id_solicitante 
                                   FROM contactos c 
                                   JOIN usuarios u ON c.id_solicitante = u.id 
                                   WHERE c.id_receptor = ? AND c.estado = 'pendiente'");
        $stmt->execute([$this->userId]);
        return $stmt->fetchAll();
    }

    /**
     * Aceptar o rechazar solicitud
     */
    public function responderSolicitud($idSolicitud, $estado) {
        if (!in_array($estado, ['aceptado', 'rechazado'])) return false;

        $stmt = $this->db->prepare("UPDATE contactos SET estado = ? WHERE id = ? AND id_receptor = ?");
        return $stmt->execute([$estado, $idSolicitud, $this->userId]);
    }

    /**
     * Listar contactos aceptados
     */
    public function listarContactos() {
        $stmt = $this->db->prepare("
            SELECT u.id, u.nombre, u.email 
            FROM contactos c
            JOIN usuarios u ON (c.id_solicitante = u.id OR c.id_receptor = u.id)
            WHERE (c.id_solicitante = ? OR c.id_receptor = ?) 
            AND c.estado = 'aceptado'
            AND u.id != ?
        ");
        $stmt->execute([$this->userId, $this->userId, $this->userId]);
        return $stmt->fetchAll();
    }

    /**
     * Compartir un enlace con un contacto
     */
    public function compartirEnlace($idEnlace, $idReceptor) {
        // Verificar que sean contactos aceptados
        $stmt = $this->db->prepare("
            SELECT id FROM contactos 
            WHERE ((id_solicitante = ? AND id_receptor = ?) OR (id_solicitante = ? AND id_receptor = ?))
            AND estado = 'aceptado'
        ");
        $stmt->execute([$this->userId, $idReceptor, $idReceptor, $this->userId]);
        
        if (!$stmt->fetch()) return false;

        // Obtener la nota original del emisor para este enlace
        $stmtNote = $this->db->prepare("SELECT notas FROM enlaces WHERE id = ? AND id_tenant = ?");
        $stmtNote->execute([$idEnlace, $this->userId]);
        $enlace = $stmtNote->fetch();
        $notasEmisor = $enlace ? $enlace['notas'] : null;

        $stmt = $this->db->prepare("INSERT INTO enlaces_compartidos (id_enlace, id_emisor, id_receptor, notas_emisor) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$idEnlace, $this->userId, $idReceptor, $notasEmisor]);
    }

    /**
     * Listar enlaces recibidos
     */
    public function listarCompartidosRecibidos() {
        $stmt = $this->db->prepare("
            SELECT ec.id as share_id, e.*, ec.notas_emisor as notas, u.nombre as emisor_nombre, ec.visto, ec.created_at as compartido_at
            FROM enlaces_compartidos ec
            JOIN enlaces e ON ec.id_enlace = e.id
            JOIN usuarios u ON ec.id_emisor = u.id
            WHERE ec.id_receptor = ?
            ORDER BY ec.created_at DESC
        ");
        $stmt->execute([$this->userId]);
        return $stmt->fetchAll();
    }

    /**
     * Marcar enlace compartido como visto
     */
    /**
     * Marcar enlace compartido como visto
     */
    public function marcarComoVisto($shareId) {
        $stmt = $this->db->prepare("UPDATE enlaces_compartidos SET visto = 1 WHERE id = ? AND id_receptor = ?");
        return $stmt->execute([$shareId, $this->userId]);
    }

    /**
     * Eliminar un enlace compartido (limpiar bandeja)
     */
    public function eliminarCompartido($shareId) {
        $stmt = $this->db->prepare("DELETE FROM enlaces_compartidos WHERE id = ? AND id_receptor = ?");
        return $stmt->execute([$shareId, $this->userId]);
    }

    /**
     * Contar enlaces compartidos no vistos
     */
    public function contarNoVistos() {
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM enlaces_compartidos WHERE id_receptor = ? AND visto = 0");
        $stmt->execute([$this->userId]);
        $res = $stmt->fetch();
        return (int)($res['total'] ?? 0);
    }
}
