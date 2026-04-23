<?php
/**
 * Controlador de Contactos - LinkViewer
 * Gestiona la red interna y compartición de enlaces
 */

require_once __DIR__ . '/../models/ContactoModel.php';

class ContactoController {
    private $contactoModel;
    private $userId;

    public function __construct($pdo, $userId) {
        $this->contactoModel = new ContactoModel($pdo, $userId);
        $this->userId = $userId;
    }

    /**
     * Buscar un usuario por email
     */
    public function buscar() {
        $email = filter_input(INPUT_GET, 'email', FILTER_VALIDATE_EMAIL);
        if (!$email) $this->jsonResponse(false, null, "Email no válido.");

        $usuario = $this->contactoModel->buscarUsuario($email);
        if ($usuario) {
            $this->jsonResponse(true, $usuario);
        } else {
            $this->jsonResponse(false, null, "Usuario no encontrado.");
        }
    }

    /**
     * Enviar solicitud de contacto
     */
    public function enviarSolicitud() {
        try {
            $idReceptor = filter_input(INPUT_POST, 'id_receptor', FILTER_VALIDATE_INT);
            if (!$idReceptor) $this->jsonResponse(false, null, "ID de usuario no válido.");

            if ($this->contactoModel->enviarSolicitud($idReceptor)) {
                $this->jsonResponse(true, null, "Solicitud enviada.");
            } else {
                $this->jsonResponse(false, null, "No se pudo enviar la solicitud.");
            }
        } catch (Exception $e) {
            $this->jsonResponse(false, null, $e->getMessage());
        }
    }

    /**
     * Listar solicitudes enviadas
     */
    public function listarEnviadas() {
        $enviadas = $this->contactoModel->listarSolicitudesEnviadas();
        $this->jsonResponse(true, $enviadas);
    }

    /**
     * Listar solicitudes pendientes
     */
    public function listarSolicitudes() {
        $solicitudes = $this->contactoModel->listarSolicitudesPendientes();
        $this->jsonResponse(true, $solicitudes);
    }

    /**
     * Aceptar o rechazar solicitud
     */
    public function responderSolicitud() {
        $idSolicitud = filter_input(INPUT_POST, 'id_solicitud', FILTER_VALIDATE_INT);
        $estado = $_POST['estado'] ?? '';

        if ($this->contactoModel->responderSolicitud($idSolicitud, $estado)) {
            $this->jsonResponse(true, null, "Solicitud $estado.");
        } else {
            $this->jsonResponse(false, null, "Error al procesar la solicitud.");
        }
    }

    /**
     * Listar mis contactos
     */
    public function listarContactos() {
        $contactos = $this->contactoModel->listarContactos();
        $this->jsonResponse(true, $contactos);
    }

    /**
     * Compartir enlace con un contacto
     */
    public function compartir() {
        $idEnlace = filter_input(INPUT_POST, 'id_enlace', FILTER_VALIDATE_INT);
        $idReceptor = filter_input(INPUT_POST, 'id_receptor', FILTER_VALIDATE_INT);

        if (!$idEnlace || !$idReceptor) $this->jsonResponse(false, null, "Datos incompletos.");

        if ($this->contactoModel->compartirEnlace($idEnlace, $idReceptor)) {
            $this->jsonResponse(true, null, "Enlace compartido correctamente.");
        } else {
            $this->jsonResponse(false, null, "Error al compartir. Verifica que el usuario sea tu contacto.");
        }
    }

    /**
     * Listar enlaces compartidos conmigo
     */
    public function listarRecibidos() {
        $enlaces = $this->contactoModel->listarCompartidosRecibidos();
        $this->jsonResponse(true, $enlaces);
    }

    /**
     * Marcar como visto
     */
    public function marcarVisto($id) {
        if ($this->contactoModel->marcarComoVisto($id)) {
            $this->jsonResponse(true);
        } else {
            $this->jsonResponse(false);
        }
    }

    /**
     * Eliminar compartido
     */
    public function eliminarCompartido() {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        if ($this->contactoModel->eliminarCompartido($id)) {
            $this->jsonResponse(true);
        } else {
            $this->jsonResponse(false);
        }
    }

    /**
     * Contar no vistos
     */
    public function contarNoVistos() {
        $total = $this->contactoModel->contarNoVistos();
        $this->jsonResponse(true, ['total' => $total]);
    }

    private function jsonResponse($success, $data = null, $error = null) {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');
        echo json_encode(['success' => $success, 'data' => $data, 'error' => $error]);
        exit();
    }
}
