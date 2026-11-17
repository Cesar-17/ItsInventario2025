<<?php
require_once __DIR__ . '/../includes/auth.php';
$nombre = htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Usuario');
$rol = $_SESSION['usuario_rol'] ?? 'usuario';
$rolesRestringidos = ['almacenista', 'mantenimiento'];
$soloProductos = in_array($rol, $rolesRestringidos);
$esAdmin = ($rol === 'admin');
$esFinanzas = ($rol === 'finanzas');
$puedeModificar = ($esAdmin || $esFinanzas);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compras — AlmacenaTec</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
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
        .tabla-titulo i { color: #c9973a; font-size: 22px; }
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
        }
        .table-almacenatec thead { background: #0B2D6B; color: #FFFFFF; }
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
        .table-almacenatec thead th i { margin-right: 6px; color: #c9973a; }
        .table-almacenatec tbody td {
            padding: 10px 16px;
            border-bottom: 1px solid #E2E8F0;
            color: #1E293B;
            vertical-align: middle;
        }
        .table-almacenatec tbody tr:hover { background: #F0F4F9; }
        .table-almacenatec tbody tr:last-child td { border-bottom: none; }
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
        .card-header-almacenatec h3 i { color: #c9973a; margin-right: 8px; }
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
        .badge-almacenatec.pendiente { background: #fef3c7; color: #d97706; }
        .badge-almacenatec.entregado { background: #d1fae5; color: #059669; }
        .badge-almacenatec.pagado { background: #dbeafe; color: #0B2D6B; }
        .badge-almacenatec.autorizada { background: #dbeafe; color: #0B2D6B; }
        .badge-almacenatec.cancelada { background: #fee2e2; color: #dc2626; }
        .badge-almacenatec.factura-pendiente { background: #fee2e2; color: #dc2626; }
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
        .btn-almacenatec.finanzas {
            background: rgba(11,45,107,.08);
            color: #0B2D6B;
        }
        .btn-almacenatec.finanzas:hover { background: #0B2D6B; color: #FFFFFF; }
        .btn-almacenatec.detalles {
            background: rgba(217,119,6,.08);
            color: #d97706;
        }
        .btn-almacenatec.detalles:hover { background: #d97706; color: #FFFFFF; }
        .btn-azul-almacenatec {
            background: #0B2D6B;
            color: #FFFFFF;
            border: none;
            padding: 9px 18px;
            border-radius: 6px;
            font-size: 13.5px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-family: 'Poppins', sans-serif;
        }
        .btn-azul-almacenatec:hover {
            background: #071d46;
            color: #FFFFFF;
            transform: translateY(-1px);
            box-shadow: 0 4px 14px rgba(11,45,107,.35);
        }
        .switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 24px;
        }
        .switch input { opacity: 0; width: 0; height: 0; }
        .slider {
            position: absolute;
            cursor: pointer;
            inset: 0;
            background-color: #ccc;
            transition: .3s;
            border-radius: 24px;
        }
        .slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: .3s;
            border-radius: 50%;
        }
        input:checked + .slider { background-color: #059669; }
        input:checked + .slider:before { transform: translateX(26px); }
        .switch.disabled { opacity: 0.6; cursor: not-allowed; }
        .switch.disabled .slider { cursor: not-allowed; }
        .badge-admin {
            font-size: 10px;
            background: #dbeafe;
            color: #0B2D6B;
            padding: 2px 8px;
            border-radius: 12px;
            font-weight: 600;
        }
        .badge-usuario {
            font-size: 10px;
            background: #e5e7eb;
            color: #6b7280;
            padding: 2px 8px;
            border-radius: 12px;
            font-weight: 600;
        }
        .texto-sin-factura {
            font-size: 11px;
            color: #dc2626;
            font-weight: 600;
        }
        .fecha-pago {
            font-size: 11px;
            color: #0B2D6B;
            font-weight: 600;
            display: block;
            margin-top: 2px;
        }
        @media (max-width: 768px) {
            .table-almacenatec thead th { font-size: 10px; padding: 8px 10px; }
            .table-almacenatec thead th i { display: none; }
            .table-almacenatec tbody td { font-size: 12px; padding: 8px 10px; }
            .search-almacenatec input { min-width: 140px; }
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
            <?php if (!$soloProductos): ?>

    <?php if ($rol !== 'finanzas'): ?>
            <a href="../activo_fijo/index.php" class="sidebar-link"><i class="bi bi-safe-fill"></i> Activo Fijo</a>
<?php endif; ?>

 <?php if ($rol !== 'finanzas'): ?>
            <a href="../activo_fijo/main_activF.php" class="sidebar-link"><i class="bi bi-clipboard2-data"></i> Historial Salidas</a>
<?php endif; ?>

            <a href="../orden_compra/requisiciones.php" class="sidebar-link"><i class="bi bi-cart3"></i> Almacén General</a>

              <?php if ($rol !== 'finanzas'): ?>
            <a href="../orden_compra/facturas_lista.php" class="sidebar-link"><i class="bi bi-file-earmark-text"></i> Facturas</a>
             <?php endif; ?>
            <a href="../orden_compra/compras.php" class="sidebar-link active"><i class="bi bi-cart-check"></i> Compras</a>
            <?php endif; ?>

              <?php if ($rol !== 'finanzas'): ?>
            <a href="../productos/main_productos.php" class="sidebar-link"><i class="bi bi-box-seam"></i> Productos</a>
             <?php endif; ?>
            
            
            <?php if ($esAdmin): ?>
            <div class="nav-section" style="margin-top:8px">Administración</div>
            <a href="../usuarios/main_usuarios.php" class="sidebar-link"><i class="bi bi-people"></i> Usuarios</a>
            <?php endif; ?>
        </nav>
        <div class="sidebar-footer"><i class="bi bi-geo-alt-fill me-1"></i> TecNM · Campus Saltillo · 2026</div>
    </aside>

    <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

    <div class="main-wrapper" id="mainWrapper">
        <header class="topbar">
            <button class="topbar-toggle" onclick="toggleSidebar()"><i class="bi bi-list" style="font-size:22px"></i></button>
            <div class="topbar-info">
                <h1><i class="bi bi-cart-check me-2" style="color:#0B2D6B"></i>Compras</h1>
                <div class="topbar-breadcrumb"><a href="../home/index.php">Inicio</a> &rsaquo; Almacén General &rsaquo; Compras</div>
            </div>
            <div style="display:flex;align-items:center;gap:10px;flex-shrink:0">
                <span style="font-size:13px;color:#475569"><i class="bi bi-person-circle me-1"></i><?= $nombre ?></span>
                <span class="<?= $esAdmin ? 'badge-admin' : 'badge-usuario' ?>"><?= $esAdmin ? '🔑 Admin' : '👤 Usuario' ?></span>
                <a href="<?= BASE_PATH ?>logout.php" class="btn-accent-brand" style="padding:6px 12px;font-size:12px"><i class="bi bi-box-arrow-right me-1"></i> Salir</a>
            </div>
        </header>

        <main class="content">
            <div class="page-header">
                <h2><i class="bi bi-cart-check me-2" style="color:#0B2D6B"></i>Control de Compras</h2>
            </div>

            <div class="tabla-titulo">
                <i class="bi bi-cart-check"></i>
                Lista de Compras
                <small>Control de compras y seguimiento</small>
            </div>

            <div class="card-panel">
                <div class="card-header-almacenatec">
                    <h3><i class="bi bi-table"></i>Compras registradas</h3>
                    <div class="search-almacenatec">
                        <i class="bi bi-search"></i>
                        <input type="text" id="searchItem" placeholder="Buscar por orden, factura, área…">
                    </div>
                </div>

                <div style="overflow-x:auto;">
                    <table class="table-almacenatec">
                        <thead>
                            <tr>
                                <th><i class="bi bi-hash"></i> No. Orden</th>
                                <th><i class="bi bi-file-earmark"></i> Factura</th>
                                <th><i class="bi bi-info-circle"></i> Descripción</th>
                                <th><i class="bi bi-building"></i> Detalles</th>
                                <th><i class="bi bi-check-circle"></i> Entregado</th>
                                <th><i class="bi bi-credit-card"></i> Pagado</th>
                                <th><i class="bi bi-coin"></i> Finanzas</th>
                                <th><i class="bi bi-tools"></i> Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="comprasTableBody">
                            <tr><td colspan="8" class="text-center text-muted">Cargando datos...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- MODAL: FINANZAS -->
<div class="modal fade" id="modalFinanzas" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background:#0B2D6B;color:#fff;">
                <h5 class="modal-title" style="color:#fff;"><i class="bi bi-coin me-2"></i>Finanzas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter:invert(1);"></button>
            </div>
            <div class="modal-body" id="modalFinanzasBody"></div>
            <div class="modal-footer">
                <button type="button" class="btn-outline-brand" data-bs-dismiss="modal"><i class="bi bi-x-lg me-1"></i> Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL: DETALLES -->
<div class="modal fade" id="modalDetalles" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background:#0B2D6B;color:#fff;">
                <h5 class="modal-title" style="color:#fff;"><i class="bi bi-info-circle me-2"></i>Detalles de la Compra</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter:invert(1);"></button>
            </div>
            <div class="modal-body" id="modalDetallesBody"></div>
            <div class="modal-footer">
                <button type="button" class="btn-outline-brand" data-bs-dismiss="modal"><i class="bi bi-x-lg me-1"></i> Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script src="../js/bootstrap.bundle.min.js"></script>
<script>
    let todasLasCompras = [];
    const esAdmin = <?= json_encode($esAdmin) ?>;
    const esFinanzas = <?= json_encode($esFinanzas) ?>;
    const puedeModificar = <?= json_encode($puedeModificar) ?>;

    function toggleSidebar() {
        const s = document.getElementById('sidebar'), o = document.getElementById('sidebarOverlay'), w = document.getElementById('mainWrapper');
        if (window.innerWidth <= 1100) { const open = s.classList.toggle('open'); o.classList.toggle('visible', open); }
        else { s.classList.toggle('collapsed'); w.classList.toggle('sidebar-hidden'); }
    }
    function closeSidebar() { document.getElementById('sidebar').classList.remove('open'); document.getElementById('sidebarOverlay').classList.remove('visible'); }

    async function fetchCompras() {
        try {
            const res = await fetch('<?= BASE_PATH ?>includes/get_compras.php');
            const data = await res.json();
            if (!data.ok) throw new Error(data.error);
            todasLasCompras = data.compras || [];
            renderCompras(todasLasCompras);
        } catch(err) { console.error(err); }
    }

    function renderCompras(rows) {
        const tbody = document.getElementById('comprasTableBody');
        tbody.innerHTML = '';
        if (!rows || rows.length === 0) {
            tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted">No hay compras registradas.</td></tr>';
            return;
        }
        for (const c of rows) {
            const tr = document.createElement('tr');
            const entregado = c.entregado == 1;
            const pagado = c.pagado == 1;
            const tieneFactura = c.no_factura !== null && c.no_factura !== '';
            
            const switchDisabled = !puedeModificar ? 'disabled' : '';
            const switchClass = !puedeModificar ? 'switch disabled' : 'switch';

            const ordenEstatusClass = c.orden_estatus || 'pendiente';
            const estatusLabels = {
                'pendiente': 'Pendiente',
                'autorizada': 'Autorizada',
                'entregada': 'Entregada',
                'cancelada': 'Cancelada'
            };

            let fechaPagoHtml = '';
            if (pagado && c.fecha_pago) {
                const fechaPago = new Date(c.fecha_pago);
                const fechaFormateada = fechaPago.toLocaleString('es-MX', {
                    year: 'numeric', month: '2-digit', day: '2-digit',
                    hour: '2-digit', minute: '2-digit'
                });
                fechaPagoHtml = `<span class="fecha-pago"><i class="bi bi-clock me-1"></i>${fechaFormateada}</span>`;
            }

            // Factura: pendiente o número
            let facturaHtml = '';
            if (tieneFactura) {
                facturaHtml = `<span style="font-weight:600;color:#0B2D6B">${c.no_factura}</span>`;
            } else {
                facturaHtml = `<span class="badge-almacenatec factura-pendiente"><i class="bi bi-exclamation-circle me-1"></i>Factura Pendiente</span>`;
            }

            tr.innerHTML = `
                <td>
                    <span style="font-weight:600;color:#0B2D6B">${c.no_orden_compra || ''}</span>
                    <br><small class="badge-almacenatec ${ordenEstatusClass}">${estatusLabels[c.orden_estatus] || c.orden_estatus}</small>
                </td>
                <td>${facturaHtml}</td>
                <td style="max-width:150px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${c.descripcion || ''}</td>
                <td>${c.area_solicitante || ''}</td>
                <td>
                    <label class="${switchClass}">
                        <input type="checkbox" ${entregado ? 'checked' : ''} ${switchDisabled} onchange="toggleEntregado(${c.id}, this.checked)">
                        <span class="slider"></span>
                    </label>
                    <span class="badge-almacenatec ${entregado ? 'entregado' : 'pendiente'}">${entregado ? '✅' : '⏳'}</span>
                </td>
                <td>
                    <label class="${switchClass}">
                        <input type="checkbox" ${pagado ? 'checked' : ''} ${switchDisabled} onchange="togglePagadoCompra(${c.id}, this.checked)">
                        <span class="slider"></span>
                    </label>
                    <span class="badge-almacenatec ${pagado ? 'pagado' : 'pendiente'}">${pagado ? '✅' : '⏳'}</span>
                    ${fechaPagoHtml}
                </td>
                <td>
                    ${tieneFactura ? `
                        <button class="btn-almacenatec finanzas" onclick="abrirFinanzas(${c.id})">
                            <i class="bi bi-coin"></i> Finanzas
                        </button>
                    ` : `<span class="text-muted" style="font-size:11px;">Esperando factura</span>`}
                </td>
                <td>
                    <button class="btn-almacenatec detalles" onclick="abrirDetalles(${c.orden_id})">
                        <i class="bi bi-info-circle"></i> Detalles
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
        }
    }

    function toggleEntregado(compraId, checked) {
        if (!puedeModificar) { alert('No tienes permisos.'); location.reload(); return; }
        if (esFinanzas && !checked) { alert('Finanzas no puede desmarcar.'); location.reload(); return; }
        if (!confirm('¿Marcar como ' + (checked ? 'entregada' : 'no entregada') + '?')) { location.reload(); return; }
        fetch('<?= BASE_PATH ?>handlers/toggle_entregado.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'id=' + compraId + '&entregado=' + (checked ? 1 : 0)
        })
        .then(r => r.json())
        .then(data => {
            if (data.ok) { alert('✅ Estado actualizado'); fetchCompras(); }
            else { alert('❌ Error: ' + data.error); }
        })
        .catch(err => { alert('❌ Error de conexión'); });
    }

    function togglePagadoCompra(compraId, checked) {
        if (!puedeModificar) { alert('No tienes permisos.'); location.reload(); return; }
        if (esFinanzas && !checked) { alert('Finanzas no puede desmarcar.'); location.reload(); return; }
        if (!confirm('¿Marcar como ' + (checked ? 'pagada' : 'no pagada') + '?')) { location.reload(); return; }
        
        fetch('<?= BASE_PATH ?>handlers/toggle_pagado.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'compra_id=' + compraId + '&pagado=' + (checked ? 1 : 0)
        })
        .then(async response => {
            const text = await response.text();
            try {
                const data = JSON.parse(text);
                return data;
            } catch (e) {
                console.error('Respuesta no JSON:', text);
                throw new Error('El servidor devolvió un error interno. Revisa los logs.');
            }
        })
        .then(data => {
            if (data.ok) {
                alert('✅ Estado de pago actualizado');
                fetchCompras();
            } else {
                alert('❌ Error: ' + (data.error || 'Desconocido'));
                fetchCompras();
            }
        })
        .catch(err => {
            alert('❌ ' + err.message);
        });
    }

    function abrirFinanzas(compraId) {
        fetch('<?= BASE_PATH ?>includes/get_finanzas.php?compra_id=' + compraId)
            .then(r => r.json())
            .then(data => {
                if (!data.ok) return alert('Error: ' + data.error);
                const f = data.finanza;
                const c = data.compra;
                const pagado = f && f.pagado == 1;

                let html = `
                    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;margin-bottom:16px">
                        <div style="background:#F0F4F9;padding:12px;border-radius:8px;border-left:3px solid #0B2D6B">
                            <div style="font-size:11px;font-weight:700;color:#94A3B8;text-transform:uppercase;">No. Orden</div>
                            <div style="font-weight:700;font-size:16px;color:#0B2D6B">${c.no_orden_compra || ''}</div>
                        </div>
                        <div style="background:#F0F4F9;padding:12px;border-radius:8px;border-left:3px solid #c9973a">
                            <div style="font-size:11px;font-weight:700;color:#94A3B8;text-transform:uppercase;">No. Factura</div>
                            <div style="font-weight:600;font-size:15px">${c.no_factura || ''}</div>
                        </div>
                        <div style="background:#F0F4F9;padding:12px;border-radius:8px;border-left:3px solid ${c.entregado ? '#059669' : '#d97706'}">
                            <div style="font-size:11px;font-weight:700;color:#94A3B8;text-transform:uppercase;">Entregado</div>
                            <div>${c.entregado ? '✅ Sí' : '❌ No'}</div>
                        </div>
                    </div>
                    <table style="width:100%;font-size:13px;margin-bottom:12px">
                        <tr><td style="padding:5px 0;color:#94A3B8;width:150px">Descripción</td><td>${c.descripcion || ''}</td></tr>
                        <tr><td style="padding:5px 0;color:#94A3B8">Área Solicitante</td><td>${c.area_solicitante || ''}</td></tr>
                    </table>
                    <hr style="border-color:#E2E8F0">
                    <h6><i class="bi bi-coin me-1"></i>Finanzas</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Estado de Pago</label>
                            <label class="switch">
                                <input type="checkbox" ${pagado ? 'checked' : ''} ${!puedeModificar ? 'disabled' : ''} onchange="togglePagadoCompra(${c.id}, this.checked)">
                                <span class="slider"></span>
                            </label>
                            <span class="badge-almacenatec ${pagado ? 'pagado' : 'pendiente'}">${pagado ? '✅ Pagado' : '⏳ Pendiente'}</span>
                            ${pagado && f.fecha_pago ? `<br><small style="font-size:11px;color:#0B2D6B;"><i class="bi bi-clock me-1"></i>${new Date(f.fecha_pago).toLocaleString('es-MX')}</small>` : ''}
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Fecha de Pago</label>
                            <input type="date" id="fechaPago" class="form-control" value="${f && f.fecha_pago ? f.fecha_pago : ''}" ${!puedeModificar ? 'disabled' : ''}>
                            ${puedeModificar ? `<button class="btn-azul-almacenatec" style="margin-top:8px;width:100%;padding:6px;" onclick="guardarFechaPago(${c.id})">Guardar Fecha</button>` : ''}
                        </div>
                    </div>
                `;
                document.getElementById('modalFinanzasBody').innerHTML = html;
                new bootstrap.Modal(document.getElementById('modalFinanzas')).show();
            })
            .catch(err => alert('Error al cargar finanzas: ' + err));
    }

    function guardarFechaPago(compraId) {
        if (!puedeModificar) { alert('No tienes permisos.'); return; }
        const fecha = document.getElementById('fechaPago').value;
        if (!fecha) { alert('Selecciona una fecha de pago.'); return; }
        fetch('<?= BASE_PATH ?>handlers/guardar_fecha_pago.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'compra_id=' + compraId + '&fecha_pago=' + fecha
        })
        .then(r => r.json())
        .then(data => {
            if (data.ok) { alert('✅ Fecha guardada'); fetchCompras(); }
            else { alert('❌ Error: ' + data.error); }
        })
        .catch(err => { alert('❌ Error de conexión'); });
    }

    function abrirDetalles(ordenId) {
        fetch('<?= BASE_PATH ?>includes/get_orden_detalle.php?id=' + ordenId)
            .then(r => r.json())
            .then(data => {
                if (!data.ok) return alert('Error: ' + data.error);
                const orden = data.orden;
                const detalles = data.detalles || [];
                if (!orden) return alert('Orden no encontrada');

                const tieneCompra = orden.compra_id !== null && orden.compra_id !== undefined;
                const entregado = orden.entregado == 1;
                const pagado = orden.pagado == 1;

                let html = `
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Detalles de la Orden de Compra</strong><br>
                        <small>Información completa de la orden y sus artículos.</small>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;margin-bottom:16px">
                        <div style="background:#F0F4F9;padding:12px;border-radius:8px;border-left:3px solid #0B2D6B">
                            <div style="font-size:10px;font-weight:700;color:#94A3B8;text-transform:uppercase;">No. Orden</div>
                            <div style="font-weight:700;font-size:14px;color:#0B2D6B">${orden.no_orden_compra || ''}</div>
                        </div>
                        <div style="background:#F0F4F9;padding:12px;border-radius:8px;border-left:3px solid #c9973a">
                            <div style="font-size:10px;font-weight:700;color:#94A3B8;text-transform:uppercase;">Fecha</div>
                            <div style="font-weight:600;font-size:14px">${orden.fecha || ''}</div>
                        </div>
                        <div style="background:#F0F4F9;padding:12px;border-radius:8px;border-left:3px solid #B81C2E">
                            <div style="font-size:10px;font-weight:700;color:#94A3B8;text-transform:uppercase;">Proveedor</div>
                            <div style="font-weight:600;font-size:14px">${orden.proveedor || ''}</div>
                        </div>
                    </div>
                    <table style="width:100%;font-size:13px;margin-bottom:12px">
                        <tr><td style="padding:5px 0;color:#94A3B8;width:150px">No. Requisición</td><td style="font-weight:600">${orden.no_requisicion || ''}</td></tr>
                        <tr><td style="padding:5px 0;color:#94A3B8">Área Solicitante</td><td>${orden.area_solicitante || ''}</td></tr>
                        <tr><td style="padding:5px 0;color:#94A3B8">Condiciones</td><td>${orden.condiciones || '—'}</td></tr>
                        <tr><td style="padding:5px 0;color:#94A3B8">Vendedor</td><td>${orden.vendedor || '—'}</td></tr>
                        <tr><td style="padding:5px 0;color:#94A3B8">Vía de Embarque</td><td>${orden.via_embarque || '—'}</td></tr>
                        <tr><td style="padding:5px 0;color:#94A3B8">Estatus</td><td><span class="badge-almacenatec ${orden.estatus || 'pendiente'}">${orden.estatus || 'Pendiente'}</span></td></tr>
                    </table>`;

                if (detalles.length > 0) {
                    html += `<hr style="border-color:#E2E8F0"><h6><i class="bi bi-list-ul me-1"></i>Artículos</h6>
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

                const estadoCompra = tieneCompra ? '✅ Tiene compra registrada' : '❌ Sin compra registrada';
                const estadoEntrega = entregado ? '✅ Entregado' : '❌ No entregado';
                const estadoPago = pagado ? '✅ Pagado' : '❌ No pagado';
                const estadoColor = tieneCompra && entregado && pagado ? '#059669' : (tieneCompra ? '#d97706' : '#dc2626');

                html += `
                    <div style="margin-top:16px;border-top:1px solid #E2E8F0;padding-top:16px">
                        <div style="display:flex;justify-content:flex-end;gap:30px;flex-wrap:wrap;">
                            <div><strong>Subtotal:</strong> $${Number(orden.subtotal || 0).toFixed(2)}</div>
                            <div><strong>IVA:</strong> $${Number(orden.iva || 0).toFixed(2)}</div>
                            <div style="font-size:18px;color:#0B2D6B;"><strong>Total:</strong> $${Number(orden.total || 0).toFixed(2)}</div>
                        </div>
                    </div>
                    <div style="margin-top:12px;padding:12px;background:#F0F4F9;border-radius:8px;border-left:4px solid ${estadoColor};">
                        <div style="font-size:12px;color:#475569;">
                            <i class="bi bi-info-circle me-1"></i>
                            <strong>Información de la compra:</strong><br>
                            <span style="display:inline-block;margin-top:4px;">
                                ${estadoCompra} | ${estadoEntrega} | ${estadoPago}
                            </span>
                            ${pagado && orden.fecha_pago ? `<br><span style="color:#0B2D6B;font-weight:600;"><i class="bi bi-clock me-1"></i>Fecha de pago: ${new Date(orden.fecha_pago).toLocaleString('es-MX')}</span>` : ''}
                            ${tieneCompra && entregado && pagado ? '<br><span style="color:#059669;font-weight:600;">🎯 PROCESO COMPLETADO</span>' : ''}
                            ${tieneCompra && entregado && !pagado ? '<br><span style="color:#d97706;font-weight:600;">⏳ PENDIENTE DE PAGO</span>' : ''}
                            ${tieneCompra && !entregado ? '<br><span style="color:#dc2626;font-weight:600;">⏳ PENDIENTE DE ENTREGA</span>' : ''}
                            ${!tieneCompra ? '<br><span style="color:#dc2626;font-weight:600;">⚠️ SIN COMPRA REGISTRADA</span>' : ''}
                        </div>
                    </div>
                `;

                document.getElementById('modalDetallesBody').innerHTML = html;
                new bootstrap.Modal(document.getElementById('modalDetalles')).show();
            })
            .catch(err => alert('Error al cargar detalles: ' + err));
    }

    document.addEventListener('DOMContentLoaded', function() {
        fetchCompras();
        document.getElementById('searchItem').addEventListener('input', function() {
            const q = this.value.toLowerCase();
            document.querySelectorAll('#comprasTableBody tr').forEach(row => {
                row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
            });
        });
    });
</script>
</body>
</html>