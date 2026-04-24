<?php
require_once __DIR__ . '/../config/config.php';

if (!getTenantId()) {
    header("Location: login.php");
    exit();
}

$userName = $_SESSION['user_name'];
$userEmail = $_SESSION['user_email'] ?? 'Sin email';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil | LinkViewer</title>
    <link rel="stylesheet" href="index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="manifest" href="manifest.json">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="apple-touch-icon" href="icon.png">
    <style>
        .profile-header { text-align: center; margin-bottom: 2.5rem; }
        .avatar-large { width: 100px; height: 100px; border-radius: 50%; background: var(--primary-gradient); display: flex; justify-content: center; align-items: center; font-size: 3rem; color: white; margin: 0 auto 1rem; border: 4px solid var(--glass-border); box-shadow: var(--shadow); }
        
        .section-card { margin-bottom: 2rem; padding: 1.5rem; }
        .section-title { font-size: 1.2rem; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.8rem; color: var(--accent); }
        
        .cat-item { display: flex; justify-content: space-between; align-items: center; padding: 0.8rem; background: rgba(255,255,255,0.03); border-radius: 12px; margin-bottom: 0.5rem; }
    </style>
</head>
<body>
    <div class="container">
        <header style="margin-bottom: 2rem; display: flex; align-items: center; gap: 1rem;">
            <a href="panel.php" style="color: white; font-size: 1.5rem;"><i class="fa-solid fa-arrow-left"></i></a>
            <h1>Mi Perfil</h1>
        </header>

        <div class="profile-header">
            <div class="avatar-large">
                <i class="fa-solid fa-user"></i>
            </div>
            <h2><?php echo htmlspecialchars($userName); ?></h2>
            <div style="display: flex; justify-content: center; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                <?php 
                    $userPlan = $_SESSION['user_suscripcion'] ?? 'gratis'; 
                ?>
                <span class="status-badge <?php echo $userPlan === 'premium' ? 'status-premium' : 'status-gratis'; ?>" style="font-size: 0.65rem;">
                    Plan <?php echo strtoupper($userPlan); ?>
                </span>
                <?php if ($userPlan !== 'premium'): ?>
                    <a href="planes.php" style="color: #f1c40f; font-size: 0.75rem; text-decoration: none; font-weight: 700;">Mejorar <i class="fa-solid fa-arrow-up-right-from-square"></i></a>
                <?php endif; ?>
            </div>
            <p style="color: var(--text-muted);"><?php echo htmlspecialchars($userEmail ?: 'Sin email'); ?></p>
            
            <?php if ($_SESSION['user_rol'] === 'admin'): ?>
            <a href="admin.php" class="btn glass" style="margin-top: 1rem; color: #f1c40f; border-color: rgba(241, 196, 15, 0.3);">
                <i class="fa-solid fa-crown"></i> Panel de Administración
            </a>
            <?php endif; ?>

            <div style="margin-top: 1.5rem;">
                <button onclick="shareAppWhatsApp()" class="btn glass" style="color: #25D366; border-color: rgba(37, 211, 102, 0.3); width: auto; margin: 0 auto;">
                    <i class="fa-brands fa-whatsapp"></i> Invitar Amigos a Montia
                </button>
            </div>
        </div>

        <!-- Gestión de Categorías -->
        <div class="section-card glass">
            <h3 class="section-title"><i class="fa-solid fa-tags"></i> Mis Categorías</h3>
            
            <form id="addCatForm" style="margin-bottom: 1.5rem;">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <div style="display: flex; gap: 0.5rem;">
                    <input type="text" name="nombre" placeholder="Nombre de categoría..." required style="flex-grow: 1;">
                    <input type="color" name="color" value="#6366f1" style="width: 45px; padding: 2px;">
                    <button type="submit" class="btn btn-primary" style="padding: 0.5rem 1rem;"><i class="fa-solid fa-plus"></i></button>
                </div>
            </form>

            <div id="catListContainer">
                <p style="text-align: center; color: var(--text-muted);">Cargando categorías...</p>
            </div>
        </div>

        <!-- Red Interna (Contactos) -->
        <div class="section-card glass">
            <h3 class="section-title"><i class="fa-solid fa-users"></i> Red Interna</h3>
            
            <!-- Buscar Usuario -->
            <div style="margin-bottom: 2rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-size: 0.85rem;">Buscar amigos por email</label>
                <div style="display: flex; gap: 0.5rem;">
                    <input type="email" id="searchEmail" placeholder="ejemplo@linkviewer.com" style="flex-grow: 1;">
                    <button onclick="searchUser()" class="btn glass" style="padding: 0.5rem 1rem;"><i class="fa-solid fa-magnifying-glass"></i></button>
                </div>
                <div id="searchResult" style="margin-top: 1rem;"></div>
            </div>

            <!-- Solicitudes Pendientes -->
            <div id="requestsSection" style="display: none; margin-bottom: 2rem;">
                <h4 style="font-size: 0.9rem; margin-bottom: 1rem; color: var(--text-muted);">Solicitudes Recibidas</h4>
                <div id="requestsList"></div>
            </div>

            <!-- Solicitudes Enviadas -->
            <div id="sentRequestsSection" style="display: none; margin-bottom: 2rem;">
                <h4 style="font-size: 0.9rem; margin-bottom: 1rem; color: var(--text-muted);">Solicitudes Enviadas</h4>
                <div id="sentRequestsList"></div>
            </div>

            <!-- Lista de Contactos -->
            <h4 style="font-size: 0.9rem; margin-bottom: 1rem; color: var(--text-muted);">Mis Contactos</h4>
            <div id="contactsList">
                <p style="text-align: center; color: var(--text-muted); font-size: 0.85rem;">Cargando contactos...</p>
            </div>
        </div>

        <!-- Cambio de Contraseña -->
        <div class="section-card glass">
            <h3 class="section-title"><i class="fa-solid fa-lock"></i> Seguridad</h3>
            <form id="changePassForm">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <div class="input-group">
                    <label style="display: block; margin-bottom: 0.5rem; font-size: 0.85rem;">Contraseña Actual</label>
                    <input type="password" name="pass_actual" required>
                </div>
                <div class="input-group">
                    <label style="display: block; margin-bottom: 0.5rem; font-size: 0.85rem;">Nueva Contraseña</label>
                    <input type="password" name="pass_nueva" required minlength="6">
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; margin-top: 1rem;">
                    Actualizar Contraseña
                </button>
            </form>
        </div>

        <a href="api.php?action=logout" class="btn" style="width: 100%; justify-content: center; color: #ff4757; border: 1px solid rgba(255, 71, 87, 0.3); background: rgba(255, 71, 87, 0.05); margin-bottom: 5rem;">
            <i class="fa-solid fa-right-from-bracket"></i> Cerrar Sesión
        </a>
    </div>

    <script>
        async function loadCategories() {
            try {
                const response = await fetch('api.php?action=list_categories');
                const result = await response.json();
                if (result.success) {
                    const scrollContainer = document.getElementById('categoriesContainer');
                    const select = document.getElementById('categorySelect');
                    const list = document.getElementById('catListContainer');
                    
                    if (scrollContainer) scrollContainer.innerHTML = '';
                    if (select) select.innerHTML = '<option value="">Sin categoría</option>';
                    if (list) list.innerHTML = '';

                    result.data.forEach(cat => {
                        if (list) {
                            const item = document.createElement('div');
                            item.className = 'cat-item';
                            item.innerHTML = `
                                <div style="display: flex; align-items: center; gap: 0.8rem;">
                                    <div style="width: 12px; height: 12px; border-radius: 50%; background: ${cat.color};"></div>
                                    <span>${cat.nombre}</span>
                                </div>
                                <i class="fa-solid fa-trash-can" style="color: var(--text-muted); cursor: pointer;" onclick="deleteCategory(${cat.id})"></i>
                            `;
                            list.appendChild(item);
                        }
                    });
                }
            } catch (e) { console.error(e); }
        }

        document.getElementById('addCatForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            try {
                const response = await fetch('api.php?action=save_category', { method: 'POST', body: formData });
                const result = await response.json();
                if (result.success) { e.target.reset(); loadCategories(); }
                else { alert(result.error); }
            } catch (e) { console.error(e); }
        });

        async function deleteCategory(id) {
            if (!confirm('¿Seguro que quieres eliminar esta categoría?')) return;
            const formData = new FormData();
            formData.append('id', id);
            formData.append('csrf_token', '<?php echo $_SESSION['csrf_token']; ?>');
            try {
                const response = await fetch('api.php?action=delete_category', { method: 'POST', body: formData });
                const result = await response.json();
                if (result.success) { loadCategories(); }
                else { alert(result.error); }
            } catch (e) { console.error(e); }
        }

        document.getElementById('changePassForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            try {
                const response = await fetch('api.php?action=update_password', { method: 'POST', body: formData });
                const result = await response.json();
                if (result.success) { 
                    alert('Contraseña actualizada correctamente.');
                    e.target.reset();
                } else { alert(result.error); }
            } catch (e) { console.error(e); }
        });

        async function searchUser() {
            const email = document.getElementById('searchEmail').value;
            const resultDiv = document.getElementById('searchResult');
            if (!email) return;

            resultDiv.innerHTML = '<p style="font-size: 0.8rem;">Buscando...</p>';
            try {
                const response = await fetch(`api.php?action=search_user&email=${encodeURIComponent(email)}`);
                const result = await response.json();
                if (result.success) {
                    const user = result.data;
                    resultDiv.innerHTML = `
                        <div class="cat-item" style="background: rgba(99, 102, 241, 0.1);">
                            <div>
                                <strong>${user.nombre}</strong><br>
                                <span style="font-size: 0.75rem; color: var(--text-muted);">${user.email}</span>
                            </div>
                            <button onclick="sendRequest(${user.id})" class="btn btn-primary" style="padding: 0.4rem 0.8rem; font-size: 0.7rem;">Solicitar</button>
                        </div>
                    `;
                } else {
                    resultDiv.innerHTML = `<p style="font-size: 0.8rem; color: #ff4757;">${result.error}</p>`;
                }
            } catch (e) { console.error(e); }
        }

        async function sendRequest(id) {
            const formData = new FormData();
            formData.append('id_receptor', id);
            formData.append('csrf_token', '<?php echo $_SESSION['csrf_token']; ?>');
            try {
                const response = await fetch('api.php?action=send_contact_request', { method: 'POST', body: formData });
                const result = await response.json();
                if (result.success) {
                    alert('Solicitud enviada correctamente.');
                    document.getElementById('searchResult').innerHTML = '';
                    document.getElementById('searchEmail').value = '';
                } else { alert(result.error); }
            } catch (e) { console.error(e); }
        }

        async function loadRequests() {
            try {
                // 1. Cargar Solicitudes Recibidas
                const responseRec = await fetch('api.php?action=list_contact_requests');
                const resultRec = await responseRec.json();
                const containerRec = document.getElementById('requestsSection');
                const listRec = document.getElementById('requestsList');
                
                if (resultRec.success && resultRec.data.length > 0) {
                    containerRec.style.display = 'block';
                    listRec.innerHTML = '';
                    resultRec.data.forEach(req => {
                        const item = document.createElement('div');
                        item.className = 'cat-item';
                        item.innerHTML = `
                            <div>
                                <strong>${req.nombre}</strong>
                            </div>
                            <div style="display: flex; gap: 0.5rem;">
                                <button onclick="respondRequest(${req.id}, 'aceptado')" class="btn" style="padding: 0.4rem; background: #2ed573; color: white; border-radius: 8px;"><i class="fa-solid fa-check"></i></button>
                                <button onclick="respondRequest(${req.id}, 'rechazado')" class="btn" style="padding: 0.4rem; background: #ff4757; color: white; border-radius: 8px;"><i class="fa-solid fa-xmark"></i></button>
                            </div>
                        `;
                        listRec.appendChild(item);
                    });
                } else {
                    containerRec.style.display = 'none';
                }

                // 2. Cargar Solicitudes Enviadas
                const responseSent = await fetch('api.php?action=list_sent_requests');
                const resultSent = await responseSent.json();
                const containerSent = document.getElementById('sentRequestsSection');
                const listSent = document.getElementById('sentRequestsList');

                if (resultSent.success && resultSent.data.length > 0) {
                    containerSent.style.display = 'block';
                    listSent.innerHTML = '';
                    resultSent.data.forEach(req => {
                        const item = document.createElement('div');
                        item.className = 'cat-item';
                        const statusColor = req.estado === 'pendiente' ? 'var(--accent)' : (req.estado === 'aceptado' ? '#2ed573' : '#ff4757');
                        item.innerHTML = `
                            <div>
                                <strong>${req.nombre}</strong><br>
                                <span style="font-size: 0.7rem; color: var(--text-muted);">${req.email}</span>
                            </div>
                            <span style="font-size: 0.75rem; font-weight: 600; color: ${statusColor}; background: rgba(255,255,255,0.05); padding: 0.2rem 0.5rem; border-radius: 6px;">
                                ${req.estado.charAt(0).toUpperCase() + req.estado.slice(1)}
                            </span>
                        `;
                        listSent.appendChild(item);
                    });
                } else {
                    containerSent.style.display = 'none';
                }
            } catch (e) { console.error(e); }
        }

        async function respondRequest(id, estado) {
            const formData = new FormData();
            formData.append('id_solicitud', id);
            formData.append('estado', estado);
            formData.append('csrf_token', '<?php echo $_SESSION['csrf_token']; ?>');
            try {
                const response = await fetch('api.php?action=respond_contact_request', { method: 'POST', body: formData });
                const result = await response.json();
                if (result.success) {
                    loadRequests();
                    loadContacts();
                }
            } catch (e) { console.error(e); }
        }

        async function loadContacts() {
            try {
                const response = await fetch('api.php?action=list_contacts');
                const result = await response.json();
                const list = document.getElementById('contactsList');
                if (result.success && result.data.length > 0) {
                    list.innerHTML = '';
                    result.data.forEach(c => {
                        const item = document.createElement('div');
                        item.className = 'cat-item';
                        item.innerHTML = `
                            <div>
                                <strong>${c.nombre}</strong><br>
                                <span style="font-size: 0.75rem; color: var(--text-muted);">${c.email}</span>
                            </div>
                            <i class="fa-solid fa-circle-check" style="color: #2ed573;"></i>
                        `;
                        list.appendChild(item);
                    });
                } else {
                    list.innerHTML = '<p style="text-align: center; color: var(--text-muted); font-size: 0.85rem;">No tienes contactos aún.</p>';
                }
            } catch (e) { console.error(e); }
        }

        loadCategories();
        loadRequests();
        loadContacts();
        function shareAppWhatsApp() {
            const text = "¡Hola! Estoy usando Montia para organizar todos mis enlaces de Instagram, TikTok y la web en un solo lugar. ¡Deberías probarlo! 🚀🔗";
            const url = "https://montia.mx"; 
            const waUrl = `https://wa.me/?text=${encodeURIComponent(text + "\n\n" + url)}`;
            window.open(waUrl, '_blank');
        }
    </script>
</body>
</html>
