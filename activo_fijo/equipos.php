<?php
require_once __DIR__ . '/../includes/auth.php';
$nombre = htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Usuario');
$rol = $_SESSION['usuario_rol'] ?? 'usuario';
$rolesRestringidos = ['almacenista', 'mantenimiento'];
$soloProductos = in_array($rol, $rolesRestringidos);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Equipos — AlmacenaTec</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        /* ── Badges de tipo ── */
        .badge-tipo {
            display: inline-block; padding: 3px 12px; border-radius: 20px; font-size: 11px; font-weight: 600;
        }
        .badge-tipo.pc { background: #dbeafe; color: #0b2d6b; }
        .badge-tipo.laptop { background: #d1fae5; color: #059669; }
        .badge-tipo.aire { background: #fef3c7; color: #d97706; }
        .badge-tipo.otro { background: #e5e7eb; color: #6b7280; }

        /* ── Badges de disponibilidad ── */
        .badge-disponible { color: #059669; font-size: 18px; }
        .badge-no-disponible { color: #dc2626; font-size: 18px; }

        /* ── Título de tabla ── */
        .tabla-titulo {
            font-family: 'Poppins', sans-serif;
            font-size: 18px;
            font-weight: 700;
            color: #0B2D6B;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 10px;
            padding-bottom: 10px;
            border-bottom: 3px solid #c9973a;
        }
        .tabla-titulo i {
            color: #c9973a;
            font-size: 22px;
        }
        .tabla-titulo small {
            font-size: 13px;
            font-weight: 400;
            color: #475569;
            margin-left: 8px;
        }

        /* ── Filtros ── */
        .filtros {
            display: flex; gap: 8px; flex-wrap: wrap; align-items: center;
        }
        .filtros .btn-filtro {
            padding: 5px 16px; border-radius: 20px; border: 1.5px solid #E2E8F0;
            background: #fff; font-size: 12px; font-weight: 600; cursor: pointer;
            transition: all 0.2s; color: #475569;
        }
        .filtros .btn-filtro:hover { border-color: #0B2D6B; color: #0B2D6B; }
        .filtros .btn-filtro.active { 
            background: #0B2D6B; 
            color: #fff; 
            border-color: #0B2D6B; 
        }
        .filtros .btn-filtro.active-pc { 
            background: #0b2d6b; 
            color: #fff; 
            border-color: #0b2d6b; 
        }
        .filtros .btn-filtro.active-laptop { 
            background: #059669; 
            color: #fff; 
            border-color: #059669; 
        }
        .filtros .btn-filtro.active-disponible { background: #059669; color: #fff; border-color: #059669; }
        .filtros .btn-filtro.active-no-disponible { background: #dc2626; color: #fff; border-color: #dc2626; }

        /* ── Pestañas ── */
        .tab-container {
            display: flex; gap: 4px; border-bottom: 2px solid #0B2D6B; margin-bottom: 16px;
        }
        .tab-btn {
            padding: 10px 24px; border: none; background: transparent; font-weight: 600;
            color: #94A3B8; cursor: pointer; border-bottom: 3px solid transparent;
            transition: all .2s; font-size: 14px;
        }
        .tab-btn:hover { color: #1E293B; }
        .tab-btn.active { color: #0B2D6B; border-bottom-color: #0B2D6B; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }

        /* ── Botón exportar ── */
        .btn-exportar {
            background: #16a34a; color: #fff; border: none; padding: 6px 16px;
            border-radius: 6px; font-size: 13px; font-weight: 600;
            cursor: pointer; transition: all .2s; display: inline-flex; align-items: center; gap: 6px;
        }
        .btn-exportar:hover { background: #15803d; color: #fff; }

        /* ── Tarjeta de equipo ── */
        .equipo-card {
            background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 10px;
            padding: 16px 20px; margin-bottom: 12px; transition: all 0.2s;
        }
        .equipo-card:hover { 
            box-shadow: 0 4px 14px rgba(0,0,0,.08); 
            border-color: #0B2D6B; 
        }
        .equipo-card .header { 
            display: flex; justify-content: space-between; align-items: center; 
            flex-wrap: wrap; gap: 8px; 
        }
        .equipo-card .header .titulo { 
            font-weight: 700; font-size: 16px; color: #1E293B; 
        }
        .equipo-card .detalles { 
            display: grid; grid-template-columns: 1fr 1fr 1fr; 
            gap: 8px; margin-top: 8px; font-size: 13px; 
        }
        .equipo-card .detalles .item { color: #94A3B8; }
        .equipo-card .detalles .item strong { color: #1E293B; font-weight: 600; }

        /* ── Sub-filtros ── */
        .filtro-sub {
            display: flex; gap: 6px; flex-wrap: wrap; align-items: center;
            padding: 8px 0 4px 0;
        }
        .filtro-sub .btn-subfiltro {
            padding: 3px 14px; border-radius: 20px; border: 1.5px solid #E2E8F0;
            background: #fff; font-size: 11px; font-weight: 600; cursor: pointer;
            transition: all 0.2s; color: #475569;
        }
        .filtro-sub .btn-subfiltro:hover { border-color: #0B2D6B; color: #0B2D6B; }
        .filtro-sub .btn-subfiltro.active { background: #0B2D6B; color: #fff; border-color: #0B2D6B; }
        .filtro-sub .btn-subfiltro.active-pc { background: #0b2d6b; color: #fff; border-color: #0b2d6b; }
        .filtro-sub .btn-subfiltro.active-laptop { background: #059669; color: #fff; border-color: #059669; }

        /* ── Card header ── */
        .card-header-almacenatec {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 20px;
            background: #FFFFFF;
            border-bottom: 1px solid #E2E8F0;
            flex-wrap: wrap;
            gap: 12px;
        }
        .card-header-almacenatec h3 {
            font-family: 'Poppins', sans-serif;
            font-size: 15px;
            font-weight: 700;
            color: #0B2D6B;
            margin: 0;
        }
        .card-header-almacenatec h3 i {
            color: #c9973a;
            margin-right: 8px;
        }

        /* ── Búsqueda ── */
        .search-almacenatec {
            position: relative;
            display: inline-flex;
            align-items: center;
        }
        .search-almacenatec i {
            position: absolute;
            left: 12px;
            color: #94A3B8;
        }
        .search-almacenatec input {
            padding: 8px 12px 8px 36px;
            border: 1.5px solid #E2E8F0;
            border-radius: 8px;
            font-size: 13px;
            font-family: 'Inter', sans-serif;
            background: #FFFFFF;
            color: #1E293B;
            transition: border-color 0.2s;
            min-width: 200px;
        }
        .search-almacenatec input:focus {
            outline: none;
            border-color: #0B2D6B;
            box-shadow: 0 0 0 3px rgba(11,45,107,.1);
        }

        /* ── Responsive ── */
        @media (max-width: 768px) { 
            .equipo-card .detalles { grid-template-columns: 1fr 1fr; } 
            .search-almacenatec input { min-width: 140px; }
        }
        @media (max-width: 480px) { 
            .equipo-card .detalles { grid-template-columns: 1fr; } 
        }
    </style>
</head>
<body>
<div class="app-layout">

    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">
                <div class="logo-mark">AT</div>
                <div class="logo-text">
                    <span class="logo-name">AlmacenaTec</span>
                    <span class="logo-sub">TecNM Saltillo</span>
                </div>
            </div>
        </div>
       <nav class="sidebar-nav">
    <div class="nav-section">Módulos</div>
    <a href="../home/index.php" class="sidebar-link"><i class="bi bi-house-door"></i> Inicio</a>
    
    <?php if (esJefeDepartamento()): ?>
    <a href="../jefe_departamento.php" class="sidebar-link"><i class="bi bi-building"></i> Mi Departamento</a>
    
    <?php elseif ($_SESSION['usuario_rol'] === 'finanzas'): ?>
    <a href="../orden_compra/compras.php" class="sidebar-link"><i class="bi bi-cart-check"></i> Compras</a>
    
    <?php elseif ($_SESSION['usuario_rol'] === 'activo_fijo' || $_SESSION['usuario_rol'] === 'almacen_general'): ?>
    <a href="../activo_fijo/index.php" class="sidebar-link"><i class="bi bi-safe-fill"></i> Activo Fijo</a>
    <a href="../activo_fijo/main_activF.php" class="sidebar-link"><i class="bi bi-clipboard2-data"></i> Historial Salidas</a>
    <a href="../orden_compra/requisiciones.php" class="sidebar-link"><i class="bi bi-cart3"></i> Almacén General</a>
 
    
    <?php elseif ($_SESSION['usuario_rol'] === 'almacenista' || $_SESSION['usuario_rol'] === 'mantenimiento'): ?>
    <a href="../productos/main_productos.php" class="sidebar-link"><i class="bi bi-box-seam"></i> Productos</a>
    
    <?php else: ?>
    <!-- Admin y Usuario: todos los módulos -->
    <a href="../activo_fijo/index.php" class="sidebar-link"><i class="bi bi-safe-fill"></i> Activo Fijo</a>
    <a href="../activo_fijo/main_activF.php" class="sidebar-link"><i class="bi bi-clipboard2-data"></i> Historial Salidas</a>
    <a href="../orden_compra/requisiciones.php" class="sidebar-link"><i class="bi bi-cart3"></i> Almacén General</a>
    <a href="../orden_compra/facturas_lista.php" class="sidebar-link"><i class="bi bi-file-earmark-text"></i> Facturas</a>
    <a href="../orden_compra/compras.php" class="sidebar-link"><i class="bi bi-cart-check"></i> Compras</a>
    <a href="../productos/main_productos.php" class="sidebar-link"><i class="bi bi-box-seam"></i> Productos</a>
    <?php if (isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'admin'): ?>
    <div class="nav-section" style="margin-top:8px">Administración</div>
    <a href="../usuarios/main_usuarios.php" class="sidebar-link"><i class="bi bi-people"></i> Usuarios</a>
    <?php endif; ?>
    <?php endif; ?>
</nav>
        <div class="sidebar-footer"><i class="bi bi-geo-alt-fill me-1"></i> TecNM · Campus Saltillo · 2025</div>
    </aside>

    <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

    <div class="main-wrapper" id="mainWrapper">
        <header class="topbar">
            <button class="topbar-toggle" onclick="toggleSidebar()"><i class="bi bi-list" style="font-size:22px"></i></button>
            <div class="topbar-info">
                <h1><i class="bi bi-laptop me-2" style="color:#0B2D6B"></i>Equipos</h1>
                <div class="topbar-breadcrumb"><a href="../home/index.php">Inicio</a> &rsaquo; <a href="index.php">Activo Fijo</a> &rsaquo; Equipos</div>
            </div>
            <div style="display:flex;align-items:center;gap:10px;flex-shrink:0">
                <span style="font-size:13px;color:#475569"><i class="bi bi-person-circle me-1"></i><?= $nombre ?></span>
                <a href="<?= BASE_PATH ?>logout.php" class="btn-accent-brand" style="padding:6px 12px;font-size:12px"><i class="bi bi-box-arrow-right me-1"></i> Salir</a>
            </div>
        </header>

        <main class="content">

            <!-- ============================================================ -->
            <!-- TÍTULO DE LA TABLA                                             -->
            <!-- ============================================================ -->
            <div class="tabla-titulo">
                <i class="bi bi-laptop"></i>
                Inventario de Equipos
                <small>PC, Laptops y Aires Acondicionados</small>
            </div>

            <!-- ============================================================ -->
            <!-- PESTAÑAS                                                      -->
            <!-- ============================================================ -->
            <div class="tab-container">
                <button class="tab-btn active" data-tab="pc_laptop" onclick="cambiarTab('pc_laptop', this)">
                    <i class="bi bi-laptop me-1"></i> PC / Laptops
                </button>
                <button class="tab-btn" data-tab="aire" onclick="cambiarTab('aire', this)">
                    <i class="bi bi-snow me-1"></i> Aires Acondicionados
                </button>
            </div>

            <!-- ============================================================ -->
            <!-- TAB 1: PC y Laptops                                           -->
            <!-- ============================================================ -->
            <div id="tab-pc_laptop" class="tab-content active">
                <div class="card-panel">
                    <div class="card-header-almacenatec">
                        <h3><i class="bi bi-laptop"></i>PC y Laptops</h3>
                        <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center">
                            <div class="search-almacenatec">
                                <i class="bi bi-search"></i>
                                <input type="text" id="searchPc" class="form-control" placeholder="Buscar…">
                            </div>
                            <button class="btn-exportar" onclick="exportarExcel()">
                                <i class="bi bi-file-earmark-excel me-1"></i> Exportar
                            </button>
                            <a href="index.php" class="btn-outline-brand" style="padding:6px 12px;font-size:12px;">
                                <i class="bi bi-arrow-left me-1"></i> Volver
                            </a>
                        </div>
                    </div>

                    <!-- Sub-filtro PC / Laptop -->
                    <div style="padding:8px 20px 12px 20px;border-bottom:1px solid #E2E8F0;background:#F0F4F9">
                        <div class="filtro-sub">
                            <span style="font-size:11px;font-weight:600;color:#475569;text-transform:uppercase;letter-spacing:.5px;margin-right:8px;">
                                <i class="bi bi-funnel me-1"></i>Tipo:
                            </span>
                            <button class="btn-subfiltro active" data-subfiltro="todos" onclick="filtrarSubTipo('todos', this)">Todos</button>
                            <button class="btn-subfiltro" data-subfiltro="pc" onclick="filtrarSubTipo('pc', this)">💻 PC</button>
                            <button class="btn-subfiltro" data-subfiltro="laptop" onclick="filtrarSubTipo('laptop', this)">🖥️ Laptop</button>
                        </div>
                    </div>

                    <div id="pcLaptopContainer" style="padding:16px 20px;">
                        <div class="text-center text-muted py-4">Cargando datos...</div>
                    </div>
                </div>
            </div>

            <!-- ============================================================ -->
            <!-- TAB 2: Aires Acondicionados                                   -->
            <!-- ============================================================ -->
            <div id="tab-aire" class="tab-content">
                <div class="card-panel">
                    <div class="card-header-almacenatec">
                        <h3><i class="bi bi-snow"></i>Aires Acondicionados</h3>
                        <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center">
                            <div class="search-almacenatec">
                                <i class="bi bi-search"></i>
                                <input type="text" id="searchAire" class="form-control" placeholder="Buscar…">
                            </div>
                            <button class="btn-exportar" onclick="exportarExcel()">
                                <i class="bi bi-file-earmark-excel me-1"></i> Exportar
                            </button>
                            <a href="index.php" class="btn-outline-brand" style="padding:6px 12px;font-size:12px;">
                                <i class="bi bi-arrow-left me-1"></i> Volver
                            </a>
                        </div>
                    </div>
                    <div id="aireContainer" style="padding:16px 20px;">
                        <div class="text-center text-muted py-4">Cargando datos...</div>
                    </div>
                </div>
            </div>

        </main>
    </div>
</div>

<script src="../js/bootstrap.bundle.min.js"></script>
<script>
    let todosLosEquipos = [];
    let subFiltroActual = 'todos';

    function toggleSidebar() {
        const s = document.getElementById('sidebar'), o = document.getElementById('sidebarOverlay'), w = document.getElementById('mainWrapper');
        if (window.innerWidth <= 1100) { const open = s.classList.toggle('open'); o.classList.toggle('visible', open); }
        else { s.classList.toggle('collapsed'); w.classList.toggle('sidebar-hidden'); }
    }
    function closeSidebar() { document.getElementById('sidebar').classList.remove('open'); document.getElementById('sidebarOverlay').classList.remove('visible'); }

    // ──────────────────────────────────────────────────────────────
    // CAMBIAR PESTAÑA
    // ──────────────────────────────────────────────────────────────
    function cambiarTab(tabId, btn) {
        document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
        document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
        document.getElementById('tab-' + tabId).classList.add('active');
        if (btn) btn.classList.add('active');
        // Resetear subfiltro al cambiar de pestaña
        if (tabId === 'pc_laptop') {
            subFiltroActual = 'todos';
            document.querySelectorAll('.btn-subfiltro').forEach(b => b.classList.remove('active', 'active-pc', 'active-laptop'));
            document.querySelector('.btn-subfiltro[data-subfiltro="todos"]').classList.add('active');
            renderPcLaptop();
        }
    }

    // ──────────────────────────────────────────────────────────────
    // FILTRAR SUB-TIPO (PC / LAPTOP / TODOS)
    // ──────────────────────────────────────────────────────────────
    function filtrarSubTipo(tipo, btn) {
        subFiltroActual = tipo;
        document.querySelectorAll('.btn-subfiltro').forEach(b => b.classList.remove('active', 'active-pc', 'active-laptop'));
        if (btn) {
            btn.classList.add('active');
            if (tipo === 'pc') btn.classList.add('active-pc');
            else if (tipo === 'laptop') btn.classList.add('active-laptop');
        }
        renderPcLaptop();
    }

    // ──────────────────────────────────────────────────────────────
    // CARGAR EQUIPOS
    // ──────────────────────────────────────────────────────────────
    async function fetchEquipos() {
        try {
            const res = await fetch('<?= BASE_PATH ?>includes/get_equipos.php');
            const data = await res.json();
            if (!data.ok) throw new Error(data.error);
            todosLosEquipos = data.equipos || [];
            renderPcLaptop();
            renderAire();
        } catch(err) {
            console.error(err);
            document.getElementById('pcLaptopContainer').innerHTML = '<div class="alert alert-danger">Error al cargar datos: ' + err.message + '</div>';
            document.getElementById('aireContainer').innerHTML = '<div class="alert alert-danger">Error al cargar datos: ' + err.message + '</div>';
        }
    }

    // ──────────────────────────────────────────────────────────────
    // RENDER PC / LAPTOP (con sub-filtro)
    // ──────────────────────────────────────────────────────────────
    function renderPcLaptop() {
        const container = document.getElementById('pcLaptopContainer');
        const busqueda = document.getElementById('searchPc').value.toLowerCase().trim();
        
        let equipos = todosLosEquipos.filter(e => e.tipo_equipo === 'pc' || e.tipo_equipo === 'laptop');
        
        // Aplicar sub-filtro
        if (subFiltroActual === 'pc') {
            equipos = equipos.filter(e => e.tipo_equipo === 'pc');
        } else if (subFiltroActual === 'laptop') {
            equipos = equipos.filter(e => e.tipo_equipo === 'laptop');
        }
        
        if (busqueda) {
            equipos = equipos.filter(e =>
                (e.descripcion_bien || '').toLowerCase().includes(busqueda) ||
                (e.marca || '').toLowerCase().includes(busqueda) ||
                (e.modelo || '').toLowerCase().includes(busqueda) ||
                (e.serie || '').toLowerCase().includes(busqueda) ||
                (e.num_oficina || '').toLowerCase().includes(busqueda) ||
                (e.memoria_ram || '').toLowerCase().includes(busqueda) ||
                (e.sistema_operativo || '').toLowerCase().includes(busqueda)
            );
        }

        if (equipos.length === 0) {
            const msg = subFiltroActual === 'pc' ? 'No hay PCs registradas.' : 
                        subFiltroActual === 'laptop' ? 'No hay Laptops registradas.' : 
                        'No hay PC o Laptops registradas.';
            container.innerHTML = '<div class="alert alert-info text-center py-4"><i class="bi bi-inbox" style="font-size:32px;display:block;margin-bottom:8px;"></i>' + msg + '</div>';
            return;
        }

        let html = '';
        const tipoLabelMap = { 'pc': '💻 PC', 'laptop': '🖥️ Laptop' };
        const tipoClassMap = { 'pc': 'pc', 'laptop': 'laptop' };
        
        for (const e of equipos) {
            const dispIcon = e.disponible ? 'bi-check-circle-fill' : 'bi-x-circle-fill';
            const dispClass = e.disponible ? 'badge-disponible' : 'badge-no-disponible';
            const tipoLabel = tipoLabelMap[e.tipo_equipo] || e.tipo_equipo;
            const tipoClass = tipoClassMap[e.tipo_equipo] || 'otro';
            
            html += `
                <div class="equipo-card">
                    <div class="header">
                        <div>
                            <span class="titulo">${e.descripcion_bien || 'Sin descripción'}</span>
                            <span class="badge-tipo ${tipoClass}">${tipoLabel}</span>
                            <span><i class="bi ${dispIcon} ${dispClass}"></i> ${e.disponible ? 'Disponible' : 'No disponible'}</span>
                        </div>
                        <div>
                            <span style="font-size:12px;color:#94A3B8;">Oficina: ${e.num_oficina || 'N/A'}</span>
                        </div>
                    </div>
                    <div class="detalles">
                        <div class="item"><strong>Marca:</strong> ${e.marca || 'N/A'}</div>
                        <div class="item"><strong>Modelo:</strong> ${e.modelo || 'N/A'}</div>
                        <div class="item"><strong>Serie:</strong> ${e.serie || 'N/A'}</div>
                        <div class="item"><strong>Memoria RAM:</strong> ${e.memoria_ram || 'N/A'}</div>
                        <div class="item"><strong>Sistema Operativo:</strong> ${e.sistema_operativo || 'N/A'}</div>
                        <div class="item"><strong>Disco Duro:</strong> ${e.tipo_disco || 'N/A'} ${e.capacidad_disco ? '(' + e.capacidad_disco + ')' : ''}</div>
                        <div class="item"><strong>Año Asignación:</strong> ${e.anio_asignacion || 'N/A'}</div>
                        <div class="item"><strong>Antigüedad:</strong> ${e.antiguedad !== null ? e.antiguedad + ' años' : 'N/A'}</div>
                        <div class="item"><strong>Departamento:</strong> ${e.departamento_recibe || 'N/A'}</div>
                    </div>
                </div>
            `;
        }
        container.innerHTML = html;
    }

    // ──────────────────────────────────────────────────────────────
    // RENDER AIRES ACONDICIONADOS
    // ──────────────────────────────────────────────────────────────
    function renderAire() {
        const container = document.getElementById('aireContainer');
        const busqueda = document.getElementById('searchAire').value.toLowerCase().trim();
        
        let equipos = todosLosEquipos.filter(e => e.tipo_equipo === 'aire');
        
        if (busqueda) {
            equipos = equipos.filter(e =>
                (e.descripcion_bien || '').toLowerCase().includes(busqueda) ||
                (e.marca || '').toLowerCase().includes(busqueda) ||
                (e.modelo || '').toLowerCase().includes(busqueda) ||
                (e.serie || '').toLowerCase().includes(busqueda) ||
                (e.num_oficina || '').toLowerCase().includes(busqueda) ||
                (e.aire_tipo || '').toLowerCase().includes(busqueda) ||
                (e.aire_ubicacion || '').toLowerCase().includes(busqueda)
            );
        }

        if (equipos.length === 0) {
            container.innerHTML = '<div class="alert alert-info text-center py-4"><i class="bi bi-inbox" style="font-size:32px;display:block;margin-bottom:8px;"></i>No hay Aires Acondicionados registrados.</div>';
            return;
        }

        let html = '';
        for (const e of equipos) {
            const dispIcon = e.disponible ? 'bi-check-circle-fill' : 'bi-x-circle-fill';
            const dispClass = e.disponible ? 'badge-disponible' : 'badge-no-disponible';
            const funcionaIcon = e.aire_funciona ? '✅ Sí' : '❌ No';
            
            html += `
                <div class="equipo-card">
                    <div class="header">
                        <div>
                            <span class="titulo">${e.descripcion_bien || 'Sin descripción'}</span>
                            <span class="badge-tipo aire">❄️ Aire Acondicionado</span>
                            <span><i class="bi ${dispIcon} ${dispClass}"></i> ${e.disponible ? 'Disponible' : 'No disponible'}</span>
                        </div>
                        <div>
                            <span style="font-size:12px;color:#94A3B8;">Oficina: ${e.num_oficina || 'N/A'}</span>
                        </div>
                    </div>
                    <div class="detalles">
                        <div class="item"><strong>Tipo:</strong> ${e.aire_tipo || 'N/A'}</div>
                        <div class="item"><strong>Capacidad:</strong> ${e.aire_capacidad || 'N/A'}</div>
                        <div class="item"><strong>Ubicación:</strong> ${e.aire_ubicacion || 'N/A'}</div>
                        <div class="item"><strong>Tecnología:</strong> ${e.aire_tecnologia || 'N/A'}</div>
                        <div class="item"><strong>Ciclo:</strong> ${e.aire_ciclo || 'N/A'}</div>
                        <div class="item"><strong>Funciona:</strong> ${funcionaIcon}</div>
                        <div class="item"><strong>Año Asignación:</strong> ${e.anio_asignacion || 'N/A'}</div>
                        <div class="item"><strong>Antigüedad:</strong> ${e.antiguedad !== null ? e.antiguedad + ' años' : 'N/A'}</div>
                        <div class="item"><strong>Marca:</strong> ${e.marca || 'N/A'}</div>
                        <div class="item"><strong>Modelo:</strong> ${e.modelo || 'N/A'}</div>
                        <div class="item"><strong>Serie:</strong> ${e.serie || 'N/A'}</div>
                        <div class="item"><strong>Departamento:</strong> ${e.departamento_recibe || 'N/A'}</div>
                    </div>
                </div>
            `;
        }
        container.innerHTML = html;
    }

    // ──────────────────────────────────────────────────────────────
    // EXPORTAR EXCEL
    // ──────────────────────────────────────────────────────────────
    function exportarExcel() {
        const tabActiva = document.querySelector('.tab-content.active');
        const isPc = tabActiva.id === 'tab-pc_laptop';
        
        let equipos = isPc ? 
            todosLosEquipos.filter(e => e.tipo_equipo === 'pc' || e.tipo_equipo === 'laptop') :
            todosLosEquipos.filter(e => e.tipo_equipo === 'aire');

        if (equipos.length === 0) {
            alert('No hay datos para exportar.');
            return;
        }

        let csv = isPc ? 
            'ID,Tipo,Oficina,Descripción,Marca,Modelo,Serie,Memoria RAM,Sistema Operativo,Tipo Disco,Capacidad Disco,Año Asignación,Antigüedad,Departamento,Disponible\n' :
            'ID,Tipo,Oficina,Descripción,Marca,Modelo,Serie,Tipo,Capacidad,Ubicación,Tecnología,Ciclo,Funciona,Año Asignación,Antigüedad,Departamento,Disponible\n';

        for (const e of equipos) {
            if (isPc) {
                csv += `${e.id},${e.tipo_equipo},${e.num_oficina || ''},"${e.descripcion_bien || ''}",${e.marca || ''},${e.modelo || ''},${e.serie || ''},${e.memoria_ram || ''},${e.sistema_operativo || ''},${e.tipo_disco || ''},${e.capacidad_disco || ''},${e.anio_asignacion || ''},${e.antiguedad || ''},${e.departamento_recibe || ''},${e.disponible ? 'Disponible' : 'No disponible'}\n`;
            } else {
                csv += `${e.id},aire,${e.num_oficina || ''},"${e.descripcion_bien || ''}",${e.marca || ''},${e.modelo || ''},${e.serie || ''},${e.aire_tipo || ''},${e.aire_capacidad || ''},${e.aire_ubicacion || ''},${e.aire_tecnologia || ''},${e.aire_ciclo || ''},${e.aire_funciona ? 'Sí' : 'No'},${e.anio_asignacion || ''},${e.antiguedad || ''},${e.departamento_recibe || ''},${e.disponible ? 'Disponible' : 'No disponible'}\n`;
            }
        }

        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = isPc ? 'pc_laptops.csv' : 'aires_acondicionados.csv';
        link.click();
        URL.revokeObjectURL(link.href);
    }

    // ──────────────────────────────────────────────────────────────
    // INICIALIZAR
    // ──────────────────────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', function() {
        fetchEquipos();
        document.getElementById('searchPc').addEventListener('input', renderPcLaptop);
        document.getElementById('searchAire').addEventListener('input', renderAire);
    });
</script>
</body>
</html>