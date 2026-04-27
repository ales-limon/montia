<?php
/**
 * API Central - LinkViewer
 * Punto de entrada para todas las peticiones AJAX
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';
require_once __DIR__ . '/../app/controllers/LinkController.php';
require_once __DIR__ . '/../app/controllers/ContactoController.php';

try {
    $action = $_GET['action'] ?? '';
    $id_tenant = getTenantId();

    // Rutas Públicas
    if ($action === 'login') {
        $auth = new AuthController($pdo);
        $auth->login();
    }

    if ($action === 'register') {
        $auth = new AuthController($pdo);
        $auth->register();
    }

    if ($action === 'logout') {
        $auth = new AuthController($pdo);
        $auth->logout();
    }

    // Rutas Protegidas (Requieren sesión)
    if (!$id_tenant) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Sesión no válida o expirada.']);
        exit();
    }

    $linkController = new LinkController($pdo, $id_tenant);

    switch ($action) {
        case 'list_links':
            $linkController->index();
            break;

        case 'list_categories':
            $linkController->listCategories();
            break;
        
        case 'save_category':
            if (!validateCsrf($_POST['csrf_token'] ?? '')) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'Error de seguridad CSRF.']);
                exit();
            }
            $linkController->storeCategory();
            break;

        case 'delete_category':
            if (!validateCsrf($_POST['csrf_token'] ?? '')) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'Error de seguridad CSRF.']);
                exit();
            }
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            $linkController->deleteCategory($id);
            break;

        case 'update_password':
            if (!validateCsrf($_POST['csrf_token'] ?? '')) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'Error de seguridad CSRF.']);
                exit();
            }
            $auth = new AuthController($pdo);
            $auth->changePassword();
            break;

        case 'save_link':
            if (!validateCsrf($_POST['csrf_token'] ?? '')) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'Error de seguridad CSRF.']);
                exit();
            }
            $linkController->store();
            break;

        case 'delete_link':
            if (!validateCsrf($_POST['csrf_token'] ?? '')) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'Error de seguridad CSRF.']);
                exit();
            }
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            $linkController->delete($id);
            break;

        case 'get_link':
            $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
            $linkController->show($id);
            break;

        case 'update_link':
            if (!validateCsrf($_POST['csrf_token'] ?? '')) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'Error de seguridad CSRF.']);
                exit();
            }
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            $linkController->update($id);
            break;

        // Red Interna (Contactos y Compartir)
        case 'search_user':
            $contactoCtrl = new ContactoController($pdo, $id_tenant);
            $contactoCtrl->buscar();
            break;

        case 'send_contact_request':
            if (!validateCsrf($_POST['csrf_token'] ?? '')) {
                echo json_encode(['success' => false, 'error' => 'Error CSRF.']); exit;
            }
            $contactoCtrl = new ContactoController($pdo, $id_tenant);
            $contactoCtrl->enviarSolicitud();
            break;

        case 'list_contact_requests':
            $contactoCtrl = new ContactoController($pdo, $id_tenant);
            $contactoCtrl->listarSolicitudes();
            break;

        case 'list_sent_requests':
            $contactoCtrl = new ContactoController($pdo, $id_tenant);
            $contactoCtrl->listarEnviadas();
            break;

        case 'respond_contact_request':
            if (!validateCsrf($_POST['csrf_token'] ?? '')) {
                echo json_encode(['success' => false, 'error' => 'Error CSRF.']); exit;
            }
            $contactoCtrl = new ContactoController($pdo, $id_tenant);
            $contactoCtrl->responderSolicitud();
            break;

        case 'list_contacts':
            $contactoCtrl = new ContactoController($pdo, $id_tenant);
            $contactoCtrl->listarContactos();
            break;

        case 'share_link_internal':
            if (!validateCsrf($_POST['csrf_token'] ?? '')) {
                echo json_encode(['success' => false, 'error' => 'Error CSRF.']); exit;
            }
            $contactoCtrl = new ContactoController($pdo, $id_tenant);
            $contactoCtrl->compartir();
            break;

        case 'list_received_links':
            $contactoCtrl = new ContactoController($pdo, $id_tenant);
            $contactoCtrl->listarRecibidos();
            break;

        case 'mark_link_seen':
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            $contactoCtrl = new ContactoController($pdo, $id_tenant);
            $contactoCtrl->marcarVisto($id);
            break;

        case 'delete_shared_link':
            $contactoCtrl = new ContactoController($pdo, $id_tenant);
            $contactoCtrl->eliminarCompartido();
            break;

        case 'count_unread_shared':
            $contactoCtrl = new ContactoController($pdo, $id_tenant);
            $contactoCtrl->contarNoVistos();
            break;

        case 'log_external_share':
            $idEnlace = filter_input(INPUT_POST, 'id_enlace', FILTER_VALIDATE_INT);
            $plataforma = filter_input(INPUT_POST, 'plataforma', FILTER_SANITIZE_SPECIAL_CHARS);
            $destinatario = filter_input(INPUT_POST, 'destinatario', FILTER_SANITIZE_SPECIAL_CHARS);
            
            if ($idEnlace) {
                $stmt = $pdo->prepare("INSERT INTO compartidos_externos (id_tenant, id_enlace, plataforma, destinatario) VALUES (?, ?, ?, ?)");
                $success = $stmt->execute([$id_tenant, $idEnlace, $plataforma, $destinatario]);
                echo json_encode(['success' => $success]);
            } else {
                echo json_encode(['success' => false]);
            }
            exit;

        case 'update_user_plan':
            require_once __DIR__ . '/../app/controllers/AdminController.php';
            $adminCtrl = new AdminController($pdo);
            $userId = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
            $newPlan = filter_input(INPUT_POST, 'plan', FILTER_SANITIZE_SPECIAL_CHARS);
            $result = $adminCtrl->updateUserPlan($userId, $newPlan);
            header('Content-Type: application/json');
            echo json_encode($result);
            exit;

        case 'update_config':
            require_once __DIR__ . '/../app/controllers/AdminController.php';
            $adminCtrl = new AdminController($pdo);
            // Tomamos todos los campos del POST excepto action
            $settings = $_POST;
            unset($settings['action']);
            $result = $adminCtrl->updateConfig($settings);
            header('Content-Type: application/json');
            echo json_encode($result);
            exit;

        case 'solicitar_mejora':
            require_once __DIR__ . '/../app/models/UsuarioModel.php';
            $plan = filter_input(INPUT_POST, 'plan', FILTER_SANITIZE_SPECIAL_CHARS);
            $userId = getTenantId();
            if (!$userId || !$plan) {
                echo json_encode(['success' => false, 'error' => 'Datos insuficientes.']); exit;
            }
            $userModel = new UsuarioModel($pdo);
            $success = $userModel->solicitarMejoraPlan($userId, $plan);
            header('Content-Type: application/json');
            echo json_encode(['success' => $success]);
            exit;

        case 'handle_upgrade':
            require_once __DIR__ . '/../app/controllers/AdminController.php';
            $adminCtrl = new AdminController($pdo);
            $requestId = filter_input(INPUT_POST, 'request_id', FILTER_VALIDATE_INT);
            $action = filter_input(INPUT_POST, 'process_action', FILTER_SANITIZE_SPECIAL_CHARS);
            $result = $adminCtrl->handleUpgradeRequest($requestId, $action);
            header('Content-Type: application/json');
            echo json_encode($result);
            exit;

        case 'delete_system_file':
            require_once __DIR__ . '/../app/controllers/AdminController.php';
            $adminCtrl = new AdminController($pdo);
            $filename = filter_input(INPUT_POST, 'filename', FILTER_SANITIZE_SPECIAL_CHARS);
            $result = $adminCtrl->deleteSystemFile($filename);
            header('Content-Type: application/json');
            echo json_encode($result);
            exit;

        default:
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Acción no reconocida.']);
            break;
    }
} catch (Throwable $e) {
    // Diagnóstico de emergencia
    $msg = "ERROR FATAL: " . $e->getMessage() . " en " . $e->getFile() . " línea " . $e->getLine();
    file_put_contents(__DIR__ . '/../error_debug.txt', $msg);
    
    if (ob_get_length()) ob_clean();
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => $msg]);
    exit();
}
