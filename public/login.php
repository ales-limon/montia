<?php
require_once __DIR__ . '/../config/config.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | LinkViewer</title>
    <link rel="stylesheet" href="index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .login-container {
            display: flex;
            flex-direction: column;
            justify-content: center;
            min-height: 100vh;
            padding: 2rem;
        }
        .login-box {
            padding: 2.5rem;
            width: 100%;
            max-width: 400px;
            margin: 0 auto;
        }
        .logo-section {
            text-align: center;
            margin-bottom: 2rem;
        }
        .logo-icon {
            font-size: 3rem;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo-section">
            <i class="fa-solid fa-link logo-icon" style="font-size: 4rem; margin-bottom: 0.5rem;"></i>
            <h1 style="font-size: 2.5rem; letter-spacing: -1px;">LinkViewer</h1>
            <p style="color: var(--text-muted); font-size: 1.1rem; margin-top: 0.5rem;">Tu biblioteca inteligente de enlaces.</p>
            
            <div style="display: flex; justify-content: center; gap: 1.5rem; margin-top: 2rem; font-size: 0.8rem; color: var(--accent);">
                <span><i class="fa-solid fa-shield-halved"></i> Privado</span>
                <span><i class="fa-solid fa-bolt"></i> Rápido</span>
                <span><i class="fa-solid fa-share-nodes"></i> Social</span>
            </div>
        </div>

        <div class="login-box glass">
            <form id="loginForm">
                <div class="input-group">
                    <label for="email" style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem;">Email</label>
                    <input type="email" id="email" name="email" placeholder="tu@email.com" required>
                </div>
                <div class="input-group">
                    <label for="password" style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem;">Contraseña</label>
                    <input type="password" id="password" name="password" placeholder="••••••••" required>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">
                    Entrar <i class="fa-solid fa-arrow-right"></i>
                </button>
            </form>

            <div style="margin-top: 1.5rem; text-align: center; font-size: 0.9rem;">
                <p>¿No tienes cuenta? <a href="registro.php" style="color: var(--accent); text-decoration: none; font-weight: 600;">Regístrate</a></p>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            
            try {
                const response = await fetch('api.php?action=login', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                
                if (result.success) {
                    window.location.href = 'panel.php';
                } else {
                    alert(result.error);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Ocurrió un error al intentar iniciar sesión.');
            }
        });
    </script>
</body>
</html>
