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
    <title>Inventario de Activos Fijos — AlmacenaTec</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .badge-tipo {
            display: inline-block; padding: 3px 12px; border-radius: 20px; font-size: 11px; font-weight: 600;
        }
        .badge-tipo.entrada { background: #d1fae5; color: #059669; }
        .badge-tipo.salida { background: #fee2e2; color: #dc2626; }
        .badge-disponible { color: #059669; font-size: 18px; }
        .badge-no-disponible { color: #dc2626; font-size: 18px; }
        .filtros {
            display: flex; gap: 12px; flex-wrap: wrap; align-items: center;
        }
        .filtros .btn-filtro {
            padding: 5px 16px; border-radius: 20px; border: 1.5px solid var(--c-border);
            background: #fff; font-size: 12px; font-weight: 600; cursor: pointer;
            transition: all 0.2s; color: var(--c-text-md);
        }
        .filtros .btn-filtro:hover { border-color: var(--c-primary); color: var(--c-primary); }
        .filtros .btn-filtro.active { background: var(--c-primary); color: #fff; border-color: var(--c-primary); }
        .filtros .btn-filtro.active-disponible { background: #059669; color: #fff; border-color: #059669; }
        .filtros .btn-filtro.active-no-disponible { background: #dc2626; color: #fff; border-color: #dc2626; }
        .btn-exportar {
            background: #16a34a; color: #fff; border: none; padding: 6px 16px;
            border-radius: var(--radius-sm); font-size: 13px; font-weight: 600;
            cursor: pointer; transition: all .2s;
        }
        .btn-exportar:hover { background: #15803d; color: #fff; }
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
    <a href="../orden_compra/facturas_lista.php" class="sidebar-link"><i class="bi bi-file-earmark-text"></i> Facturas</a>
    
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
                <h1><i class="bi bi-grid-3x3-gap-fill me-2" style="color:#059669"></i>Inventario de Activos Fijos</h1>
                <div class="topbar-breadcrumb"><a href="../home/index.php">Inicio</a> &rsaquo; <a href="index.php">Activo Fijo</a> &rsaquo; Inventario</div>
            </div>
            <div style="display:flex;align-items:center;gap:10px;flex-shrink:0">
                <span style="font-size:13px;color:var(--c-text-md)"><i class="bi bi-person-circle me-1"></i><?= $nombre ?></span>
                <a href="<?= BASE_PATH ?>logout.php" class="btn-accent-brand" style="padding:6px 12px;font-size:12px"><i class="bi bi-box-arrow-right me-1"></i> Salir</a>
            </div>
        </header>

        <main class="content">
            <div class="page-header">
                <h2><i class="bi bi-grid-3x3-gap-fill me-2" style="color:#059669"></i>Inventario de Activos Fijos</h2>
                <div style="display:flex;gap:8px;flex-wrap:wrap">
                    <button class="btn-primary-brand" onclick="location.href='capturar.php?tipo=entrada'"><i class="bi bi-plus-lg"></i> Nueva Entrada</button>
                    <button class="btn-exportar" onclick="exportarExcel()"><i class="bi bi-file-earmark-excel me-1"></i> Exportar Excel</button>
                </div>
            </div>

            <div class="card-panel">
                <div class="card-panel-header">
                    <h3><i class="bi bi-table me-2" style="color:var(--c-primary)"></i>Lista de Activos Fijos</h3>
                    <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center">
                        <div class="search-wrap" style="min-width:200px">
                            <i class="bi bi-search"></i>
                            <input type="text" id="searchItem" class="form-control" placeholder="Buscar…">
                        </div>
                    </div>
                </div>

                <!-- Filtros -->
                <div style="padding:12px 20px;border-bottom:1px solid var(--c-border);background:#fafbfc">
                    <div class="filtros">
                        <span style="font-size:12px;font-weight:600;color:var(--c-text-muted);text-transform:uppercase;letter-spacing:.5px"><i class="bi bi-funnel me-1"></i>Filtrar:</span>
                        <button class="btn-filtro active" data-filtro="todos" onclick="filtrar('todos', this)">Todos</button>
                        <button class="btn-filtro" data-filtro="disponible" onclick="filtrar('disponible', this)">✅ Disponibles</button>
                        <button class="btn-filtro" data-filtro="no_disponible" onclick="filtrar('no_disponible', this)">❌ No disponibles</button>
                    </div>
                </div>

                <div class="table-wrap">
                    <table class="table table-striped table-hover text-center mb-0" id="inventarioTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Folio</th>
                                <th>Núm. Oficina</th>
                                <th>Fecha</th>
                                <th>Descripción</th>
                                <th>Marca</th>
                                <th>Modelo</th>
                                <th>Serie</th>
                                <th>Departamento</th>
                                <th>Disponible</th>
                            </tr>
                        </thead>
                        <tbody id="inventarioTableBody">
                            <tr><td colspan="10" class="text-muted">Cargando datos...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
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

    let todosLosActivos = [];
    let filtroActual = 'todos';

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

    async function fetchActivos() {
        const errDiv = getOrCreateErrorDiv();
        try {
            const res = await fetch('<?= BASE_PATH ?>includes/get_activos_inventario.php');
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
            todosLosActivos = data.activos || [];
            aplicarFiltros();
        } catch(err) {
            const d = getOrCreateErrorDiv();
            d.style.display = 'block';
            d.textContent = 'Error: ' + (err && err.message ? err.message : String(err));
        }
    }

    function renderActivos(rows) {
        const tbody = document.getElementById('inventarioTableBody');
        tbody.innerHTML = '';
        if (!rows || rows.length === 0) {
            tbody.innerHTML = '<tr><td colspan="10" class="text-muted">No hay activos fijos registrados.</td></tr>';
            return;
        }
        for (const a of rows) {
            const tr = document.createElement('tr');
            const dispIcon = a.disponible ? 'bi-check-circle-fill' : 'bi-x-circle-fill';
            const dispClass = a.disponible ? 'badge-disponible' : 'badge-no-disponible';
            const dispText = a.disponible ? 'Disponible' : 'No disponible';
            tr.innerHTML = `
                <td><span style="font-weight:600;color:var(--c-primary)">${a.id}</span></td>
                <td><span style="font-family:var(--font-brand);font-weight:600">${a.folio || ''}</span></td>
                <td>${a.num_oficina || ''}</td>
                <td><small style="color:var(--c-text-md)">${a.fecha || ''}</small></td>
                <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="${a.descripcion_bien || ''}">${a.descripcion_bien || ''}</td>
                <td>${a.marca || ''}</td>
                <td>${a.modelo || ''}</td>
                <td>${a.serie || ''}</td>
                <td>${a.departamento_recibe || ''}</td>
                <td><i class="bi ${dispIcon} ${dispClass}"></i> ${dispText}</td>
            `;
            tbody.appendChild(tr);
        }
    }

    function filtrar(tipo, btn) {
        filtroActual = tipo;
        document.querySelectorAll('.btn-filtro').forEach(b => b.classList.remove('active', 'active-disponible', 'active-no-disponible'));
        if (btn) {
            btn.classList.add('active');
            if (tipo === 'disponible') btn.classList.add('active-disponible');
            else if (tipo === 'no_disponible') btn.classList.add('active-no-disponible');
        }
        aplicarFiltros();
    }

    function aplicarFiltros() {
        const busqueda = (document.getElementById('searchItem').value || '').toLowerCase().trim();
        let filtradas = [...todosLosActivos];

        if (filtroActual === 'disponible') {
            filtradas = filtradas.filter(a => a.disponible == 1);
        } else if (filtroActual === 'no_disponible') {
            filtradas = filtradas.filter(a => a.disponible == 0);
        }

        if (busqueda) {
            filtradas = filtradas.filter(a =>
                (a.num_oficina || '').toLowerCase().includes(busqueda) ||
                (a.descripcion_bien || '').toLowerCase().includes(busqueda) ||
                (a.marca || '').toLowerCase().includes(busqueda) ||
                (a.modelo || '').toLowerCase().includes(busqueda) ||
                (a.serie || '').toLowerCase().includes(busqueda) ||
                (a.departamento_recibe || '').toLowerCase().includes(busqueda) ||
                (a.folio || '').toLowerCase().includes(busqueda)
            );
        }

        renderActivos(filtradas);
    }

    function exportarExcel() {
        // Obtener datos visibles (filtrados)
        const rows = document.querySelectorAll('#inventarioTableBody tr');
        let csv = 'ID,Folio,Núm.Oficina,Fecha,Descripción,Marca,Modelo,Serie,Departamento,Disponible\n';
        
        rows.forEach(row => {
            if (row.style.display === 'none') return;
            const cells = row.querySelectorAll('td');
            if (cells.length < 10) return;
            const data = [
                cells[0].textContent.trim(),
                cells[1].textContent.trim(),
                cells[2].textContent.trim(),
                cells[3].textContent.trim(),
                cells[4].textContent.trim(),
                cells[5].textContent.trim(),
                cells[6].textContent.trim(),
                cells[7].textContent.trim(),
                cells[8].textContent.trim(),
                cells[9].textContent.trim()
            ];
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

    document.addEventListener('DOMContentLoaded', function() {
        fetchActivos();
        document.getElementById('searchItem').addEventListener('input', function() {
            aplicarFiltros();
        });
    });
</script>
</body>
</html>