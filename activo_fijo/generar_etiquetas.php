<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

$nombre = htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Usuario');
$rol = $_SESSION['usuario_rol'] ?? 'usuario';
$rolesRestringidos = ['almacenista', 'mantenimiento'];
$soloProductos = in_array($rol, $rolesRestringidos);

$activo_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$activo_id) {
    die('ID de activo no válido');
}

// Obtener datos del activo
$stmt = $pdo->prepare("SELECT * FROM activos_fijos WHERE id = ?");
$stmt->execute([$activo_id]);
$activo = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$activo) {
    die('Activo no encontrado');
}

// Obtener etiquetas generadas
$stmt = $pdo->prepare("SELECT * FROM etiquetas_activos WHERE activo_id = ? ORDER BY consecutivo ASC");
$stmt->execute([$activo_id]);
$etiquetas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Etiquetas Generadas — AlmacenaTec</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
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
        .table-almacenatec tbody tr:hover { background: #F0F4F9; }
        .table-almacenatec tbody tr:last-child td { border-bottom: none; }
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
        .card-header-almacenatec h3 i { color: #c9973a; margin-right: 8px; }

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
        .btn-primary-almacenatec:hover { background: #071d46; color: #FFFFFF; }
        .btn-primary-almacenatec i { color: #c9973a; }

        .btn-outline-almacenatec {
            background: transparent;
            color: #0B2D6B;
            border: 1.5px solid #0B2D6B;
            padding: 7px 17px;
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
        .btn-outline-almacenatec:hover { background: #0B2D6B; color: #FFFFFF; }
        .btn-outline-almacenatec i { color: #0B2D6B; }
        .btn-outline-almacenatec:hover i { color: #FFFFFF; }

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

        /* ── Etiqueta ── */
        .etiqueta-card {
            background: #fff;
            border: 2px solid #0B2D6B;
            border-radius: 10px;
            padding: 16px;
            margin-bottom: 16px;
            text-align: center;
            page-break-inside: avoid;
        }
        .etiqueta-card h4 {
            font-family: 'Poppins', sans-serif;
            font-size: 12px;
            color: #0B2D6B;
            font-weight: 700;
            letter-spacing: 1px;
            margin-bottom: 10px;
            text-transform: uppercase;
            border-bottom: 2px solid #c9973a;
            padding-bottom: 6px;
        }
        .etiqueta-card .campo {
            font-size: 11px;
            text-align: left;
            margin-bottom: 4px;
        }
        .etiqueta-card .campo strong {
            color: #0B2D6B;
            display: inline-block;
            width: 100px;
        }
        .etiqueta-card .barcode {
            margin-top: 10px;
            padding-top: 6px;
            border-top: 1px dashed #E2E8F0;
        }

        .info-activo {
            background: #F0F4F9;
            padding: 16px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 4px solid #0B2D6B;
        }
        .info-activo .label {
            font-size: 11px;
            color: #94A3B8;
            text-transform: uppercase;
            font-weight: 600;
        }
        .info-activo .value {
            font-weight: 600;
            font-size: 15px;
            color: #1E293B;
        }

        @media print {
            .no-print { display: none !important; }
            body { background: #fff !important; padding: 10px !important; }
            .app-layout { display: block !important; }
            .sidebar, .topbar, .page-header, .info-activo, .card-header-almacenatec { display: none !important; }
            .main-wrapper { margin-left: 0 !important; }
            .content { padding: 0 !important; }
            .card-panel { box-shadow: none !important; border: none !important; }
            .etiqueta-card { border: 2px solid #0B2D6B !important; page-break-inside: avoid; }
            .etiqueta-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
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
            <a href="../activo_fijo/index.php" class="sidebar-link active"><i class="bi bi-safe-fill"></i> Activo Fijo</a>
            <a href="../activo_fijo/main_activF.php" class="sidebar-link"><i class="bi bi-clipboard2-data"></i> Historial Salidas</a>
            <a href="../activo_fijo/equipos.php" class="sidebar-link"><i class="bi bi-laptop"></i> Equipos</a>
            <a href="../orden_compra/requisiciones.php" class="sidebar-link"><i class="bi bi-cart3"></i> Almacén General</a>
            <?php endif; ?>
            <a href="../productos/main_productos.php" class="sidebar-link"><i class="bi bi-box-seam"></i> Productos</a>
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
                <h1><i class="bi bi-tag me-2" style="color:#0B2D6B"></i>Etiquetas Generadas</h1>
                <div class="topbar-breadcrumb"><a href="../home/index.php">Inicio</a> &rsaquo; <a href="index.php">Activo Fijo</a> &rsaquo; Etiquetas</div>
            </div>
            <div style="display:flex;align-items:center;gap:10px;flex-shrink:0">
                <span style="font-size:13px;color:#475569"><i class="bi bi-person-circle me-1"></i><?= $nombre ?></span>
                <a href="<?= BASE_PATH ?>logout.php" class="btn-accent-almacenatec" style="padding:6px 12px;font-size:12px"><i class="bi bi-box-arrow-right me-1"></i> Salir</a>
            </div>
        </header>

        <main class="content">

            <!-- Info del activo -->
            <div class="info-activo">
                <div class="row">
                    <div class="col-md-4">
                        <div class="label">Descripción</div>
                        <div class="value"><?= htmlspecialchars($activo['descripcion_bien'] ?? '') ?></div>
                    </div>
                    <div class="col-md-4">
                        <div class="label">Marca / Modelo</div>
                        <div class="value"><?= htmlspecialchars($activo['marca'] ?? '') ?> <?= htmlspecialchars($activo['modelo'] ?? '') ?></div>
                    </div>
                    <div class="col-md-4">
                        <div class="label">Serie</div>
                        <div class="value"><?= htmlspecialchars($activo['serie'] ?? '') ?></div>
                    </div>
                    <div class="col-md-4">
                        <div class="label">Cantidad de Unidades</div>
                        <div class="value"><?= (int)($activo['cantidad_unidades'] ?? 1) ?></div>
                    </div>
                    <div class="col-md-4">
                        <div class="label">N. Inventario Base</div>
                        <div class="value"><?= htmlspecialchars($activo['num_inventario_base'] ?? '—') ?></div>
                    </div>
                    <div class="col-md-4">
                        <div class="label">Año</div>
                        <div class="value"><?= htmlspecialchars($activo['num_inventario_anio'] ?? '—') ?></div>
                    </div>
                </div>
            </div>

            <div class="page-header no-print">
                <h2><i class="bi bi-tag me-2" style="color:#0B2D6B"></i>Etiquetas de Inventario</h2>
                <div style="display:flex;gap:8px;flex-wrap:wrap">
                    <a href="index.php" class="btn-outline-almacenatec"><i class="bi bi-arrow-left me-1"></i> Volver</a>
                    <button class="btn-primary-almacenatec" onclick="imprimirTodasEtiquetas()"><i class="bi bi-printer me-1"></i> Imprimir Todas</button>
                </div>
            </div>

            <div class="tabla-titulo no-print">
                <i class="bi bi-tag"></i>
                Etiquetas Generadas
                <small><?= count($etiquetas) ?> etiqueta(s)</small>
            </div>

            <?php if (empty($etiquetas)): ?>
            <div class="alert alert-info text-center py-5">
                <i class="bi bi-inbox" style="font-size:48px;display:block;margin-bottom:12px;color:#94A3B8"></i>
                <h4>No hay etiquetas generadas</h4>
                <p class="text-muted">Regresa a la entrada para generar las etiquetas.</p>
            </div>
            <?php else: ?>
            <div class="card-panel">
                <div class="card-header-almacenatec no-print">
                    <h3><i class="bi bi-table"></i>Lista de Etiquetas</h3>
                    <button class="btn-primary-almacenatec" onclick="imprimirTodasEtiquetas()"><i class="bi bi-printer me-1"></i> Imprimir Todas</button>
                </div>

                <div class="table-responsive-almacenatec no-print">
                    <table class="table-almacenatec">
                        <thead>
                            <tr>
                                <th><i class="bi bi-hash"></i> Consecutivo</th>
                                <th><i class="bi bi-tag"></i> N. Inventario</th>
                                <th><i class="bi bi-person"></i> Usuario</th>
                                <th><i class="bi bi-geo-alt"></i> Área</th>
                                <th><i class="bi bi-file-earmark"></i> Licitación</th>
                                <th><i class="bi bi-tools"></i> Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($etiquetas as $et): ?>
                            <tr>
                                <td><span style="font-weight:600;color:#0B2D6B"><?= htmlspecialchars($et['num_inventario_completo'] ?? '') ?></span></td>
                                <td><?= htmlspecialchars($et['num_inventario_base'] ?? '') ?></td>
                                <td><?= htmlspecialchars($et['usuario_etiqueta'] ?? '') ?></td>
                                <td><?= htmlspecialchars($et['area_etiqueta'] ?? '') ?></td>
                                <td><?= htmlspecialchars($et['licitacion'] ?? '—') ?></td>
                                <td>
                                    <button class="btn-outline-almacenatec" style="padding:4px 10px;font-size:12px;" onclick="imprimirEtiquetaIndividual(<?= $et['id'] ?>)">
                                        <i class="bi bi-printer"></i> Imprimir
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Vista previa de etiquetas (visible en impresión) -->
                <div style="padding:20px;" id="etiquetasPreview">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                        <?php foreach ($etiquetas as $et): ?>
                        <div class="etiqueta-card" id="etiqueta-<?= $et['id'] ?>">
                            <h4>INSTITUTO TECNOLÓGICO DE SALTILLO</h4>
                            <div class="campo"><strong>N. INVENTARIO:</strong> <?= htmlspecialchars($et['num_inventario_completo'] ?? '') ?></div>
                            <div class="campo"><strong>USUARIO:</strong> <?= htmlspecialchars($et['usuario_etiqueta'] ?? '—') ?></div>
                            <div class="campo"><strong>ÁREA:</strong> <?= htmlspecialchars($et['area_etiqueta'] ?? '—') ?></div>
                            <div class="campo"><strong>LICITACIÓN:</strong> <?= htmlspecialchars($et['licitacion'] ?? '—') ?></div>
                            <div class="barcode">
                                <svg class="barcode-svg" data-code="<?= htmlspecialchars($et['num_inventario_completo'] ?? '') ?>"></svg>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

            </div>
            <?php endif; ?>

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

    // ──────────────────────────────────────────────────────────────
    // GENERAR CÓDIGOS DE BARRAS
    // ──────────────────────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof JsBarcode !== 'undefined') {
            document.querySelectorAll('.barcode-svg').forEach(function(el) {
                try {
                    JsBarcode(el, el.dataset.code, {
                        format: 'CODE128',
                        width: 2,
                        height: 40,
                        displayValue: false
                    });
                } catch(e) { console.error('Barcode error', e); }
            });
        }
    });

    // ──────────────────────────────────────────────────────────────
    // IMPRIMIR TODAS LAS ETIQUETAS
    // ──────────────────────────────────────────────────────────────
    function imprimirTodasEtiquetas() {
        const etiquetas = document.querySelectorAll('.etiqueta-card');
        if (etiquetas.length === 0) {
            alert('No hay etiquetas para imprimir.');
            return;
        }

        const ventana = window.open('', '_blank', 'width=900,height=700');
        if (!ventana) {
            alert('Permite ventanas emergentes para imprimir.');
            return;
        }

        let html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Etiquetas</title>';
        html += '<style>';
        html += '* { margin: 0; padding: 0; box-sizing: border-box; }';
        html += 'body { font-family: Arial, sans-serif; padding: 10px; background: #fff; }';
        html += '.grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }';
        html += '.etiqueta { border: 2px solid #0B2D6B; border-radius: 8px; padding: 12px; text-align: center; page-break-inside: avoid; }';
        html += '.etiqueta h4 { font-size: 11px; color: #0B2D6B; font-weight: 700; letter-spacing: 1px; margin-bottom: 8px; text-transform: uppercase; border-bottom: 2px solid #c9973a; padding-bottom: 5px; }';
        html += '.etiqueta .campo { font-size: 10px; text-align: left; margin-bottom: 3px; }';
        html += '.etiqueta .campo strong { color: #0B2D6B; display: inline-block; width: 90px; }';
        html += '.etiqueta .barcode { margin-top: 8px; padding-top: 5px; border-top: 1px dashed #E2E8F0; }';
        html += '@media print { body { padding: 5px; } .grid { gap: 8px; } }';
        html += '</style></head><body>';
        html += '<div class="grid">';

        etiquetas.forEach(function(et) {
            html += et.outerHTML;
        });

        html += '</div>';
        html += '<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"><\/script>';
        html += '<script>';
        html += 'document.querySelectorAll(".barcode-svg").forEach(function(el) {';
        html += '  try { JsBarcode(el, el.dataset.code, { format: "CODE128", width: 2, height: 40, displayValue: false }); } catch(e) {}';
        html += '});';
        html += 'setTimeout(function() { window.print(); }, 500);';
        html += '<\/script>';
        html += '</body></html>';

        ventana.document.write(html);
        ventana.document.close();
    }

    // ──────────────────────────────────────────────────────────────
    // IMPRIMIR ETIQUETA INDIVIDUAL
    // ──────────────────────────────────────────────────────────────
    function imprimirEtiquetaIndividual(id) {
        const etiqueta = document.getElementById('etiqueta-' + id);
        if (!etiqueta) {
            alert('Etiqueta no encontrada.');
            return;
        }

        const ventana = window.open('', '_blank', 'width=500,height=400');
        if (!ventana) {
            alert('Permite ventanas emergentes para imprimir.');
            return;
        }

        let html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Etiqueta</title>';
        html += '<style>';
        html += '* { margin: 0; padding: 0; box-sizing: border-box; }';
        html += 'body { font-family: Arial, sans-serif; padding: 20px; display: flex; justify-content: center; background: #fff; }';
        html += '.etiqueta { border: 2px solid #0B2D6B; border-radius: 8px; padding: 16px; text-align: center; width: 400px; }';
        html += '.etiqueta h4 { font-size: 12px; color: #0B2D6B; font-weight: 700; letter-spacing: 1px; margin-bottom: 10px; text-transform: uppercase; border-bottom: 2px solid #c9973a; padding-bottom: 6px; }';
        html += '.etiqueta .campo { font-size: 11px; text-align: left; margin-bottom: 4px; }';
        html += '.etiqueta .campo strong { color: #0B2D6B; display: inline-block; width: 100px; }';
        html += '.etiqueta .barcode { margin-top: 10px; padding-top: 6px; border-top: 1px dashed #E2E8F0; }';
        html += '@media print { body { padding: 0; } }';
        html += '</style></head><body>';
        html += etiqueta.outerHTML;
        html += '<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"><\/script>';
        html += '<script>';
        html += 'document.querySelectorAll(".barcode-svg").forEach(function(el) {';
        html += '  try { JsBarcode(el, el.dataset.code, { format: "CODE128", width: 2, height: 50, displayValue: false }); } catch(e) {}';
        html += '});';
        html += 'setTimeout(function() { window.print(); }, 500);';
        html += '<\/script>';
        html += '</body></html>';

        ventana.document.write(html);
        ventana.document.close();
    }
</script>
</body>
</html>