<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/controllers/AdminController.php';

// Protección Admin
if (!getTenantId() || $_SESSION['user_rol'] !== 'admin') {
    header("Location: panel.php");
    exit();
}

$admin = new AdminController($pdo);
$section = $_GET['section'] ?? 'dashboard';
$userName = $_SESSION['user_name'];

// Obtener datos según sección
$metrics = ($section === 'dashboard') ? $admin->getMetrics()['data'] : null;
$usersList = ($section === 'users') ? $admin->getUsers()['data'] : null;
$moderationLogs = ($section === 'moderation') ? $admin->getBlockedLogs()['data'] : null;
$globalConfig = ($section === 'config') ? $admin->getConfig()['data'] : null;
$upgradeRequests = ($section === 'requests') ? $admin->getUpgradeRequests()['data'] : null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Montia</title>
    <link rel="stylesheet" href="index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .metric-card { padding: 1.5rem; display: flex; align-items: center; gap: 1.5rem; }
        .metric-icon { width: 60px; height: 60px; border-radius: 15px; display: flex; justify-content: center; align-items: center; font-size: 1.5rem; background: rgba(99, 102, 241, 0.1); color: var(--primary); }
        .metric-info h4 { font-size: 0.85rem; color: var(--text-muted); margin-bottom: 0.2rem; }
        .metric-info p { font-size: 1.8rem; font-weight: 800; color: white; }
        
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 1rem; color: var(--text-muted); font-size: 0.8rem; border-bottom: 1px solid var(--glass-border); }
        td { padding: 1rem; border-bottom: 1px solid rgba(255,255,255,0.05); font-size: 0.9rem; }
        
        .status-badge { padding: 0.3rem 0.8rem; border-radius: 20px; font-size: 0.7rem; font-weight: 600; text-transform: uppercase; }
        .status-premium { background: rgba(46, 213, 115, 0.1); color: #2ed573; }
        .status-gratis { background: rgba(255, 255, 255, 0.05); color: var(--text-muted); }
        
        .admin-select, .admin-input { background: #1a1a2e; color: white; border: 1px solid var(--glass-border); padding: 0.6rem; border-radius: 8px; font-size: 0.9rem; width: 100%; }
        .config-row { display: flex; justify-content: space-between; align-items: center; padding: 1.5rem; border-bottom: 1px solid rgba(255,255,255,0.05); }
        
        .nav-link.has-badge { position: relative; }
        .nav-badge { position: absolute; top: 10px; right: 10px; background: #ff4757; color: white; width: 18px; height: 18px; border-radius: 50%; display: flex; justify-content: center; align-items: center; font-size: 0.65rem; font-weight: 800; }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-brand">
                <i class="fa-solid fa-shield-halved"></i> AdminPanel
            </div>
            <nav class="sidebar-nav">
                <a href="admin.php?section=dashboard" class="nav-link <?php echo $section === 'dashboard' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-chart-line"></i> Dashboard
                </a>
                <a href="admin.php?section=users" class="nav-link <?php echo $section === 'users' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-users"></i> Usuarios
                </a>
                <a href="admin.php?section=requests" class="nav-link has-badge <?php echo $section === 'requests' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-bell"></i> Solicitudes
                    <?php 
                        $pendientesCount = $pdo->query("SELECT COUNT(*) FROM solicitudes_plan WHERE estado = 'pendiente'")->fetchColumn();
                        if ($pendientesCount > 0) echo '<span class="nav-badge">'.$pendientesCount.'</span>';
                    ?>
                </a>
                <a href="admin.php?section=moderation" class="nav-link <?php echo $section === 'moderation' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-eye"></i> Moderación
                </a>
                <a href="admin.php?section=config" class="nav-link <?php echo $section === 'config' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-gear"></i> Configuración
                </a>
            </nav>
            <div style="padding: 1rem; border-top: 1px solid var(--glass-border);">
                <a href="panel.php" class="nav-link">
                    <i class="fa-solid fa-arrow-left"></i> Volver a la App
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-wrapper">
            <header class="dashboard-header">
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <i class="fa-solid fa-bars" id="mobileToggle" style="display: none; cursor: pointer; font-size: 1.2rem;"></i>
                    <h2 style="font-size: 1.1rem;">
                        <?php 
                        if ($section === 'dashboard') echo 'Resumen General';
                        elseif ($section === 'users') echo 'Gestión de Usuarios';
                        elseif ($section === 'moderation') echo 'Centro de Moderación';
                        elseif ($section === 'config') echo 'Configuración Global';
                        elseif ($section === 'requests') echo 'Solicitudes de Membresía';
                        ?>
                    </h2>
                </div>
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <span style="font-size: 0.85rem; color: var(--text-muted);"><?php echo $userName; ?> (Admin)</span>
                    <div class="glass" style="width: 35px; height: 35px; border-radius: 50%; display: flex; justify-content: center; align-items: center;">
                        <i class="fa-solid fa-user-tie"></i>
                    </div>
                </div>
            </header>

            <div class="content-body">
                <?php if ($section === 'dashboard'): ?>
                    <!-- DASHBOARD VIEW -->
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.5rem;">
                        <div class="metric-card glass">
                            <div class="metric-icon"><i class="fa-solid fa-users"></i></div>
                            <div class="metric-info">
                                <h4>Usuarios Totales</h4>
                                <p><?php echo $metrics['total_usuarios']; ?></p>
                            </div>
                        </div>
                        <div class="metric-card glass">
                            <div class="metric-icon" style="color: #6366f1;"><i class="fa-solid fa-link"></i></div>
                            <div class="metric-info">
                                <h4>Enlaces Totales</h4>
                                <p><?php echo $metrics['total_enlaces']; ?></p>
                            </div>
                        </div>
                        <div class="metric-card glass">
                            <div class="metric-icon" style="color: #2ed573;"><i class="fa-solid fa-star"></i></div>
                            <div class="metric-info">
                                <h4>Premium Activos</h4>
                                <p><?php echo $metrics['premium_users']; ?></p>
                            </div>
                        </div>
                        <div class="metric-card glass">
                            <div class="metric-icon" style="color: #f1c40f;"><i class="fa-solid fa-sack-dollar"></i></div>
                            <div class="metric-info">
                                <h4>Ingresos Est.</h4>
                                <p>$<?php echo number_format($metrics['revenue_est'], 2); ?> <span style="font-size: 0.7rem;">MXN</span></p>
                            </div>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 1.5rem; margin-top: 2rem;">
                        <div class="glass" style="padding: 1.5rem;">
                            <h3 style="margin-bottom: 1.5rem; font-size: 1.1rem;"><i class="fa-solid fa-user-plus"></i> Usuarios Recientes</h3>
                            <table>
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Suscripción</th>
                                        <th>Fecha</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($metrics['usuarios_recientes'] as $u): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($u['nombre']); ?></strong><br>
                                            <span style="font-size: 0.7rem; color: var(--text-muted);"><?php echo htmlspecialchars($u['email']); ?></span>
                                        </td>
                                        <td>
                                            <span class="status-badge <?php echo $u['suscripcion'] === 'premium' ? 'status-premium' : 'status-gratis'; ?>">
                                                <?php echo $u['suscripcion']; ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('d M', strtotime($u['created_at'])); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="glass" style="padding: 1.5rem;">
                            <h3 style="margin-bottom: 1.5rem; font-size: 1.1rem;"><i class="fa-solid fa-bell"></i> Solicitudes Pendientes</h3>
                            <?php 
                                $stmt = $pdo->query("SELECT s.*, u.nombre FROM solicitudes_plan s JOIN usuarios u ON s.id_usuario = u.id WHERE s.estado = 'pendiente' LIMIT 5");
                                $solicitudes = $stmt->fetchAll();
                            ?>
                            <table>
                                <thead>
                                    <tr>
                                        <th>Usuario</th>
                                        <th>Plan</th>
                                        <th style="text-align: right;">Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($solicitudes)): ?>
                                        <tr><td colspan="3" style="text-align: center; color: var(--text-muted); padding: 2rem;">Todo al día.</td></tr>
                                    <?php else: ?>
                                        <?php foreach ($solicitudes as $s): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($s['nombre']); ?></td>
                                            <td><strong style="color: var(--primary);"><?php echo strtoupper($s['plan_solicitado']); ?></strong></td>
                                            <td style="text-align: right;">
                                                <a href="admin.php?section=requests" class="btn glass" style="padding: 0.3rem 0.6rem; font-size: 0.75rem;">Ver</a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                <?php elseif ($section === 'users'): ?>
                    <!-- USERS MANAGEMENT VIEW -->
                    <div class="glass" style="padding: 1.5rem;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                            <h3 style="font-size: 1.1rem;"><i class="fa-solid fa-list-check"></i> Directorio de Usuarios</h3>
                            <input type="text" id="userSearch" placeholder="Filtrar por nombre o email..." class="glass" style="padding: 0.5rem 1rem; font-size: 0.85rem; width: 300px;">
                        </div>
                        <table id="usersTable">
                            <thead>
                                <tr>
                                    <th>Usuario</th>
                                    <th>Email</th>
                                    <th>Plan Actual</th>
                                    <th>Registro</th>
                                    <th style="text-align: right;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($usersList as $u): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($u['nombre']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($u['email']); ?></td>
                                    <td>
                                        <select class="admin-select" onchange="updatePlan(<?php echo $u['id']; ?>, this.value)">
                                            <option value="gratis" <?php echo $u['suscripcion'] === 'gratis' ? 'selected' : ''; ?>>Gratis</option>
                                            <option value="pro" <?php echo $u['suscripcion'] === 'pro' ? 'selected' : ''; ?>>Pro</option>
                                            <option value="premium" <?php echo $u['suscripcion'] === 'premium' ? 'selected' : ''; ?>>Premium</option>
                                        </select>
                                    </td>
                                    <td style="color: var(--text-muted);"><?php echo date('d/m/Y', strtotime($u['created_at'])); ?></td>
                                    <td style="text-align: right;">
                                        <button class="btn glass" style="padding: 0.4rem; color: #ff4757;" onclick="alert('Función de suspensión en desarrollo')"><i class="fa-solid fa-user-slash"></i></button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                <?php elseif ($section === 'requests'): ?>
                    <!-- UPGRADE REQUESTS VIEW -->
                    <div class="glass" style="padding: 1.5rem;">
                        <h3 style="margin-bottom: 1.5rem; font-size: 1.1rem;"><i class="fa-solid fa-envelope-open-text"></i> Solicitudes de Mejora Pendientes</h3>
                        <table>
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Usuario</th>
                                    <th>Plan Solicitado</th>
                                    <th style="text-align: right;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($upgradeRequests)): ?>
                                    <tr><td colspan="4" style="text-align: center; padding: 3rem; color: var(--text-muted);">No hay solicitudes pendientes.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($upgradeRequests as $r): ?>
                                    <tr id="request-<?php echo $r['id']; ?>">
                                        <td><?php echo date('d/m H:i', strtotime($r['created_at'])); ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($r['usuario_nombre']); ?></strong><br>
                                            <span style="font-size: 0.7rem; color: var(--text-muted);"><?php echo htmlspecialchars($r['usuario_email']); ?></span>
                                        </td>
                                        <td>
                                            <span class="status-badge <?php echo $r['plan_solicitado'] === 'premium' ? 'status-premium' : 'status-gratis'; ?>" style="background: rgba(99, 102, 241, 0.1); color: var(--primary);">
                                                <?php echo strtoupper($r['plan_solicitado']); ?>
                                            </span>
                                        </td>
                                        <td style="text-align: right;">
                                            <button class="btn btn-primary" style="padding: 0.4rem 0.8rem; font-size: 0.75rem;" onclick="processRequest(<?php echo $r['id']; ?>, 'aprobar')">Aprobar</button>
                                            <button class="btn glass" style="padding: 0.4rem 0.8rem; font-size: 0.75rem; color: #ff4757;" onclick="processRequest(<?php echo $r['id']; ?>, 'cancelar')">Ignorar</button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                <?php elseif ($section === 'moderation'): ?>
                    <!-- MODERATION VIEW -->
                    <div class="glass" style="padding: 1.5rem;">
                        <h3 style="margin-bottom: 1.5rem; font-size: 1.1rem;"><i class="fa-solid fa-shield-virus"></i> Intentos de Contenido Bloqueado</h3>
                        <table>
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Usuario</th>
                                    <th>Detalles</th>
                                    <th>IP</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($moderationLogs)): ?>
                                    <tr><td colspan="4" style="text-align: center; padding: 2rem;">Sin registros.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($moderationLogs as $log): ?>
                                    <tr>
                                        <td><?php echo date('d/m H:i', strtotime($log['created_at'])); ?></td>
                                        <td><strong><?php echo htmlspecialchars($log['usuario_nombre']); ?></strong></td>
                                        <td style="font-size: 0.8rem;"><?php echo htmlspecialchars($log['detalles']); ?></td>
                                        <td><?php echo $log['ip_address']; ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                <?php elseif ($section === 'config'): ?>
                    <!-- CONFIGURATION VIEW -->
                    <div class="glass" style="padding: 0;">
                        <div style="padding: 1.5rem; border-bottom: 1px solid var(--glass-border); display: flex; justify-content: space-between; align-items: center;">
                            <h3 style="font-size: 1.1rem;"><i class="fa-solid fa-sliders"></i> Ajustes del Sistema</h3>
                            <button form="configForm" class="btn btn-primary">Guardar Cambios</button>
                        </div>
                        <form id="configForm" style="padding: 0;">
                            <?php foreach ($globalConfig as $c): ?>
                                <div class="config-row">
                                    <div style="flex: 1;">
                                        <label style="display: block; font-weight: 600; font-size: 0.9rem;"><?php echo $c['c_label']; ?></label>
                                        <span style="font-size: 0.75rem; color: var(--text-muted);"><?php echo $c['c_key']; ?></span>
                                    </div>
                                    <div style="flex: 1;">
                                        <?php if ($c['c_key'] === 'maintenance_mode' || $c['c_key'] === 'allow_registrations'): ?>
                                            <select name="<?php echo $c['c_key']; ?>" class="admin-select">
                                                <option value="1" <?php echo $c['c_value'] == '1' ? 'selected' : ''; ?>>Activado</option>
                                                <option value="0" <?php echo $c['c_value'] == '0' ? 'selected' : ''; ?>>Desactivado</option>
                                            </select>
                                        <?php else: ?>
                                            <input type="text" name="<?php echo $c['c_key']; ?>" value="<?php echo htmlspecialchars($c['c_value']); ?>" class="admin-input">
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </form>
                    </div>
                <?php endif; ?>
            </div>

            <footer class="dashboard-footer">
                Montia Admin &copy; <?php echo date('Y'); ?> | <span style="color: var(--primary);">Versión 1.2.0</span>
            </footer>
        </main>
    </div>

    <script>
        const toggle = document.getElementById('mobileToggle');
        const sidebar = document.getElementById('sidebar');
        if (window.innerWidth <= 992) toggle.style.display = 'block';
        toggle.onclick = () => sidebar.classList.toggle('open');

        async function updatePlan(userId, newPlan) {
            const fd = new FormData();
            fd.append('user_id', userId);
            fd.append('plan', newPlan);
            await fetch('api.php?action=update_user_plan', { method: 'POST', body: fd });
        }

        async function processRequest(requestId, action) {
            if (!confirm(`¿Estás seguro de ${action} esta solicitud?`)) return;
            
            const fd = new FormData();
            fd.append('request_id', requestId);
            fd.append('process_action', action);
            
            try {
                const response = await fetch('api.php?action=handle_upgrade', { method: 'POST', body: fd });
                const result = await response.json();
                if (result.success) {
                    document.getElementById(`request-${requestId}`).remove();
                    location.reload(); // Recargar para actualizar contadores
                } else alert("Error: " + result.error);
            } catch (e) { alert("Error de conexión."); }
        }

        const configForm = document.getElementById('configForm');
        if (configForm) {
            configForm.onsubmit = async (e) => {
                e.preventDefault();
                const fd = new FormData(configForm);
                fd.append('action', 'update_config');
                const response = await fetch('api.php?action=update_config', { method: 'POST', body: fd });
                const result = await response.json();
                if (result.success) alert("Configuración actualizada.");
            };
        }

        const userSearch = document.getElementById('userSearch');
        if (userSearch) {
            userSearch.oninput = () => {
                const term = userSearch.value.toLowerCase();
                document.querySelectorAll('#usersTable tbody tr').forEach(row => {
                    row.style.display = row.innerText.toLowerCase().includes(term) ? '' : 'none';
                });
            };
        }
    </script>
</body>
</html>
