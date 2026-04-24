<?php
require_once __DIR__ . '/../config/config.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Términos y Privacidad | Montia</title>
    <link rel="stylesheet" href="index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: var(--bg); color: var(--text); line-height: 1.6; }
        .legal-container { max-width: 800px; margin: 3rem auto; padding: 2rem; }
        .legal-box { padding: 3rem; }
        h1 { color: var(--primary); margin-bottom: 2rem; border-bottom: 2px solid var(--glass-border); padding-bottom: 1rem; }
        h2 { color: var(--accent); margin-top: 2rem; margin-bottom: 1rem; font-size: 1.3rem; }
        p { margin-bottom: 1rem; color: var(--text-muted); }
        ul { margin-bottom: 1rem; padding-left: 1.5rem; color: var(--text-muted); }
        li { margin-bottom: 0.5rem; }
        .back-link { display: inline-block; margin-top: 2rem; color: var(--primary); text-decoration: none; font-weight: 600; }
    </style>
</head>
<body>
    <div class="legal-container">
        <div class="legal-box glass">
            <h1>Términos de Servicio y Privacidad</h1>
            
            <p>Bienvenido a <strong>Montia</strong>. Al utilizar nuestra plataforma, aceptas cumplir con los siguientes términos y condiciones de uso.</p>

            <h2>1. Uso Aceptable</h2>
            <p>Montia es una herramienta para la organización personal y profesional de enlaces. Queda estrictamente prohibido:</p>
            <ul>
                <li>Almacenar o compartir contenido de carácter pornográfico o sexual explícito.</li>
                <li>Compartir enlaces que promuevan el odio, la violencia, la discriminación o actividades ilegales.</li>
                <li>Utilizar la plataforma para distribuir software malicioso (malware) o realizar estafas (phishing).</li>
                <li>Acosar o enviar spam a otros usuarios a través de la red interna.</li>
            </ul>
            <p>El incumplimiento de estas normas resultará en la <strong>suspensión inmediata y definitiva</strong> de la cuenta sin previo aviso.</p>

            <h2>2. Tratamiento de Datos Personales</h2>
            <p>En Montia solo solicitamos y almacenamos dos datos personales: tu <strong>Nombre</strong> y tu <strong>Correo Electrónico</strong>. El tratamiento de estos datos es el siguiente:</p>
            <ul>
                <li><strong>Identificación:</strong> Tu nombre se utiliza para personalizar tu experiencia en el panel y para que tus contactos puedan identificarte en la red interna.</li>
                <li><strong>Comunicación y Acceso:</strong> Tu correo electrónico es tu identificador único de acceso (login) y se utiliza exclusivamente para la gestión de tu cuenta y seguridad (como recuperación de contraseña).</li>
                <li><strong>No compartición:</strong> Estos datos NUNCA son vendidos, alquilados ni compartidos con empresas externas para fines publicitarios o de marketing.</li>
                <li><strong>Visibilidad en Red:</strong> Tu nombre y correo solo serán visibles para otros usuarios si tú decides enviarles una solicitud de contacto o si ellos te buscan por correo electrónico para conectarse contigo.</li>
            </ul>

            <h2>3. Privacidad y Seguridad (Estándar FORJIATO)</h2>
            <p>Tu privacidad técnica está garantizada bajo los más altos estándares:</p>
            <ul>
                <li><strong>Aislamiento Multi-tenant:</strong> Tu base de datos personal está aislada lógicamente; un usuario jamás podrá ver los enlaces o datos de otro.</li>
                <li><strong>Protocolos Seguros:</strong> Implementamos sesiones protegidas con <code>HttpOnly</code> y <code>Secure</code>, además de protección contra ataques <code>CSRF</code>.</li>
            </ul>

            <h2>3. Responsabilidad</h2>
            <p>Montia no se hace responsable del contenido de los sitios web externos vinculados a través de la plataforma. El usuario es el único responsable de la legalidad de los enlaces que decide guardar o compartir.</p>

            <h2>4. Modificaciones</h2>
            <p>Nos reservamos el derecho de actualizar estos términos en cualquier momento para adaptarlos a nuevas funciones o requisitos legales.</p>

            <a href="registro.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> Volver al registro</a>
        </div>
    </div>
</body>
</html>
