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
    <title>Facturas — AlmacenaTec</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        /* ============================================================
           ESTILOS UNIFICADOS - PALETA LOGIN
           ============================================================ */
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
        .table-almacenatec {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
            background: #FFFFFF;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,.07), 0 4px 14px rgba(0,0,0,.05);
        }
        .table-almacenatec thead {
            background: #0B2D6B;
            color: #FFFFFF;
        }
        .table-almacenatec thead th {
            padding: 12px 16px;
            text-align: left;
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 3px solid #c9973a;
            white-space: nowrap;
        }
        .table-almacenatec thead th i {
            margin-right: 6px;
            color: #c9973a;
        }
        .table-almacenatec tbody td {
            padding: 10px 16px;
            border-bottom: 1px solid #E2E8F0;
            color: #1E293B;
            vertical-align: middle;
        }
        .table-almacenatec tbody tr:hover {
            background: #F0F4F9;
        }
        .table-almacenatec tbody tr:last-child td {
            border-bottom: none;
        }
        .table-responsive-almacenatec {
            overflow-x: auto;
            border-radius: 10px;
        }
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
        .badge-almacenatec {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .badge-almacenatec.pendiente {
            background: #fef3c7;
            color: #d97706;
        }
        .badge-almacenatec.emitida {
            background: #d1fae5;
            color: #059669;
        }
        .badge-almacenatec.cancelada {
            background: #fee2e2;
            color: #dc2626;
        }
        .btn-almacenatec {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
        }
        .btn-almacenatec.ver {
            background: rgba(11,45,107,.08);
            color: #0B2D6B;
        }
        .btn-almacenatec.ver:hover {
            background: #0B2D6B;
            color: #FFFFFF;
        }
        .btn-almacenatec.editar {
            background: rgba(217,119,6,.08);
            color: #d97706;
        }
        .btn-almacenatec.editar:hover {
            background: #d97706;
            color: #FFFFFF;
        }
        .btn-almacenatec.eliminar {
            background: rgba(184,28,46,.08);
            color: #B81C2E;
        }
        .btn-almacenatec.eliminar:hover {
            background: #B81C2E;
            color: #FFFFFF;
        }
        .btn-almacenatec.success {
            background: rgba(5,150,105,.08);
            color: #059669;
        }
        .btn-almacenatec.success:hover {
            background: #059669;
            color: #FFFFFF;
        }
        .acciones-botones {
            display: flex;
            gap: 4px;
            flex-wrap: wrap;
            justify-content: center;
        }
        /* ── Botones de acción principales (azules) ── */
        .btn-primary-almacenatec {
            background: #0B2D6B;
            color: #FFFFFF;
            border: none;
            padding: 9px 18px;
            border-radius: 8px;
            font-size: 13.5px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-primary-almacenatec:hover {
            background: #071d46;
            color: #FFFFFF;
        }
        .btn-primary-almacenatec i {
            color: #FFFFFF;
        }
        .btn-primary-almacenatec i:hover {
            color: #FFFFFF;
        }
        .btn-accent-almacenatec {
            background: #B81C2E;
            color: #FFFFFF;
            border: none;
            padding: 9px 18px;
            border-radius: 8px;
            font-size: 13.5px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-accent-almacenatec:hover {
            background: #8f1522;
            color: #FFFFFF;
        }
        .btn-accent-almacenatec i {
            color: #c9973a;
        }
        .btn-accent-almacenatec i:hover {
            color: #FFFFFF;
        }
        .btn-success-almacenatec {
            background: #059669;
            color: #FFFFFF;
            border: none;
            padding: 9px 18px;
            border-radius: 8px;
            font-size: 13.5px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-success-almacenatec:hover {
            background: #047857;
            color: #FFFFFF;
        }
        .btn-warning-almacenatec {
            background: #0B2D6B;
            color: #FFFFFF;
            border: none;
            padding: 9px 18px;
            border-radius: 8px;
            font-size: 13.5px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-warning-almacenatec:hover {
            background: #0B2D6B;
            color: #FFFFFF;
        }
        @media (max-width: 768px) {
            .table-almacenatec thead th {
                font-size: 10px;
                padding: 8px 10px;
            }
            .table-almacenatec tbody td {
                font-size: 12px;
                padding: 8px 10px;
            }
            .table-almacenatec thead th i {
                display: none;
            }
            .search-almacenatec input {
                min-width: 140px;
            }
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
    
    <?php elseif ($_SESSION['usuario_rol'] === 'almacen_general'): ?>
    <a href="../activo_fijo/index.php" class="sidebar-link"><i class="bi bi-safe-fill"></i> Activo Fijo</a>
    <a href="../activo_fijo/main_activF.php" class="sidebar-link"><i class="bi bi-clipboard2-data"></i> Historial Salidas</a>
    <a href="../orden_compra/requisiciones.php" class="sidebar-link"><i class="bi bi-cart3"></i> Almacén General</a>
   
    <?php elseif ($_SESSION['usuario_rol'] === 'activo_fijo'): ?>
    <a href="../activo_fijo/index.php" class="sidebar-link"><i class="bi bi-safe-fill"></i> Activo Fijo</a>
    <a href="../activo_fijo/main_activF.php" class="sidebar-link"><i class="bi bi-clipboard2-data"></i> Historial Salidas</a>
     <a href="../activo_fijo/requisiciones_af.php" class="sidebar-link"><i class="bi bi-cart3"></i>Requisiciones</a>
<a href="../activo_fijo/facturas_af.php" class="sidebar-link"><i class="bi bi-cart3"></i>Facturas</a>
    
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
                <h1><i class="bi bi-file-earmark-text me-2" style="color:#0B2D6B"></i>Facturas</h1>
                <div class="topbar-breadcrumb"><a href="../home/index.php">Inicio</a> &rsaquo; <a href="requisiciones.php">Almacén General</a> &rsaquo; Facturas</div>
            </div>
            <div style="display:flex;align-items:center;gap:10px;flex-shrink:0">
                <span style="font-size:13px;color:#475569"><i class="bi bi-person-circle me-1"></i><?= $nombre ?></span>
                <a href="<?= BASE_PATH ?>logout.php" class="btn-accent-almacenatec" style="padding:6px 12px;font-size:12px"><i class="bi bi-box-arrow-right me-1"></i> Salir</a>
            </div>
        </header>

        <main class="content">
            <div class="page-header">
                <h2><i class="bi bi-file-earmark-text me-2" style="color:#0B2D6B"></i>Lista de Facturas</h2>
                <div style="display:flex;gap:8px;flex-wrap:wrap">
                    <a href="requisiciones.php" class="btn-primary-almacenatec"><i class="bi bi-cart3"></i> Requisiciones</a>
                    <a href="ordenes_compra.php" class="btn-primary-almacenatec"><i class="bi bi-cart-check"></i> Órdenes de Compra</a>
                
                </div>
            </div>

            <!-- ============================================================ -->
            <!-- TÍTULO DE LA TABLA                                             -->
            <!-- ============================================================ -->
            <div class="tabla-titulo">
                <i class="bi bi-file-earmark-text"></i>
                Lista de Facturas
                <small>Control de facturas generadas</small>
            </div>

            <div class="card-panel">
                <div class="card-header-almacenatec">
                    <h3><i class="bi bi-table"></i>Facturas registradas</h3>
                    <div class="search-almacenatec">
                        <i class="bi bi-search"></i>
                        <input type="text" id="searchItem" placeholder="Buscar por número, cliente, orden…">
                    </div>
                </div>

                <div class="table-responsive-almacenatec">
                    <table class="table-almacenatec">
                        <thead>
                            <tr>
                                <th><i class="bi bi-hash"></i> N° Factura</th>
                                <th><i class="bi bi-calendar"></i> Fecha</th>
                                <th><i class="bi bi-person"></i> Cliente</th>
                                <th><i class="bi bi-cart"></i> No. Orden</th>
                                <th><i class="bi bi-file-earmark"></i> No. Requisición</th>
                                <th><i class="bi bi-cash"></i> Total</th>
                                <th><i class="bi bi-info-circle"></i> Estatus</th>
                                <th><i class="bi bi-tools"></i> Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="facturasTableBody">
                            <tr><td colspan="8" class="text-muted">Cargando datos...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- MODAL: DETALLE DE FACTURA -->
<div class="modal fade" id="modalFactura" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background:#0B2D6B;color:#fff;">
                <h5 class="modal-title" style="color:#fff;"><i class="bi bi-file-earmark-text me-2"></i>Detalle de Factura</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter:invert(1);"></button>
            </div>
            <div class="modal-body" id="modalFacturaBody"></div>
            <div class="modal-footer">
                <button type="button" class="btn-outline-brand" data-bs-dismiss="modal"><i class="bi bi-x-lg me-1"></i> Cerrar</button>
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
    function closeSidebar() { document.getElementById('sidebar').classList.remove('open'); document.getElementById('sidebarOverlay').classList.remove('visible'); }

    function getOrCreateErrorDiv() {
        let d = document.getElementById('fetchError');
        if (!d) {
            d = document.createElement('div');
            d.id = 'fetchError';
            d.style.cssText = 'margin:10px 0;color:#856404;background:#fff3cd;padding:8px 12px;border-radius:6px;display:none;font-size:13px;';
            const c = document.querySelector('.card-panel');
            if (c) c.insertBefore(d, c.firstChild);
        }
        return d;
    }

    // ──────────────────────────────────────────────────────────────
    // CARGAR FACTURAS
    // ──────────────────────────────────────────────────────────────
    async function fetchFacturas() {
        const errDiv = getOrCreateErrorDiv();
        try {
            const res = await fetch('<?= BASE_PATH ?>includes/get_facturas.php');
            const text = await res.text();
            let data;
            try { data = JSON.parse(text); } catch(e) {
                errDiv.style.display = 'block';
                errDiv.textContent = 'Respuesta no JSON: ' + text.substring(0, 1000);
                return;
            }
            if (!res.ok || !data.ok) {
                errDiv.style.display = 'block';
                errDiv.textContent = 'Error: ' + (data && data.error ? data.error : res.status);
                return;
            }
            errDiv.style.display = 'none';
            renderFacturas(data.facturas || []);
        } catch(err) {
            const d = getOrCreateErrorDiv();
            d.style.display = 'block';
            d.textContent = 'Error: ' + (err && err.message ? err.message : String(err));
        }
    }

    function renderFacturas(rows) {
        const tbody = document.getElementById('facturasTableBody');
        tbody.innerHTML = '';
        if (!rows || rows.length === 0) {
            tbody.innerHTML = '<tr><td colspan="8" class="text-muted">No hay facturas registradas.</td></tr>';
            return;
        }

        const estatusLabels = { 'pendiente': 'Pendiente', 'emitida': 'Emitida', 'cancelada': 'Cancelada' };
        const estatusClass = { 'pendiente': 'pendiente', 'emitida': 'emitida', 'cancelada': 'cancelada' };

        for (const f of rows) {
            const tr = document.createElement('tr');
            
            tr.innerHTML = `
                <td><span style="font-weight:600;color:#0B2D6B">${f.numero_factura || ''}</span></td>
                <td>${f.fecha || ''}</td>
                <td style="max-width:150px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${f.cliente_nombre || ''}</td>
                <td>${f.no_orden_compra || ''}</td>
                <td>${f.no_requisicion || ''}</td>
                <td>$${Number(f.total || 0).toFixed(2)}</td>
                <td><span class="badge-almacenatec ${estatusClass[f.estatus] || 'pendiente'}">${estatusLabels[f.estatus] || f.estatus}</span></td>
                <td>
                    <div class="acciones-botones">
                        <button class="btn-almacenatec ver" onclick="verDetalle(${f.id})" title="Ver detalle"><i class="bi bi-eye"></i></button>
                        <button class="btn-almacenatec editar" onclick="editarFactura(${f.id})" title="Editar"><i class="bi bi-pencil"></i></button>
                        <button class="btn-almacenatec eliminar" onclick="eliminarFactura(${f.id})" title="Eliminar"><i class="bi bi-trash"></i></button>
                    </div>
                </td>
            `;
            tbody.appendChild(tr);
        }
    }

    // ──────────────────────────────────────────────────────────────
    // VER DETALLE DE FACTURA
    // ──────────────────────────────────────────────────────────────
    function verDetalle(id) {
        fetch('<?= BASE_PATH ?>includes/get_factura_detalle.php?id=' + id)
            .then(r => r.json())
            .then(data => {
                if (!data.ok) return alert('Error: ' + data.error);
                const f = data.factura;
                const detalles = data.detalles || [];
                if (!f) return alert('Factura no encontrada');

                const estatusLabels = { 'pendiente': 'Pendiente', 'emitida': 'Emitida', 'cancelada': 'Cancelada' };
                const estatusClass = { 'pendiente': 'pendiente', 'emitida': 'emitida', 'cancelada': 'cancelada' };

                let html = `
                    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;margin-bottom:16px">
                        <div style="background:#F0F4F9;padding:12px;border-radius:8px;border-left:3px solid #0B2D6B">
                            <div style="font-size:11px;font-weight:700;color:#94A3B8;text-transform:uppercase;">N° Factura</div>
                            <div style="font-weight:700;font-size:16px;color:#0B2D6B">${f.numero_factura || ''}</div>
                        </div>
                        <div style="background:#F0F4F9;padding:12px;border-radius:8px;border-left:3px solid #c9973a">
                            <div style="font-size:11px;font-weight:700;color:#94A3B8;text-transform:uppercase;">Fecha</div>
                            <div style="font-weight:600;font-size:15px">${f.fecha || ''}</div>
                        </div>
                        <div style="background:#F0F4F9;padding:12px;border-radius:8px;border-left:3px solid ${f.estatus === 'emitida' ? '#059669' : '#d97706'}">
                            <div style="font-size:11px;font-weight:700;color:#94A3B8;text-transform:uppercase;">Estatus</div>
                            <div><span class="badge-almacenatec ${estatusClass[f.estatus] || 'pendiente'}">${estatusLabels[f.estatus] || f.estatus}</span></div>
                        </div>
                    </div>
                    <table style="width:100%;font-size:13px;margin-bottom:12px">
                        <tr><td style="padding:5px 0;color:#94A3B8;width:150px">Cliente</td><td style="font-weight:600">${f.cliente_nombre || ''}</td></tr>
                        <tr><td style="padding:5px 0;color:#94A3B8">Dirección</td><td>${f.cliente_direccion || ''}</td></tr>
                        <tr><td style="padding:5px 0;color:#94A3B8">RFC</td><td style="font-weight:600">${f.cliente_rfc || ''}</td></tr>
                        <tr><td style="padding:5px 0;color:#94A3B8">No. Orden de Compra</td><td style="font-weight:600">${f.no_orden_compra || ''}</td></tr>
                        <tr><td style="padding:5px 0;color:#94A3B8">No. Requisición</td><td style="font-weight:600">${f.no_requisicion || ''}</td></tr>
                        <tr><td style="padding:5px 0;color:#94A3B8">Condiciones</td><td>${f.condiciones || '—'}</td></tr>
                        <tr><td style="padding:5px 0;color:#94A3B8">Vendedor</td><td>${f.vendedor || '—'}</td></tr>
                        <tr><td style="padding:5px 0;color:#94A3B8">Vía de Embarque</td><td>${f.via_embarque || '—'}</td></tr>
                    </table>`;

                if (detalles.length > 0) {
                    html += `<hr style="border-color:#E2E8F0"><h6 style="font-weight:700;margin-bottom:8px"><i class="bi bi-list-ul me-1"></i>Detalles de la Factura</h6>
                    <div style="overflow-x:auto"><table class="table-almacenatec">
                        <thead><tr>
                            <th>Código</th>
                            <th>Nombre</th>
                            <th>Unidad</th>
                            <th>Unidades</th>
                            <th>Precio</th>
                            <th>Descuento</th>
                            <th>Importe</th>
                        </tr></thead><tbody>`;
                    for (const d of detalles) {
                        html += `<tr>
                            <td>${d.articulo_codigo || ''}</td>
                            <td>${d.nombre_caracteristicas || ''}</td>
                            <td>${d.unidad_medida || ''}</td>
                            <td>${d.unidades || ''}</td>
                            <td>$${Number(d.precio || 0).toFixed(2)}</td>
                            <td>$${Number(d.descuento || 0).toFixed(2)}</td>
                            <td>$${Number(d.importe || 0).toFixed(2)}</td>
                        </tr>`;
                    }
                    html += `</tbody></table></div>`;
                }

                html += `
                    <div style="margin-top:16px;border-top:1px solid #E2E8F0;padding-top:16px">
                        <div style="display:flex;justify-content:flex-end;gap:30px;flex-wrap:wrap;">
                            <div><strong>Subtotal:</strong> $${Number(f.subtotal || 0).toFixed(2)}</div>
                            <div><strong>IVA (16%):</strong> $${Number(f.iva || 0).toFixed(2)}</div>
                            <div style="font-size:18px;color:#0B2D6B;"><strong>Total:</strong> $${Number(f.total || 0).toFixed(2)}</div>
                        </div>
                    </div>`;

                document.getElementById('modalFacturaBody').innerHTML = html;
                new bootstrap.Modal(document.getElementById('modalFactura')).show();
            })
            .catch(err => alert('Error al cargar detalles: ' + err));
    }

    // ──────────────────────────────────────────────────────────────
    // EDITAR FACTURA
    // ──────────────────────────────────────────────────────────────
    function editarFactura(id) {
        window.location.href = 'editar_factura.php?factura_id=' + id;
    }

    // ──────────────────────────────────────────────────────────────
    // ELIMINAR FACTURA
    // ──────────────────────────────────────────────────────────────
    function eliminarFactura(id) {
        if (!confirm('⚠️ ¿Estás seguro de ELIMINAR esta factura?\nEsta acción no se puede deshacer.')) {
            return;
        }

        fetch('<?= BASE_PATH ?>handlers/eliminar_factura.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'id=' + id
        })
        .then(r => r.json())
        .then(data => {
            if (data.ok) {
                alert('✅ Factura eliminada correctamente');
                location.reload();
            } else {
                alert('❌ Error: ' + data.error);
            }
        })
        .catch(err => alert('❌ Error de conexión'));
    }

    // ──────────────────────────────────────────────────────────────
    // INICIALIZAR
    // ──────────────────────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', function() {
        fetchFacturas();
        document.getElementById('searchItem').addEventListener('input', function() {
            const q = this.value.toLowerCase();
            document.querySelectorAll('#facturasTableBody tr').forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(q) ? '' : 'none';
            });
        });
    });
</script>
</body>
</html>