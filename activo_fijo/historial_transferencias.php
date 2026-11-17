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
    <title>Historial de Transferencias — AlmacenaTec</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        /* ============================================================
           PALETA DE COLORES LOGIN
           ============================================================ */
        :root {
            --c-primary: #0B2D6B;
            --c-primary-dk: #071d46;
            --c-gold: #c9973a;
            --c-accent: #B81C2E;
            --c-bg: #F0F4F9;
            --c-surface: #FFFFFF;
            --c-border: #E2E8F0;
            --c-text: #1E293B;
            --c-text-md: #475569;
            --c-text-muted: #94A3B8;
            --radius-sm: 6px;
            --radius: 10px;
            --shadow-md: 0 4px 14px rgba(0,0,0,.08);
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

        /* ── Badge transferencia ── */
        .badge-transferencia {
            display: inline-block;
            padding: 3px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            background: #fef3c7;
            color: #d97706;
        }

        /* ── Tarjeta de transferencia ── */
        .card-transferencia {
            background: #FFFFFF;
            border: 1px solid #E2E8F0;
            border-radius: 10px;
            padding: 16px 20px;
            margin-bottom: 12px;
            transition: all 0.2s;
        }
        .card-transferencia:hover {
            box-shadow: 0 4px 14px rgba(0,0,0,.08);
            border-color: #0B2D6B;
        }
        .card-transferencia .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
        }
        .card-transferencia .header .titulo {
            font-weight: 700;
            font-size: 16px;
            color: #1E293B;
        }
        .card-transferencia .detalles {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 8px;
            margin-top: 8px;
            font-size: 13px;
        }
        .card-transferencia .detalles .item {
            color: #94A3B8;
        }
        .card-transferencia .detalles .item strong {
            color: #1E293B;
            font-weight: 600;
        }
        .card-transferencia .arrow {
            color: #8b5cf6;
            font-size: 20px;
            font-weight: 700;
        }

        /* ── Botones ── */
        .btn-transferir {
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
        .btn-transferir:hover {
            background: #0B2D6B;
            color: #fff;
        }

        .btn-outline-brand {
            background: transparent;
            color: #0B2D6B !important;
            border: 1.5px solid #0B2D6B;
            padding: 8px 18px;
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
        .btn-outline-brand:hover {
            background: #0B2D6B;
            color: #fff !important;
        }

        /* ── Responsive ── */
        @media (max-width: 768px) {
            .card-transferencia .detalles {
                grid-template-columns: 1fr 1fr;
            }
            .search-almacenatec input {
                min-width: 140px;
            }
        }
        @media (max-width: 480px) {
            .card-transferencia .detalles {
                grid-template-columns: 1fr;
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
                <h1><i class="bi bi-clock-history me-2" style="color:#8b5cf6"></i>Historial de Transferencias</h1>
                <div class="topbar-breadcrumb"><a href="../home/index.php">Inicio</a> &rsaquo; <a href="index.php">Activo Fijo</a> &rsaquo; Historial Transferencias</div>
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
                <i class="bi bi-clock-history"></i>
                Historial de Transferencias
                <small>Registro de todos los movimientos de activos entre áreas y resguardantes</small>
            </div>

            <!-- ============================================================ -->
            <!-- BOTONES DE ACCIÓN                                             -->
            <!-- ============================================================ -->
            <div class="page-header" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;margin-bottom:20px;">
                <div></div>
                <div style="display:flex;gap:8px;flex-wrap:wrap;">
                    <a href="transferir.php" class="btn-transferir">
                        <i class="bi bi-arrow-left-right me-1"></i> Nueva Transferencia
                    </a>
                    <a href="index.php" class="btn-outline-brand">
                        <i class="bi bi-arrow-left me-1"></i> Volver
                    </a>
                </div>
            </div>

            <!-- ============================================================ -->
            <!-- LISTA DE TRANSFERENCIAS                                       -->
            <!-- ============================================================ -->
            <div class="card-panel">
                <div class="card-header-almacenatec">
                    <h3><i class="bi bi-clock-history"></i>Registros de Transferencias</h3>
                    <div class="search-almacenatec">
                        <i class="bi bi-search"></i>
                        <input type="text" id="searchItem" placeholder="Buscar por activo, área, resguardante…">
                    </div>
                </div>
                <div id="transferenciasContainer" style="padding:16px 20px;">
                    <div class="text-center text-muted py-4">Cargando datos...</div>
                </div>
            </div>

        </main>
    </div>
</div>

<script src="../js/bootstrap.bundle.min.js"></script>
<script>
    let todasLasTransferencias = [];

    function toggleSidebar() {
        const s = document.getElementById('sidebar'), o = document.getElementById('sidebarOverlay'), w = document.getElementById('mainWrapper');
        if (window.innerWidth <= 1100) { const open = s.classList.toggle('open'); o.classList.toggle('visible', open); }
        else { s.classList.toggle('collapsed'); w.classList.toggle('sidebar-hidden'); }
    }
    function closeSidebar() { document.getElementById('sidebar').classList.remove('open'); document.getElementById('sidebarOverlay').classList.remove('visible'); }

    // ──────────────────────────────────────────────────────────────
    // CARGAR TRANSFERENCIAS
    // ──────────────────────────────────────────────────────────────
    async function fetchTransferencias() {
        try {
            const res = await fetch('<?= BASE_PATH ?>includes/get_historial_transferencias.php');
            const data = await res.json();
            if (!data.ok) throw new Error(data.error);
            todasLasTransferencias = data.transferencias || [];
            renderTransferencias(todasLasTransferencias);
        } catch(err) {
            console.error(err);
            document.getElementById('transferenciasContainer').innerHTML = '<div class="alert alert-danger">Error al cargar datos: ' + err.message + '</div>';
        }
    }

    function renderTransferencias(rows) {
        const container = document.getElementById('transferenciasContainer');
        const busqueda = document.getElementById('searchItem').value.toLowerCase().trim();

        let filtradas = rows;
        if (busqueda) {
            filtradas = filtradas.filter(t =>
                (t.descripcion_bien || '').toLowerCase().includes(busqueda) ||
                (t.marca || '').toLowerCase().includes(busqueda) ||
                (t.modelo || '').toLowerCase().includes(busqueda) ||
                (t.serie || '').toLowerCase().includes(busqueda) ||
                (t.num_oficina || '').toLowerCase().includes(busqueda) ||
                (t.area_origen || '').toLowerCase().includes(busqueda) ||
                (t.area_destino || '').toLowerCase().includes(busqueda) ||
                (t.resguardante_origen || '').toLowerCase().includes(busqueda) ||
                (t.resguardante_destino || '').toLowerCase().includes(busqueda)
            );
        }

        if (filtradas.length === 0) {
            container.innerHTML = '<div class="alert alert-info text-center py-4"><i class="bi bi-inbox" style="font-size:32px;display:block;margin-bottom:8px;"></i>No hay transferencias registradas.</div>';
            return;
        }

        let html = '';
        for (const t of filtradas) {
            html += `
                <div class="card-transferencia">
                    <div class="header">
                        <div>
                            <span class="titulo">${t.descripcion_bien || 'Sin descripción'}</span>
                            <span class="badge-transferencia">Transferencia</span>
                            <span style="font-size:12px;color:#94A3B8;margin-left:8px;">${t.fecha || ''}</span>
                        </div>
                        <div>
                            <span style="font-size:12px;color:#94A3B8;">Oficina: ${t.num_oficina || 'N/A'}</span>
                        </div>
                    </div>
                    <div class="detalles">
                        <div class="item"><strong>Serie:</strong> ${t.serie || 'N/A'}</div>
                        <div class="item"><strong>Marca:</strong> ${t.marca || 'N/A'}</div>
                        <div class="item"><strong>Modelo:</strong> ${t.modelo || 'N/A'}</div>
                        <div class="item" style="grid-column: span 3; text-align:center; padding:8px 0; background:#F0F4F9; border-radius:6px;">
                            <span style="font-size:13px;color:#475569;">
                                <strong style="color:#0B2D6B;">${t.area_origen || 'Sin asignar'}</strong> 
                                <span class="arrow"> ➜ </span>
                                <strong style="color:#0B2D6B;">${t.area_destino || 'Sin asignar'}</strong>
                            </span>
                            <br>
                            <span style="font-size:12px;color:#475569;">
                                <strong style="color:#0B2D6B;">${t.resguardante_origen || 'Sin asignar'}</strong> 
                                <span class="arrow"> ➜ </span>
                                <strong style="color:#0B2D6B;">${t.resguardante_destino || 'Sin asignar'}</strong>
                            </span>
                        </div>
                        ${t.motivo ? `<div class="item" style="grid-column: span 3;"><strong>Motivo:</strong> ${t.motivo}</div>` : ''}
                    </div>
                </div>
            `;
        }
        container.innerHTML = html;
    }

    document.addEventListener('DOMContentLoaded', function() {
        fetchTransferencias();
        document.getElementById('searchItem').addEventListener('input', function() {
            renderTransferencias(todasLasTransferencias);
        });
    });
</script>
</body>
</html>