<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

if (!esJefeDepartamento()) {
    header('Location: ' . BASE_PATH . 'home/index.php');
    exit;
}

$nombre = htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Usuario');
$departamento = $_SESSION['usuario_departamento'] ?? 'Sin departamento';
$busqueda = '%' . $departamento . '%';

// ================================================================
// 1. REQUISICIONES (del departamento)
// ================================================================
$requisiciones = [];
try {
    $stmt = $pdo->prepare("
        SELECT r.*
        FROM requisiciones r
        WHERE r.area_solicitante LIKE :busqueda
           OR r.jefe_solicitante LIKE :busqueda
           OR r.descripcion LIKE :busqueda
        ORDER BY r.id DESC
        LIMIT 50
    ");
    $stmt->execute([':busqueda' => $busqueda]);
    $requisiciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) { $requisiciones = []; }

// ================================================================
// 2. ACTIVOS FIJOS
// ================================================================
$activos = [];
try {
    $stmt = $pdo->prepare("
        SELECT * FROM activos_fijos
        WHERE departamento_recibe LIKE :busqueda
           OR jefe_departamento LIKE :busqueda
           OR area_pertenece LIKE :busqueda
           OR resguardante LIKE :busqueda
        ORDER BY id DESC
        LIMIT 50
    ");
    $stmt->execute([':busqueda' => $busqueda]);
    $activos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) { $activos = []; }

// ================================================================
// 3. SALIDAS DE INVENTARIO
// ================================================================
$salidas = [];
try {
    $stmt = $pdo->prepare("
        SELECT s.*, d.nombre AS departamento_nombre
        FROM salidas_inventario s
        LEFT JOIN departamentos d ON d.id = s.departamento_id
        WHERE s.proyecto LIKE :busqueda
           OR s.proveedor LIKE :busqueda
           OR s.observaciones LIKE :busqueda
           OR d.nombre LIKE :busqueda
        ORDER BY s.id DESC
        LIMIT 50
    ");
    $stmt->execute([':busqueda' => $busqueda]);
    $salidas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) { $salidas = []; }

// ================================================================
// 4. VALES DE MATERIAL
// ================================================================
$vales_material = [];
try {
    $stmt = $pdo->prepare("
        SELECT * FROM vales_material
        WHERE area_uso LIKE :busqueda
           OR solicita LIKE :busqueda
           OR descripcion LIKE :busqueda
        ORDER BY id DESC
        LIMIT 50
    ");
    $stmt->execute([':busqueda' => $busqueda]);
    $vales_material = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) { $vales_material = []; }

// ================================================================
// 5. VALES DE HERRAMIENTA
// ================================================================
$vales_herramienta = [];
try {
    $stmt = $pdo->prepare("
        SELECT * FROM vales_herramienta
        WHERE solicita LIKE :busqueda
           OR descripcion LIKE :busqueda
           OR autoriza LIKE :busqueda
        ORDER BY id DESC
        LIMIT 50
    ");
    $stmt->execute([':busqueda' => $busqueda]);
    $vales_herramienta = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) { $vales_herramienta = []; }

// ================================================================
// 6. COMPRAS
// ================================================================
$compras = [];
try {
    $stmt = $pdo->prepare("
        SELECT c.*, 
               oc.no_orden_compra, 
               f.numero_factura AS no_factura, 
               oc.area_solicitante,
               oc.descripcion_general AS descripcion,
               fin.pagado,
               fin.fecha_pago
        FROM compras c
        LEFT JOIN ordenes_compra oc ON oc.id = c.orden_compra_id
        LEFT JOIN facturas f ON f.id = c.factura_id
        LEFT JOIN finanzas fin ON fin.compra_id = c.id
        WHERE oc.area_solicitante LIKE :busqueda
           OR oc.descripcion_general LIKE :busqueda
           OR oc.proveedor LIKE :busqueda
        ORDER BY c.id DESC
        LIMIT 50
    ");
    $stmt->execute([':busqueda' => $busqueda]);
    $compras = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) { $compras = []; }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Departamento — AlmacenaTec</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
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
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,.07), 0 4px 14px rgba(0,0,0,.05);
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
        .table-responsive-almacenatec { overflow-x: auto; border-radius: 10px; }
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
        .badge-almacenatec.autorizada,
        .badge-almacenatec.autorizado { background: #dbeafe; color: #0B2D6B; }
        .badge-almacenatec.entregada,
        .badge-almacenatec.entregado { background: #d1fae5; color: #059669; }
        .badge-almacenatec.cancelada,
        .badge-almacenatec.cancelado { background: #fee2e2; color: #dc2626; }
        .badge-almacenatec.pagado { background: #dbeafe; color: #0B2D6B; }
        .badge-almacenatec.activo { background: #d1fae5; color: #059669; }
        .badge-almacenatec.inactivo { background: #fee2e2; color: #dc2626; }
        .badge-almacenatec.orden_generada { background: #d1fae5; color: #059669; }
        .badge-almacenatec.rechazada { background: #fee2e2; color: #dc2626; }
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
        .btn-accent-almacenatec:hover { background: #8f1522; color: #FFFFFF; }
        .btn-ver-almacenatec {
            background: rgba(11,45,107,.08);
            color: #0B2D6B;
            border: none;
            padding: 5px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .btn-ver-almacenatec:hover {
            background: #0B2D6B;
            color: #FFFFFF;
        }
        .info-banner {
            background: linear-gradient(135deg, #0B2D6B, #071d46);
            color: #fff;
            padding: 20px 24px;
            border-radius: 12px;
            margin-bottom: 20px;
        }
        .info-banner h2 {
            font-family: 'Poppins', sans-serif;
            font-size: 20px;
            font-weight: 700;
            color: #fff;
            margin-bottom: 4px;
        }
        .info-banner p {
            font-size: 13px;
            color: rgba(255,255,255,.7);
            margin: 0;
        }
        .info-banner i { color: #c9973a; }
        .resumen-card {
            background: #fff;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,.06);
            border-left: 4px solid #0B2D6B;
        }
        .resumen-card .numero {
            font-family: 'Poppins', sans-serif;
            font-size: 32px;
            font-weight: 800;
            color: #0B2D6B;
        }
        .resumen-card .label {
            font-size: 12px;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: .5px;
            margin-top: 4px;
        }
        .sin-resultados {
            text-align: center;
            padding: 40px 20px;
            color: #94A3B8;
        }
        .sin-resultados i {
            font-size: 48px;
            display: block;
            margin-bottom: 12px;
            color: #E2E8F0;
        }

        /* Modal detalle */
        .modal-header-almacenatec {
            background: #0B2D6B;
            color: #fff;
            border-radius: 10px 10px 0 0;
            padding: 16px 20px;
            border-bottom: none;
        }
        .modal-header-almacenatec h5 { color: #fff; margin: 0; }
        .modal-header-almacenatec .btn-close { filter: invert(1); }
        .detalle-row {
            display: flex;
            padding: 8px 0;
            border-bottom: 1px solid #F0F4F9;
        }
        .detalle-row:last-child { border-bottom: none; }
        .detalle-row .label {
            font-weight: 600;
            color: #475569;
            width: 180px;
            flex-shrink: 0;
            font-size: 13px;
        }
        .detalle-row .value {
            color: #1E293B;
            font-size: 13px;
            flex: 1;
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
           
            <?php if (esJefeDepartamento()): ?>
            <a href="home/index.php" class="sidebar-link"><i class="bi bi-house-door"></i> Inicio</a>
            <a href="jefe_departamento.php" class="sidebar-link active"><i class="bi bi-building"></i> Mi Departamento</a>
            <a href="usuarios/main_usuarios.php" class="sidebar-link"><i class="bi bi-people"></i> Gestión Usuarios</a>
            
            <?php elseif ($_SESSION['usuario_rol'] === 'finanzas'): ?>
            <a href="../orden_compra/compras.php" class="sidebar-link"><i class="bi bi-cart-check"></i> Compras</a>
            
            <?php elseif ($_SESSION['usuario_rol'] === 'activo_fijo' || $_SESSION['usuario_rol'] === 'almacen_general'): ?>
            <a href="../activo_fijo/index.php" class="sidebar-link"><i class="bi bi-safe-fill"></i> Activo Fijo</a>
            <a href="../activo_fijo/main_activF.php" class="sidebar-link"><i class="bi bi-clipboard2-data"></i> Historial Salidas</a>
            <a href="../orden_compra/requisiciones.php" class="sidebar-link"><i class="bi bi-cart3"></i> Almacén General</a>
            <a href="../orden_compra/facturas_lista.php" class="sidebar-link"><i class="bi bi-file-earmark-text"></i> Facturas</a>
            
            <?php elseif ($_SESSION['usuario_rol'] === 'mantenimiento'): ?>
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
        <div class="sidebar-footer"><i class="bi bi-geo-alt-fill me-1"></i> TecNM · Campus Saltillo · 2025</div>
    </aside>

    <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

    <div class="main-wrapper" id="mainWrapper">
        <header class="topbar">
            <button class="topbar-toggle" onclick="toggleSidebar()"><i class="bi bi-list" style="font-size:22px"></i></button>
            <div class="topbar-info">
                <h1><i class="bi bi-building me-2" style="color:#0B2D6B"></i>Mi Departamento</h1>
                <div class="topbar-breadcrumb"><a href="home/index.php">Inicio</a> &rsaquo; <?= htmlspecialchars($departamento) ?></div>
            </div>
            <div style="display:flex;align-items:center;gap:10px;flex-shrink:0">
                <span style="font-size:13px;color:#475569"><i class="bi bi-person-circle me-1"></i><?= $nombre ?></span>
                <a href="logout.php" class="btn-accent-almacenatec" style="padding:6px 12px;font-size:12px"><i class="bi bi-box-arrow-right me-1"></i> Salir</a>
            </div>
        </header>

        <main class="content">

            <!-- Banner de información -->
            <div class="info-banner">
                <h2><i class="bi bi-building me-2"></i>Departamento de <?= htmlspecialchars($departamento) ?></h2>
                <p><i class="bi bi-info-circle me-1"></i>Vista de solo lectura — Información relacionada con su departamento</p>
            </div>

            <!-- Botón de Gestión de Usuarios para Jefes -->
            <a href="usuarios/main_usuarios.php" style="display: flex; align-items: center; background: #fff; border: 1px solid #E2E8F0; border-radius: 12px; padding: 20px; margin-bottom: 24px; text-decoration: none; box-shadow: 0 4px 10px rgba(0,0,0,.03); transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                <div style="width: 48px; height: 48px; background: #dbeafe; border-radius: 10px; display: flex; align-items: center; justify-content: center; margin-right: 16px;">
                    <i class="bi bi-people-fill" style="font-size: 24px; color: #0B2D6B;"></i>
                </div>
                <div style="flex-grow: 1;">
                    <h5 style="margin: 0; color: #1E293B; font-weight: 600; font-size: 16px;">Gestión de Usuarios</h5>
                    <span style="color: #94A3B8; font-size: 14px;">Crear y administrar cuentas de tu departamento</span>
                </div>
                <i class="bi bi-chevron-right" style="font-size: 20px; color: #94A3B8;"></i>
            </a>

            <!-- Resumen de cantidades -->
            <div class="row g-3 mb-4">
                <div class="col-6 col-md-3">
                    <div class="resumen-card">
                        <div class="numero"><?= count($requisiciones) ?></div>
                        <div class="label">Requisiciones</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="resumen-card">
                        <div class="numero"><?= count($activos) ?></div>
                        <div class="label">Activos Fijos</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="resumen-card">
                        <div class="numero"><?= count($vales_material) + count($vales_herramienta) ?></div>
                        <div class="label">Vales</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="resumen-card">
                        <div class="numero"><?= count($compras) ?></div>
                        <div class="label">Compras</div>
                    </div>
                </div>
            </div>

            <!-- ============================================================ -->
            <!-- REQUISICIONES                                                 -->
            <!-- ============================================================ -->
            <div class="tabla-titulo">
                <i class="bi bi-file-earmark-text"></i>
                Requisiciones
                <small>Solicitudes de su departamento</small>
            </div>

            <div class="card-panel" style="margin-bottom:24px;">
                <div class="table-responsive-almacenatec">
                    <table class="table-almacenatec">
                        <thead>
                            <tr>
                                <th><i class="bi bi-hash"></i> Folio</th>
                                <th><i class="bi bi-calendar"></i> Fecha</th>
                                <th><i class="bi bi-person"></i> Jefe Solicitante</th>
                                <th><i class="bi bi-tag"></i> Partida</th>
                                <th><i class="bi bi-cash"></i> Costo Estimado</th>
                                <th><i class="bi bi-info-circle"></i> Estatus</th>
                                <th><i class="bi bi-tools"></i> Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($requisiciones)): ?>
                            <tr><td colspan="7"><div class="sin-resultados"><i class="bi bi-inbox"></i>No hay requisiciones para su departamento.</div></td></tr>
                            <?php else: ?>
                            <?php foreach ($requisiciones as $r): ?>
                            <tr>
                                <td><span style="font-weight:600;color:#0B2D6B"><?= htmlspecialchars($r['folio'] ?? '') ?></span></td>
                                <td><?= htmlspecialchars($r['fecha'] ?? '') ?></td>
                                <td><?= htmlspecialchars($r['jefe_solicitante'] ?? '') ?></td>
                                <td><?= htmlspecialchars($r['partida'] ?? '') ?></td>
                                <td>$<?= number_format($r['costo_estimado_total'] ?? 0, 2) ?></td>
                                <td><span class="badge-almacenatec <?= $r['estatus'] ?? 'pendiente' ?>"><?= ucfirst($r['estatus'] ?? 'Pendiente') ?></span></td>
                                <td>
                                    <button class="btn-ver-almacenatec" onclick="verRequisicion(<?= $r['id'] ?>)">
                                        <i class="bi bi-eye"></i> Ver más
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ============================================================ -->
            <!-- ACTIVOS FIJOS                                                 -->
            <!-- ============================================================ -->
            <div class="tabla-titulo">
                <i class="bi bi-safe-fill"></i>
                Activos Fijos
                <small>Bienes asignados a su departamento</small>
            </div>

            <div class="card-panel" style="margin-bottom:24px;">
                <div class="table-responsive-almacenatec">
                    <table class="table-almacenatec">
                        <thead>
                            <tr>
                                <th><i class="bi bi-hash"></i> Folio</th>
                                <th><i class="bi bi-info-circle"></i> Descripción</th>
                                <th><i class="bi bi-tag"></i> Marca/Modelo</th>
                                <th><i class="bi bi-person"></i> Resguardante</th>
                                <th><i class="bi bi-circle"></i> Disponible</th>
                                <th><i class="bi bi-tools"></i> Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($activos)): ?>
                            <tr><td colspan="6"><div class="sin-resultados"><i class="bi bi-inbox"></i>No hay activos fijos relacionados con su departamento.</div></td></tr>
                            <?php else: ?>
                            <?php foreach ($activos as $a): ?>
                            <tr>
                                <td><span style="font-weight:600;color:#0B2D6B"><?= htmlspecialchars($a['folio'] ?? '') ?></span></td>
                                <td><?= htmlspecialchars($a['descripcion_bien'] ?? '') ?></td>
                                <td><?= htmlspecialchars(($a['marca'] ?? '') . ' ' . ($a['modelo'] ?? '')) ?></td>
                                <td><?= htmlspecialchars($a['resguardante'] ?? '—') ?></td>
                                <td><span class="badge-almacenatec <?= $a['disponible'] ? 'activo' : 'inactivo' ?>"><?= $a['disponible'] ? 'Disponible' : 'No disponible' ?></span></td>
                                <td>
                                    <button class="btn-ver-almacenatec" onclick="verActivo(<?= $a['id'] ?>)">
                                        <i class="bi bi-eye"></i> Ver más
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ============================================================ -->
            <!-- SALIDAS DE INVENTARIO                                          -->
            <!-- ============================================================ -->
            <div class="tabla-titulo">
                <i class="bi bi-clipboard2-data"></i>
                Salidas de Inventario
                <small>Movimientos de su departamento</small>
            </div>

            <div class="card-panel" style="margin-bottom:24px;">
                <div class="table-responsive-almacenatec">
                    <table class="table-almacenatec">
                        <thead>
                            <tr>
                                <th><i class="bi bi-hash"></i> Folio</th>
                                <th><i class="bi bi-calendar"></i> Fecha</th>
                                <th><i class="bi bi-building"></i> Departamento</th>
                                <th><i class="bi bi-person"></i> Proveedor</th>
                                <th><i class="bi bi-info-circle"></i> Observaciones</th>
                                <th><i class="bi bi-tools"></i> Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($salidas)): ?>
                            <tr><td colspan="6"><div class="sin-resultados"><i class="bi bi-inbox"></i>No hay salidas de inventario para su departamento.</div></td></tr>
                            <?php else: ?>
                            <?php foreach ($salidas as $s): ?>
                            <tr>
                                <td><span style="font-weight:600;color:#0B2D6B"><?= htmlspecialchars($s['folio'] ?? '') ?></span></td>
                                <td><?= htmlspecialchars($s['fecha'] ?? '') ?></td>
                                <td><?= htmlspecialchars($s['departamento_nombre'] ?? '—') ?></td>
                                <td><?= htmlspecialchars($s['proveedor'] ?? '') ?></td>
                                <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= htmlspecialchars($s['observaciones'] ?? '') ?></td>
                                <td>
                                    <button class="btn-ver-almacenatec" onclick="verSalida(<?= $s['id'] ?>)">
                                        <i class="bi bi-eye"></i> Ver más
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ============================================================ -->
            <!-- VALES DE MATERIAL                                             -->
            <!-- ============================================================ -->
            <div class="tabla-titulo">
                <i class="bi bi-file-earmark-text"></i>
                Vales de Material
                <small>Solicitudes de su departamento</small>
            </div>

            <div class="card-panel" style="margin-bottom:24px;">
                <div class="table-responsive-almacenatec">
                    <table class="table-almacenatec">
                        <thead>
                            <tr>
                                <th><i class="bi bi-hash"></i> Folio</th>
                                <th><i class="bi bi-calendar"></i> Fecha</th>
                                <th><i class="bi bi-person"></i> Solicita</th>
                                <th><i class="bi bi-geo-alt"></i> Área de uso</th>
                                <th><i class="bi bi-info-circle"></i> Estatus</th>
                                <th><i class="bi bi-tools"></i> Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($vales_material)): ?>
                            <tr><td colspan="6"><div class="sin-resultados"><i class="bi bi-inbox"></i>No hay vales de material para su departamento.</div></td></tr>
                            <?php else: ?>
                            <?php foreach ($vales_material as $v): ?>
                            <tr>
                                <td><span style="font-weight:600;color:#0B2D6B"><?= htmlspecialchars($v['folio'] ?? '') ?></span></td>
                                <td><?= htmlspecialchars($v['fecha'] ?? '') ?></td>
                                <td><?= htmlspecialchars($v['solicita'] ?? '') ?></td>
                                <td><?= htmlspecialchars($v['area_uso'] ?? '') ?></td>
                                <td><span class="badge-almacenatec <?= $v['estatus'] ?? 'pendiente' ?>"><?= ucfirst($v['estatus'] ?? 'Pendiente') ?></span></td>
                                <td>
                                    <button class="btn-ver-almacenatec" onclick="verValeMaterial(<?= $v['id'] ?>)">
                                        <i class="bi bi-eye"></i> Ver más
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ============================================================ -->
            <!-- VALES DE HERRAMIENTA                                          -->
            <!-- ============================================================ -->
            <div class="tabla-titulo">
                <i class="bi bi-tools"></i>
                Vales de Herramienta
                <small>Solicitudes de su departamento</small>
            </div>

            <div class="card-panel" style="margin-bottom:24px;">
                <div class="table-responsive-almacenatec">
                    <table class="table-almacenatec">
                        <thead>
                            <tr>
                                <th><i class="bi bi-hash"></i> Folio</th>
                                <th><i class="bi bi-calendar"></i> Fecha</th>
                                <th><i class="bi bi-person"></i> Solicita</th>
                                <th><i class="bi bi-person-check"></i> Autoriza</th>
                                <th><i class="bi bi-info-circle"></i> Estatus</th>
                                <th><i class="bi bi-tools"></i> Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($vales_herramienta)): ?>
                            <tr><td colspan="6"><div class="sin-resultados"><i class="bi bi-inbox"></i>No hay vales de herramienta para su departamento.</div></td></tr>
                            <?php else: ?>
                            <?php foreach ($vales_herramienta as $v): ?>
                            <tr>
                                <td><span style="font-weight:600;color:#0B2D6B"><?= htmlspecialchars($v['folio'] ?? '') ?></span></td>
                                <td><?= htmlspecialchars($v['fecha'] ?? '') ?></td>
                                <td><?= htmlspecialchars($v['solicita'] ?? '') ?></td>
                                <td><?= htmlspecialchars($v['autoriza'] ?? '') ?></td>
                                <td><span class="badge-almacenatec <?= $v['estatus'] ?? 'pendiente' ?>"><?= ucfirst($v['estatus'] ?? 'Pendiente') ?></span></td>
                                <td>
                                    <button class="btn-ver-almacenatec" onclick="verValeHerramienta(<?= $v['id'] ?>)">
                                        <i class="bi bi-eye"></i> Ver más
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ============================================================ -->
            <!-- COMPRAS                                                       -->
            <!-- ============================================================ -->
            <div class="tabla-titulo">
                <i class="bi bi-cart-check"></i>
                Compras
                <small>Compras relacionadas con su departamento</small>
            </div>

            <div class="card-panel">
                <div class="table-responsive-almacenatec">
                    <table class="table-almacenatec">
                        <thead>
                            <tr>
                                <th><i class="bi bi-hash"></i> No. Orden</th>
                                <th><i class="bi bi-file-earmark"></i> Factura</th>
                                <th><i class="bi bi-info-circle"></i> Descripción</th>
                                <th><i class="bi bi-check-circle"></i> Entregado</th>
                                <th><i class="bi bi-credit-card"></i> Pagado</th>
                                <th><i class="bi bi-clock"></i> Fecha pago</th>
                                <th><i class="bi bi-tools"></i> Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($compras)): ?>
                            <tr><td colspan="7"><div class="sin-resultados"><i class="bi bi-inbox"></i>No hay compras para su departamento.</div></td></tr>
                            <?php else: ?>
                            <?php foreach ($compras as $c): ?>
                            <tr>
                                <td><span style="font-weight:600;color:#0B2D6B"><?= htmlspecialchars($c['no_orden_compra'] ?? '') ?></span></td>
                                <td><?= htmlspecialchars($c['no_factura'] ?? '—') ?></td>
                                <td><?= htmlspecialchars($c['descripcion'] ?? '') ?></td>
                                <td><span class="badge-almacenatec <?= $c['entregado'] ? 'entregado' : 'pendiente' ?>"><?= $c['entregado'] ? '✅ Entregado' : '⏳ Pendiente' ?></span></td>
                                <td><span class="badge-almacenatec <?= $c['pagado'] ? 'pagado' : 'pendiente' ?>"><?= $c['pagado'] ? '✅ Pagado' : '⏳ Pendiente' ?></span></td>
                                <td><?= $c['fecha_pago'] ? date('d/m/Y H:i', strtotime($c['fecha_pago'])) : '—' ?></td>
                                <td>
                                    <button class="btn-ver-almacenatec" onclick="verCompra(<?= $c['orden_compra_id'] ?>)">
                                        <i class="bi bi-eye"></i> Ver más
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </main>
    </div>
</div>

<!-- ============================================================ -->
<!-- MODAL: DETALLE GENÉRICO                                       -->
<!-- ============================================================ -->
<div class="modal fade" id="modalDetalle" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header-almacenatec">
                <h5 class="modal-title" id="modalDetalleTitle"><i class="bi bi-info-circle me-2"></i>Detalle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modalDetalleBody"></div>
            <div class="modal-footer">
                <button type="button" class="btn-outline-almacenatec" data-bs-dismiss="modal"><i class="bi bi-x-lg me-1"></i> Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script src="js/bootstrap.bundle.min.js"></script>
<script>
    function toggleSidebar() {
        const s = document.getElementById('sidebar'), o = document.getElementById('sidebarOverlay'), w = document.getElementById('mainWrapper');
        if (window.innerWidth <= 1100) { const open = s.classList.toggle('open'); o.classList.toggle('visible', open); }
        else { s.classList.toggle('collapsed'); w.classList.toggle('sidebar-hidden'); }
    }
    function closeSidebar() { document.getElementById('sidebar').classList.remove('open'); document.getElementById('sidebarOverlay').classList.remove('visible'); }

    function escapeHtml(text) {
        if (!text && text !== 0) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function row(label, value) {
        return `<div class="detalle-row"><div class="label">${label}</div><div class="value">${escapeHtml(value) || '—'}</div></div>`;
    }

    // ── VER REQUISICIÓN ──
    function verRequisicion(id) {
        const r = <?= json_encode($requisiciones) ?>.find(x => x.id == id);
        if (!r) return alert('Requisición no encontrada');
        let html = '';
        html += row('Folio', r.folio);
        html += row('Fecha', r.fecha);
        html += row('Jefe Solicitante', r.jefe_solicitante);
        html += row('Área Solicitante', r.area_solicitante);
        html += row('Programa Operativo Anual', r.programa_operativo_anual ? '✅ Completado' : '❌ No completado');
        html += row('Clave Presupuestal', r.clave_presupuestal);
        html += row('Partida', r.partida);
        html += row('Cantidad', r.cantidad);
        html += row('Unidad', r.unidad);
        html += row('Descripción', r.descripcion);
        html += row('Costo Estimado Total', '$' + Number(r.costo_estimado_total || 0).toFixed(2));
        html += row('Estatus', r.estatus);
        document.getElementById('modalDetalleTitle').innerHTML = '<i class="bi bi-file-earmark-text me-2"></i>Detalle de Requisición';
        document.getElementById('modalDetalleBody').innerHTML = html;
        new bootstrap.Modal(document.getElementById('modalDetalle')).show();
    }

    // ── VER ACTIVO ──
    function verActivo(id) {
        const a = <?= json_encode($activos) ?>.find(x => x.id == id);
        if (!a) return alert('Activo no encontrado');
        let html = '';
        html += row('Folio', a.folio);
        html += row('Núm. Oficina', a.num_oficina);
        html += row('Fecha', a.fecha);
        html += row('Jefe de Departamento', a.jefe_departamento);
        html += row('Descripción del Bien', a.descripcion_bien);
        html += row('Número SIMENU', a.num_simenu);
        html += row('Código SEP', a.sep);
        html += row('Etiqueta', a.etiqueta);
        html += row('Departamento que Recibe', a.departamento_recibe);
        html += row('Área Pertenece', a.area_pertenece);
        html += row('Resguardante', a.resguardante);
        html += row('Marca', a.marca);
        html += row('Modelo', a.modelo);
        html += row('Serie', a.serie);
        html += row('Disponible', a.disponible ? '✅ Sí' : '❌ No');
        if (a.memoria_ram) html += row('Memoria RAM', a.memoria_ram);
        if (a.sistema_operativo) html += row('Sistema Operativo', a.sistema_operativo);
        if (a.tipo_disco) html += row('Tipo de Disco', a.tipo_disco);
        if (a.capacidad_disco) html += row('Capacidad del Disco', a.capacidad_disco);
        if (a.anio_asignacion) html += row('Año de Asignación', a.anio_asignacion);
        if (a.antiguedad) html += row('Antigüedad', a.antiguedad + ' años');
        if (a.aire_tipo) html += row('Tipo de Aire', a.aire_tipo);
        if (a.aire_capacidad) html += row('Capacidad', a.aire_capacidad);
        if (a.aire_ubicacion) html += row('Ubicación', a.aire_ubicacion);
        if (a.aire_tecnologia) html += row('Tecnología', a.aire_tecnologia);
        if (a.aire_ciclo) html += row('Ciclo de Climatización', a.aire_ciclo);
        if (a.aire_funciona !== undefined && a.aire_funciona !== null) html += row('Funciona', a.aire_funciona ? '✅ Sí' : '❌ No');
        document.getElementById('modalDetalleTitle').innerHTML = '<i class="bi bi-safe-fill me-2"></i>Detalle de Activo Fijo';
        document.getElementById('modalDetalleBody').innerHTML = html;
        new bootstrap.Modal(document.getElementById('modalDetalle')).show();
    }

    // ── VER SALIDA ──
    function verSalida(id) {
        const s = <?= json_encode($salidas) ?>.find(x => x.id == id);
        if (!s) return alert('Salida no encontrada');
        let html = '';
        html += row('Folio', s.folio);
        html += row('Fecha', s.fecha);
        html += row('Departamento', s.departamento_nombre);
        html += row('Proveedor', s.proveedor);
        html += row('Factura', s.factura);
        html += row('Proyecto', s.proyecto);
        html += row('Observaciones', s.observaciones);
        document.getElementById('modalDetalleTitle').innerHTML = '<i class="bi bi-clipboard2-data me-2"></i>Detalle de Salida';
        document.getElementById('modalDetalleBody').innerHTML = html;
        new bootstrap.Modal(document.getElementById('modalDetalle')).show();
    }

    // ── VER VALE DE MATERIAL ──
    function verValeMaterial(id) {
        const v = <?= json_encode($vales_material) ?>.find(x => x.id == id);
        if (!v) return alert('Vale no encontrado');
        let html = '';
        html += row('Folio', v.folio);
        html += row('Fecha', v.fecha);
        html += row('Solicita', v.solicita);
        html += row('Área de Uso', v.area_uso);
        html += row('Descripción', v.descripcion);
        html += row('Autoriza', v.autoriza);
        html += row('Estatus', v.estatus);
        document.getElementById('modalDetalleTitle').innerHTML = '<i class="bi bi-file-earmark-text me-2"></i>Detalle de Vale de Material';
        document.getElementById('modalDetalleBody').innerHTML = html;
        new bootstrap.Modal(document.getElementById('modalDetalle')).show();
    }

    // ── VER VALE DE HERRAMIENTA ──
    function verValeHerramienta(id) {
        const v = <?= json_encode($vales_herramienta) ?>.find(x => x.id == id);
        if (!v) return alert('Vale no encontrado');
        let html = '';
        html += row('Folio', v.folio);
        html += row('Fecha', v.fecha);
        html += row('Solicita', v.solicita);
        html += row('Descripción', v.descripcion);
        html += row('Autoriza', v.autoriza);
        html += row('Estatus', v.estatus);
        document.getElementById('modalDetalleTitle').innerHTML = '<i class="bi bi-tools me-2"></i>Detalle de Vale de Herramienta';
        document.getElementById('modalDetalleBody').innerHTML = html;
        new bootstrap.Modal(document.getElementById('modalDetalle')).show();
    }

    // ── VER COMPRA ──
    function verCompra(ordenId) {
        const c = <?= json_encode($compras) ?>.find(x => x.orden_compra_id == ordenId);
        if (!c) return alert('Compra no encontrada');
        let html = '';
        html += row('No. Orden', c.no_orden_compra);
        html += row('No. Factura', c.no_factura);
        html += row('Descripción', c.descripcion);
        html += row('Entregado', c.entregado ? '✅ Sí' : '❌ No');
        html += row('Pagado', c.pagado ? '✅ Sí' : '❌ No');
        html += row('Fecha de Pago', c.fecha_pago ? new Date(c.fecha_pago).toLocaleString('es-MX') : '—');
        document.getElementById('modalDetalleTitle').innerHTML = '<i class="bi bi-cart-check me-2"></i>Detalle de Compra';
        document.getElementById('modalDetalleBody').innerHTML = html;
        new bootstrap.Modal(document.getElementById('modalDetalle')).show();
    }
</script>
</body>
</html>