<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mantenimiento | Montia</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #6366f1;
            --bg: #0f172a;
            --text: #f8fafc;
        }
        body {
            margin: 0;
            padding: 0;
            font-family: 'Outfit', sans-serif;
            background: var(--bg);
            color: var(--text);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        .container {
            text-align: center;
            padding: 2rem;
            position: relative;
            z-index: 1;
        }
        .logo {
            font-size: 3rem;
            font-weight: 600;
            margin-bottom: 1rem;
            background: linear-gradient(to right, #818cf8, #c084fc);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: -1px;
        }
        .icon {
            font-size: 4rem;
            margin-bottom: 2rem;
            animation: pulse 2s infinite ease-in-out;
        }
        h1 { font-size: 2rem; margin-bottom: 1rem; }
        p { color: #94a3b8; max-width: 400px; margin: 0 auto; line-height: 1.6; }
        
        .bg-glow {
            position: absolute;
            width: 300px;
            height: 300px;
            background: var(--primary);
            filter: blur(120px);
            opacity: 0.2;
            border-radius: 50%;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 0;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 0.8; }
            50% { transform: scale(1.1); opacity: 1; }
        }
    </style>
</head>
<body>
    <div class="bg-glow"></div>
    <div class="container">
        <div class="icon">🚀</div>
        <div class="logo">Montia</div>
        <h1>Estamos mejorando para ti</h1>
        <p>En este momento estamos aplicando actualizaciones importantes. Volveremos en unos minutos con una mejor experiencia.</p>
    </div>
</body>
</html>
