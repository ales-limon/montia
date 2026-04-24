<?php
require_once __DIR__ . '/../config/config.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="manifest" href="manifest.json">
    <link rel="icon" type="image/png" href="icon.png">
    <link rel="apple-touch-icon" href="icon.png">
    <meta name="theme-color" content="#6366f1">
    <title>Registro | Montia</title>
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
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo-section">
            <i class="fa-solid fa-user-plus" style="font-size: 3rem; color: var(--primary); margin-bottom: 1rem;"></i>
            <h1>Crear Cuenta</h1>
            <p style="color: var(--text-muted)">Únete a Montia y organiza tus enlaces.</p>
        </div>

        <div class="login-box glass">
            <form id="registerForm">
                <div class="input-group">
                    <label for="nombre" style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem;">Nombre Completo</label>
                    <input type="text" id="nombre" name="nombre" placeholder="Ej. Juan Pérez" required>
                </div>
                <div class="input-group">
                    <label for="email" style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem;">Email</label>
                    <input type="email" id="email" name="email" placeholder="tu@email.com" required>
                </div>
                <div class="input-group">
                    <label for="password" style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem;">Contraseña (mín. 6 caracteres)</label>
                    <input type="password" id="password" name="password" placeholder="••••••••" required minlength="6">
                </div>
                
                <div class="input-group" style="display: flex; align-items: flex-start; gap: 0.5rem; margin-top: 1rem;">
                    <input type="checkbox" id="terms" name="terms" style="width: auto; margin-top: 0.2rem;" required>
                    <label for="terms" style="font-size: 0.8rem; color: var(--text-muted); line-height: 1.4;">
                        Acepto los <a href="terminos.php" target="_blank" style="color: var(--accent);">Términos de Servicio</a> y la <a href="terminos.php" target="_blank" style="color: var(--accent);">Política de Privacidad</a>.
                    </label>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; margin-top: 1rem;">
                    Registrarme <i class="fa-solid fa-check"></i>
                </button>
            </form>

            <div style="margin-top: 1.5rem; text-align: center; font-size: 0.9rem;">
                <p>¿Ya tienes cuenta? <a href="login.php" style="color: var(--accent); text-decoration: none; font-weight: 600;">Inicia Sesión</a></p>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('registerForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            
            try {
                const response = await fetch('api.php?action=register', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                
                if (result.success) {
                    alert(result.error); // El mensaje de éxito viene en el campo 'error' en mi controlador
                    window.location.href = 'login.php';
                } else {
                    alert(result.error);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Ocurrió un error al intentar registrarte.');
            }
        });
    </script>
</body>
</html>
