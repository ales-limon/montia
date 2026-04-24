<?php
require_once __DIR__ . '/../config/config.php';

if (!getTenantId()) {
    header("Location: login.php");
    exit();
}

$currentPlan = $_SESSION['user_suscripcion'] ?? 'gratis';

// Obtener límites de la configuración para mostrar datos reales
$stmt = $pdo->query("SELECT c_key, c_value FROM configuracion WHERE c_key IN ('limit_free', 'limit_pro', 'limit_premium', 'price_premium_mxn', 'price_pro_mxn', 'price_extra_block_mxn', 'extra_block_size')");
$config = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

$limitFree = $config['limit_free'] ?? 50;
$limitPro = $config['limit_pro'] ?? 750;
$limitPremium = $config['limit_premium'] ?? 1500;
$pricePremium = $config['price_premium_mxn'] ?? 150;
$pricePro = $config['price_pro_mxn'] ?? 75;
$priceExtra = $config['price_extra_block_mxn'] ?? 50;
$blockSize = $config['extra_block_size'] ?? 150;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Planes | GuardaLink</title>
    <link rel="stylesheet" href="index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .pricing-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }
        .plan-card {
            padding: 2.5rem 2rem;
            text-align: center;
            display: flex;
            flex-direction: column;
            position: relative;
            transition: transform 0.3s ease;
        }
        .plan-card:hover { transform: translateY(-10px); }
        .plan-card.active { border: 2px solid var(--primary); }
        .plan-badge {
            position: absolute;
            top: -12px;
            left: 50%;
            transform: translateX(-50%);
            background: var(--primary-gradient);
            padding: 0.4rem 1.2rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
        }
        .plan-price { font-size: 2.5rem; font-weight: 800; margin: 1.5rem 0; }
        .plan-price span { font-size: 1rem; color: var(--text-muted); }
        .plan-features { list-style: none; padding: 0; margin: 2rem 0; text-align: left; flex-grow: 1; }
        .plan-features li { margin-bottom: 1rem; font-size: 0.9rem; display: flex; align-items: center; gap: 0.8rem; }
        .plan-features i { color: #2ed573; }
    </style>
</head>
<body>
    <div class="container">
        <header style="text-align: center; margin-bottom: 3rem;">
            <a href="panel.php" style="float: left; color: white; font-size: 1.5rem;"><i class="fa-solid fa-arrow-left"></i></a>
            <h1 style="display: inline-block;">Elige tu Plan</h1>
            <p style="color: var(--text-muted); margin-top: 1rem;">Lleva tu organización de enlaces al siguiente nivel.</p>
        </header>

        <div class="pricing-container">
            <!-- Plan Gratis -->
            <div class="plan-card glass <?php echo $currentPlan === 'gratis' ? 'active' : ''; ?>">
                <?php if ($currentPlan === 'gratis'): ?><div class="plan-badge">PLAN ACTUAL</div><?php endif; ?>
                <h2 style="color: var(--text-muted);">Gratis</h2>
                <div class="plan-price">$0 <span>/ siempre</span></div>
                <ul class="plan-features">
                    <li><i class="fa-solid fa-check"></i> Hasta <?php echo $limitFree; ?> enlaces</li>
                    <li><i class="fa-solid fa-check"></i> Categorías personalizadas</li>
                    <li><i class="fa-solid fa-check"></i> Previsualización inteligente</li>
                    <li style="color: rgba(255,255,255,0.2);"><i class="fa-solid fa-xmark" style="color: #ff4757;"></i> Red interna de contactos</li>
                </ul>
                <button class="btn glass" disabled>Plan Activo</button>
            </div>

            <!-- Plan Pro -->
            <div class="plan-card glass <?php echo $currentPlan === 'pro' ? 'active' : ''; ?>" style="border-color: rgba(99, 102, 241, 0.3);">
                <?php if ($currentPlan === 'pro'): ?><div class="plan-badge">PLAN ACTUAL</div><?php endif; ?>
                <h2 style="color: var(--primary);">Pro</h2>
                <div class="plan-price">$<?php echo $pricePro; ?> <span>/ mes MXN</span></div>
                <ul class="plan-features">
                    <li><i class="fa-solid fa-check"></i> Hasta <?php echo $limitPro; ?> enlaces</li>
                    <li><i class="fa-solid fa-check"></i> Red interna completa</li>
                    <li><i class="fa-solid fa-check"></i> Compartir con amigos</li>
                    <li style="color: #6366f1;"><i class="fa-solid fa-plus-circle"></i> +$<?php echo $priceExtra; ?> por cada <?php echo $blockSize; ?> links extra</li>
                </ul>
                <button onclick="requestUpgrade('Pro')" class="btn btn-primary">Mejorar a Pro</button>
            </div>

            <!-- Plan Premium -->
            <div class="plan-card glass <?php echo $currentPlan === 'premium' ? 'active' : ''; ?>" style="background: rgba(99, 102, 241, 0.05);">
                <?php if ($currentPlan === 'premium'): ?><div class="plan-badge">PLAN ACTUAL</div><?php endif; ?>
                <h2 style="color: #f1c40f;">Premium</h2>
                <div class="plan-price">$<?php echo $pricePremium; ?> <span>/ mes MXN</span></div>
                <ul class="plan-features">
                    <li><i class="fa-solid fa-check"></i> Hasta <?php echo $limitPremium; ?> enlaces</li>
                    <li><i class="fa-solid fa-check"></i> Todo lo del plan Pro</li>
                    <li><i class="fa-solid fa-check"></i> Sin límites de categorías</li>
                    <li><i class="fa-solid fa-crown" style="color: #f1c40f;"></i> Insignia de Fundador</li>
                </ul>
                <button onclick="requestUpgrade('Premium')" class="btn" style="background: #f1c40f; color: #0f172a; font-weight: 800;">Ser Premium</button>
            </div>
        </div>

        <footer style="margin-top: 4rem; text-align: center; color: var(--text-muted); font-size: 0.85rem;">
            <p><i class="fa-solid fa-shield-halved"></i> Pagos procesados de forma segura y manual.</p>
        </footer>
    </div>

    <script>
        async function requestUpgrade(plan) {
            const fd = new FormData();
            fd.append('plan', plan.toLowerCase());

            try {
                const response = await fetch('api.php?action=solicitar_mejora', {
                    method: 'POST',
                    body: fd
                });
                const result = await response.json();
                if (result.success) {
                    alert(`¡Solicitud enviada! El administrador ha sido notificado y se pondrá en contacto contigo pronto.`);
                } else {
                    alert("Error al enviar solicitud: " + result.error);
                }
            } catch (e) { alert("Error de conexión."); }
        }
    </script>
</body>
</html>
