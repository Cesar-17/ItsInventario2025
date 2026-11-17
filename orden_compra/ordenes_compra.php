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
    <title>Órdenes de Compra — AlmacenaTec</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        /* ── Badges de estatus ── */
        .badge-estatus {
            display: inline-block; padding: 3px 12px; border-radius: 20px; font-size: 11px; font-weight: 600;
        }
        .badge-estatus.pendiente { background: #fef3c7; color: #d97706; }
        .badge-estatus.autorizada { background: #dbeafe; color: #0B2D6B; }
        .badge-estatus.entregada { background: #d1fae5; color: #059669; }
        .badge-estatus.cancelada { background: #fee2e2; color: #dc2626; }

        /* ── Select de estatus ── */
        .select-estatus {
            padding: 2px 6px; border-radius: 4px; border: 1.5px solid #E2E8F0;
            font-size: 12px; background: #fff; cursor: pointer;
            color: #1E293B;
            font-weight: 500;
        }
        .select-estatus:focus {
            outline: none;
            border-color: #0B2D6B;
        }

        /* ── Botones de acción ── */
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
            background: rgba(11,45,107,.08);
            color: #0B2D6B;
        }
        .btn-almacenatec.editar:hover {
            background: #0B2D6B;
            color: #FFFFFF;
        }
        .btn-almacenatec.factura {
            background: rgba(11,45,107,.08);
            color: #0B2D6B;
        }
        .btn-almacenatec.factura:hover {
            background: #0B2D6B;
            color: #FFFFFF;
        }
        .btn-almacenatec.editar-factura {
            background: rgba(217,119,6,.08);
            color: #d97706;
        }
        .btn-almacenatec.editar-factura:hover {
            background: #d97706;
            color: #FFFFFF;
        }

        .acciones-botones {
            display: flex;
            gap: 4px;
            flex-wrap: wrap;
            justify-content: center;
        }

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

        /* ── Tabla ── */
        .table-almacenatec {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
            background: #FFFFFF;
        }
        .table-almacenatec thead {
            background: #0B2D6B;
            color: #FFFFFF;
        }
        .table-almacenatec thead th {
            padding: 12px 16px;
            text-align: center;
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
            text-align: center;
        }
        .table-almacenatec tbody tr:hover {
            background: #F0F4F9;
        }
        .table-almacenatec tbody tr:last-child td {
            border-bottom: none;
        }

        /* ── Botones de navegación ── */
        .btn-nav-almacenatec {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 18px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            font-family: 'Poppins', sans-serif;
        }
        .btn-nav-almacenatec.requisiciones {
            background: #0B2D6B;
            color: #FFFFFF;
        }
        .btn-nav-almacenatec.requisiciones:hover {
            background: #0B2D6B;
            color: #FFFFFF;
        }
        .btn-nav-almacenatec.facturas {
            background: #0B2D6B;
            color: #FFFFFF;
        }
        .btn-nav-almacenatec.facturas:hover {
            background: #071d46;
            color: #FFFFFF;
        }
        .btn-nav-almacenatec.compras {
            background: #0B2D6B;
            color: #FFFFFF;
        }
        .btn-nav-almacenatec.compras:hover {
            background: #071d46;
            color: #FFFFFF;
        }

        /* ── Responsive ── */
        @media (max-width: 768px) {
            .table-almacenatec thead th {
                font-size: 10px;
                padding: 8px 10px;
            }
            .table-almacenatec tbody td {
                font-size: 12px;
                padding: 8px 10px;
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
                <h1><i class="bi bi-cart-check me-2" style="color:#B81C2E"></i>Órdenes de Compra</h1>
                <div class="topbar-breadcrumb"><a href="../home/index.php">Inicio</a> &rsaquo; <a href="requisiciones.php">Almacén General</a> &rsaquo; Órdenes de Compra</div>
            </div>
            <div style="display:flex;align-items:center;gap:10px;flex-shrink:0">
                <span style="font-size:13px;color:#475569"><i class="bi bi-person-circle me-1"></i><?= $nombre ?></span>
                <a href="<?= BASE_PATH ?>logout.php" class="btn-accent-brand" style="padding:6px 12px;font-size:12px"><i class="bi bi-box-arrow-right me-1"></i> Salir</a>
            </div>
        </header>

        <main class="content">
            <div class="page-header">
                <h2><i class="bi bi-cart-check me-2" style="color:#B81C2E"></i>Órdenes de Compra</h2>
                <div style="display:flex;gap:8px;flex-wrap:wrap">
                    <a href="requisiciones.php" class="btn-nav-almacenatec requisiciones">
                        <i class="bi bi-file-earmark-text"></i> Requisiciones
                    </a>
                    <a href="facturas_lista.php" class="btn-nav-almacenatec facturas">
                        <i class="bi bi-file-earmark-text"></i> Facturas
                    </a>
                   
                </div>
            </div>

            <!-- ============================================================ -->
            <!-- TÍTULO DE LA TABLA                                             -->
            <!-- ============================================================ -->
            <div class="tabla-titulo">
                <i class="bi bi-cart-check"></i>
                Lista de Órdenes de Compra
                <small>Control de órdenes generadas</small>
            </div>

            <div class="card-panel">
                <div class="card-header-almacenatec">
                    <h3><i class="bi bi-table"></i>Órdenes de Compra</h3>
                    <div class="search-almacenatec">
                        <i class="bi bi-search"></i>
                        <input type="text" id="searchItem" placeholder="Buscar por orden, proveedor, área…">
                    </div>
                </div>

                <div class="table-responsive-almacenatec" style="overflow-x:auto;">
                    <table class="table-almacenatec">
                        <thead>
                            <tr>
                                <th><i class="bi bi-hash"></i> No. Orden</th>
                                <th><i class="bi bi-calendar"></i> Fecha</th>
                                <th><i class="bi bi-building"></i> Proveedor</th>
                                <th><i class="bi bi-file-earmark-text"></i> No. Requisición</th>
                                <th><i class="bi bi-pin-map"></i> Área</th>
                                <th><i class="bi bi-cash"></i> Total</th>
                                <th><i class="bi bi-info-circle"></i> Estatus</th>
                                <th><i class="bi bi-tools"></i> Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="ordenesTableBody">
                            <tr><td colspan="8" class="text-muted">Cargando datos...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- MODALES -->
<div class="modal fade" id="modalDetalle" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background:#0B2D6B;color:#fff;">
                <h5 class="modal-title" style="color:#fff;"><i class="bi bi-file-earmark-text me-2"></i>Detalle de Orden de Compra</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter:invert(1);"></button>
            </div>
            <div class="modal-body modal-detalle" id="modalDetalleBody"></div>
            <div class="modal-footer">
                <button type="button" class="btn-outline-brand" data-bs-dismiss="modal"><i class="bi bi-x-lg me-1"></i> Cerrar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalRequisicion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background:#0B2D6B;color:#fff;">
                <h5 class="modal-title" style="color:#fff;"><i class="bi bi-file-earmark-text me-2"></i>Detalle de Requisición</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter:invert(1);"></button>
            </div>
            <div class="modal-body" id="modalRequisicionBody"></div>
            <div class="modal-footer">
                <button type="button" class="btn-outline-brand" data-bs-dismiss="modal"><i class="bi bi-x-lg me-1"></i> Cerrar</button>
            </div>
        </div>
    </div>
</div>

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

    async function fetchOrdenes() {
        try {
            const res = await fetch('<?= BASE_PATH ?>includes/get_ordenes_compra.php');
            const data = await res.json();
            if (!data.ok) throw new Error(data.error);
            renderOrdenes(data.ordenes || []);
        } catch(err) { console.error(err); }
    }

    function renderOrdenes(rows) {
        const tbody = document.getElementById('ordenesTableBody');
        tbody.innerHTML = '';
        if (!rows || rows.length === 0) {
            tbody.innerHTML = '<tr><td colspan="8" class="text-muted">No hay órdenes de compra registradas.</td></tr>';
            return;
        }
        const estatusLabels = { 'pendiente': 'Pendiente', 'autorizada': 'Autorizada', 'entregada': 'Entregada', 'cancelada': 'Cancelada' };
        for (const o of rows) {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td><span style="font-weight:600;color:#0B2D6B">${o.no_orden_compra || ''}</span></td>
                <td><small>${o.fecha || ''}</small></td>
                <td>${o.proveedor || ''}</td>
                <td><a href="#" onclick="verRequisicion('${o.no_requisicion}'); return false;" style="font-weight:600;color:#0B2D6B;text-decoration:underline;">${o.no_requisicion || ''}</a></td>
                <td>${o.area_solicitante || ''}</td>
                <td>$${Number(o.total || 0).toFixed(2)}</td>
                <td>
                    <select class="select-estatus" onchange="cambiarEstatus(${o.id}, this.value)">
                        <option value="pendiente" ${o.estatus === 'pendiente' ? 'selected' : ''}>Pendiente</option>
                        <option value="autorizada" ${o.estatus === 'autorizada' ? 'selected' : ''}>Autorizada</option>
                        <option value="entregada" ${o.estatus === 'entregada' ? 'selected' : ''}>Entregada</option>
                        <option value="cancelada" ${o.estatus === 'cancelada' ? 'selected' : ''}>Cancelada</option>
                    </select>
                </td>
                <td>
                    <div class="acciones-botones">
                        <button class="btn-almacenatec ver" onclick="verDetalle(${o.id})"><i class="bi bi-eye"></i></button>
                        <button class="btn-almacenatec editar" onclick="editarOrden(${o.id})"><i class="bi bi-pencil"></i></button>
                        <button class="btn-almacenatec factura" onclick="generarFactura(${o.id})"><i class="bi bi-file-earmark-text"></i></button>
                        <button class="btn-almacenatec editar-factura" onclick="editarFactura(${o.id})"><i class="bi bi-pencil-square"></i></button>
                    </div>
                </td>
            `;
            tbody.appendChild(tr);
        }
    }

    function verDetalle(id) {
        fetch('<?= BASE_PATH ?>includes/get_orden_detalle.php?id=' + id)
            .then(r => r.json())
            .then(data => {
                if (!data.ok) return alert('Error: ' + data.error);
                const orden = data.orden, detalles = data.detalles || [];
                let html = `
                    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;margin-bottom:16px">
                        <div style="background:#F0F4F9;padding:12px;border-radius:8px;border-left:3px solid #0B2D6B">
                            <div style="font-size:11px;font-weight:700;color:#94A3B8;text-transform:uppercase;">No. Orden</div>
                            <div style="font-weight:700;font-size:16px;color:#0B2D6B">${orden.no_orden_compra || ''}</div>
                        </div>
                        <div style="background:#F0F4F9;padding:12px;border-radius:8px;border-left:3px solid #c9973a">
                            <div style="font-size:11px;font-weight:700;color:#94A3B8;text-transform:uppercase;">Fecha</div>
                            <div style="font-weight:600;font-size:15px">${orden.fecha || ''}</div>
                        </div>
                        <div style="background:#F0F4F9;padding:12px;border-radius:8px;border-left:3px solid #B81C2E">
                            <div style="font-size:11px;font-weight:700;color:#94A3B8;text-transform:uppercase;">Proveedor</div>
                            <div style="font-weight:600;font-size:15px">${orden.proveedor || ''}</div>
                        </div>
                    </div>
                    <table style="width:100%;font-size:13px;margin-bottom:12px">
                        <tr><td style="padding:5px 0;color:#94A3B8;width:150px">No. Requisición</td><td style="font-weight:600">${orden.no_requisicion || ''}</td></tr>
                        <tr><td style="padding:5px 0;color:#94A3B8">Área Solicitante</td><td>${orden.area_solicitante || ''}</td></tr>
                        <tr><td style="padding:5px 0;color:#94A3B8">Condiciones</td><td>${orden.condiciones || '—'}</td></tr>
                        <tr><td style="padding:5px 0;color:#94A3B8">Vendedor</td><td>${orden.vendedor || '—'}</td></tr>
                        <tr><td style="padding:5px 0;color:#94A3B8">Vía de Embarque</td><td>${orden.via_embarque || '—'}</td></tr>
                        <tr><td style="padding:5px 0;color:#94A3B8">Estatus</td><td><span class="badge-estatus ${orden.estatus || 'pendiente'}">${orden.estatus || 'Pendiente'}</span></td></tr>
                    </table>`;
                if (detalles.length > 0) {
                    html += `<hr style="border-color:#E2E8F0;"><h6 style="font-weight:700;color:#0B2D6B;"><i class="bi bi-list-ul me-1"></i>Detalles</h6><div style="overflow-x:auto"><table class="table table-sm table-striped" style="width:100%;border-collapse:collapse;font-size:13px;"><thead style="background:#0B2D6B;color:#fff;"><tr><th style="padding:8px;">Código</th><th style="padding:8px;">Nombre</th><th style="padding:8px;">Unidad</th><th style="padding:8px;">Unidades</th><th style="padding:8px;">Precio</th><th style="padding:8px;">Descuento</th><th style="padding:8px;">Importe</th></tr></thead><tbody>`;
                    for (const d of detalles) {
                        html += `<tr><td style="padding:6px;border-bottom:1px solid #E2E8F0;">${d.articulo_codigo || ''}</td><td style="padding:6px;border-bottom:1px solid #E2E8F0;">${d.nombre_caracteristicas || ''}</td><td style="padding:6px;border-bottom:1px solid #E2E8F0;">${d.unidad_medida || ''}</td><td style="padding:6px;border-bottom:1px solid #E2E8F0;">${d.unidades || ''}</td><td style="padding:6px;border-bottom:1px solid #E2E8F0;">$${Number(d.precio || 0).toFixed(2)}</td><td style="padding:6px;border-bottom:1px solid #E2E8F0;">$${Number(d.descuento || 0).toFixed(2)}</td><td style="padding:6px;border-bottom:1px solid #E2E8F0;">$${Number(d.importe || 0).toFixed(2)}</td></tr>`;
                    }
                    html += `</tbody></table></div>`;
                }
                html += `<div style="margin-top:16px;border-top:1px solid #E2E8F0;padding-top:16px">
                    <div style="display:flex;justify-content:flex-end;gap:30px;flex-wrap:wrap;">
                        <div><strong>Subtotal:</strong> $${Number(orden.subtotal || 0).toFixed(2)}</div>
                        <div><strong>IVA:</strong> $${Number(orden.iva || 0).toFixed(2)}</div>
                        <div style="font-size:18px;color:#0B2D6B;font-weight:700;"><strong>Total:</strong> $${Number(orden.total || 0).toFixed(2)}</div>
                    </div>
                </div>`;
                document.getElementById('modalDetalleBody').innerHTML = html;
                new bootstrap.Modal(document.getElementById('modalDetalle')).show();
            })
            .catch(err => alert('Error al cargar detalles: ' + err));
    }

    function editarOrden(id) { window.location.href = 'capturar_orden.php?edit_id=' + id; }

    function cambiarEstatus(id, nuevoEstatus) {
        if (!confirm('¿Estás seguro de cambiar el estatus a "' + nuevoEstatus + '"?')) { location.reload(); return; }
        fetch('<?= BASE_PATH ?>handlers/cambiar_estatus_orden.php', {
            method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'id=' + id + '&estatus=' + nuevoEstatus
        })
        .then(r => r.json())
        .then(data => {
            if (data.ok) { alert('✅ Estatus actualizado'); location.reload(); }
            else { alert('❌ Error: ' + data.error); location.reload(); }
        })
        .catch(err => { alert('❌ Error de conexión'); location.reload(); });
    }

    function verRequisicion(folio) {
        fetch('<?= BASE_PATH ?>includes/get_requisicion_by_folio.php?folio=' + encodeURIComponent(folio))
            .then(r => r.json())
            .then(data => {
                if (!data.ok) return alert('Error: ' + data.error);
                const r = data.requisicion;
                if (!r) return alert('Requisición no encontrada');
                const estatusLabels = { 'pendiente': 'Pendiente', 'aprobada': 'Aprobada', 'rechazada': 'Rechazada', 'orden_generada': 'Orden Generada' };
                const html = `
                    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;margin-bottom:16px">
                        <div style="background:#F0F4F9;padding:12px;border-radius:8px;border-left:3px solid #0B2D6B">
                            <div style="font-size:11px;font-weight:700;color:#94A3B8;text-transform:uppercase;">Folio</div>
                            <div style="font-weight:700;font-size:16px;color:#0B2D6B">${r.folio || ''}</div>
                        </div>
                        <div style="background:#F0F4F9;padding:12px;border-radius:8px;border-left:3px solid #c9973a">
                            <div style="font-size:11px;font-weight:700;color:#94A3B8;text-transform:uppercase;">Fecha</div>
                            <div style="font-weight:600;font-size:15px">${r.fecha || ''}</div>
                        </div>
                        <div style="background:#F0F4F9;padding:12px;border-radius:8px;border-left:3px solid ${r.estatus === 'orden_generada' ? '#059669' : '#d97706'}">
                            <div style="font-size:11px;font-weight:700;color:#94A3B8;text-transform:uppercase;">Estatus</div>
                            <div><span class="badge-estatus ${r.estatus || 'pendiente'}">${estatusLabels[r.estatus] || r.estatus}</span></div>
                        </div>
                    </div>
                    <table style="width:100%;font-size:13px;margin-bottom:12px">
                        <tr><td style="padding:5px 0;color:#94A3B8;width:180px">Jefe Solicitante</td><td style="font-weight:600">${r.jefe_solicitante || ''}</td></tr>
                        <tr><td style="padding:5px 0;color:#94A3B8">Área Solicitante</td><td>${r.area_solicitante || ''}</td></tr>
                        <tr><td style="padding:5px 0;color:#94A3B8">Programa Operativo Anual</td><td>${r.programa_operativo_anual ? '✅ Completado' : '❌ No completado'}</td></tr>
                        <tr><td style="padding:5px 0;color:#94A3B8">Clave Presupuestal</td><td style="font-weight:600">${r.clave_presupuestal || ''}</td></tr>
                        <tr><td style="padding:5px 0;color:#94A3B8">Partida</td><td style="font-weight:600">${r.partida || ''}</td></tr>
                        <tr><td style="padding:5px 0;color:#94A3B8">Cantidad</td><td>${r.cantidad || ''} ${r.unidad || ''}</td></tr>
                        <tr><td style="padding:5px 0;color:#94A3B8">Costo Estimado Total</td><td style="font-weight:700;color:#0B2D6B">$${Number(r.costo_estimado_total || 0).toFixed(2)}</td></tr>
                        <tr><td style="padding:5px 0;color:#94A3B8">Descripción</td><td>${r.descripcion || ''}</td></tr>
                    </table>
                `;
                document.getElementById('modalRequisicionBody').innerHTML = html;
                new bootstrap.Modal(document.getElementById('modalRequisicion')).show();
            })
            .catch(err => alert('Error al cargar requisición: ' + err));
    }

    function generarFactura(id) {
        fetch('<?= BASE_PATH ?>includes/get_factura_by_orden.php?orden_id=' + id)
            .then(r => r.json())
            .then(data => {
                if (data.ok && data.factura) {
                    mostrarFactura(data.factura.id);
                } else {
                    if (confirm('No existe factura para esta orden. ¿Deseas crearla?')) {
                        window.location.href = 'editar_factura.php?orden_id=' + id;
                    }
                }
            })
            .catch(err => { alert('Error al verificar factura'); });
    }

    function editarFactura(id) {
        fetch('<?= BASE_PATH ?>includes/get_factura_by_orden.php?orden_id=' + id)
            .then(r => r.json())
            .then(data => {
                if (data.ok && data.factura) {
                    window.location.href = 'editar_factura.php?factura_id=' + data.factura.id;
                } else {
                    if (confirm('No existe factura para esta orden. ¿Deseas crearla?')) {
                        window.location.href = 'editar_factura.php?orden_id=' + id;
                    }
                }
            })
            .catch(err => { alert('Error al verificar factura'); });
    }

    function mostrarFactura(facturaId) {
        fetch('<?= BASE_PATH ?>includes/get_factura_detalle.php?id=' + facturaId)
            .then(r => r.json())
            .then(data => {
                if (!data.ok) return alert('Error: ' + data.error);
                const f = data.factura, detalles = data.detalles || [];
                const estatusLabels = { 'pendiente': 'Pendiente', 'emitida': 'Emitida', 'cancelada': 'Cancelada' };
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
                            <div><span class="badge-estatus ${f.estatus || 'pendiente'}">${estatusLabels[f.estatus] || f.estatus}</span></div>
                        </div>
                    </div>
                    <table style="width:100%;font-size:13px;margin-bottom:12px">
                        <tr><td style="padding:5px 0;color:#94A3B8;width:150px">Cliente</td><td style="font-weight:600">${f.cliente_nombre || ''}</td></tr>
                        <tr><td style="padding:5px 0;color:#94A3B8">Dirección</td><td>${f.cliente_direccion || ''}</td></tr>
                        <tr><td style="padding:5px 0;color:#94A3B8">RFC</td><td style="font-weight:600">${f.cliente_rfc || ''}</td></tr>
                        <tr><td style="padding:5px 0;color:#94A3B8">No. Orden</td><td style="font-weight:600">${f.no_orden_compra || ''}</td></tr>
                        <tr><td style="padding:5px 0;color:#94A3B8">No. Requisición</td><td style="font-weight:600">${f.no_requisicion || ''}</td></tr>
                        <tr><td style="padding:5px 0;color:#94A3B8">Condiciones</td><td>${f.condiciones || '—'}</td></tr>
                        <tr><td style="padding:5px 0;color:#94A3B8">Vendedor</td><td>${f.vendedor || '—'}</td></tr>
                        <tr><td style="padding:5px 0;color:#94A3B8">Vía de Embarque</td><td>${f.via_embarque || '—'}</td></tr>
                    </table>`;
                if (detalles.length > 0) {
                    html += `<hr style="border-color:#E2E8F0;"><h6 style="font-weight:700;color:#0B2D6B;"><i class="bi bi-list-ul me-1"></i>Detalles</h6><div style="overflow-x:auto"><table class="table table-sm table-striped" style="width:100%;border-collapse:collapse;font-size:13px;"><thead style="background:#0B2D6B;color:#fff;"><tr><th style="padding:8px;">Código</th><th style="padding:8px;">Nombre</th><th style="padding:8px;">Unidad</th><th style="padding:8px;">Unidades</th><th style="padding:8px;">Precio</th><th style="padding:8px;">Descuento</th><th style="padding:8px;">Importe</th></tr></thead><tbody>`;
                    for (const d of detalles) {
                        html += `<tr><td style="padding:6px;border-bottom:1px solid #E2E8F0;">${d.articulo_codigo || ''}</td><td style="padding:6px;border-bottom:1px solid #E2E8F0;">${d.nombre_caracteristicas || ''}</td><td style="padding:6px;border-bottom:1px solid #E2E8F0;">${d.unidad_medida || ''}</td><td style="padding:6px;border-bottom:1px solid #E2E8F0;">${d.unidades || ''}</td><td style="padding:6px;border-bottom:1px solid #E2E8F0;">$${Number(d.precio || 0).toFixed(2)}</td><td style="padding:6px;border-bottom:1px solid #E2E8F0;">$${Number(d.descuento || 0).toFixed(2)}</td><td style="padding:6px;border-bottom:1px solid #E2E8F0;">$${Number(d.importe || 0).toFixed(2)}</td></tr>`;
                    }
                    html += `</tbody></table></div>`;
                }
                html += `<div style="margin-top:16px;border-top:1px solid #E2E8F0;padding-top:16px">
                    <div style="display:flex;justify-content:flex-end;gap:30px;flex-wrap:wrap;">
                        <div><strong>Subtotal:</strong> $${Number(f.subtotal || 0).toFixed(2)}</div>
                        <div><strong>IVA:</strong> $${Number(f.iva || 0).toFixed(2)}</div>
                        <div style="font-size:18px;color:#0B2D6B;font-weight:700;"><strong>Total:</strong> $${Number(f.total || 0).toFixed(2)}</div>
                    </div>
                </div>`;
                document.getElementById('modalFacturaBody').innerHTML = html;
                new bootstrap.Modal(document.getElementById('modalFactura')).show();
            })
            .catch(err => alert('Error al cargar detalles: ' + err));
    }

    document.addEventListener('DOMContentLoaded', function() {
        fetchOrdenes();
        document.getElementById('searchItem').addEventListener('input', function() {
            const q = this.value.toLowerCase();
            document.querySelectorAll('#ordenesTableBody tr').forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(q) ? '' : 'none';
            });
        });
    });
</script>
</body>
</html>