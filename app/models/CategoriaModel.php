<?php
/**
 * Modelo de Categoria - LinkViewer
 * Gestiona las categorías de cada usuario
 */

class CategoriaModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Listar categorías de un usuario
     */
    public function listarPorUsuario($id_tenant) {
        $stmt = $this->db->prepare("SELECT * FROM categorias WHERE id_tenant = ? ORDER BY nombre ASC");
        $stmt->execute([$id_tenant]);
        return $stmt->fetchAll();
    }

    /**
     * Crear una nueva categoría
     */
    public function crear($data) {
        $stmt = $this->db->prepare("INSERT INTO categorias (id_tenant, nombre, color, icono) VALUES (?, ?, ?, ?)");
        return $stmt->execute([
            $data['id_tenant'],
            $data['nombre'],
            $data['color'] ?? '#6366f1',
            $data['icono'] ?? 'fa-tag'
        ]);
    }

    /**
     * Eliminar una categoría
     */
    public function eliminar($id, $id_tenant) {
        $stmt = $this->db->prepare("DELETE FROM categorias WHERE id = ? AND id_tenant = ?");
        return $stmt->execute([$id, $id_tenant]);
    }
}
