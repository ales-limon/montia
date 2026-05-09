<?php
require_once __DIR__ . '/../config/config.php';

// Protección de ruta reactivada
if (!getTenantId()) {
    header("Location: login.php");
    exit();
}

$userName = $_SESSION['user_name'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel | Montia</title>
    <link rel="stylesheet" href="index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#6366f1">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="apple-touch-icon" href="icon.png">
    <script>
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('service-worker.js');
        }
    </script>
    <style>        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem; }
        .favorite-star {
            position: absolute;
            top: 10px;
            right: 10px;
            color: #ffcc00;
            filter: drop-shadow(0 0 5px rgba(0,0,0,0.5));
        }

        .share-count-badge {
            position: absolute;
            top: 10px;
            left: 10px;
            background: #25D366;
            color: white;
            font-size: 0.7rem;
            padding: 2px 8px;
            border-radius: 10px;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 4px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.3);
            z-index: 2;
        }
        #pullIndicator {
            position: fixed;
            top: 0;
            left: 50%;
            transform: translateX(-50%) translateY(-60px);
            background: var(--accent);
            color: white;
            padding: 0.4rem 1.2rem;
            border-radius: 0 0 20px 20px;
            font-size: 0.78rem;
            font-weight: 600;
            z-index: 9999;
            transition: transform 0.25s ease;
            pointer-events: none;
        }
        #pullIndicator.visible {
            transform: translateX(-50%) translateY(0);
        }
        .sticky-top {
            position: sticky; 
            top: 0; 
            background: transparent; 
            backdrop-filter: blur(0px); 
            -webkit-backdrop-filter: blur(0px); 
            z-index: 1000; 
            padding-top: 1rem; 
            padding-bottom: 0.5rem; 
            margin-top: -1.5rem; 
            margin-left: -1.5rem; 
            margin-right: -1.5rem; 
            padding-left: 1.5rem; 
            padding-right: 1.5rem;
            transition: all 0.4s ease;
            border-bottom: 1px solid transparent;
        }
        .header-scrolled {
            background: rgba(15, 23, 42, 0.7) !important;
            backdrop-filter: blur(12px) !important;
            -webkit-backdrop-filter: blur(12px) !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08) !important;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        .search-bar { margin-bottom: 0.8rem; }
        .categories-scroll { 
            display: flex; 
            gap: 0.8rem; 
            overflow-x: auto; 
            padding-bottom: 0.8rem; 
            scrollbar-width: thin; 
            scrollbar-color: var(--primary) transparent;
            cursor: grab;
            user-select: none;
            scroll-behavior: smooth;
        }
        .categories-scroll:active { cursor: grabbing; }
        .categories-scroll::-webkit-scrollbar { height: 4px; }
        .categories-scroll::-webkit-scrollbar-track { background: transparent; }
        .categories-scroll::-webkit-scrollbar-thumb { background: var(--glass-border); border-radius: 10px; }
        .categories-scroll:hover::-webkit-scrollbar-thumb { background: var(--primary); }
        .cat-chip { padding: 0.5rem 1.2rem; border-radius: 20px; white-space: nowrap; background: var(--bg-card); border: 1px solid var(--glass-border); font-size: 0.85rem; cursor: pointer; transition: var(--transition); }
        .cat-chip.active { background: var(--primary); color: white; border-color: var(--primary); }
        
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 2000; justify-content: center; align-items: center; padding: 1.5rem; }
        .modal-content { width: 100%; max-width: 500px; padding: 2rem; overflow-y: auto; max-height: 90vh; }

        #linksContainer { 
            display: grid; 
            grid-template-columns: repeat(2, 1fr); 
            gap: 1rem; 
            margin-top: 1rem; 
        }

        @media (max-width: 480px) {
            #linksContainer { grid-template-columns: repeat(2, 1fr); gap: 0.8rem; }
        }

        .modal-actions {
            margin-top: 2rem;
            display: flex;
            gap: 0.8rem;
            flex-wrap: wrap;
        }
        .modal-actions > * {
            flex: 1 1 100px;
            min-width: 100px;
            justify-content: center !important;
        }
        .modal-actions a.btn-primary {
            flex: 2 1 150px;
        }

    </style>
</head>
<body>
    <div id="pullIndicator"><i class="fa-solid fa-rotate-right"></i> Actualizando...</div>
    <div class="container">
        <div class="sticky-top">
            <header class="header">
                <div>
                    <p style="color: var(--text-muted); font-size: 0.8rem;">Hola, <?php echo htmlspecialchars($userName); ?> <i class="fa-solid fa-hand-peace" style="color: var(--accent); margin-left: 5px;"></i></p>
                    <h1 style="font-size: 1.4rem;">Mis Enlaces</h1>
                </div>
                <div style="display: flex; gap: 0.5rem; align-items: center;">
                    <a href="planes.php" class="btn glass" style="padding: 0.5rem; color: #f1c40f; border-color: rgba(241, 196, 15, 0.3); font-size: 0.8rem; height: 40px; display: flex; align-items: center; gap: 0.3rem;">
                        <i class="fa-solid fa-star"></i> <span class="hide-mobile">Mejorar</span>
                    </a>
                    <div class="glass" style="width: 40px; height: 40px; border-radius: 50%; display: flex; justify-content: center; align-items: center; cursor: pointer;" onclick="location.href='perfil.php'">
                        <i class="fa-solid fa-user"></i>
                    </div>
                    <a href="api.php?action=logout" style="color: var(--text-muted); font-size: 1.1rem; padding: 0.5rem;"><i class="fa-solid fa-right-from-bracket"></i></a>
                </div>
            </header>

            <div class="search-bar">
                <div class="input-group" style="position: relative;">
                    <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 1rem; top: 1rem; color: var(--text-muted);"></i>
                    <input type="text" id="searchInput" placeholder="Buscar enlaces..." style="padding-left: 3rem;">
                </div>
            </div>

            <div class="categories-scroll" id="categoriesContainer">
                <div class="cat-chip active" onclick="filterCategory(null, this)">Todos</div>
                <div class="cat-chip" onclick="filterCategory('fav', this)">Favoritos</div>
                <div class="cat-chip" onclick="filterCategory('shared', this)" style="color: var(--accent);"><i class="fa-solid fa-inbox"></i> Recibidos</div>
            </div>
        </div>

        <div id="linksContainer">
            <p style="text-align: center; color: var(--text-muted);">Cargando tus tesoros...</p>
        </div>
    </div>

    <!-- Navegación Móvil -->
    <nav class="mobile-nav">
        <a href="panel.php" class="nav-item active"><i class="fa-solid fa-house"></i></a>
        <a href="#" class="nav-item" onclick="filterCategory('shared', document.querySelector('.cat-chip:nth-child(3)'))">
            <i class="fa-solid fa-inbox"></i>
            <span id="notifBadge" class="badge" style="display:none;">0</span>
        </a>
        <a href="#" class="nav-item" onclick="openModal()"><div class="add-btn-main"><i class="fa-solid fa-plus"></i></div></a>
        <a href="perfil.php" class="nav-item"><i class="fa-solid fa-user"></i></a>
    </nav>

    <!-- Modal Añadir Enlace -->
    <div id="addModal" class="modal">
        <div class="modal-content glass">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h2 style="font-size: 1.5rem;">Añadir Enlace</h2>
                <i class="fa-solid fa-xmark" style="cursor: pointer; font-size: 1.5rem;" onclick="closeModal()"></i>
            </div>
            <form id="addLinkForm">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <input type="hidden" name="id" id="editLinkId">
                <div class="input-group" id="urlGroup">
                    <label style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem;">URL del Enlace</label>
                    <input type="url" name="url" placeholder="https://..." required id="editLinkUrl">
                </div>
                <div class="input-group">
                    <label style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem;">Categoría</label>
                    <select name="id_categoria" id="categorySelect" class="glass" style="width: 100%; padding: 0.8rem; border-radius: 12px; background: #1a1a2e; color: white; border: 1px solid var(--glass-border);">
                        <option value="" style="background: #1a1a2e; color: white;">Sin categoría</option>
                    </select>
                </div>
                <div class="input-group">
                    <label style="display: block; margin-bottom: 0.5rem; font-size: 0.9rem;">Notas (opcional)</label>
                    <textarea name="notas" rows="3" placeholder="¿Por qué guardaste esto?"></textarea>
                </div>
                <div style="display: flex; gap: 1rem; margin-bottom: 1.5rem;">
                    <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.8rem;">
                        <input type="checkbox" name="es_favorito" style="width: auto;"> Favorito
                    </label>
                    <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.8rem;">
                        <input type="checkbox" name="ver_mas_tarde" style="width: auto;"> Ver más tarde
                    </label>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">
                    Guardar Enlace <i class="fa-solid fa-cloud-arrow-up"></i>
                </button>
            </form>
        </div>
    </div>

    <!-- Modal Compartir -->
    <div id="shareModal" class="modal">
        <div class="modal-content glass" style="max-width: 400px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h2 style="font-size: 1.3rem;">Compartir</h2>
                <i class="fa-solid fa-xmark" style="cursor: pointer; font-size: 1.5rem;" onclick="closeShareModal()"></i>
            </div>
            <div style="display: flex; flex-direction: column; gap: 1rem;">
                <div class="input-group" style="margin-bottom: 0.5rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-size: 0.8rem; color: var(--text-muted);"><i class="fa-brands fa-whatsapp"></i> ¿A quién se lo envías? (Opcional)</label>
                    <input type="text" id="waRecipient" placeholder="Nombre o alias..." style="background: rgba(255,255,255,0.05); border-radius: 10px; padding: 0.6rem;">
                </div>
                <button onclick="shareViaWhatsApp()" class="btn" style="background: #25D366; color: white; justify-content: center; font-weight: 600;">
                    Enviar por WhatsApp <i class="fa-solid fa-paper-plane"></i>
                </button>
                <div style="text-align: center; color: var(--text-muted); font-size: 0.8rem; margin: 0.5rem 0;">— Red Interna —</div>
                <div id="contactsShareList" style="display: flex; flex-direction: column; gap: 0.5rem; max-height: 200px; overflow-y: auto;">
                    <p style="text-align: center; font-size: 0.85rem; color: var(--text-muted);">Cargando tus contactos...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Detalles -->
    <div id="detailModal" class="modal">
        <div class="modal-content glass">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h2 id="detailTitle" style="font-size: 1.3rem; margin-right: 1rem;">Detalles del Enlace</h2>
                <i class="fa-solid fa-xmark" style="cursor: pointer; font-size: 1.5rem;" onclick="closeDetailModal()"></i>
            </div>
            <div id="detailBody">
                <!-- Se llena dinámicamente -->
            </div>
            <div class="modal-actions">
                <button id="detailEditBtn" class="btn glass" style="color: var(--accent);"><i class="fa-solid fa-pen-to-square"></i> Editar</button>
                <a id="detailOpenBtn" href="#" target="_blank" class="btn btn-primary">
                    Abrir Enlace <i class="fa-solid fa-external-link"></i>
                </a>
            </div>
        </div>
    </div>

    <script>
        let currentFilter = {};

        async function loadCategories() {
            try {
                const response = await fetch('api.php?action=list_categories');
                const result = await response.json();
                console.log("Categorías recibidas:", result.data ? result.data.length : 0);
                
                if (result.success && result.data) {
                    const scrollContainer = document.getElementById('categoriesContainer');
                    const select = document.getElementById('categorySelect');
                    
                    // 1. Cargar Chips del Panel
                    if (scrollContainer) {
                        scrollContainer.innerHTML = `
                            <div class="cat-chip ${!currentFilter.cat && !currentFilter.fav ? 'active' : ''}" onclick="filterCategory(null, this)">Todos</div>
                            <div class="cat-chip ${currentFilter.fav ? 'active' : ''}" onclick="filterCategory('fav', this)">Favoritos</div>
                        `;
                        result.data.forEach(cat => {
                            const chip = document.createElement('div');
                            chip.className = `cat-chip ${currentFilter.cat == cat.id ? 'active' : ''}`;
                            chip.innerText = cat.nombre;
                            chip.onclick = () => filterCategory({cat: cat.id}, chip);
                            scrollContainer.appendChild(chip);
                        });
                    }

                    // 2. Cargar Selector del Modal
                    if (select) {
                        select.innerHTML = '<option value="">Sin categoría</option>';
                        result.data.forEach(cat => {
                            const option = document.createElement('option');
                            option.value = cat.id;
                            option.innerText = cat.nombre;
                            option.style.background = "#1a1a2e";
                            option.style.color = "white";
                            select.appendChild(option);
                        });
                    }
                }
            } catch (e) { console.error("Error cargando categorías:", e); }
        }


        async function loadLinks() {
            let url = 'api.php?action=list_links';
            if (currentFilter.fav) url += '&favoritos=1';
            if (currentFilter.cat) url += '&categoria=' + currentFilter.cat;
            if (currentFilter.shared) url = 'api.php?action=list_received_links';
            
            try {
                const response = await fetch(url);
                const result = await response.json();
                
                const container = document.getElementById('linksContainer');
                container.innerHTML = '';

                if (result.success && result.data.length > 0) {
                    const links = result.data;
                    links.forEach(link => {
                        const isShared = !!link.share_id;
                        const card = document.createElement('div');
                        card.className = 'link-card glass' + (isShared && !link.visto ? ' unread' : '');
                        card.onclick = (e) => {
                            if (e.target.closest('button') || e.target.closest('.btn-primary')) return;
                            showLinkDetails(link);
                        };

                        card.innerHTML = `
                            ${link.imagen_url ? `<img src="${link.imagen_url}" class="link-image" alt="" onerror="refreshLinkImage(this,${link.id})">` : ''}
                            <div class="link-content">
                                ${isShared ? `<div class="shared-by"><i class="fa-solid fa-user-tag"></i> De: ${link.emisor_nombre}</div>` : ''}
                                <div style="display: flex; justify-content: space-between; align-items: start; gap: 0.5rem;">
                                    <h3 class="link-title">${link.titulo || 'Sin título'}</h3>
                                    ${link.es_favorito ? '<i class="fa-solid fa-star favorite-star"></i>' : ''}
                                    ${link.total_compartidos > 0 ? `<div class="share-count-badge" title="Compartido ${link.total_compartidos} veces en WhatsApp"><i class="fa-brands fa-whatsapp"></i> ${link.total_compartidos}</div>` : ''}
                                </div>
                                <p class="link-desc">${link.descripcion || 'Sin descripción disponible.'}</p>
                                ${link.notas ? `<p class="link-notes-card"><i class="fa-solid fa-note-sticky"></i> "${link.notas}"</p>` : ''}
                                <div style="margin-top: auto; display: flex; justify-content: space-between; align-items: center; padding-top: 0.5rem;">
                                    <div style="display: flex; gap: 0.5rem; align-items: center;">
                                        ${isShared ? 
                                            `<button onclick="saveSharedLink(${JSON.stringify(link).replace(/"/g, '&quot;')})" class="btn glass" style="padding: 0.3rem 0.5rem; font-size: 0.65rem; color: #2ed573;"><i class="fa-solid fa-plus"></i> Guardar</button>
                                             <button onclick="deleteSharedLink(${link.share_id})" class="btn glass" style="padding: 0.3rem 0.5rem; font-size: 0.65rem; color: #ff4757;"><i class="fa-solid fa-trash"></i></button>` :
                                            `<button onclick="deleteLink(${link.id})" style="background:none; border:none; color:var(--text-muted); cursor:pointer; padding: 0.5rem 0.2rem;"><i class="fa-solid fa-trash-can"></i></button>
                                             <button onclick="event.stopPropagation(); openShareModal(${link.id}, '${btoa(encodeURIComponent(link.titulo || ''))}', '${btoa(link.url)}')" style="background:none; border:none; color:var(--accent); cursor:pointer; padding: 0.5rem 0.2rem;"><i class="fa-solid fa-share-nodes"></i></button>`
                                        }
                                    </div>
                                    <a href="${link.url}" target="_blank" class="btn btn-primary" style="padding: 0.4rem 0.6rem; font-size: 0.7rem; border-radius: 8px;" onclick="${isShared ? `markAsSeen(${link.share_id})` : ''}">
                                        Ir <i class="fa-solid fa-external-link"></i>
                                    </a>
                                </div>
                            </div>
                        `;
                        container.appendChild(card);
                    });
                } else {
                    container.innerHTML = '<div style="text-align: center; margin-top: 3rem;"><i class="fa-solid fa-box-open" style="font-size: 3rem; color: var(--text-muted); margin-bottom: 1rem;"></i><p>Aún no hay enlaces aquí.</p></div>';
                }
            } catch (error) { console.error('Error:', error); }
        }

        document.getElementById('searchInput').addEventListener('input', (e) => {
            const term = e.target.value.toLowerCase();
            const cards = document.querySelectorAll('.link-card');
            
            cards.forEach(card => {
                const text = card.innerText.toLowerCase();
                if (text.includes(term)) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
        });

        async function openModal() { 
            document.getElementById('editLinkId').value = '';
            document.getElementById('urlGroup').style.display = 'block';
            document.getElementById('editLinkUrl').required = true;
            document.getElementById('addLinkForm').reset();
            document.querySelector('#addModal h2').innerText = 'Añadir Enlace';
            document.getElementById('addModal').style.display = 'flex'; 

            // Inteligencia de Portapapeles (Auto-Paste UX)
            if (navigator.clipboard && navigator.clipboard.readText) {
                try {
                    const text = await navigator.clipboard.readText();
                    if (text && (text.startsWith('http://') || text.startsWith('https://'))) {
                        const urlInput = document.getElementById('editLinkUrl');
                        urlInput.value = text.trim();
                        // Pequeño efecto visual para avisar que se pegó algo
                        urlInput.style.borderColor = 'var(--accent)';
                        setTimeout(() => urlInput.style.borderColor = '', 1000);
                    }
                } catch (err) {
                    console.log("Portapapeles no disponible o permiso denegado.");
                }
            }
        }

        async function openEditModal(id) {
            try {
                const response = await fetch(`api.php?action=get_link&id=${id}`);
                const result = await response.json();
                if (result.success) {
                    const link = result.data;
                    document.getElementById('editLinkId').value = link.id;
                    document.getElementById('editLinkUrl').value = link.url;
                    document.getElementById('urlGroup').style.display = 'none'; // No editar URL
                    document.getElementById('editLinkUrl').required = false;
                    
                    document.querySelector('#addLinkForm textarea[name="notas"]').value = link.notes || link.notas || '';
                    document.getElementById('categorySelect').value = link.id_categoria || '';
                    document.querySelector('#addLinkForm input[name="es_favorito"]').checked = !!link.es_favorito;
                    document.querySelector('#addLinkForm input[name="ver_mas_tarde"]').checked = !!link.ver_mas_tarde;

                    document.querySelector('#addModal h2').innerText = 'Editar Enlace';
                    document.getElementById('addModal').style.display = 'flex';
                }
            } catch (e) { console.error(e); }
        }

        function closeModal() { document.getElementById('addModal').style.display = 'none'; }

        function showLinkDetails(link) {
            const body = document.getElementById('detailBody');
            document.getElementById('detailTitle').innerText = link.titulo || 'Detalles';
            
            body.innerHTML = `
                ${link.imagen_url ? `<img src="${link.imagen_url}" class="detail-img">` : ''}
                <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 1rem;">${link.url}</p>
                ${link.categoria_nombre ? `<span style="font-size: 0.7rem; color: white; background: ${link.categoria_color || 'var(--primary)'}; padding: 0.2rem 0.8rem; border-radius: 10px; margin-bottom: 1rem; display: inline-block;">${link.categoria_nombre}</span>` : ''}
                <div style="margin-top: 1rem; line-height: 1.6;">
                    ${link.descripcion || '<p style="color: var(--text-muted);">Sin descripción disponible.</p>'}
                </div>
                ${link.notas ? `<div class="detail-notes">"${link.notas}"</div>` : ''}
            `;

            document.getElementById('detailOpenBtn').href = link.url;
            document.getElementById('detailEditBtn').onclick = () => {
                closeDetailModal();
                openEditModal(link.id);
            };

            // Añadir botón de compartir al detalle si no existe o actualizarlo
            let shareBtn = document.getElementById('detailShareBtn');
            if (!shareBtn) {
                shareBtn = document.createElement('button');
                shareBtn.id = 'detailShareBtn';
                shareBtn.className = 'btn glass';
                shareBtn.style.cssText = 'flex: 1; justify-content: center; color: var(--primary); margin-top: 1rem;';
                shareBtn.innerHTML = '<i class="fa-solid fa-share-nodes"></i> Compartir';
                document.querySelector('#detailModal .modal-actions').prepend(shareBtn);
            }
            shareBtn.onclick = () => {
                closeDetailModal();
                openShareModal(link.id, btoa(encodeURIComponent(link.titulo || '')), btoa(link.url));
            };

            document.getElementById('detailModal').style.display = 'flex';

            // Cargar Historial de Compartidos Externos
            loadShareHistory(link.id);
        }

        async function loadShareHistory(linkId) {
            const body = document.getElementById('detailBody');
            try {
                const response = await fetch(`api.php?action=get_share_history&id=${linkId}`);
                const result = await response.json();
                
                if (result.success && result.data.length > 0) {
                    const historyHtml = `
                        <div style="margin-top: 1.5rem; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 1rem;">
                            <h4 style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 0.8rem;"><i class="fa-solid fa-history"></i> Historial de Compartidos (WhatsApp)</h4>
                            <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                                ${result.data.map(h => `
                                    <div style="background: rgba(255,255,255,0.03); padding: 0.5rem 0.8rem; border-radius: 8px; display: flex; justify-content: space-between; align-items: center; font-size: 0.85rem;">
                                        <span><i class="fa-solid fa-user" style="color: #25D366; font-size: 0.7rem;"></i> <strong>${h.destinatario || 'Alguien'}</strong></span>
                                        <span style="font-size: 0.7rem; color: var(--text-muted);">${new Date(h.created_at).toLocaleDateString()}</span>
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                    `;
                    body.insertAdjacentHTML('beforeend', historyHtml);
                }
            } catch (e) { console.error("Error al cargar historial:", e); }
        }

        function closeDetailModal() { document.getElementById('detailModal').style.display = 'none'; }

        let currentShareData = null;
        async function openShareModal(id, titleBase64, urlBase64) {
            currentShareData = {
                id: id,
                titulo: decodeURIComponent(atob(titleBase64)),
                url: atob(urlBase64)
            };
            document.getElementById('waRecipient').value = ''; // Limpiar campo
            document.getElementById('shareModal').style.display = 'flex';
            loadContactsForSharing();
        }

        async function loadContactsForSharing() {
            const list = document.getElementById('contactsShareList');
            try {
                const response = await fetch('api.php?action=list_contacts');
                const result = await response.json();
                if (result.success && result.data.length > 0) {
                    list.innerHTML = '';
                    result.data.forEach(contact => {
                        const btn = document.createElement('button');
                        btn.className = 'btn glass';
                        btn.style.justifyContent = 'space-between';
                        btn.innerHTML = `<span><i class="fa-solid fa-user"></i> ${contact.nombre}</span> <i class="fa-solid fa-paper-plane"></i>`;
                        btn.onclick = () => shareWithContact(contact.id);
                        list.appendChild(btn);
                    });
                } else {
                    list.innerHTML = '<p style="text-align: center; font-size: 0.85rem; color: var(--text-muted);">No tienes contactos aún. Agrégalos en tu perfil.</p>';
                }
            } catch (e) { list.innerHTML = 'Error al cargar contactos.'; }
        }

        async function shareWithContact(idReceptor) {
            const formData = new FormData();
            formData.append('id_enlace', currentShareData.id);
            formData.append('id_receptor', idReceptor);
            formData.append('csrf_token', '<?php echo $_SESSION['csrf_token']; ?>');

            try {
                const response = await fetch('api.php?action=share_link_internal', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                if (result.success) {
                    alert("¡Enlace compartido!");
                    closeShareModal();
                } else { alert(result.error); }
            } catch (e) { alert("Error de conexión."); }
        }

        function closeShareModal() { document.getElementById('shareModal').style.display = 'none'; }

        async function saveSharedLink(link) {
            if (!confirm('¿Quieres guardar este enlace en tu colección personal?')) return;
            const formData = new FormData();
            formData.append('url', link.url);
            formData.append('titulo', link.titulo);
            formData.append('descripcion', link.descripcion);
            formData.append('imagen_url', link.imagen_url);
            // Preservar la nota del emisor
            const notaFinal = (link.notas ? link.notas + "\n" : "") + `(De: ${link.emisor_nombre})`;
            formData.append('notas', notaFinal);
            formData.append('csrf_token', '<?php echo $_SESSION['csrf_token']; ?>');

            try {
                const response = await fetch('api.php?action=save_link', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                if (result.success) {
                    alert("¡Guardado en tu colección!");
                    // Eliminar de la bandeja de compartidos
                    const delData = new FormData();
                    delData.append('id', link.share_id);
                    await fetch('api.php?action=delete_shared_link', { method: 'POST', body: delData });
                    
                    if (currentFilter.shared) loadLinks();
                    updateNotifBadge();
                }
            } catch (e) { console.error(e); }
        }

        async function deleteSharedLink(shareId) {
            if (!confirm('¿Quieres eliminar este enlace de tus recibidos?')) return;
            const delData = new FormData();
            delData.append('id', shareId);
            await fetch('api.php?action=delete_shared_link', { method: 'POST', body: delData });
            if (currentFilter.shared) loadLinks();
            updateNotifBadge();
        }

        async function refreshLinkImage(img, linkId) {
            img.onerror = null;
            img.style.display = 'none';
            try {
                const res = await fetch(`api.php?action=refresh_metadata&id=${linkId}`);
                const result = await res.json();
                if (result.success && result.data?.imagen_url) {
                    img.src = result.data.imagen_url;
                    img.onerror = () => img.style.display = 'none';
                    img.style.display = '';
                }
            } catch (e) { /* silencioso */ }
        }

        async function markAsSeen(shareId) {
            const formData = new FormData();
            formData.append('id', shareId);
            await fetch('api.php?action=mark_link_seen', { method: 'POST', body: formData });
            // Si estamos en la vista de compartidos, refrescar para quitar el indicador de 'unread'
            if (currentFilter.shared) loadLinks();
        }

        async function shareViaWhatsApp() {
            if (!currentShareData) return;
            const recipient = document.getElementById('waRecipient').value;
            
            // Registrar el compartido en el servidor (sin esperar la respuesta para no retrasar)
            const formData = new FormData();
            formData.append('id_enlace', currentShareData.id);
            formData.append('plataforma', 'whatsapp');
            formData.append('destinatario', recipient);
            fetch('api.php?action=log_external_share', { method: 'POST', body: formData });

            const text = `Mira lo que encontré: ${currentShareData.titulo}\n\n${currentShareData.url}\n\nTe lo comparto desde Montia.\nGuarda tus propios enlaces en: https://montia.mx`;
            const waUrl = `https://wa.me/?text=${encodeURIComponent(text)}`;
            // Anchor trick: window.open(_blank) no funciona en iOS PWA standalone
            const a = document.createElement('a');
            a.href = waUrl;
            a.target = '_blank';
            a.rel = 'noopener noreferrer';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            closeShareModal();
        }

        function filterCategory(type, el) {
            document.querySelectorAll('.cat-chip').forEach(c => c.classList.remove('active'));
            el.classList.add('active');
            
            if (!type) currentFilter = {};
            else if (type === 'fav') currentFilter = {fav: true};
            else if (type === 'shared') currentFilter = {shared: true};
            else if (type.cat) currentFilter = {cat: type.cat};

            loadLinks();
        }

        document.getElementById('addLinkForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const isEdit = formData.get('id') !== '';
            const action = isEdit ? 'update_link' : 'save_link';

            const btn = e.target.querySelector('button');
            const originalText = btn.innerHTML;
            btn.innerHTML = 'Procesando... <i class="fa-solid fa-spinner fa-spin"></i>';
            btn.disabled = true;

            try {
                const response = await fetch(`api.php?action=${action}`, {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                if (result.success) {
                    closeModal();
                    e.target.reset();
                    // Limpiar filtros al guardar
                    currentFilter = {};
                    loadCategories();
                    loadLinks();
                } else { 
                    alert("Error: " + result.error); 
                }
            } catch (error) { 
                console.error('Error:', error);
                alert("Error de conexión al servidor.");
            }
            finally { btn.innerHTML = originalText; btn.disabled = false; }
        });

        async function deleteLink(id) {
            if (!confirm('¿Seguro que quieres eliminar este enlace?')) return;
            
            const formData = new FormData();
            formData.append('id', id);
            formData.append('csrf_token', '<?php echo $_SESSION['csrf_token']; ?>');

            try {
                const response = await fetch('api.php?action=delete_link', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                if (result.success) {
                    loadLinks();
                } else { alert(result.error); }
            } catch (error) { console.error('Error:', error); }
        }

        window.addEventListener('scroll', () => {
            const header = document.querySelector('.sticky-top');
            if (window.scrollY > 20) {
                header.classList.add('header-scrolled');
            } else {
                header.classList.remove('header-scrolled');
            }
        });

        async function updateNotifBadge() {
            try {
                const response = await fetch('api.php?action=count_unread_shared');
                const result = await response.json();
                const badge = document.getElementById('notifBadge');
                if (result.success && result.data.total > 0) {
                    badge.innerText = result.data.total;
                    badge.style.display = 'flex';
                } else {
                    badge.style.display = 'none';
                }
            } catch (e) { console.error(e); }
        }

        // Manejar datos compartidos desde otras aplicaciones (Web Share Target)
        window.addEventListener('DOMContentLoaded', () => {
            const params = new URLSearchParams(window.location.search);
            const sharedUrl = params.get('url') || params.get('text');
            const sharedTitle = params.get('title');

            if (sharedUrl) {
                const urlMatch = sharedUrl.match(/https?:\/\/[^\s]+/);
                if (urlMatch) {
                    const cleanUrl = urlMatch[0];
                    openModal();
                    document.getElementById('editLinkUrl').value = cleanUrl;
                    if (sharedTitle || (sharedUrl !== cleanUrl)) {
                        const nota = sharedTitle || sharedUrl.replace(cleanUrl, '').trim();
                        document.querySelector('#addLinkForm textarea[name="notas"]').value = nota;
                    }
                    window.history.replaceState({}, document.title, window.location.pathname);
                }
            }
        });

        loadCategories();
        loadLinks();
        updateNotifBadge();
        setInterval(updateNotifBadge, 30000);

        // Pull-to-refresh
        const pullIndicator = document.getElementById('pullIndicator');
        let touchStartY = 0;
        let pulling = false;
        document.addEventListener('touchstart', (e) => {
            touchStartY = e.touches[0].clientY;
        }, { passive: true });
        document.addEventListener('touchmove', (e) => {
            if (window.scrollY === 0 && e.touches[0].clientY - touchStartY > 70) {
                pulling = true;
                pullIndicator.classList.add('visible');
            }
        }, { passive: true });
        document.addEventListener('touchend', () => {
            if (pulling) {
                pulling = false;
                loadCategories();
                loadLinks();
                updateNotifBadge();
                setTimeout(() => pullIndicator.classList.remove('visible'), 800);
            }
        });

        // Refrescar al volver desde WhatsApp u otra app
        document.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'visible') {
                loadLinks();
                updateNotifBadge();
            }
        });
        window.addEventListener('pageshow', (e) => {
            if (e.persisted) {
                loadLinks();
                updateNotifBadge();
            }
        });

        // Habilitar Scroll Horizontal con la Rueda del Mouse en PC
        const scrollContainer = document.querySelector(".categories-scroll");
        scrollContainer.addEventListener("wheel", (evt) => {
            evt.preventDefault();
            scrollContainer.scrollLeft += evt.deltaY * 2.5; // Multiplicador para velocidad
        });

        // Habilitar Drag-to-Scroll (Arrastrar con el mouse)
        let isDown = false;
        let startX;
        let scrollLeft;

        scrollContainer.addEventListener('mousedown', (e) => {
            isDown = true;
            scrollContainer.classList.add('active');
            startX = e.pageX - scrollContainer.offsetLeft;
            scrollLeft = scrollContainer.scrollLeft;
        });
        scrollContainer.addEventListener('mouseleave', () => {
            isDown = false;
        });
        scrollContainer.addEventListener('mouseup', () => {
            isDown = false;
        });
        scrollContainer.addEventListener('mousemove', (e) => {
            if(!isDown) return;
            e.preventDefault();
            const x = e.pageX - scrollContainer.offsetLeft;
            const walk = (x - startX) * 2; // Velocidad de arrastre
            scrollContainer.scrollLeft = scrollLeft - walk;
        });
    </script>
</body>
</html>
