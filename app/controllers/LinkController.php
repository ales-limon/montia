<?php
/**
 * Controlador de Enlaces - LinkViewer
 * Orquesta la creación y gestión de enlaces
 */

require_once __DIR__ . '/../models/EnlaceModel.php';
require_once __DIR__ . '/../models/CategoriaModel.php';
require_once __DIR__ . '/../services/MetadataService.php';

class LinkController {
    private $linkModel;
    private $categoriaModel;
    private $metadataService;
    private $tenantId;

    public function __construct($pdo, $tenantId) {
        $this->linkModel = new EnlaceModel($pdo, $tenantId);
        $this->categoriaModel = new CategoriaModel($pdo);
        $this->metadataService = new MetadataService();
        $this->tenantId = $tenantId;
    }

    public function listCategories() {
        $categorias = $this->categoriaModel->listarPorUsuario($this->tenantId);
        $this->jsonResponse(true, $categorias);
    }

    /**
     * Guardar nueva categoría
     */
    public function storeCategory() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nombre = filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_SPECIAL_CHARS);
            $color = filter_input(INPUT_POST, 'color', FILTER_SANITIZE_SPECIAL_CHARS) ?: '#6366f1';

            if (!$nombre) {
                $this->jsonResponse(false, null, "El nombre es obligatorio.");
                return;
            }

            $data = [
                'id_tenant' => $this->tenantId,
                'nombre' => $nombre,
                'color' => $color
            ];

            if ($this->categoriaModel->crear($data)) {
                $this->jsonResponse(true, null, "Categoría creada.");
            } else {
                $this->jsonResponse(false, null, "Error al crear categoría.");
            }
        }
    }

    /**
     * Eliminar categoría
     */
    public function deleteCategory($id) {
        if (!$id) {
            $this->jsonResponse(false, null, "ID no válido.");
            return;
        }

        if ($this->categoriaModel->eliminar($id, $this->tenantId)) {
            $this->jsonResponse(true, null, "Categoría eliminada.");
        } else {
            $this->jsonResponse(false, null, "Error al eliminar.");
        }
    }

    /**
     * Guardar un nuevo enlace (Procesa metadatos automáticamente)
     */
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $url = filter_input(INPUT_POST, 'url', FILTER_VALIDATE_URL);
            $id_categoria = $_POST['id_categoria'] !== '' ? filter_input(INPUT_POST, 'id_categoria', FILTER_VALIDATE_INT) : null;
            $notas = filter_input(INPUT_POST, 'notas', FILTER_SANITIZE_SPECIAL_CHARS);

            if (!$url) {
                $this->jsonResponse(false, null, "URL no válida.");
                return;
            }

            // Extraer metadatos automáticamente
            $metadata = $this->metadataService->extract($url);
            
            // VALIDACIÓN DE SEGURIDAD (Moderación Proactiva)
            if (!$this->metadataService->checkContentSafety($metadata)) {
                // LOG DE MODERACIÓN: Registrar intento de subida de contenido prohibido
                try {
                    $pdo = $this->linkModel->getDb(); // Necesito acceso al PDO
                    $stmt = $pdo->prepare("INSERT INTO logs_actividad (id_tenant, accion, detalles, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([
                        $this->tenantId, 
                        'CONTENIDO_BLOQUEADO', 
                        "URL: " . $url . " | Motivo: Filtro de seguridad", 
                        $_SERVER['REMOTE_ADDR'], 
                        $_SERVER['HTTP_USER_AGENT']
                    ]);
                } catch (Exception $e) { /* Fallo silencioso del log para no interrumpir la app */ }

                $this->jsonResponse(false, null, "Contenido no permitido. LinkViewer prohíbe enlaces con temáticas sensibles o prohibidas.");
                return;
            }
            
            $data = [
                'url' => $url,
                'id_categoria' => $id_categoria,
                'titulo' => $metadata['titulo'],
                'descripcion' => $metadata['descripcion'],
                'imagen_url' => $metadata['imagen_url'],
                'notas' => $notas,
                'es_favorito' => isset($_POST['es_favorito']) ? 1 : 0,
                'ver_mas_tarde' => isset($_POST['ver_mas_tarde']) ? 1 : 0
            ];

            if ($this->linkModel->crear($data)) {
                $this->jsonResponse(true, $data, "Enlace guardado correctamente.");
            } else {
                $this->jsonResponse(false, null, "Error al guardar en la base de datos.");
            }
        }
    }

    /**
     * Listar enlaces para el Dashboard
     */
    public function index() {
        $filtros = [];
        if (isset($_GET['categoria'])) $filtros['categoria'] = $_GET['categoria'];
        if (isset($_GET['favoritos'])) $filtros['favorito'] = true;

        $enlaces = $this->linkModel->listar($filtros);
        $this->jsonResponse(true, $enlaces);
    }

    /**
     * Eliminar un enlace
     */
    public function delete($id) {
        if (!$id) {
            $this->jsonResponse(false, null, "ID no válido.");
            return;
        }

        if ($this->linkModel->eliminar($id)) {
            $this->jsonResponse(true, null, "Enlace eliminado.");
        } else {
            $this->jsonResponse(false, null, "Error al eliminar.");
        }
    }

    /**
     * Obtener un enlace específico
     */
    public function show($id) {
        $enlace = $this->linkModel->obtenerPorId($id);
        if ($enlace) {
            $this->jsonResponse(true, $enlace);
        } else {
            $this->jsonResponse(false, null, "Enlace no encontrado.");
        }
    }

    /**
     * Actualizar enlace
     */
    public function update($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'id_categoria' => $_POST['id_categoria'] !== '' ? filter_input(INPUT_POST, 'id_categoria', FILTER_VALIDATE_INT) : null,
                'notas' => filter_input(INPUT_POST, 'notas', FILTER_SANITIZE_SPECIAL_CHARS),
                'es_favorito' => isset($_POST['es_favorito']) ? 1 : 0,
                'ver_mas_tarde' => isset($_POST['ver_mas_tarde']) ? 1 : 0
            ];

            if ($this->linkModel->actualizar($id, $data)) {
                $this->jsonResponse(true, null, "Enlace actualizado.");
            } else {
                $this->jsonResponse(false, null, "Error al actualizar.");
            }
        }
    }

    /**
     * Respuesta JSON estándar FORJIATO
     */
    private function jsonResponse($success, $data = null, $error = null) {
        // Limpiar cualquier salida accidental (avisos de PHP, etc)
        if (ob_get_length()) ob_clean();
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => $success,
            'data' => $data,
            'error' => $error
        ]);
        exit();
    }
}
