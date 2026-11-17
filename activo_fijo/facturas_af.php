<?php
require_once __DIR__ . '/../includes/auth.php';
$nombre = htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Usuario');
$rol = $_SESSION['usuario_rol'] ?? 'usuario';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facturas AF — AlmacenaTec</title>
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
        .badge-almacenatec.emitida { background: #d1fae5; color: #059669; }
        .badge-almacenatec.cancelada { background: #fee2e2; color: #dc2626; }
        .badge-almacenatec.partida_5000 { background: #dbeafe; color: #0B2D6B; }
        .badge-almacenatec.licitacion { background: #d1fae5; color: #059669; }
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
        .btn-almacenatec.ver {
            background: rgba(11,45,107,.08);
            color: #0B2D6B;
            border: none;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
        }
        .btn-almacenatec.ver:hover { background: #0B2D6B; color: #FFFFFF; }
        
        .btn-switch {
            background: #0B2D6B;
            color: #fff;
            border: none;
            padding: 9px 18px;
            border-radius: 6px;
            font-size: 13.5px;
            font-weight: 600;
            cursor: pointer;
            transition: all .2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-switch:hover { background: #071d46; color: #fff; }
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
            <a href="index.php" class="sidebar-link"><i class="bi bi-safe-fill"></i> Activo Fijo</a>
            <a href="main_activF.php" class="sidebar-link"><i class="bi bi-clipboard2-data"></i> Historial Salidas</a>
            
            <!-- NUEVO BOTÓN UNIFICADO -->
            <a href="requisiciones_af.php" class="sidebar-link active"><i class="bi bi-cart3"></i> Almacén General</a>
        </nav>
        <div class="sidebar-footer"><i class="bi bi-geo-alt-fill me-1"></i> TecNM · Campus Saltillo · 2025</div>
    </aside>

    <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

    <div class="main-wrapper" id="mainWrapper">
        <header class="topbar">
            <button class="topbar-toggle" onclick="toggleSidebar()"><i class="bi bi-list" style="font-size:22px"></i></button>
            <div class="topbar-info">
                <h1><i class="bi bi-file-earmark-text me-2" style="color:#0B2D6B"></i>Facturas AF</h1>
                <div class="topbar-breadcrumb"><a href="../home/index.php">Inicio</a> &rsaquo; Activo Fijo &rsaquo; Facturas</div>
            </div>
            <div style="display:flex;align-items:center;gap:10px;flex-shrink:0">
                <span style="font-size:13px;color:#475569"><i class="bi bi-person-circle me-1"></i><?= $nombre ?></span>
                <a href="<?= BASE_PATH ?>logout.php" class="btn-accent-brand" style="padding:6px 12px;font-size:12px"><i class="bi bi-box-arrow-right me-1"></i> Salir</a>
            </div>
        </header>

        <main class="content">
            <div class="page-header">
                <h2><i class="bi bi-file-earmark-text me-2" style="color:#0B2D6B"></i>Facturas de Activo Fijo</h2>
                <!-- BOTÓN PARA IR A REQUISICIONES -->
                <a href="requisiciones_af.php" class="btn-switch">
                    <i class="bi bi-file-earmark-text"></i> Ir a Requisiciones
                </a>
            </div>

            <div class="tabla-titulo">
                <i class="bi bi-file-earmark-text"></i>
                Facturas de Partida 5000 y Licitación
                <small>Solo facturas de activos fijos</small>
            </div>

            <div class="card-panel">
                <div class="card-header-almacenatec">
                    <h3><i class="bi bi-table"></i>Lista de Facturas</h3>
                    <div class="search-almacenatec">
                        <i class="bi bi-search"></i>
                        <input type="text" id="searchItem" placeholder="Buscar por número, cliente, orden…">
                    </div>
                </div>

                <div style="overflow-x:auto;">
                    <table class="table-almacenatec">
                        <thead>
                            <tr>
                                <th><i class="bi bi-hash"></i> N° Factura</th>
                                <th><i class="bi bi-calendar"></i> Fecha</th>
                                <th><i class="bi bi-person"></i> Cliente</th>
                                <th><i class="bi bi-cart"></i> No. Orden</th>
                                <th><i class="bi bi-tags"></i> Tipo</th>
                                <th><i class="bi bi-cash"></i> Total</th>
                                <th><i class="bi bi-info-circle"></i> Estatus</th>
                                <th><i class="bi bi-tools"></i> Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="facturasTableBody">
                            <tr><td colspan="8" class="text-center text-muted">Cargando datos...</td></tr>
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

    async function fetchFacturas() {
        try {
            const res = await fetch('<?= BASE_PATH ?>includes/get_facturas.php');
            const data = await res.json();
            if (!data.ok) throw new Error(data.error);
            renderFacturas(data.facturas || []);
        } catch(err) { console.error(err); }
    }

    function renderFacturas(rows) {
        const tbody = document.getElementById('facturasTableBody');
        tbody.innerHTML = '';
        if (!rows || rows.length === 0) {
            tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted">No hay facturas de Partida 5000 o Licitación.</td></tr>';
            return;
        }
        const estatusLabels = { 'pendiente': 'Pendiente', 'emitida': 'Emitida', 'cancelada': 'Cancelada' };
        const tipoLabels = { 'normal': 'Normal', 'partida_5000': 'Partida 5000', 'licitacion': 'Licitación' };
        for (const f of rows) {
            const tipo = f.tipo_compra || 'normal';
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td><span style="font-weight:600;color:#0B2D6B">${f.numero_factura || ''}</span></td>
                <td>${f.fecha || ''}</td>
                <td>${f.cliente_nombre || ''}</td>
                <td>${f.no_orden_compra || ''}</td>
                <td><span class="badge-almacenatec ${tipo}">${tipoLabels[tipo] || tipo}</span></td>
                <td>$${Number(f.total || 0).toFixed(2)}</td>
                <td><span class="badge-almacenatec ${f.estatus || 'pendiente'}">${estatusLabels[f.estatus] || f.estatus}</span></td>
                <td><button class="btn-almacenatec ver" onclick="verDetalle(${f.id})"><i class="bi bi-eye"></i></button></td>
            `;
            tbody.appendChild(tr);
        }
    }

    function verDetalle(id) {
        fetch('<?= BASE_PATH ?>includes/get_factura_detalle.php?id=' + id)
            .then(r => r.json())
            .then(data => {
                if (!data.ok) return alert('Error: ' + data.error);
                const f = data.factura;
                const detalles = data.detalles || [];
                if (!f) return alert('Factura no encontrada');

                const estatusLabels = { 'pendiente': 'Pendiente', 'emitida': 'Emitida', 'cancelada': 'Cancelada' };
                const tipoLabels = { 'normal': 'Normal', 'partida_5000': 'Partida 5000', 'licitacion': 'Licitación' };
                const tipo = f.tipo_compra || 'normal';

                let html = `
                    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;margin-bottom:16px">
                        <div style="background:#f8faff;padding:12px;border-radius:8px;border-left:3px solid #0B2D6B">
                            <div style="font-size:11px;font-weight:700;color:#94A3B8;text-transform:uppercase;">N° Factura</div>
                            <div style="font-weight:700;font-size:16px;color:#0B2D6B">${f.numero_factura || ''}</div>
                        </div>
                        <div style="background:#f8faff;padding:12px;border-radius:8px;border-left:3px solid #c9973a">
                            <div style="font-size:11px;font-weight:700;color:#94A3B8;text-transform:uppercase;">Fecha</div>
                            <div style="font-weight:600;font-size:15px">${f.fecha || ''}</div>
                        </div>
                        <div style="background:#f8faff;padding:12px;border-radius:8px;border-left:3px solid #059669">
                            <div style="font-size:11px;font-weight:700;color:#94A3B8;text-transform:uppercase;">Tipo</div>
                            <div><span class="badge-almacenatec ${tipo}">${tipoLabels[tipo] || tipo}</span></div>
                        </div>
                    </div>
                    <table style="width:100%;font-size:13px;margin-bottom:12px">
                        <tr><td style="padding:5px 0;color:#94A3B8;width:150px">Cliente</td><td style="font-weight:600">${f.cliente_nombre || ''}</td></tr>
                        <tr><td style="padding:5px 0;color:#94A3B8">Dirección</td><td>${f.cliente_direccion || ''}</td></tr>
                        <tr><td style="padding:5px 0;color:#94A3B8">RFC</td><td style="font-weight:600">${f.cliente_rfc || ''}</td></tr>
                        <tr><td style="padding:5px 0;color:#94A3B8">No. Orden</td><td style="font-weight:600">${f.no_orden_compra || ''}</td></tr>
                        <tr><td style="padding:5px 0;color:#94A3B8">No. Requisición</td><td style="font-weight:600">${f.no_requisicion || ''}</td></tr>
                    </table>`;

                if (detalles.length > 0) {
                    html += `<hr style="border-color:#E2E8F0"><h6><i class="bi bi-list-ul me-1"></i>Detalles</h6>
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
                            <div><strong>IVA:</strong> $${Number(f.iva || 0).toFixed(2)}</div>
                            <div style="font-size:18px;color:#0B2D6B;"><strong>Total:</strong> $${Number(f.total || 0).toFixed(2)}</div>
                        </div>
                    </div>`;

                document.getElementById('modalFacturaBody').innerHTML = html;
                new bootstrap.Modal(document.getElementById('modalFactura')).show();
            })
            .catch(err => alert('Error al cargar detalles: ' + err));
    }

    document.addEventListener('DOMContentLoaded', function() {
        fetchFacturas();
        document.getElementById('searchItem').addEventListener('input', function() {
            const q = this.value.toLowerCase();
            document.querySelectorAll('#facturasTableBody tr').forEach(row => {
                row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
            });
        });
    });
</script>
</body>
</html>