<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id) {
    die('ID de orden no válido');
}

// Obtener datos de la orden
$stmt = $pdo->prepare("SELECT * FROM ordenes_compra WHERE id = ?");
$stmt->execute([$id]);
$orden = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$orden) {
    die('Orden no encontrada');
}

// Obtener detalles de la orden
$stmt = $pdo->prepare("SELECT * FROM detalles_orden_compra WHERE orden_compra_id = ?");
$stmt->execute([$id]);
$detalles = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener datos de la requisición para el cliente
$stmt = $pdo->prepare("SELECT * FROM requisiciones WHERE folio = ?");
$stmt->execute([$orden['no_requisicion']]);
$requisicion = $stmt->fetch(PDO::FETCH_ASSOC);

// Número de factura (generado automáticamente)
$numero_factura = 'FAC-' . date('Ymd') . '-' . str_pad($id, 4, '0', STR_PAD_LEFT);

// Datos del cliente (desde la requisición)
$cliente_nombre = 'TecNM Campus Saltillo';
$cliente_direccion = 'Boulevard Venustiano Carranza #2400, Col. Tecnológico, Saltillo, Coahuila, C.P. 25280';
$cliente_rfc = 'TNM-850101-XXX';

$nombre_usuario = htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Usuario');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura — AlmacenaTec</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        @media print {
            .no-print { display: none !important; }
            .factura-container { box-shadow: none !important; border: 1px solid #ccc !important; }
            body { background: #fff !important; padding: 20px !important; }
        }
        .factura-container {
            max-width: 900px;
            margin: 20px auto;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 24px rgba(0,0,0,.1);
            padding: 40px;
        }
        .factura-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 3px solid var(--c-primary);
            padding-bottom: 16px;
            margin-bottom: 24px;
        }
        .factura-header .logo h2 {
            font-family: 'Poppins', sans-serif;
            color: var(--c-primary);
            margin: 0;
        }
        .factura-header .logo span { color: var(--c-accent); }
        .factura-header .logo small {
            display: block;
            color: var(--c-text-muted);
            font-size: 12px;
        }
        .factura-header .factura-info {
            text-align: right;
        }
        .factura-header .factura-info .numero {
            font-size: 22px;
            font-weight: 700;
            color: var(--c-primary);
        }
        .factura-header .factura-info .label {
            font-size: 11px;
            color: var(--c-text-muted);
            text-transform: uppercase;
            letter-spacing: .5px;
        }
        .factura-datos {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 24px;
            background: #f8faff;
            padding: 16px 20px;
            border-radius: 8px;
        }
        .factura-datos .campo .label {
            font-size: 11px;
            color: var(--c-text-muted);
            text-transform: uppercase;
            letter-spacing: .5px;
            font-weight: 600;
        }
        .factura-datos .campo .valor {
            font-weight: 600;
            font-size: 14px;
            color: var(--c-text);
        }
        .factura-table {
            width: 100%;
            border-collapse: collapse;
            margin: 16px 0;
            font-size: 13px;
        }
        .factura-table thead th {
            background: var(--c-primary);
            color: #fff;
            padding: 10px 12px;
            text-align: left;
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: .5px;
        }
        .factura-table tbody td {
            padding: 8px 12px;
            border-bottom: 1px solid var(--c-border);
        }
        .factura-table tbody tr:hover {
            background: #f8faff;
        }
        .factura-table tfoot td {
            padding: 8px 12px;
            font-weight: 700;
        }
        .factura-table tfoot .total-row {
            background: #f0f4f9;
            font-size: 16px;
        }
        .factura-table tfoot .total-row td {
            padding: 12px;
        }
        .factura-footer {
            margin-top: 24px;
            padding-top: 16px;
            border-top: 2px dashed var(--c-border);
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 20px;
        }
        .factura-footer .firma {
            text-align: center;
        }
        .factura-footer .firma .linea {
            width: 180px;
            border-bottom: 1px solid #000;
            margin: 20px auto 4px;
        }
        .factura-footer .firma .label {
            font-size: 11px;
            color: var(--c-text-muted);
        }
        .btn-print-factura {
            background: var(--c-primary);
            color: #fff;
            border: none;
            padding: 10px 24px;
            border-radius: var(--radius-sm);
            font-weight: 600;
            cursor: pointer;
            transition: all .2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-print-factura:hover {
            background: var(--c-primary-dk);
            color: #fff;
        }
        .btn-pdf-factura {
            background: #dc2626;
            color: #fff;
            border: none;
            padding: 10px 24px;
            border-radius: var(--radius-sm);
            font-weight: 600;
            cursor: pointer;
            transition: all .2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-pdf-factura:hover {
            background: #b91c1c;
            color: #fff;
        }
        .acciones {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
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
                <h1><i class="bi bi-file-earmark-text me-2" style="color:var(--c-primary)"></i>Factura</h1>
                <div class="topbar-breadcrumb"><a href="../home/index.php">Inicio</a> &rsaquo; <a href="ordenes_compra.php">Almacén General</a> &rsaquo; Factura</div>
            </div>
            <div style="display:flex;align-items:center;gap:10px;flex-shrink:0">
                <span style="font-size:13px;color:var(--c-text-md)"><i class="bi bi-person-circle me-1"></i><?= $nombre_usuario ?></span>
                <a href="<?= BASE_PATH ?>logout.php" class="btn-accent-brand" style="padding:6px 12px;font-size:12px"><i class="bi bi-box-arrow-right me-1"></i> Salir</a>
            </div>
        </header>

        <main class="content">

            <!-- Acciones -->
            <div class="acciones no-print">
                <a href="ordenes_compra.php" class="btn-outline-brand"><i class="bi bi-arrow-left me-1"></i> Volver</a>
                <button class="btn-print-factura" onclick="window.print()"><i class="bi bi-printer me-1"></i> Imprimir</button>
                <button class="btn-pdf-factura" onclick="generarPDF()"><i class="bi bi-file-pdf me-1"></i> Guardar PDF</button>
            </div>

            <!-- Factura -->
            <div class="factura-container" id="facturaContainer">
                
                <!-- Encabezado -->
                <div class="factura-header">
                    <div class="logo">
                        <h2>Almacena<span>Tec</span></h2>
                        <small>TecNM Campus Saltillo</small>
                        <small style="margin-top:4px;">Boulevard Venustiano Carranza #2400, Col. Tecnológico, Saltillo, Coahuila, C.P. 25280</small>
                        <small>RFC: TNM-850101-XXX</small>
                    </div>
                    <div class="factura-info">
                        <div class="label">Factura</div>
                        <div class="numero"><?= $numero_factura ?></div>
                        <div style="font-size:12px;color:var(--c-text-muted);margin-top:4px;">
                            <i class="bi bi-calendar me-1"></i> <?= date('d/m/Y', strtotime($orden['fecha'])) ?>
                        </div>
                    </div>
                </div>

                <!-- Datos del cliente -->
                <div class="factura-datos">
                    <div class="campo">
                        <div class="label">Cliente</div>
                        <div class="valor"><?= $cliente_nombre ?></div>
                    </div>
                    <div class="campo">
                        <div class="label">Dirección</div>
                        <div class="valor"><?= $cliente_direccion ?></div>
                    </div>
                    <div class="campo">
                        <div class="label">RFC</div>
                        <div class="valor"><?= $cliente_rfc ?></div>
                    </div>
                    <div class="campo">
                        <div class="label">Orden de Compra</div>
                        <div class="valor"><?= $orden['no_orden_compra'] ?></div>
                    </div>
                    <div class="campo">
                        <div class="label">No. Requisición</div>
                        <div class="valor"><?= $orden['no_requisicion'] ?></div>
                    </div>
                    <div class="campo">
                        <div class="label">Área Solicitante</div>
                        <div class="valor"><?= $orden['area_solicitante'] ?></div>
                    </div>
                    <div class="campo" style="grid-column: span 2;">
                        <div class="label">Condiciones</div>
                        <div class="valor"><?= $orden['condiciones'] ?: '—' ?></div>
                    </div>
                    <div class="campo">
                        <div class="label">Vendedor</div>
                        <div class="valor"><?= $orden['vendedor'] ?: '—' ?></div>
                    </div>
                    <div class="campo">
                        <div class="label">Vía de Embarque</div>
                        <div class="valor"><?= $orden['via_embarque'] ?: '—' ?></div>
                    </div>
                </div>

                <!-- Tabla de artículos -->
                <table class="factura-table">
                    <thead>
                        <tr>
                            <th style="width:12%;">Artículo</th>
                            <th style="width:30%;">Nombre y características</th>
                            <th style="width:12%;">Unidad</th>
                            <th style="width:10%;">Unidades</th>
                            <th style="width:12%;">Precio</th>
                            <th style="width:10%;">Desct.</th>
                            <th style="width:14%;">Importe</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $subtotal = 0;
                        foreach ($detalles as $det): 
                            $importe = ($det['unidades'] * $det['precio']) - $det['descuento'];
                            $subtotal += $importe;
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($det['articulo_codigo'] ?: '—') ?></td>
                            <td><?= htmlspecialchars($det['nombre_caracteristicas']) ?></td>
                            <td><?= htmlspecialchars($det['unidad_medida']) ?></td>
                            <td class="text-center"><?= $det['unidades'] ?></td>
                            <td class="text-end">$<?= number_format($det['precio'], 2) ?></td>
                            <td class="text-end">$<?= number_format($det['descuento'], 2) ?></td>
                            <td class="text-end">$<?= number_format($importe, 2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="6" style="text-align:right;font-weight:600;">SUBTOTAL</td>
                            <td style="text-align:right;font-weight:600;">$<?= number_format($subtotal, 2) ?></td>
                        </tr>
                        <tr>
                            <td colspan="6" style="text-align:right;font-weight:600;">IVA (16%)</td>
                            <td style="text-align:right;font-weight:600;">$<?= number_format($orden['iva'] ?? 0, 2) ?></td>
                        </tr>
                        <tr class="total-row">
                            <td colspan="6" style="text-align:right;font-weight:700;font-size:16px;">TOTAL</td>
                            <td style="text-align:right;font-weight:700;font-size:18px;color:var(--c-primary);">$<?= number_format($orden['total'] ?? 0, 2) ?></td>
                        </tr>
                        <tr>
                            <td colspan="7" style="font-size:11px;color:var(--c-text-muted);text-align:right;">
                                <i class="bi bi-info-circle"></i> Total en letras: 
                                <?php 
                                    function numeroALetras($num) {
                                        $unidades = ['', 'Un', 'Dos', 'Tres', 'Cuatro', 'Cinco', 'Seis', 'Siete', 'Ocho', 'Nueve'];
                                        $decenas = ['', 'Diez', 'Veinte', 'Treinta', 'Cuarenta', 'Cincuenta', 'Sesenta', 'Setenta', 'Ochenta', 'Noventa'];
                                        if ($num < 10) return $unidades[$num];
                                        if ($num < 100) {
                                            $d = floor($num / 10);
                                            $u = $num % 10;
                                            return $decenas[$d] . ($u > 0 ? ' y ' . $unidades[$u] : '');
                                        }
                                        return number_format($num, 2);
                                    }
                                    $totalEntero = floor($orden['total'] ?? 0);
                                    echo numeroALetras($totalEntero) . ' pesos ' . number_format(($orden['total'] ?? 0) - $totalEntero, 2) . '/100 M.N.';
                                ?>
                            </td>
                        </tr>
                    </tfoot>
                </table>

                <!-- Firmas -->
                <div class="factura-footer">
                    <div class="firma">
                        <div class="linea"></div>
                        <div class="label">ENTREGÓ</div>
                    </div>
                    <div class="firma">
                        <div class="linea"></div>
                        <div class="label">RECIBIÓ</div>
                    </div>
                    <div class="firma">
                        <div class="linea"></div>
                        <div class="label">Vo.Bo. ALMACÉN</div>
                    </div>
                </div>

                <div style="text-align:center;font-size:10px;color:var(--c-text-muted);margin-top:16px;border-top:1px solid var(--c-border);padding-top:12px;">
                    <i class="bi bi-geo-alt-fill me-1"></i> TecNM Campus Saltillo · <?= date('Y') ?> · Este documento es una factura generada por AlmacenaTec
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

    function generarPDF() {
        // Usar html2pdf para generar PDF
        const element = document.getElementById('facturaContainer');
        const opt = {
            margin: 10,
            filename: 'factura_<?= $numero_factura ?>.pdf',
            image: { type: 'jpeg', quality: 0.98 },
            html2canvas: { scale: 2, useCORS: true },
            jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
        };
        
        // Verificar si html2pdf está disponible
        if (typeof html2pdf !== 'undefined') {
            html2pdf().set(opt).from(element).save();
        } else {
            // Si no está instalado, usar window.print()
            window.print();
        }
    }

    // Cargar html2pdf desde CDN si no está disponible
    if (typeof html2pdf === 'undefined') {
        const script = document.createElement('script');
        script.src = 'https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js';
        document.head.appendChild(script);
    }
</script>
</body>
</html>