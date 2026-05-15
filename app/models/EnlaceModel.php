<?php
/**
 * Modelo de Enlace - LinkViewer
 * Maneja el CRUD de enlaces con filtrado estricto por id_tenant
 */

class EnlaceModel {
    private $db;
    private $tenantId;

    public function __construct($pdo, $tenantId) {
        $this->db = $pdo;
        $this->tenantId = $tenantId;
    }

    public function getDb() {
        return $this->db;
    }

    /**
     * Elimina caracteres de 4 bytes (emojis, símbolos raros) que MySQL utf8 rechaza
     */
    private function limpiarUtf8($texto) {
        return preg_replace('/[\x{10000}-\x{10FFFF}]/u', '', $texto ?? '');
    }

    /**
     * Crear un nuevo enlace
     */
    public function crear($data) {
        $stmt = $this->db->prepare("INSERT INTO enlaces 
            (id_tenant, id_categoria, url, titulo, descripcion, imagen_url, notas, es_favorito, ver_mas_tarde) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $titulo      = mb_substr($this->limpiarUtf8($data['titulo'] ?? ''), 0, 255);
        $descripcion = mb_substr($this->limpiarUtf8($data['descripcion'] ?? ''), 0, 500);

        return $stmt->execute([
            $this->tenantId,
            $data['id_categoria'] ?? null,
            $data['url'],
            $titulo,
            $descripcion,
            $data['imagen_url'] ?? '',
            $data['notas'] ?? '',
            $data['es_favorito'] ?? 0,
            $data['ver_mas_tarde'] ?? 0
        ]);
    }

    /**
     * Listar enlaces del tenant
     */
    public function listar($filtros = []) {
        $sql = "SELECT e.*, c.nombre as categoria_nombre, c.color as categoria_color,
                (SELECT COUNT(*) FROM compartidos_externos WHERE id_enlace = e.id) as total_compartidos
                FROM enlaces e 
                LEFT JOIN categorias c ON e.id_categoria = c.id 
                WHERE e.id_tenant = ?";
        $params = [$this->tenantId];

        if (!empty($filtros['categoria'])) {
            $sql .= " AND e.id_categoria = ?";
            $params[] = $filtros['categoria'];
        }

        if (isset($filtros['favorito'])) {
            $sql .= " AND e.es_favorito = 1";
        }

        $sql .= " ORDER BY e.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Obtener enlace por ID
     */
    public function obtenerPorId($id) {
        $stmt = $this->db->prepare("SELECT * FROM enlaces WHERE id = ? AND id_tenant = ?");
        $stmt->execute([$id, $this->tenantId]);
        return $stmt->fetch();
    }

    /**
     * Eliminar enlace (Verificando tenant)
     */
    public function eliminar($id) {
        $stmt = $this->db->prepare("DELETE FROM enlaces WHERE id = ? AND id_tenant = ?");
        return $stmt->execute([$id, $this->tenantId]);
    }

    /**
     * Actualizar un enlace existente
     */
    public function actualizar($id, $data) {
        $stmt = $this->db->prepare("UPDATE enlaces SET 
            id_categoria = ?, 
            notas = ?, 
            es_favorito = ?, 
            ver_mas_tarde = ? 
            WHERE id = ? AND id_tenant = ?");
        
        return $stmt->execute([
            $data['id_categoria'] ?? null,
            $data['notas'] ?? '',
            $data['es_favorito'] ?? 0,
            $data['ver_mas_tarde'] ?? 0,
            $id,
            $this->tenantId
        ]);
    }

    /**
     * Alternar favorito/ver más tarde
     */
    public function toggleCampo($id, $campo) {
        $camposValidos = ['es_favorito', 'ver_mas_tarde'];
        if (!in_array($campo, $camposValidos)) return false;

        $stmt = $this->db->prepare("UPDATE enlaces SET $campo = NOT $campo WHERE id = ? AND id_tenant = ?");
        return $stmt->execute([$id, $this->tenantId]);
    }

    /**
     * Obtener historial de compartidos externos
     */
    public function actualizarMetadata($id, $imagen_url, $titulo, $descripcion) {
        $stmt = $this->db->prepare(
            "UPDATE enlaces SET imagen_url = ?, titulo = ?, descripcion = ? WHERE id = ? AND id_tenant = ?"
        );
        return $stmt->execute([$imagen_url, $titulo, $descripcion, $id, $this->tenantId]);
    }

    public function listarCompartidosExternos($idEnlace) {
        $stmt = $this->db->prepare("
            SELECT plataforma, destinatario, created_at 
            FROM compartidos_externos 
            WHERE id_enlace = ? AND id_tenant = ? 
            ORDER BY created_at DESC
        ");
        $stmt->execute([$idEnlace, $this->tenantId]);
        return $stmt->fetchAll();
    }
}
