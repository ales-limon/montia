<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/controllers/AdminController.php';

// Protección Admin
if (!getTenantId() || $_SESSION['user_rol'] !== 'admin') {
    header("Location: dashboard.php");
    exit();
}

$admin = new AdminController($pdo);
$metrics = $admin->getMetrics()['data'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Admin | LinkViewer</title>
    <link rel="stylesheet" href="index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .metric-card {
            padding: 2rem;
            text-align: center;
            margin-bottom: 1.5rem;
        }
        .metric-value {
            font-size: 3rem;
            font-weight: 800;
            color: var(--primary);
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Panel de Administración</h1>
        <p style="color: var(--text-muted); margin-bottom: 2rem;">Métricas de uso del sistema.</p>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
            <div class="metric-card glass">
                <div class="metric-value"><?php echo $metrics['total_usuarios']; ?></div>
                <div style="font-size: 0.9rem; color: var(--text-muted);">Usuarios Totales</div>
            </div>
            <div class="metric-card glass">
                <div class="metric-value"><?php echo $metrics['total_enlaces']; ?></div>
                <div style="font-size: 0.9rem; color: var(--text-muted);">Enlaces Guardados</div>
            </div>
            <div class="metric-card glass">
                <div class="metric-value" style="color: #2ed573;"><?php echo $metrics['premium_users']; ?></div>
                <div style="font-size: 0.9rem; color: var(--text-muted);">Cuentas Premium</div>
            </div>
            <div class="metric-card glass">
                <div class="metric-value" style="color: #f1c40f;">$<?php echo $metrics['revenue_est']; ?></div>
                <div style="font-size: 0.9rem; color: var(--text-muted);">Ingresos Est. (USD)</div>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem; margin-top: 2rem;">
            <!-- Usuarios Recientes -->
            <div>
                <h3 style="margin-bottom: 1rem;"><i class="fa-solid fa-clock-rotate-left"></i> Registros Recientes</h3>
                <div class="glass" style="padding: 1rem; overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 0.85rem;">
                        <thead>
                            <tr style="text-align: left; border-bottom: 1px solid var(--glass-border);">
                                <th style="padding: 0.8rem;">Usuario</th>
                                <th style="padding: 0.8rem;">Plan</th>
                                <th style="padding: 0.8rem;">Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($metrics['usuarios_recientes'] as $u): ?>
                            <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                                <td style="padding: 0.8rem;">
                                    <strong><?php echo htmlspecialchars($u['nombre']); ?></strong><br>
                                    <span style="font-size: 0.75rem; color: var(--text-muted);"><?php echo htmlspecialchars($u['email']); ?></span>
                                </td>
                                <td style="padding: 0.8rem;">
                                    <span class="badge" style="position:static; font-size: 0.6rem; background: <?php echo $u['suscripcion'] === 'premium' ? '#2ed573' : 'rgba(255,255,255,0.1)'; ?>;">
                                        <?php echo strtoupper($u['suscripcion']); ?>
                                    </span>
                                </td>
                                <td style="padding: 0.8rem;"><?php echo date('d/m/Y', strtotime($u['created_at'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Top Usuarios -->
            <div>
                <h3 style="margin-bottom: 1rem;"><i class="fa-solid fa-trophy" style="color: #f1c40f;"></i> Top Usuarios (Links)</h3>
                <div class="glass" style="padding: 1rem; overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 0.85rem;">
                        <thead>
                            <tr style="text-align: left; border-bottom: 1px solid var(--glass-border);">
                                <th style="padding: 0.8rem;">Usuario</th>
                                <th style="padding: 0.8rem; text-align: center;">Links</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($metrics['top_users'] as $u): ?>
                            <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                                <td style="padding: 0.8rem;">
                                    <strong><?php echo htmlspecialchars($u['nombre']); ?></strong><br>
                                    <span style="font-size: 0.75rem; color: var(--text-muted);"><?php echo htmlspecialchars($u['email']); ?></span>
                                </td>
                                <td style="padding: 0.8rem; text-align: center;">
                                    <span style="font-weight: 800; color: var(--accent);"><?php echo $u['total_links']; ?></span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <a href="panel.php" class="btn glass" style="margin-top: 3rem; width: 100%; justify-content: center;">
            <i class="fa-solid fa-arrow-left"></i> Volver al Panel Principal
        </a>
    </div>
</body>
</html>
