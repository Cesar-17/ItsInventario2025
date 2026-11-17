<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Vales — AlmacenaTec</title>
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
        .badge-almacenatec.autorizado { background: #dbeafe; color: #0B2D6B; }
        .badge-almacenatec.entregado { background: #d1fae5; color: #059669; }
        .badge-almacenatec.cancelado { background: #fee2e2; color: #dc2626; }
        .badge-almacenatec.herramienta { background: #fef3c7; color: #d97706; }
        .badge-almacenatec.material { background: #dbeafe; color: #0B2D6B; }

        .btn-primary-almacenatec {
            background: #0B2D6B; color: #FFFFFF; border: none;
            padding: 9px 18px; border-radius: 8px; font-size: 13.5px;
            font-weight: 600; cursor: pointer; transition: all 0.2s;
            text-decoration: none; display: inline-flex; align-items: center; gap: 6px;
        }
        .btn-primary-almacenatec:hover { background: #071d46; color: #FFFFFF; }
        .btn-primary-almacenatec i { color: #c9973a; }
        .btn-primary-almacenatec i:hover { color: #FFFFFF; }

        .btn-outline-almacenatec {
            background: transparent; color: #0B2D6B; border: 1.5px solid #0B2D6B;
            padding: 7px 17px; border-radius: 8px; font-size: 13.5px;
            font-weight: 600; cursor: pointer; transition: all 0.2s;
            text-decoration: none; display: inline-flex; align-items: center; gap: 6px;
        }
        .btn-outline-almacenatec:hover { background: #0B2D6B; color: #FFFFFF; }
        .btn-outline-almacenatec i { color: #0B2D6B; }
        .btn-outline-almacenatec:hover i { color: #FFFFFF; }

        .btn-accent-almacenatec {
            background: #B81C2E; color: #FFFFFF; border: none;
            padding: 9px 18px; border-radius: 8px; font-size: 13.5px;
            font-weight: 600; cursor: pointer; transition: all 0.2s;
            text-decoration: none; display: inline-flex; align-items: center; gap: 6px;
        }
        .btn-accent-almacenatec:hover { background: #8f1522; color: #FFFFFF; }

        .btn-warning-almacenatec {
            background: #d97706; color: #FFFFFF; border: none;
            padding: 9px 18px; border-radius: 8px; font-size: 13.5px;
            font-weight: 600; cursor: pointer; transition: all 0.2s;
            text-decoration: none; display: inline-flex; align-items: center; gap: 6px;
        }
        .btn-warning-almacenatec:hover { background: #b45309; color: #FFFFFF; }

        .btn-print-almacenatec {
            background: #0B2D6B; color: #FFFFFF; border: none;
            padding: 6px 14px; border-radius: 6px; font-size: 12px;
            font-weight: 600; cursor: pointer; transition: all 0.2s;
            display: inline-flex; align-items: center; gap: 4px;
        }
        .btn-print-almacenatec:hover { background: #071d46; color: #FFFFFF; }

        .btn-eliminar-almacenatec {
            background: #B81C2E; color: #FFFFFF; border: none;
            padding: 6px 10px; border-radius: 6px; font-size: 12px;
            font-weight: 600; cursor: pointer; transition: all 0.2s;
            display: inline-flex; align-items: center; gap: 4px;
        }
        .btn-eliminar-almacenatec:hover { background: #8f1522; color: #FFFFFF; }

        .btn-ver-almacenatec {
            background: rgba(11,45,107,.08); color: #0B2D6B; border: none;
            padding: 6px 10px; border-radius: 6px; font-size: 12px;
            font-weight: 600; cursor: pointer; transition: all 0.2s;
            display: inline-flex; align-items: center; gap: 4px;
        }
        .btn-ver-almacenatec:hover { background: #0B2D6B; color: #FFFFFF; }

        .select-estatus-almacenatec {
            width: auto; font-size: 12px; padding: 4px 24px 4px 8px;
            border-radius: 6px; border: 1.5px solid #E2E8F0;
            background: #fff; cursor: pointer; color: #1E293B;
        }
        .select-estatus-almacenatec:focus { outline: none; border-color: #0B2D6B; }

        .vale-card {
            background: #fff; border: 1px solid #E2E8F0; border-radius: 10px;
            padding: 16px 20px; margin-bottom: 12px; transition: all 0.2s; cursor: pointer;
        }
        .vale-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,.08); border-color: #0B2D6B; }
        .vale-card .folio { font-weight: 700; color: #0B2D6B; font-size: 15px; }

        .filtros-almacenatec {
            display: flex; gap: 12px; margin-bottom: 16px;
            flex-wrap: wrap; align-items: center;
        }
        .filtros-almacenatec select,
        .filtros-almacenatec input {
            padding: 8px 12px; border: 1.5px solid #E2E8F0; border-radius: 8px;
            font-size: 13px; font-family: 'Inter', sans-serif;
            background: #FFFFFF; color: #1E293B;
        }
        .filtros-almacenatec select:focus,
        .filtros-almacenatec input:focus {
            outline: none; border-color: #0B2D6B;
        }

        .total-vales {
            background: #0B2D6B; color: #fff; padding: 12px 20px;
            border-radius: 10px; margin-bottom: 16px;
            display: flex; justify-content: space-between;
            align-items: center; flex-wrap: wrap; gap: 8px;
        }
        .total-vales span { font-weight: 700; font-size: 18px; }

        .search-almacenatec {
            position: relative; display: inline-flex; align-items: center;
        }
        .search-almacenatec i {
            position: absolute; left: 12px; color: #94A3B8;
        }
        .search-almacenatec input {
            padding: 8px 12px 8px 36px; border: 1.5px solid #E2E8F0;
            border-radius: 8px; font-size: 13px; font-family: 'Inter', sans-serif;
            background: #FFFFFF; color: #1E293B; transition: border-color 0.2s;
            min-width: 200px;
        }
        .search-almacenatec input:focus {
            outline: none; border-color: #0B2D6B;
            box-shadow: 0 0 0 3px rgba(11,45,107,.1);
        }

        /* ── Modal Reporte ── */
        .modal-header-almacenatec {
            background: #0B2D6B; color: #fff;
            border-radius: 10px 10px 0 0;
            padding: 16px 20px; border-bottom: none;
        }
        .modal-header-almacenatec h5 { color: #fff; margin: 0; }
        .modal-header-almacenatec .btn-close { filter: invert(1); }
        .filtro-reporte {
            background: #F0F4F9; padding: 16px; border-radius: 10px;
            margin-bottom: 16px;
        }
        .filtro-reporte label {
            font-size: 12px; font-weight: 600; color: #475569;
            margin-bottom: 4px; display: block;
        }
        .filtro-reporte .form-control,
        .filtro-reporte .form-select {
            border: 1.5px solid #E2E8F0; border-radius: 8px;
            padding: 8px 12px; font-size: 13px;
        }
        .filtro-reporte .form-control:focus,
        .filtro-reporte .form-select:focus {
            border-color: #0B2D6B; box-shadow: 0 0 0 3px rgba(11,45,107,.1);
        }

        @media print {
            .no-print { display: none !important; }
            .vale-card { border: 1px solid #ccc !important; page-break-inside: avoid; }
            .vale-detalle { display: block !important; }
            .select-estatus-almacenatec { display: none !important; }
            .btn-eliminar-almacenatec { display: none !important; }
            .btn-print-almacenatec { display: none !important; }
            .btn-ver-almacenatec { display: none !important; }
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
            <a href="../orden_compra/facturas_lista.php" class="sidebar-link"><i class="bi bi-file-earmark-text"></i> Facturas</a>
            
            <?php elseif ($_SESSION['usuario_rol'] === 'almacenista' || $_SESSION['usuario_rol'] === 'mantenimiento'): ?>
            <a href="../productos/main_productos.php" class="sidebar-link"><i class="bi bi-box-seam"></i> Productos</a>
            
            <?php else: ?>
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
        <div class="sidebar-footer"><i class="bi bi-geo-alt-fill me-1"></i> TecNM · Campus Saltillo · 2026</div>
    </aside>

    <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

    <div class="main-wrapper" id="mainWrapper">
        <header class="topbar">
            <button class="topbar-toggle" onclick="toggleSidebar()"><i class="bi bi-list" style="font-size:22px"></i></button>
            <div class="topbar-info">
                <h1><i class="bi bi-list-ul me-2" style="color:#0B2D6B"></i>Lista de Vales</h1>
                <div class="topbar-breadcrumb">
                    <a href="../home/index.php">Inicio</a> &rsaquo;
                    <a href="../productos/main_productos.php">Productos</a> &rsaquo; Vales
                </div>
            </div>
            <div style="display:flex;align-items:center;gap:10px;flex-shrink:0">
                <span style="font-size:13px;color:#475569"><i class="bi bi-person-circle me-1"></i><?= htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Usuario') ?></span>
                <a href="<?= BASE_PATH ?>logout.php" class="btn-accent-almacenatec" style="padding:6px 12px;font-size:12px"><i class="bi bi-box-arrow-right me-1"></i> Salir</a>
            </div>
        </header>

        <main class="content">

            <div class="page-header no-print">
                <h2><i class="bi bi-list-ul me-2" style="color:#0B2D6B"></i>Vales registrados</h2>
                <div style="display:flex;gap:8px;flex-wrap:wrap">
                    <a href="../productos/main_productos.php" class="btn-outline-almacenatec"><i class="bi bi-arrow-left me-1"></i> Volver</a>
                    <button onclick="abrirModalReporte()" class="btn-primary-almacenatec"><i class="bi bi-file-earmark-bar-graph me-1"></i> Generar Reporte</button>
                </div>
            </div>

            <?php
            // Obtener vales de herramienta
            $vales_herramienta = array();
            try {
                $stmt = $pdo->query("
                    SELECT vh.*, 
                           COUNT(dvh.id) as total_items
                    FROM vales_herramienta vh
                    LEFT JOIN detalles_vale_herramienta dvh ON dvh.vale_herramienta_id = vh.id
                    GROUP BY vh.id
                    ORDER BY vh.created_at DESC
                ");
                $vales_herramienta = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (Exception $e) {
                echo '<div class="alert alert-warning">No se pudieron cargar los vales de herramienta</div>';
            }

            // Obtener vales de material
            $vales_material = array();
            try {
                $stmt = $pdo->query("
                    SELECT vm.*, 
                           COUNT(ds.id) as total_items
                    FROM vales_material vm
                    LEFT JOIN detalles_salida ds ON ds.salida_id = vm.id
                    GROUP BY vm.id
                    ORDER BY vm.created_at DESC
                ");
                $vales_material = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (Exception $e) {
                echo '<div class="alert alert-warning">No se pudieron cargar los vales de material</div>';
            }

            $total_vales = count($vales_herramienta) + count($vales_material);
            ?>

            <div class="total-vales">
                <span><i class="bi bi-file-earmark-text me-2"></i>Total de vales: <?php echo $total_vales; ?></span>
                <span>
                    <span class="badge-almacenatec herramienta">Herramienta: <?php echo count($vales_herramienta); ?></span>
                    <span class="badge-almacenatec material" style="margin-left:8px;">Material: <?php echo count($vales_material); ?></span>
                </span>
            </div>

            <!-- Filtros -->
            <div class="filtros-almacenatec no-print">
                <select id="filtroTipo" onchange="filtrarVales()">
                    <option value="todos">Todos los vales</option>
                    <option value="herramienta">Vales de Herramienta</option>
                    <option value="material">Vales de Material</option>
                </select>
                <select id="filtroEstatus" onchange="filtrarVales()">
                    <option value="todos">Todos los estatus</option>
                    <option value="pendiente">Pendiente</option>
                    <option value="autorizado">Autorizado</option>
                    <option value="entregado">Entregado</option>
                    <option value="cancelado">Cancelado</option>
                </select>
                <div class="search-almacenatec">
                    <i class="bi bi-search"></i>
                    <input type="text" id="filtroBuscar" placeholder="Buscar por folio o solicita..." onkeyup="filtrarVales()">
                </div>
            </div>

            <div id="valesContainer">

                <!-- VALES DE HERRAMIENTA -->
                <?php if (!empty($vales_herramienta)): ?>
                <h4 class="mb-3" style="color:#0B2D6B"><i class="bi bi-tools me-2"></i>Vales de Herramienta</h4>
                <?php foreach ($vales_herramienta as $vale): ?>
                <div class="vale-card tipo-herramienta-card" 
                     data-id="<?= $vale['id'] ?>"
                     data-tipo="herramienta" 
                     data-fecha="<?= $vale['fecha'] ?>"
                     data-estatus="<?= $vale['estatus'] ?>" 
                     data-folio="<?= htmlspecialchars($vale['folio']) ?>" 
                     data-solicita="<?= strtolower(htmlspecialchars($vale['solicita'])) ?>"
                     data-autoriza="<?= htmlspecialchars($vale['autoriza']) ?>"
                     data-descripcion="<?= htmlspecialchars($vale['descripcion']) ?>">
                    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px">
                        <div>
                            <span class="folio"><?= htmlspecialchars($vale['folio']) ?></span>
                            <span class="badge-almacenatec herramienta"><i class="bi bi-tools me-1"></i>Herramienta</span>
                            <span class="badge-almacenatec <?= $vale['estatus'] ?>"><?= ucfirst($vale['estatus']) ?></span>
                        </div>
                        <div style="display:flex;gap:6px;flex-wrap:wrap">
                            <button class="btn-print-almacenatec no-print" onclick="imprimirVale(<?= $vale['id'] ?>, 'herramienta')">
                                <i class="bi bi-printer me-1"></i> Imprimir
                            </button>
                            <button class="btn-ver-almacenatec no-print" onclick="toggleDetalle(this)"><i class="bi bi-eye me-1"></i> Ver</button>
                            <select class="select-estatus-almacenatec no-print" onchange="cambiarEstatus(<?= $vale['id'] ?>, 'herramienta', this.value)">
                                <option value="pendiente" <?= $vale['estatus'] == 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                                <option value="autorizado" <?= $vale['estatus'] == 'autorizado' ? 'selected' : '' ?>>Autorizado</option>
                                <option value="entregado" <?= $vale['estatus'] == 'entregado' ? 'selected' : '' ?>>Entregado</option>
                                <option value="cancelado" <?= $vale['estatus'] == 'cancelado' ? 'selected' : '' ?>>Cancelado</option>
                            </select>
                            <button class="btn-eliminar-almacenatec no-print" onclick="eliminarVale(<?= $vale['id'] ?>, 'herramienta')">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                    <div style="font-size:13px;color:#475569;margin-top:4px">
                        <i class="bi bi-calendar me-1"></i><?= date('d/m/Y', strtotime($vale['fecha'])) ?>
                        <i class="bi bi-person me-1 ms-3"></i>Solicita: <?= htmlspecialchars($vale['solicita']) ?>
                        <i class="bi bi-person-check me-1 ms-3"></i>Autoriza: <?= htmlspecialchars($vale['autoriza']) ?>
                        <i class="bi bi-box me-1 ms-3"></i>Items: <?= $vale['total_items'] ?? 0 ?>
                    </div>
                    <div class="vale-detalle">
                        <div style="font-weight:600;margin-bottom:8px">Descripción:</div>
                        <div style="background:#F0F4F9;padding:10px 14px;border-radius:6px;margin-bottom:8px">
                            <?= nl2br(htmlspecialchars($vale['descripcion'])) ?>
                        </div>
                        <?php
                        try {
                            $stmt2 = $pdo->prepare("
                                SELECT dvh.*, p.descripcion as producto_desc, p.codigo_barras, u.abreviatura as unidad
                                FROM detalles_vale_herramienta dvh
                                LEFT JOIN productos p ON p.id = dvh.producto_id
                                LEFT JOIN unidades_medida u ON u.id = dvh.unidad_medida_id
                                WHERE dvh.vale_herramienta_id = ?
                            ");
                            $stmt2->execute([$vale['id']]);
                            $detalles = $stmt2->fetchAll(PDO::FETCH_ASSOC);
                            if (!empty($detalles)):
                        ?>
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>Descripción</th>
                                    <th class="text-center">Cantidad</th>
                                    <th class="text-center">Unidad</th>
                                    <th class="text-end">Precio</th>
                                    <th class="text-end">Importe</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                            $total = 0;
                            foreach ($detalles as $det):
                                $total += $det['importe'];
                            ?>
                                <tr>
                                    <td><?= htmlspecialchars($det['codigo_barras'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($det['descripcion'] ?? '') ?></td>
                                    <td class="text-center"><?= $det['cantidad'] ?></td>
                                    <td class="text-center"><?= htmlspecialchars($det['unidad'] ?? 'pz') ?></td>
                                    <td class="text-end">$<?= number_format($det['precio_unitario'], 2) ?></td>
                                    <td class="text-end">$<?= number_format($det['importe'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                                <tr style="font-weight:700;background:#F0F4F9">
                                    <td colspan="5" class="text-end">Total:</td>
                                    <td class="text-end">$<?= number_format($total, 2) ?></td>
                                </tr>
                            </tbody>
                        </table>
                        <?php endif; } catch (Exception $e) {} ?>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>

                <!-- VALES DE MATERIAL -->
                <?php if (!empty($vales_material)): ?>
                <h4 class="mb-3 mt-4" style="color:#0B2D6B"><i class="bi bi-file-earmark-text me-2"></i>Vales de Material</h4>
                <?php foreach ($vales_material as $vale): ?>
                <div class="vale-card tipo-material-card" 
                     data-id="<?= $vale['id'] ?>"
                     data-tipo="material" 
                     data-fecha="<?= $vale['fecha'] ?>"
                     data-estatus="<?= $vale['estatus'] ?>" 
                     data-folio="<?= htmlspecialchars($vale['folio']) ?>" 
                     data-solicita="<?= strtolower(htmlspecialchars($vale['solicita'])) ?>"
                     data-autoriza="<?= htmlspecialchars($vale['autoriza']) ?>"
                     data-area="<?= htmlspecialchars($vale['area_uso']) ?>"
                     data-descripcion="<?= htmlspecialchars($vale['descripcion']) ?>">
                    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px">
                        <div>
                            <span class="folio"><?= htmlspecialchars($vale['folio']) ?></span>
                            <span class="badge-almacenatec material"><i class="bi bi-file-earmark-text me-1"></i>Material</span>
                            <span class="badge-almacenatec <?= $vale['estatus'] ?>"><?= ucfirst($vale['estatus']) ?></span>
                        </div>
                        <div style="display:flex;gap:6px;flex-wrap:wrap">
                            <button class="btn-print-almacenatec no-print" onclick="imprimirVale(<?= $vale['id'] ?>, 'material')">
                                <i class="bi bi-printer me-1"></i> Imprimir
                            </button>
                            <button class="btn-ver-almacenatec no-print" onclick="toggleDetalle(this)"><i class="bi bi-eye me-1"></i> Ver</button>
                            <select class="select-estatus-almacenatec no-print" onchange="cambiarEstatus(<?= $vale['id'] ?>, 'material', this.value)">
                                <option value="pendiente" <?= $vale['estatus'] == 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                                <option value="autorizado" <?= $vale['estatus'] == 'autorizado' ? 'selected' : '' ?>>Autorizado</option>
                                <option value="entregado" <?= $vale['estatus'] == 'entregado' ? 'selected' : '' ?>>Entregado</option>
                                <option value="cancelado" <?= $vale['estatus'] == 'cancelado' ? 'selected' : '' ?>>Cancelado</option>
                            </select>
                            <button class="btn-eliminar-almacenatec no-print" onclick="eliminarVale(<?= $vale['id'] ?>, 'material')">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                    <div style="font-size:13px;color:#475569;margin-top:4px">
                        <i class="bi bi-calendar me-1"></i><?= date('d/m/Y', strtotime($vale['fecha'])) ?>
                        <i class="bi bi-person me-1 ms-3"></i>Solicita: <?= htmlspecialchars($vale['solicita']) ?>
                        <i class="bi bi-person-check me-1 ms-3"></i>Autoriza: <?= htmlspecialchars($vale['autoriza']) ?>
                        <i class="bi bi-geo-alt me-1 ms-3"></i>Área: <?= htmlspecialchars($vale['area_uso']) ?>
                        <i class="bi bi-box me-1 ms-3"></i>Items: <?= $vale['total_items'] ?? 0 ?>
                    </div>
                    <div class="vale-detalle">
                        <div style="font-weight:600;margin-bottom:8px">Descripción:</div>
                        <div style="background:#F0F4F9;padding:10px 14px;border-radius:6px;margin-bottom:8px">
                            <?= nl2br(htmlspecialchars($vale['descripcion'])) ?>
                        </div>
                        <?php
                        try {
                            $stmt2 = $pdo->prepare("
                                SELECT ds.*, p.descripcion as producto_desc, p.codigo_barras, u.abreviatura as unidad
                                FROM detalles_salida ds
                                LEFT JOIN productos p ON p.id = ds.producto_id
                                LEFT JOIN unidades_medida u ON u.id = ds.unidad_medida_id
                                WHERE ds.salida_id = ?
                            ");
                            $stmt2->execute([$vale['id']]);
                            $detalles = $stmt2->fetchAll(PDO::FETCH_ASSOC);
                            if (!empty($detalles)):
                        ?>
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>Descripción</th>
                                    <th class="text-center">Cantidad</th>
                                    <th class="text-center">Unidad</th>
                                    <th class="text-end">Precio</th>
                                    <th class="text-end">Importe</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                            $total = 0;
                            foreach ($detalles as $det):
                                $total += $det['importe'];
                            ?>
                                <tr>
                                    <td><?= htmlspecialchars($det['codigo_barras'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($det['descripcion'] ?? '') ?></td>
                                    <td class="text-center"><?= $det['cantidad'] ?></td>
                                    <td class="text-center"><?= htmlspecialchars($det['unidad'] ?? 'pz') ?></td>
                                    <td class="text-end">$<?= number_format($det['precio_unitario'], 2) ?></td>
                                    <td class="text-end">$<?= number_format($det['importe'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                                <tr style="font-weight:700;background:#F0F4F9">
                                    <td colspan="5" class="text-end">Total:</td>
                                    <td class="text-end">$<?= number_format($total, 2) ?></td>
                                </tr>
                            </tbody>
                        </table>
                        <?php endif; } catch (Exception $e) {} ?>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>

                <?php if (empty($vales_herramienta) && empty($vales_material)): ?>
                <div class="alert alert-info text-center py-5">
                    <i class="bi bi-inbox" style="font-size:48px;display:block;margin-bottom:12px;color:#94A3B8"></i>
                    <h4>No hay vales registrados</h4>
                    <p class="text-muted">Crea tu primer vale desde <a href="vale_herramienta.php" style="color:#0B2D6B;text-decoration:underline;">Vale de Herramienta</a> o <a href="vale_material.php" style="color:#0B2D6B;text-decoration:underline;">Vale por Material</a></p>
                </div>
                <?php endif; ?>

            </div>

        </main>
    </div>
</div>

<!-- ============================================================ -->
<!-- MODAL: GENERAR REPORTE                                       -->
<!-- ============================================================ -->
<div class="modal fade" id="modalReporte" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header-almacenatec">
                <h5 class="modal-title"><i class="bi bi-file-earmark-bar-graph me-2"></i>Generar Reporte de Vales</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                
                <div class="filtro-reporte">
                    <h6 style="font-family:'Poppins',sans-serif;font-weight:700;color:#0B2D6B;margin-bottom:12px;">
                        <i class="bi bi-funnel me-1" style="color:#c9973a;"></i>Filtros del Reporte
                    </h6>
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label>Fecha Inicio</label>
                            <input type="date" id="reporteFechaInicio" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label>Fecha Fin</label>
                            <input type="date" id="reporteFechaFin" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label>Tipo de Vale</label>
                            <select id="reporteTipo" class="form-select">
                                <option value="ambos">Ambos (Herramienta y Material)</option>
                                <option value="herramienta">Solo Vales de Herramienta</option>
                                <option value="material">Solo Vales de Material</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label>Estatus</label>
                            <select id="reporteEstatus" class="form-select">
                                <option value="todos">Todos los estatus</option>
                                <option value="pendiente">Pendiente</option>
                                <option value="autorizado">Autorizado</option>
                                <option value="entregado">Entregado</option>
                                <option value="cancelado">Cancelado</option>
                            </select>
                        </div>
                    </div>
                    
                    <div style="margin-top:12px;padding:10px 14px;background:#fff;border-radius:8px;border-left:3px solid #c9973a;">
                        <small style="color:#475569;">
                            <i class="bi bi-info-circle me-1"></i>
                            Si no seleccionas fechas, se incluirán <strong>todos los vales</strong>.
                        </small>
                    </div>
                </div>

                <!-- Vista previa -->
                <div id="vistaPreviaReporte" style="display:none;">
                    <div class="tabla-titulo" style="font-size:15px;">
                        <i class="bi bi-eye"></i>
                        Vista Previa
                        <small><span id="previewCount">0</span> vales encontrados</small>
                    </div>
                    <div id="previewLista" style="max-height:250px;overflow-y:auto;background:#F0F4F9;border-radius:8px;padding:12px;"></div>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn-outline-almacenatec" onclick="previsualizarReporte()">
                    <i class="bi bi-eye me-1"></i> Previsualizar
                </button>
                <button type="button" class="btn-outline-almacenatec" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg me-1"></i> Cancelar
                </button>
                <button type="button" class="btn-primary-almacenatec" onclick="generarReporte()">
                    <i class="bi bi-printer me-1"></i> Generar Reporte
                </button>
            </div>
        </div>
    </div>
</div>

<script src="../js/bootstrap.bundle.min.js"></script>
<script>
    // ============================================================
    // SIDEBAR
    // ============================================================
    function toggleSidebar(){
        var s = document.getElementById('sidebar');
        var o = document.getElementById('sidebarOverlay');
        var w = document.getElementById('mainWrapper');
        if(window.innerWidth <= 1100){
            var open = s.classList.toggle('open');
            o.classList.toggle('visible', open);
        }else{
            s.classList.toggle('collapsed');
            w.classList.toggle('sidebar-hidden');
        }
    }
    function closeSidebar(){
        document.getElementById('sidebar').classList.remove('open');
        document.getElementById('sidebarOverlay').classList.remove('visible');
    }

    // ============================================================
    // TOGGLE DETALLE
    // ============================================================
    function toggleDetalle(btn) {
        var detalle = btn.closest('.vale-card').querySelector('.vale-detalle');
        detalle.classList.toggle('show');
        btn.innerHTML = detalle.classList.contains('show') ? 
            '<i class="bi bi-eye-slash me-1"></i> Ocultar' : 
            '<i class="bi bi-eye me-1"></i> Ver';
    }

    // ============================================================
    // FILTRAR VALES
    // ============================================================
    function filtrarVales() {
        var tipo = document.getElementById('filtroTipo').value;
        var estatus = document.getElementById('filtroEstatus').value;
        var buscar = document.getElementById('filtroBuscar').value.toLowerCase();

        document.querySelectorAll('.vale-card').forEach(function(card) {
            var mostrar = true;
            
            if (tipo !== 'todos' && card.dataset.tipo !== tipo) mostrar = false;
            if (estatus !== 'todos' && card.dataset.estatus !== estatus) mostrar = false;
            if (buscar && !card.dataset.folio.toLowerCase().includes(buscar) && 
                !card.dataset.solicita.includes(buscar)) mostrar = false;
            
            card.style.display = mostrar ? 'block' : 'none';
        });
    }

    // ============================================================
    // MODAL REPORTE
    // ============================================================
    function abrirModalReporte() {
        // Establecer fecha de hoy por defecto
        const hoy = new Date().toISOString().split('T')[0];
        document.getElementById('reporteFechaFin').value = hoy;
        
        // Limpiar fecha inicio
        document.getElementById('reporteFechaInicio').value = '';
        
        // Ocultar vista previa
        document.getElementById('vistaPreviaReporte').style.display = 'none';
        
        new bootstrap.Modal(document.getElementById('modalReporte')).show();
    }

    // ============================================================
    // OBTENER VALES FILTRADOS PARA REPORTE
    // ============================================================
    function obtenerValesFiltrados() {
        const fechaInicio = document.getElementById('reporteFechaInicio').value;
        const fechaFin = document.getElementById('reporteFechaFin').value;
        const tipo = document.getElementById('reporteTipo').value;
        const estatus = document.getElementById('reporteEstatus').value;

        let vales = [];

        document.querySelectorAll('.vale-card').forEach(card => {
            const cardFecha = card.dataset.fecha;
            const cardTipo = card.dataset.tipo;
            const cardEstatus = card.dataset.estatus;

            // Filtro de tipo
            if (tipo !== 'ambos' && cardTipo !== tipo) return;

            // Filtro de estatus
            if (estatus !== 'todos' && cardEstatus !== estatus) return;

            // Filtro de fecha inicio
            if (fechaInicio && cardFecha < fechaInicio) return;

            // Filtro de fecha fin
            if (fechaFin && cardFecha > fechaFin) return;

            vales.push({
                id: card.dataset.id,
                tipo: cardTipo,
                fecha: cardFecha,
                estatus: cardEstatus,
                folio: card.dataset.folio,
                solicita: card.dataset.solicita,
                autoriza: card.dataset.autoriza,
                area: card.dataset.area || '',
                descripcion: card.dataset.descripcion || '',
                html: card.innerHTML
            });
        });

        return vales;
    }

    // ============================================================
    // PREVISUALIZAR REPORTE
    // ============================================================
    function previsualizarReporte() {
        const vales = obtenerValesFiltrados();
        const preview = document.getElementById('vistaPreviaReporte');
        const lista = document.getElementById('previewLista');
        const count = document.getElementById('previewCount');

        if (vales.length === 0) {
            preview.style.display = 'block';
            count.textContent = '0';
            lista.innerHTML = '<p style="text-align:center;color:#94A3B8;padding:20px;">No se encontraron vales con esos filtros.</p>';
            return;
        }

        preview.style.display = 'block';
        count.textContent = vales.length;

        let html = '';
        vales.forEach(v => {
            const tipoIcon = v.tipo === 'herramienta' ? '🔧' : '📄';
            const tipoLabel = v.tipo === 'herramienta' ? 'Herramienta' : 'Material';
            html += `
                <div style="background:#fff;padding:10px 14px;border-radius:8px;margin-bottom:8px;border-left:3px solid ${v.tipo === 'herramienta' ? '#d97706' : '#0B2D6B'};">
                    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:6px;">
                        <div>
                            <strong style="color:#0B2D6B;">${v.folio}</strong>
                            <span class="badge-almacenatec ${v.tipo}" style="margin-left:6px;">${tipoIcon} ${tipoLabel}</span>
                            <span class="badge-almacenatec ${v.estatus}" style="margin-left:4px;">${v.estatus}</span>
                        </div>
                        <small style="color:#475569;">${v.fecha}</small>
                    </div>
                    <div style="font-size:12px;color:#475569;margin-top:4px;">
                        Solicita: ${v.solicita} | Autoriza: ${v.autoriza}
                    </div>
                </div>
            `;
        });
        lista.innerHTML = html;
    }

    // ============================================================
    // GENERAR REPORTE (Imprimir)
    // ============================================================
    function generarReporte() {
        const vales = obtenerValesFiltrados();
        const fechaInicio = document.getElementById('reporteFechaInicio').value;
        const fechaFin = document.getElementById('reporteFechaFin').value;
        const tipo = document.getElementById('reporteTipo').value;
        const estatus = document.getElementById('reporteEstatus').value;

        if (vales.length === 0) {
            alert('No hay vales que coincidan con los filtros seleccionados.');
            return;
        }

        // Crear ventana de reporte
        const ventana = window.open('', '_blank', 'width=1000,height=800');
        if (!ventana) {
            alert('Permite ventanas emergentes para generar el reporte');
            return;
        }

        // Etiquetas
        const tipoLabel = tipo === 'ambos' ? 'Todos los Vales' : 
                         (tipo === 'herramienta' ? 'Vales de Herramienta' : 'Vales de Material');
        const estatusLabel = estatus === 'todos' ? 'Todos los estatus' : estatus.charAt(0).toUpperCase() + estatus.slice(1);
        const rangoFechas = (fechaInicio || fechaFin) ? 
            `${fechaInicio || 'Inicio'} al ${fechaFin || 'Hoy'}` : 
            'Todas las fechas';

        // Agrupar por tipo
        const valesHerramienta = vales.filter(v => v.tipo === 'herramienta');
        const valesMaterial = vales.filter(v => v.tipo === 'material');

        // Construir HTML del reporte
        let html = `<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Reporte de Vales - AlmacenaTec</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; padding: 30px; background: #fff; color: #1E293B; }
        
        .report-header {
            background: #0B2D6B;
            color: #fff;
            padding: 24px 30px;
            border-radius: 10px 10px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .report-header .logo {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .report-header .logo-mark {
            width: 45px; height: 45px;
            background: #B81C2E;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-weight: 800; font-size: 16px;
        }
        .report-header h1 {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 2px;
        }
        .report-header small { font-size: 12px; color: rgba(255,255,255,.7); }
        .report-header .fecha-generacion {
            text-align: right;
            font-size: 12px;
            color: rgba(255,255,255,.8);
        }
        .report-header .fecha-generacion strong { display: block; font-size: 14px; color: #c9973a; }

        .report-title {
            background: #F0F4F9;
            padding: 16px 30px;
            border-left: 5px solid #c9973a;
            margin-bottom: 20px;
        }
        .report-title h2 {
            font-size: 18px;
            color: #0B2D6B;
            margin-bottom: 4px;
        }
        .report-title .info {
            display: flex;
            gap: 20px;
            font-size: 12px;
            color: #475569;
            flex-wrap: wrap;
        }
        .report-title .info span strong { color: #0B2D6B; }

        .resumen {
            display: flex;
            gap: 12px;
            margin-bottom: 20px;
        }
        .resumen-card {
            flex: 1;
            background: #F0F4F9;
            padding: 14px 18px;
            border-radius: 8px;
            border-left: 4px solid #0B2D6B;
        }
        .resumen-card.tipo-herramienta { border-left-color: #d97706; }
        .resumen-card.tipo-material { border-left-color: #0B2D6B; }
        .resumen-card .num {
            font-size: 24px;
            font-weight: 800;
            color: #0B2D6B;
        }
        .resumen-card .lbl {
            font-size: 11px;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: .5px;
        }

        .seccion-titulo {
            background: #0B2D6B;
            color: #fff;
            padding: 10px 16px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 700;
            margin: 20px 0 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .seccion-titulo.tipo-herramienta { background: #d97706; }
        .seccion-titulo.tipo-material { background: #0B2D6B; }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            margin-bottom: 16px;
        }
        thead th {
            background: #F0F4F9;
            color: #0B2D6B;
            padding: 10px 8px;
            text-align: left;
            font-weight: 700;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: .3px;
            border-bottom: 2px solid #0B2D6B;
        }
        tbody td {
            padding: 8px;
            border-bottom: 1px solid #E2E8F0;
            color: #1E293B;
        }
        tbody tr:hover { background: #F8FAFC; }

        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .badge.pendiente { background: #fef3c7; color: #d97706; }
        .badge.autorizado { background: #dbeafe; color: #0B2D6B; }
        .badge.entregado { background: #d1fae5; color: #059669; }
        .badge.cancelado { background: #fee2e2; color: #dc2626; }

        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #E2E8F0;
            text-align: center;
            font-size: 11px;
            color: #94A3B8;
        }
        .footer strong { color: #0B2D6B; }

        @media print {
            body { padding: 15px; }
            .report-header { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .seccion-titulo { -webkit-print-color-adjust: exact; print-color-adjust: exact; page-break-after: avoid; }
            table { page-break-inside: auto; }
            tr { page-break-inside: avoid; }
        }
    </style>
</head>
<body>

    <!-- Encabezado -->
    <div class="report-header">
        <div class="logo">
            <div class="logo-mark">AT</div>
            <div>
                <h1>AlmacenaTec</h1>
                <small>TecNM Campus Saltillo</small>
            </div>
        </div>
        <div class="fecha-generacion">
            Fecha de generación
            <strong>${new Date().toLocaleDateString('es-MX', { day: '2-digit', month: 'long', year: 'numeric' })}</strong>
        </div>
    </div>

    <!-- Título del reporte -->
    <div class="report-title">
        <h2><i style="color:#c9973a;">📄</i> Reporte de Vales</h2>
        <div class="info">
            <span><strong>Tipo:</strong> ${tipoLabel}</span>
            <span><strong>Estatus:</strong> ${estatusLabel}</span>
            <span><strong>Rango:</strong> ${rangoFechas}</span>
        </div>
    </div>

    <!-- Resumen -->
    <div class="resumen">
        <div class="resumen-card">
            <div class="num">${vales.length}</div>
            <div class="lbl">Total de Vales</div>
        </div>
        ${valesHerramienta.length > 0 ? `
        <div class="resumen-card tipo-herramienta">
            <div class="num">${valesHerramienta.length}</div>
            <div class="lbl">Vales de Herramienta</div>
        </div>` : ''}
        ${valesMaterial.length > 0 ? `
        <div class="resumen-card tipo-material">
            <div class="num">${valesMaterial.length}</div>
            <div class="lbl">Vales de Material</div>
        </div>` : ''}
    </div>
`;

        // Tabla de vales de herramienta
        if (valesHerramienta.length > 0) {
            html += `
            <div class="seccion-titulo tipo-herramienta">
                <span>🔧 Vales de Herramienta</span>
                <span>${valesHerramienta.length} registros</span>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Folio</th>
                        <th>Fecha</th>
                        <th>Solicita</th>
                        <th>Autoriza</th>
                        <th>Descripción</th>
                        <th>Estatus</th>
                    </tr>
                </thead>
                <tbody>
            `;
            valesHerramienta.forEach((v, i) => {
                html += `
                    <tr>
                        <td>${i + 1}</td>
                        <td><strong>${v.folio}</strong></td>
                        <td>${v.fecha}</td>
                        <td>${v.solicita}</td>
                        <td>${v.autoriza}</td>
                        <td>${(v.descripcion || '').substring(0, 60)}${(v.descripcion || '').length > 60 ? '...' : ''}</td>
                        <td><span class="badge ${v.estatus}">${v.estatus}</span></td>
                    </tr>
                `;
            });
            html += `</tbody></table>`;
        }

        // Tabla de vales de material
        if (valesMaterial.length > 0) {
            html += `
            <div class="seccion-titulo tipo-material">
                <span>📄 Vales de Material</span>
                <span>${valesMaterial.length} registros</span>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Folio</th>
                        <th>Fecha</th>
                        <th>Solicita</th>
                        <th>Autoriza</th>
                        <th>Área de Uso</th>
                        <th>Estatus</th>
                    </tr>
                </thead>
                <tbody>
            `;
            valesMaterial.forEach((v, i) => {
                html += `
                    <tr>
                        <td>${i + 1}</td>
                        <td><strong>${v.folio}</strong></td>
                        <td>${v.fecha}</td>
                        <td>${v.solicita}</td>
                        <td>${v.autoriza}</td>
                        <td>${v.area || '—'}</td>
                        <td><span class="badge ${v.estatus}">${v.estatus}</span></td>
                    </tr>
                `;
            });
            html += `</tbody></table>`;
        }

        // Footer
        html += `
    <div class="footer">
        <strong>AlmacenaTec</strong> — Sistema de Gestión de Inventario<br>
        Tecnológico Nacional de México · Campus Saltillo · ${new Date().getFullYear()}
    </div>

    <script>
        window.onload = function() {
            setTimeout(function() { window.print(); }, 300);
        };
    <\/script>
</body>
</html>`;

        ventana.document.open();
        ventana.document.write(html);
        ventana.document.close();
    }

    // ============================================================
    // IMPRIMIR VALE INDIVIDUAL (Función existente)
    // ============================================================
    function imprimirVale(id, tipo) {
        var cards = document.querySelectorAll('.vale-card');
        var valeCard = null;
        
        for (var i = 0; i < cards.length; i++) {
            var card = cards[i];
            if (card.innerHTML.indexOf('imprimirVale(' + id) > -1) {
                valeCard = card;
                break;
            }
        }
        
        if (!valeCard) {
            alert('No se encontró el vale');
            return;
        }
        
        var contenido = valeCard.cloneNode(true);
        var botones = contenido.querySelectorAll('.no-print, .btn-print-almacenatec, .btn-ver-almacenatec, .btn-eliminar-almacenatec, button, .select-estatus-almacenatec');
        for (var j = 0; j < botones.length; j++) {
            botones[j].style.display = 'none';
        }
        
        var detalle = contenido.querySelector('.vale-detalle');
        if (detalle) detalle.style.display = 'block';
        
        var ventana = window.open('', '_blank', 'width=900,height=700');
        if (!ventana) {
            alert('Permite ventanas emergentes para imprimir');
            return;
        }
        
        var titulo = tipo === 'herramienta' ? 'VALE DE HERRAMIENTA' : 'VALE POR MATERIAL';
        var color = tipo === 'herramienta' ? '#d97706' : '#0B2D6B';
        
        ventana.document.write('<!DOCTYPE html>');
        ventana.document.write('<html>');
        ventana.document.write('<head>');
        ventana.document.write('<meta charset="UTF-8">');
        ventana.document.write('<title>' + titulo + '</title>');
        ventana.document.write('<style>');
        ventana.document.write('* { margin: 0; padding: 0; box-sizing: border-box; }');
        ventana.document.write('body { font-family: Arial, sans-serif; padding: 40px; background: #fff; }');
        ventana.document.write('.header { background: ' + color + '; color: #fff; padding: 20px 24px; border-radius: 8px 8px 0 0; text-align: center; }');
        ventana.document.write('.header h1 { font-size: 24px; margin: 0; }');
        ventana.document.write('.header small { color: rgba(255,255,255,0.8); }');
        ventana.document.write('.content { padding: 24px; border: 1px solid #e5e7eb; border-top: none; }');
        ventana.document.write('.row { display: flex; margin-bottom: 8px; }');
        ventana.document.write('.label { font-weight: 600; width: 130px; color: #6b7280; }');
        ventana.document.write('.value { flex: 1; }');
        ventana.document.write('.badge-almacenatec { display: inline-block; padding: 2px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }');
        ventana.document.write('.badge-almacenatec.herramienta { background: #fef3c7; color: #d97706; }');
        ventana.document.write('.badge-almacenatec.material { background: #dbeafe; color: #0B2D6B; }');
        ventana.document.write('.badge-almacenatec.pendiente { background: #fef3c7; color: #d97706; }');
        ventana.document.write('.badge-almacenatec.autorizado { background: #dbeafe; color: #0B2D6B; }');
        ventana.document.write('.badge-almacenatec.entregado { background: #d1fae5; color: #059669; }');
        ventana.document.write('.badge-almacenatec.cancelado { background: #fee2e2; color: #dc2626; }');
        ventana.document.write('table { width: 100%; border-collapse: collapse; margin-top: 12px; }');
        ventana.document.write('th { background: ' + color + '; color: #fff; padding: 8px 10px; text-align: left; font-size: 12px; }');
        ventana.document.write('td { padding: 6px 10px; border-bottom: 1px solid #e5e7eb; }');
        ventana.document.write('.footer { margin-top: 24px; padding-top: 16px; border-top: 2px dashed #e5e7eb; display: flex; justify-content: space-between; }');
        ventana.document.write('.firma { text-align: center; }');
        ventana.document.write('.firma .linea { width: 180px; border-bottom: 1px solid #000; margin: 16px auto 4px; }');
        ventana.document.write('.firma .label { font-size: 11px; color: #6b7280; }');
        ventana.document.write('.footer-print { text-align: center; font-size: 10px; color: #999; margin-top: 16px; border-top: 1px solid #e5e7eb; padding-top: 12px; }');
        ventana.document.write('</style>');
        ventana.document.write('</head>');
        ventana.document.write('<body>');
        
        ventana.document.write('<div class="header">');
        ventana.document.write('<h1>' + titulo + '</h1>');
        ventana.document.write('<small>TecNM Campus Saltillo</small>');
        ventana.document.write('</div>');
        
        ventana.document.write('<div class="content">');
        
        var folio = '', fecha = '', solicita = '', autoriza = '', area = '', descripcion = '', estatus = '';
        
        var infoDiv = valeCard.querySelector('[style*="font-size:13px"]');
        if (infoDiv) {
            var texto = infoDiv.textContent;
            var partes = texto.split('Solicita:');
            var fechaParte = partes[0].trim();
            fecha = fechaParte.replace('i bi-calendar', '').trim();
            
            if (partes.length > 1) {
                var solPartes = partes[1].split('Autoriza:');
                solicita = solPartes[0].trim();
                if (solPartes.length > 1) {
                    var autPartes = solPartes[1].split('Items:');
                    autoriza = autPartes[0].trim();
                }
            }
            
            var areaMatch = texto.match(/Área:\s*([^\s]+(?:\s+[^\s]+)*)/);
            if (areaMatch) area = areaMatch[1].trim();
        }
        
        var folioEl = valeCard.querySelector('.folio');
        if (folioEl) folio = folioEl.textContent.trim();
        
        var descDiv = valeCard.querySelector('.vale-detalle [style*="background:#F0F4F9"]');
        if (descDiv) descripcion = descDiv.textContent.trim();
        
        var estatusSpan = valeCard.querySelector('.badge-almacenatec.pendiente, .badge-almacenatec.autorizado, .badge-almacenatec.entregado, .badge-almacenatec.cancelado');
        if (estatusSpan) estatus = estatusSpan.textContent.trim();
        
        ventana.document.write('<div class="row"><span class="label">Folio:</span><span class="value"><strong>' + htmlEntities(folio) + '</strong></span></div>');
        ventana.document.write('<div class="row"><span class="label">Fecha:</span><span class="value">' + htmlEntities(fecha) + '</span></div>');
        ventana.document.write('<div class="row"><span class="label">Solicita:</span><span class="value">' + htmlEntities(solicita) + '</span></div>');
        if (area) ventana.document.write('<div class="row"><span class="label">Área de uso:</span><span class="value">' + htmlEntities(area) + '</span></div>');
        ventana.document.write('<div class="row"><span class="label">Autoriza:</span><span class="value">' + htmlEntities(autoriza) + '</span></div>');
        ventana.document.write('<div class="row"><span class="label">Estatus:</span><span class="value"><span class="badge-almacenatec ' + estatus.toLowerCase() + '">' + htmlEntities(estatus) + '</span></span></div>');
        
        ventana.document.write('<div style="margin-top:12px;"><div style="font-weight:600;margin-bottom:4px;">Descripción:</div>');
        ventana.document.write('<div style="background:#F0F4F9;padding:10px 14px;border-radius:6px;">' + htmlEntities(descripcion) + '</div></div>');
        
        var tabla = valeCard.querySelector('table');
        if (tabla) ventana.document.write(tabla.outerHTML);
        
        ventana.document.write('<div class="footer">');
        ventana.document.write('<div class="firma"><div class="linea"></div><div class="label">Solicita</div></div>');
        ventana.document.write('<div class="firma"><div class="linea"></div><div class="label">Autoriza</div></div>');
        ventana.document.write('<div class="firma"><div class="linea"></div><div class="label">Vo.Bo. Almacén</div></div>');
        ventana.document.write('</div>');
        ventana.document.write('<div class="footer-print">TecNM Campus Saltillo - ' + new Date().getFullYear() + '</div>');
        ventana.document.write('</div></body></html>');
        ventana.document.close();
        
        setTimeout(function() { ventana.print(); }, 600);
    }

    function htmlEntities(text) {
        if (!text) return '';
        var div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // ============================================================
    // CAMBIAR ESTATUS
    // ============================================================
    function cambiarEstatus(id, tipo, nuevoEstatus) {
        if (!confirm('¿Estás seguro de cambiar el estatus a "' + nuevoEstatus + '"?')) {
            location.reload();
            return;
        }
        
        fetch('<?= BASE_PATH ?>handlers/cambiar_estatus_vale.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'id=' + id + '&tipo=' + tipo + '&estatus=' + nuevoEstatus
        })
        .then(r => r.json())
        .then(data => {
            if (data.ok) {
                alert('✅ Estatus actualizado correctamente');
                location.reload();
            } else {
                alert('❌ Error: ' + data.error);
                location.reload();
            }
        })
        .catch(error => {
            alert('❌ Error de conexión');
            location.reload();
        });
    }

    // ============================================================
    // ELIMINAR VALE
    // ============================================================
    function eliminarVale(id, tipo) {
        if (!confirm('⚠️ ¿Estás seguro de ELIMINAR este vale?\nEsta acción no se puede deshacer.')) return;
        if (!confirm('Confirmar eliminación del vale ID: ' + id)) return;
        
        fetch('<?= BASE_PATH ?>handlers/eliminar_vale.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'id=' + id + '&tipo=' + tipo
        })
        .then(r => r.json())
        .then(data => {
            if (data.ok) {
                alert('🗑️ Vale eliminado correctamente');
                location.reload();
            } else {
                alert('❌ Error: ' + data.error);
            }
        })
        .catch(error => { alert('❌ Error de conexión'); });
    }
</script>
</body>
</html>