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
    <title>Activo Fijo — AlmacenaTec</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        /* ── Badges ── */
        .badge-tipo {
            display: inline-block; padding: 4px 14px; border-radius: 20px;
            font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.3px;
        }
        .badge-tipo.entrada { background: #d1fae5; color: #059669; }
        .badge-tipo.salida { background: #fee2e2; color: #dc2626; }
        
        .badge-disponible { color: #059669; font-size: 18px; }
        .badge-no-disponible { color: #dc2626; font-size: 18px; }

        /* ── Filtros ── */
        .filtros-almacenatec {
            display: flex; gap: 10px; flex-wrap: wrap; align-items: center;
            padding: 12px 18px; background: #f8faff; border-radius: 8px;
            border: 1px solid #E2E8F0; margin-bottom: 16px;
        }
        .filtros-almacenatec .btn-filtro {
            padding: 5px 16px; border-radius: 20px; border: 1.5px solid #E2E8F0;
            background: #FFFFFF; font-size: 12px; font-weight: 600;
            cursor: pointer; transition: all 0.2s; color: #475569;
        }
        .filtros-almacenatec .btn-filtro:hover { border-color: #0B2D6B; color: #0B2D6B; }
        .filtros-almacenatec .btn-filtro.active { background: #0B2D6B; color: #FFFFFF; border-color: #0B2D6B; }
        .filtros-almacenatec .btn-filtro.active-disponible { background: #059669; color: #FFFFFF; border-color: #059669; }
        .filtros-almacenatec .btn-filtro.active-no-disponible { background: #dc2626; color: #FFFFFF; border-color: #dc2626; }

        /* ── Búsqueda ── */
        .search-almacenatec { position: relative; display: inline-flex; align-items: center; }
        .search-almacenatec i { position: absolute; left: 12px; color: #94A3B8; font-size: 15px; }
        .search-almacenatec input {
            padding: 8px 12px 8px 36px; border: 1.5px solid #E2E8F0; border-radius: 8px;
            font-size: 13px; font-family: 'Inter', sans-serif; background: #FFFFFF;
            color: #1E293B; transition: border-color 0.2s; min-width: 200px;
        }
        .search-almacenatec input:focus { outline: none; border-color: #0B2D6B; box-shadow: 0 0 0 3px rgba(11,45,107,.1); }

        /* ── Tabla ── */
        .table-almacenatec {
            width: 100%; border-collapse: collapse; font-size: 14px;
            background: #FFFFFF; border-radius: 10px; overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,.07), 0 4px 14px rgba(0,0,0,.05);
        }
        .table-almacenatec thead { background: #0B2D6B; color: #FFFFFF; }
        .table-almacenatec thead th {
            padding: 12px 16px; text-align: left; font-weight: 600;
            font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;
            border-bottom: 3px solid #c9973a; white-space: nowrap;
        }
        .table-almacenatec thead th i { margin-right: 6px; color: #c9973a; }
        .table-almacenatec tbody td {
            padding: 10px 16px; border-bottom: 1px solid #E2E8F0;
            color: #1E293B; vertical-align: middle;
        }
        .table-almacenatec tbody tr:hover { background: #F0F4F9; }
        .table-almacenatec tbody tr:last-child td { border-bottom: none; }
        .table-almacenatec .text-muted { color: #94A3B8 !important; text-align: center; padding: 30px 0; }

        /* ── Tabla responsiva ── */
        .table-responsive-almacenatec { overflow-x: auto; border-radius: 10px; }

        /* ── Título de tabla ── */
        .tabla-titulo {
            font-family: 'Poppins', sans-serif; font-size: 18px; font-weight: 700;
            color: #0B2D6B; margin-bottom: 16px; display: flex; align-items: center;
            gap: 10px; padding-bottom: 10px; border-bottom: 3px solid #c9973a;
        }
        .tabla-titulo i { color: #c9973a; font-size: 22px; }
        .tabla-titulo small { font-size: 13px; font-weight: 400; color: #475569; margin-left: 8px; }

        /* ── Card header ── */
        .card-header-almacenatec {
            display: flex; justify-content: space-between; align-items: center;
            padding: 14px 20px; background: #FFFFFF; border-bottom: 1px solid #E2E8F0;
            flex-wrap: wrap; gap: 12px;
        }
        .card-header-almacenatec h3 {
            font-family: 'Poppins', sans-serif; font-size: 15px; font-weight: 700;
            color: #0B2D6B; margin: 0;
        }
        .card-header-almacenatec h3 i { color: #c9973a; margin-right: 8px; }

        /* ── Botones ── */
        .btn-almacenatec {
            background: #0B2D6B; color: #fff; border: none; padding: 9px 18px;
            border-radius: 6px; font-size: 13.5px; font-weight: 600; cursor: pointer;
            transition: all .2s; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;
        }
        .btn-almacenatec:hover { background: #071d46; color: #fff; }
        
        .btn-etiqueta-ver {
            background: transparent; color: #0B2D6B; border: 1.5px solid #0B2D6B;
            padding: 5px 12px; border-radius: 6px; font-size: 11.5px; font-weight: 600;
            cursor: pointer; transition: all 0.2s; display: inline-flex; align-items: center;
            gap: 4px; white-space: nowrap;
        }
        .btn-etiqueta-ver:hover { background: #0B2D6B; color: #fff; }

        /* ── Pestañas ── */
        .tab-container { display: flex; gap: 4px; border-bottom: 2px solid #E2E8F0; margin-bottom: 16px; }
        .tab-btn {
            padding: 10px 24px; border: none; background: transparent; font-weight: 600;
            color: #475569; cursor: pointer; border-bottom: 3px solid transparent;
            transition: all .2s; font-size: 14px;
        }
        .tab-btn:hover { color: #1E293B; }
        .tab-btn.active { color: #0B2D6B; border-bottom-color: #0B2D6B; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
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
            <a href="index.php" class="sidebar-link active"><i class="bi bi-safe-fill"></i> Activo Fijo</a>
            <a href="main_activF.php" class="sidebar-link"><i class="bi bi-clipboard2-data"></i> Historial Salidas</a>
            <a href="requisiciones_af.php" class="sidebar-link"><i class="bi bi-cart3"></i> Almacén General</a>
      

            
                    <?php if ($rol !== 'almacen_general' && $rol !== 'activo_fijo'): ?>
            <a href="../productos/main_productos.php" class="sidebar-link"><i class="bi bi-box-seam"></i> Productos</a>
            <?php endif; ?>
            <?php if ($rol === 'admin'): ?>
            <div class="nav-section" style="margin-top:8px">Administración</div>
            <a href="../usuarios/main_usuarios.php" class="sidebar-link"><i class="bi bi-people"></i> Usuarios</a>
            <?php endif; ?>
        </nav>
        <div class="sidebar-footer"><i class="bi bi-geo-alt-fill me-1"></i> TecNM · Campus Saltillo · 2025</div>
    </aside>

    <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

    <div class="main-wrapper" id="mainWrapper">
        <header class="topbar">
            <button class="topbar-toggle" onclick="toggleSidebar()"><i class="bi bi-list" style="font-size:22px"></i></button>
            <div class="topbar-info">
                <h1><i class="bi bi-safe-fill me-2" style="color:#059669"></i>Activo Fijo</h1>
                <div class="topbar-breadcrumb"><a href="../home/index.php">Inicio</a> &rsaquo; Activo Fijo</div>
            </div>
            <div style="display:flex;align-items:center;gap:10px;flex-shrink:0">
                <span style="font-size:13px;color:var(--c-text-md)"><i class="bi bi-person-circle me-1"></i><?= $nombre ?></span>
                <a href="<?= BASE_PATH ?>logout.php" class="btn-accent-brand" style="padding:6px 12px;font-size:12px"><i class="bi bi-box-arrow-right me-1"></i> Salir</a>
            </div>
        </header>

        <main class="content">
            <div class="page-header">
                <h2><i class="bi bi-safe-fill me-2" style="color:#0B2D6B"></i>Gestión de Activos Fijos</h2>
                <div style="display:flex;gap:8px;flex-wrap:wrap">
                    <button onclick="location.href='capturar.php?tipo=entrada'" class="btn-almacenatec">
                        <i class="bi bi-arrow-down-circle" style="color:#c9973a;"></i> Entrada
                    </button>
                    <button onclick="location.href='capturar.php?tipo=salida'" class="btn-almacenatec">
                        <i class="bi bi-arrow-up-circle" style="color:#c9973a;"></i> Salida
                    </button>
                    <a href="equipos.php" class="btn-almacenatec">
                        <i class="bi bi-laptop" style="color:#c9973a;"></i> Equipos
                    </a>
                    <a href="transferir.php" class="btn-almacenatec">
                        <i class="bi bi-arrow-left-right" style="color:#c9973a;"></i> Transferir
                    </a>
                    <a href="historial_transferencias.php" class="btn-almacenatec">
                        <i class="bi bi-clock-history" style="color:#c9973a;"></i> Historial Transferencias
                    </a>
                   
                    <button onclick="exportarExcel()" style="background:#16a34a;color:#fff;border:none;padding:9px 18px;border-radius:8px;font-size:13.5px;font-weight:600;cursor:pointer;display:inline-flex;align-items:center;gap:6px;" onmouseover="this.style.background='#15803d'" onmouseout="this.style.background='#16a34a'">
                        <i class="bi bi-file-earmark-excel"></i> Exportar
                    </button>
                </div>
            </div>

            <div class="tab-container">
                <button class="tab-btn active" data-tab="movimientos" onclick="cambiarTab('movimientos', this)">
                    <i class="bi bi-clock-history me-1"></i> Movimientos
                </button>
                <button class="tab-btn" data-tab="inventario" onclick="cambiarTab('inventario', this)">
                    <i class="bi bi-grid-3x3-gap-fill me-1"></i> Inventario
                </button>
            </div>

            <!-- ============================================================ -->
            <!-- TAB 1: MOVIMIENTOS                                            -->
            <!-- ============================================================ -->
            <div id="tab-movimientos" class="tab-content active">
                <div class="tabla-titulo">
                    <i class="bi bi-clock-history"></i>
                    Historial de Movimientos
                    <small>Registro de entradas y salidas de activos</small>
                </div>

                <div class="card-panel">
                    <div class="card-header-almacenatec">
                        <h3><i class="bi bi-table"></i>Lista de Movimientos</h3>
                        <div class="search-almacenatec">
                            <i class="bi bi-search"></i>
                            <input type="text" id="searchItemMov" placeholder="Buscar por oficina, descripción…">
                        </div>
                    </div>

                    <div class="filtros-almacenatec">
                        <span style="font-size:12px;font-weight:600;color:#475569;text-transform:uppercase;letter-spacing:.5px;">
                            <i class="bi bi-funnel"></i> Filtrar:
                        </span>
                        <button class="btn-filtro active" data-filtro="todos" onclick="filtrarMovimientos('todos', this)">Todos</button>
                        <button class="btn-filtro" data-filtro="entrada" onclick="filtrarMovimientos('entrada', this)">Entradas</button>
                        <button class="btn-filtro" data-filtro="salida" onclick="filtrarMovimientos('salida', this)">Salidas</button>
                    </div>

                    <div class="table-responsive-almacenatec">
                        <table class="table-almacenatec">
                            <thead>
                                <tr>
                                    <th><i class="bi bi-hash"></i> ID</th>
                                    <th><i class="bi bi-tag"></i> Tipo</th>
                                    <th><i class="bi bi-building"></i> Núm. Oficina</th>
                                    <th><i class="bi bi-calendar"></i> Fecha</th>
                                    <th><i class="bi bi-person"></i> Jefe Dpto.</th>
                                    <th><i class="bi bi-file-text"></i> Descripción</th>
                                    <th><i class="bi bi-diagram-2"></i> Departamento</th>
                                    <th><i class="bi bi-check-circle"></i> Disponible</th>
                                </tr>
                            </thead>
                            <tbody id="movimientosTableBody">
                                <tr><td colspan="8" class="text-muted">Cargando datos...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- ============================================================ -->
            <!-- TAB 2: INVENTARIO                                             -->
            <!-- ============================================================ -->
            <div id="tab-inventario" class="tab-content">
                <div class="tabla-titulo">
                    <i class="bi bi-grid-3x3-gap-fill"></i>
                    Inventario de Activos Fijos
                    <small>Lista de activos disponibles y no disponibles</small>
                </div>

                <div class="card-panel">
                    <div class="card-header-almacenatec">
                        <h3><i class="bi bi-table"></i>Lista de Activos</h3>
                        <div class="search-almacenatec">
                            <i class="bi bi-search"></i>
                            <input type="text" id="searchItemInv" placeholder="Buscar por oficina, descripción, serie…">
                        </div>
                    </div>

                    <div class="filtros-almacenatec">
                        <span style="font-size:12px;font-weight:600;color:#475569;text-transform:uppercase;letter-spacing:.5px;">
                            <i class="bi bi-funnel"></i> Filtrar:
                        </span>
                        <button class="btn-filtro active" data-filtro="todos" onclick="filtrarInventario('todos', this)">Todos</button>
                        <button class="btn-filtro" data-filtro="disponible" onclick="filtrarInventario('disponible', this)">✅ Disponibles</button>
                        <button class="btn-filtro" data-filtro="no_disponible" onclick="filtrarInventario('no_disponible', this)">❌ No disponibles</button>
                    </div>

                    <!-- 12 Columnas exactas -->
                    <div class="table-responsive-almacenatec">
                        <table class="table-almacenatec" id="inventarioTable">
                            <thead>
                                <tr>
                                    <th><i class="bi bi-hash"></i> ID</th>
                                    <th><i class="bi bi-file-text"></i> Folio</th>
                                    <th><i class="bi bi-building"></i> Núm. Oficina</th>
                                    <th><i class="bi bi-calendar"></i> Fecha</th>
                                    <th><i class="bi bi-file-text"></i> Descripción</th>
                                    <th><i class="bi bi-tag"></i> Marca</th>
                                    <th><i class="bi bi-tag"></i> Modelo</th>
                                    <th><i class="bi bi-hash"></i> Serie</th>
                                    <th><i class="bi bi-diagram-2"></i> Departamento</th>
                                    <th><i class="bi bi-check-circle"></i> Disponible</th>
                                    <th><i class="bi bi-tags"></i> Tipo Compra</th>
                                    <th><i class="bi bi-tag"></i> Etiqueta</th>
                                </tr>
                            </thead>
                            <tbody id="inventarioTableBody">
                                <tr><td colspan="12" class="text-muted">Cargando datos...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- ============================================================ -->
<!-- MODAL PARA GENERAR ETIQUETAS                                 -->
<!-- ============================================================ -->
<div class="modal fade" id="modalGenerarEtiqueta" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg"> 
        <div class="modal-content">
            <div class="modal-header" style="background:#0B2D6B;color:#fff;">
                <h5 class="modal-title" style="color:#fff;"><i class="bi bi-tag me-2"></i>Generar Etiquetas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter:invert(1);"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info" id="infoActivoEtiqueta" style="font-size:13px;"></div>

                <input type="hidden" id="etq_activo_id">
                
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label">N. Inventario Base <span class="text-danger">*</span></label>
                        <input type="text" id="etq_base" class="form-control" placeholder="Ej: 180000064">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Año <span class="text-danger">*</span></label>
                        <input type="text" id="etq_anio" class="form-control" value="<?= date('Y') ?>" maxlength="4">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Licitación (Opcional)</label>
                        <input type="text" id="etq_licitacion" class="form-control" placeholder="Ej: E019-2024">
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-2">
                    <label class="form-label mb-0 fw-bold">Asignación de Etiquetas por Usuario/Área</label>
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="agregarFilaUsuario()">
                        <i class="bi bi-plus-lg"></i> Agregar Usuario
                    </button>
                </div>
                
                <div id="listaUsuarios" class="border rounded p-3 bg-light">
                    <!-- Aquí se inyectan las filas con JS -->
                </div>
                
                <div id="etq_error" class="alert alert-danger" style="display:none;margin-top:12px;font-size:13px;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn-almacenatec" id="btnGuardarEtiqueta" onclick="guardarEtiqueta()">
                    <i class="bi bi-save me-1"></i> Generar e Imprimir
                </button>
            </div>
        </div>
    </div>
</div>

<script src="../js/bootstrap.bundle.min.js"></script>
<script>
    function toggleSidebar() {
        const s = document.getElementById('sidebar'), o = document.getElementById('sidebarOverlay'), w = document.getElementById('mainWrapper');
        if (window.innerWidth <= 1100) { const open = s.classList.toggle('open'); o.classList.toggle('visible', open); }
        else { s.classList.toggle('collapsed'); w.classList.toggle('sidebar-hidden'); }
    }
    function closeSidebar() {
        document.getElementById('sidebar').classList.remove('open');
        document.getElementById('sidebarOverlay').classList.remove('visible');
    }

    function cambiarTab(tabId, btn) {
        document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
        document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
        document.getElementById('tab-' + tabId).classList.add('active');
        if (btn) btn.classList.add('active');
    }

    function getOrCreateErrorDiv(containerId) {
        const container = document.getElementById(containerId);
        if (!container) return null;
        let d = container.parentNode.querySelector('.fetchError');
        if (!d) {
            d = document.createElement('div');
            d.className = 'fetchError';
            d.style.cssText = 'margin:10px 0;color:#856404;background:#fff3cd;padding:8px 12px;border-radius:6px;display:none;font-size:13px;';
            container.parentNode.insertBefore(d, container);
        }
        return d;
    }

    // ──────────────────────────────────────────────────────────────
    // MOVIMIENTOS
    // ──────────────────────────────────────────────────────────────
    let todosLosMovimientos = [];
    let filtroMovimientos = 'todos';

  async function fetchMovimientos() {
    const errDiv = getOrCreateErrorDiv('movimientosTableBody');
    try {
        // 🔥 ESTA ES LA RUTA CORRECTA 🔥
        const res = await fetch('<?= BASE_PATH ?>includes/get_activos_movimientos.php');
        const data = await res.json();
        
        if (!data.ok) throw new Error(data.error || 'Error desconocido');
        if (errDiv) errDiv.style.display = 'none';
        
        todosLosMovimientos = data.activos || [];
        renderMovimientos(todosLosMovimientos);
    } catch(err) {
        if (errDiv) { 
            errDiv.style.display = 'block'; 
            errDiv.textContent = 'Error: ' + err.message; 
        }
    }
}
    function renderMovimientos(rows) {
        const tbody = document.getElementById('movimientosTableBody');
        tbody.innerHTML = '';
        if (!rows || rows.length === 0) {
            tbody.innerHTML = '<tr><td colspan="8" class="text-muted">No hay movimientos registrados.</td></tr>';
            return;
        }
        for (const a of rows) {
            const tr = document.createElement('tr');
            const tipoClass = a.tipo === 'entrada' ? 'entrada' : 'salida';
            const tipoIcon = a.tipo === 'entrada' ? 'bi-arrow-down-circle' : 'bi-arrow-up-circle';
            const dispIcon = a.disponible ? 'bi-check-circle-fill' : 'bi-x-circle-fill';
            const dispClass = a.disponible ? 'badge-disponible' : 'badge-no-disponible';
            tr.innerHTML = `
                <td><span style="font-weight:600;color:#0B2D6B">${a.id}</span></td>
                <td><span class="badge-tipo ${tipoClass}"><i class="bi ${tipoIcon} me-1"></i>${a.tipo}</span></td>
                <td>${a.num_oficina || ''}</td>
                <td><small style="color:#475569">${a.fecha || ''}</small></td>
                <td>${a.jefe_departamento || ''}</td>
                <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="${a.descripcion_bien || ''}">${a.descripcion_bien || ''}</td>
                <td>${a.departamento_recibe || ''}</td>
                <td><i class="bi ${dispIcon} ${dispClass}"></i></td>
            `;
            tbody.appendChild(tr);
        }
    }

    function filtrarMovimientos(tipo, btn) {
        filtroMovimientos = tipo;
        document.querySelectorAll('#tab-movimientos .btn-filtro').forEach(b => b.classList.remove('active'));
        if (btn) btn.classList.add('active');
        let filtradas = [...todosLosMovimientos];
        if (tipo !== 'todos') filtradas = filtradas.filter(a => a.tipo === tipo);
        renderMovimientos(filtradas);
    }

    // ──────────────────────────────────────────────────────────────
    // INVENTARIO
    // ──────────────────────────────────────────────────────────────
    let todosLosActivos = [];
    let filtroInventario = 'todos';

    async function fetchInventario() {
        const errDiv = getOrCreateErrorDiv('inventarioTableBody');
        try {
            const res = await fetch('<?= BASE_PATH ?>includes/get_activos_inventario.php');
            const data = await res.json();
            if (!data.ok) throw new Error(data.error || 'Error desconocido');
            if (errDiv) errDiv.style.display = 'none';
            todosLosActivos = data.activos || [];
            renderInventario(todosLosActivos);
        } catch(err) {
            if (errDiv) { errDiv.style.display = 'block'; errDiv.textContent = 'Error al cargar inventario: ' + err.message; }
        }
    }

    function renderInventario(rows) {
        const tbody = document.getElementById('inventarioTableBody');
        tbody.innerHTML = '';
        if (!rows || rows.length === 0) {
            tbody.innerHTML = '<tr><td colspan="12" class="text-muted">No hay activos fijos registrados.</td></tr>';
            return;
        }
        for (const a of rows) {
            const tr = document.createElement('tr');
            const dispIcon = a.disponible ? 'bi-check-circle-fill' : 'bi-x-circle-fill';
            const dispClass = a.disponible ? 'badge-disponible' : 'badge-no-disponible';
            
            const tipoClass = a.tipo === 'entrada' ? 'entrada' : 'salida';
            const tipoIcon = a.tipo === 'entrada' ? 'bi-arrow-down-circle' : 'bi-arrow-up-circle';
            
            let etiquetaHtml = '';
            if (a.num_inventario_completo && String(a.num_inventario_completo).trim() !== '') {
                etiquetaHtml = `
                    <button class="btn-etiqueta-ver" onclick="verEtiquetasActivo(${a.id})" title="Ver todas las etiquetas">
                        <i class="bi bi-tag me-1"></i> Ver Etiquetas
                    </button>
                `;
            } else {
                const cant = a.cantidad_unidades || 1;
                etiquetaHtml = `
                    <button class="btn-etiqueta-ver" onclick="abrirModalEtiqueta(${a.id}, ${cant})" title="Generar etiquetas">
                        <i class="bi bi-plus-circle me-1"></i> Generar (${cant})
                    </button>
                `;
            }

            tr.innerHTML = `
                <td><span style="font-weight:600;color:#0B2D6B">${a.id}</span></td>
                <td><span class="badge-tipo ${tipoClass}"><i class="bi ${tipoIcon} me-1"></i>${a.folio || ''}</span></td>
                <td>${a.num_oficina || ''}</td>
                <td><small style="color:#475569">${a.fecha || ''}</small></td>
                <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="${a.descripcion_bien || ''}">${a.descripcion_bien || ''}</td>
                <td>${a.marca || ''}</td>
                <td>${a.modelo || ''}</td>
                <td>${a.serie || ''}</td>
                <td>${a.departamento_recibe || ''}</td>
                <td><i class="bi ${dispIcon} ${dispClass}"></i></td>
                <td>${a.tipo_compra ? a.tipo_compra.replace('_', ' ').toUpperCase() : 'REGULAR'}</td>
                <td>${etiquetaHtml}</td>
            `;
            tbody.appendChild(tr);
        }
    }

    function filtrarInventario(tipo, btn) {
        filtroInventario = tipo;
        document.querySelectorAll('#tab-inventario .btn-filtro').forEach(b => b.classList.remove('active', 'active-disponible', 'active-no-disponible'));
        if (btn) {
            btn.classList.add('active');
            if (tipo === 'disponible') btn.classList.add('active-disponible');
            else if (tipo === 'no_disponible') btn.classList.add('active-no-disponible');
        }
        let filtradas = [...todosLosActivos];
        if (tipo === 'disponible') filtradas = filtradas.filter(a => a.disponible == 1);
        else if (tipo === 'no_disponible') filtradas = filtradas.filter(a => a.disponible == 0);
        renderInventario(filtradas);
    }

    // ──────────────────────────────────────────────────────────────
    // GENERACIÓN DE ETIQUETAS (USUARIOS MÚLTIPLES)
    // ──────────────────────────────────────────────────────────────
    let cantidadTotalEtiquetas = 1;

    function abrirModalEtiqueta(activoId, cantidad) {
        cantidadTotalEtiquetas = cantidad || 1;
        document.getElementById('etq_activo_id').value = activoId;
        document.getElementById('etq_error').style.display = 'none';
        document.getElementById('listaUsuarios').innerHTML = '';

        const activo = todosLosActivos.find(a => a.id == activoId);
        const infoDiv = document.getElementById('infoActivoEtiqueta');
        
        if (activo) {
            infoDiv.innerHTML = `
                <i class="bi bi-info-circle me-1"></i>
                <strong>${activo.descripcion_bien || ''}</strong><br>
                <small>Este activo requiere un total de <strong>${cantidadTotalEtiquetas} etiqueta(s)</strong>.</small>
            `;
            document.getElementById('etq_base').value = activo.num_inventario_base || '';
            document.getElementById('etq_anio').value = activo.num_inventario_anio || new Date().getFullYear();
            document.getElementById('etq_licitacion').value = activo.licitacion || '';
        }

        // Primera fila por defecto
        agregarFilaUsuario(activo ? activo.usuario_etiqueta || activo.jefe_departamento : '', activo ? activo.area_etiqueta || activo.departamento_recibe : '', cantidadTotalEtiquetas);

        new bootstrap.Modal(document.getElementById('modalGenerarEtiqueta')).show();
    }

    function agregarFilaUsuario(user = '', area = '', cant = '') {
        const container = document.getElementById('listaUsuarios');
        const row = document.createElement('div');
        row.className = 'row g-2 mb-2 align-items-end fila-usuario';
        row.innerHTML = `
            <div class="col-md-5">
                <label class="form-label" style="font-size:11px;margin-bottom:2px;">Usuario</label>
                <input type="text" class="form-control form-control-sm inp-usuario" value="${user}" placeholder="Nombre" required>
            </div>
            <div class="col-md-4">
                <label class="form-label" style="font-size:11px;margin-bottom:2px;">Área</label>
                <input type="text" class="form-control form-control-sm inp-area" value="${area}" placeholder="Área" required>
            </div>
            <div class="col-md-2">
                <label class="form-label" style="font-size:11px;margin-bottom:2px;">Cantidad</label>
                <input type="number" class="form-control form-control-sm inp-cantidad" value="${cant}" min="1" onchange="validarSumaCantidades()" required>
            </div>
            <div class="col-md-1 text-end">
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="this.parentElement.parentElement.remove(); validarSumaCantidades();">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        `;
        container.appendChild(row);
        validarSumaCantidades();
    }

    function validarSumaCantidades() {
        let suma = 0;
        document.querySelectorAll('.inp-cantidad').forEach(inp => {
            suma += parseInt(inp.value) || 0;
        });
        
        const btn = document.getElementById('btnGuardarEtiqueta');
        const errDiv = document.getElementById('etq_error');

        if (suma !== cantidadTotalEtiquetas) {
            errDiv.innerHTML = `⚠️ La suma de cantidades asignadas (${suma}) debe ser exactamente igual al total requerido (${cantidadTotalEtiquetas}).`;
            errDiv.style.display = 'block';
            btn.disabled = true;
        } else {
            errDiv.style.display = 'none';
            btn.disabled = false;
        }
    }

    async function guardarEtiqueta() {
        const activoId = document.getElementById('etq_activo_id').value;
        const base = document.getElementById('etq_base').value.trim();
        const anio = document.getElementById('etq_anio').value.trim();
        const licitacion = document.getElementById('etq_licitacion').value.trim();
        
        const usuarios = [];
        document.querySelectorAll('.fila-usuario').forEach(row => {
            usuarios.push({
                usuario: row.querySelector('.inp-usuario').value.trim(),
                area: row.querySelector('.inp-area').value.trim(),
                cantidad: parseInt(row.querySelector('.inp-cantidad').value)
            });
        });

        const btn = document.getElementById('btnGuardarEtiqueta');
        btn.disabled = true;
        btn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i> Generando...';

        try {
            const formData = new URLSearchParams();
            formData.append('activo_id', activoId);
            formData.append('base', base);
            formData.append('anio', anio);
            formData.append('licitacion', licitacion);
            formData.append('usuarios', JSON.stringify(usuarios)); // Enviamos el arreglo JSON

            const res = await fetch('<?= BASE_PATH ?>handlers/guardar_etiqueta.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: formData.toString()
            });

            const data = await res.json();
            if (!data.ok) throw new Error(data.error || 'Error al generar');

            bootstrap.Modal.getInstance(document.getElementById('modalGenerarEtiqueta')).hide();
            await fetchInventario();
            mostrarVentanaEtiquetas(data.etiquetas);

        } catch (err) {
            const errDiv = document.getElementById('etq_error');
            errDiv.textContent = err.message;
            errDiv.style.display = 'block';
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-save me-1"></i> Generar e Imprimir';
        }
    }

    async function verEtiquetasActivo(activoId) {
        try {
            const res = await fetch('<?= BASE_PATH ?>handlers/get_etiquetas_activo.php?activo_id=' + activoId);
            const data = await res.json();
            if (!data.ok) throw new Error(data.error);
            if (!data.etiquetas || data.etiquetas.length === 0) {
                alert('Este activo no tiene etiquetas generadas.');
                return;
            }
            mostrarVentanaEtiquetas(data.etiquetas);
        } catch (err) {
            alert('Error de conexión: ' + err.message);
        }
    }

    // ──────────────────────────────────────────────────────────────
    // VENTANA DE IMPRESIÓN (3 columnas x 10 filas = 30 por hoja)
    // ──────────────────────────────────────────────────────────────
        function mostrarVentanaEtiquetas(etiquetas) {
        const ventana = window.open('', '_blank', 'width=1000,height=800');
        if (!ventana) {
            alert('Permite ventanas emergentes para ver las etiquetas');
            return;
        }

        let etiquetasHtml = '';
        for (const e of etiquetas) {
            // DISEÑO COMPACTO: 6.7cm x 2.5cm CON CÓDIGO DE BARRAS
            etiquetasHtml += `
                <div class="etiqueta">
                    <div class="header">INSTITUTO TECNOLÓGICO DE SALTILLO</div>
                    <div class="body">
                        <div class="campo"><strong>N. INV:</strong> <span>${e.num_inventario_completo}</span></div>
                        <div class="campo"><strong>USUARIO:</strong> <span>${e.usuario_etiqueta || '—'}</span></div>
                        <div class="campo"><strong>ÁREA:</strong> <span>${e.area_etiqueta || '—'}</span></div>
                        ${e.licitacion ? `<div class="campo"><strong>LIC:</strong> <span>${e.licitacion}</span></div>` : ''}
                        <div class="barcode-container">
                            <svg class="barcode" data-code="${e.num_inventario_completo}"></svg>
                        </div>
                    </div>
                </div>
            `;
        }

        ventana.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="UTF-8">
                <title>Impresión de Etiquetas</title>
                <style>
                    @page {
                        size: letter portrait;
                        margin: 10mm; 
                    }
                    * { margin: 0; padding: 0; box-sizing: border-box; font-family: Arial, sans-serif; }
                    body { background: #525659; padding: 20px; }
                    
                    .toolbar {
                        position: sticky; top: 10px; background: #fff; padding: 12px;
                        border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,.3);
                        margin-bottom: 20px; display: flex; gap: 10px;
                        justify-content: center; z-index: 100;
                    }
                    .toolbar button {
                        padding: 10px 20px; border-radius: 6px; font-size: 14px;
                        font-weight: bold; cursor: pointer; border: none;
                    }
                    .btn-print { background: #0B2D6B; color: #fff; }
                    .btn-close { background: #e2e8f0; color: #1e293b; }

                    /* GRID PARA IMPRESIÓN: 3 COLUMNAS, 10 FILAS */
                    .hoja {
                        background: #fff;
                        width: 215mm;
                        min-height: 279mm;
                        margin: 0 auto;
                        padding: 5mm;
                        display: grid;
                        grid-template-columns: repeat(3, 67mm); /* 6.7cm */
                        grid-auto-rows: 25mm; /* 2.5cm */
                        gap: 2mm;
                    }

                    /* DISEÑO DE LA ETIQUETA COMPACTA */
                    .etiqueta {
                        width: 67mm;
                        height: 25mm;
                        border: 1px dashed #999;
                        padding: 2mm;
                        display: flex;
                        flex-direction: column;
                        overflow: hidden;
                    }
                    .etiqueta .header {
                        font-size: 6pt;
                        font-weight: bold;
                        color: #0B2D6B;
                        text-align: center;
                        border-bottom: 1px solid #c9973a;
                        padding-bottom: 0.5mm;
                        margin-bottom: 1mm;
                        text-transform: uppercase;
                        line-height: 1;
                    }
                    .etiqueta .body { flex-grow: 1; display: flex; flex-direction: column; justify-content: space-between; }
                    .etiqueta .campo {
                        font-size: 6pt;
                        color: #333;
                        line-height: 1.1;
                        white-space: nowrap;
                        overflow: hidden;
                        text-overflow: ellipsis;
                    }
                    .etiqueta .campo strong { color: #0B2D6B; }
                    
                    /* CÓDIGO DE BARRAS */
                    .barcode-container {
                        text-align: center;
                        margin-top: 0.5mm;
                    }
                    .barcode-container svg {
                        max-width: 100%;
                        height: 25px; /* Altura fija para que quepa */
                    }

                    @media print {
                        body { background: #fff; padding: 0; }
                        .toolbar { display: none; }
                        .hoja { margin: 0; padding: 0; border: none; width: 100%; }
                        .etiqueta { border: 1px solid #ccc; page-break-inside: avoid; }
                    }
                </style>
            </head>
            <body>
                <div class="toolbar">
                    <button class="btn-print" onclick="window.print()">🖨️ Imprimir Todo</button>
                    <button class="btn-close" onclick="window.close()">✖ Cerrar</button>
                </div>
                
                <div class="hoja">
                    ${etiquetasHtml}
                </div>
                
                <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"><\/script>
                <script>
                    document.querySelectorAll('.barcode').forEach(svg => {
                        try {
                            JsBarcode(svg, svg.dataset.code, {
                                format: 'CODE128', 
                                width: 1.1, 
                                height: 25, 
                                displayValue: true, 
                                fontSize: 8,
                                margin: 0
                            });
                        } catch(e) { console.error(e); }
                    });
                <\/script>
            </body>
            </html>
        `);
        ventana.document.close();
    }

    // ──────────────────────────────────────────────────────────────
    // EXPORTAR EXCEL
    // ──────────────────────────────────────────────────────────────
    function exportarExcel() {
        const rows = document.querySelectorAll('#inventarioTableBody tr');
        let csv = 'ID,Folio,Núm.Oficina,Fecha,Descripción,Marca,Modelo,Serie,Departamento,Disponible,Tipo Compra\n';
        rows.forEach(row => {
            if (row.style.display === 'none') return;
            const cells = row.querySelectorAll('td');
            if (cells.length < 11) return;
            const data = [];
            for (let i = 0; i < 11; i++) data.push(cells[i].textContent.trim());
            csv += data.join(',') + '\n';
        });
        const blob = new Blob([csv], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'inventario_activos_fijos_' + new Date().toISOString().slice(0,10) + '.csv';
        a.click();
        window.URL.revokeObjectURL(url);
    }

    // ──────────────────────────────────────────────────────────────
    // INICIALIZACIÓN
    // ──────────────────────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', function() {
        fetchMovimientos();
        fetchInventario();

        document.getElementById('searchItemMov').addEventListener('input', function() {
            const q = this.value.toLowerCase();
            let filtradas = [...todosLosMovimientos];
            if (filtroMovimientos !== 'todos') filtradas = filtradas.filter(a => a.tipo === filtroMovimientos);
            if (q) filtradas = filtradas.filter(a => (a.num_oficina||'').toLowerCase().includes(q) || (a.descripcion_bien||'').toLowerCase().includes(q) || (a.jefe_departamento||'').toLowerCase().includes(q));
            renderMovimientos(filtradas);
        });

        document.getElementById('searchItemInv').addEventListener('input', function() {
            const q = this.value.toLowerCase();
            let filtradas = [...todosLosActivos];
            if (filtroInventario === 'disponible') filtradas = filtradas.filter(a => a.disponible == 1);
            else if (filtroInventario === 'no_disponible') filtradas = filtradas.filter(a => a.disponible == 0);
            if (q) filtradas = filtradas.filter(a => (a.num_oficina||'').toLowerCase().includes(q) || (a.descripcion_bien||'').toLowerCase().includes(q) || (a.serie||'').toLowerCase().includes(q) || (a.marca||'').toLowerCase().includes(q));
            renderInventario(filtradas);
        });

        const params = new URLSearchParams(window.location.search);
        const nuevoId = params.get('nuevo');
        const cantParam = params.get('cant');

        if (nuevoId) {
            setTimeout(() => {
                const cant = parseInt(cantParam) || 1;
                abrirModalEtiqueta(parseInt(nuevoId), cant);
                window.history.replaceState({}, '', window.location.pathname);
            }, 800);
        }
    });
</script>
</body>
</html>