<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
$nombre_usuario = htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Usuario');
$rol = $_SESSION['usuario_rol'] ?? 'usuario';
$rolesRestringidos = ['almacenista', 'mantenimiento'];
$soloProductos = in_array($rol, $rolesRestringidos);

// Obtener parámetros
$factura_id = isset($_GET['factura_id']) ? (int)$_GET['factura_id'] : 0;
$orden_id = isset($_GET['orden_id']) ? (int)$_GET['orden_id'] : 0;

$factura = null;
$orden = null;
$detalles_factura = [];
$detalles_orden = [];

// Si hay factura_id, obtener la factura existente
if ($factura_id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM facturas WHERE id = ?");
    $stmt->execute([$factura_id]);
    $factura = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($factura) {
        $orden_id = $factura['orden_compra_id'];
        
        // Obtener detalles de la factura
        $stmt = $pdo->prepare("SELECT * FROM detalles_factura WHERE factura_id = ?");
        $stmt->execute([$factura_id]);
        $detalles_factura = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Si hay orden_id o se obtuvo desde factura, obtener la orden
if ($orden_id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM ordenes_compra WHERE id = ?");
    $stmt->execute([$orden_id]);
    $orden = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($orden) {
        // Obtener detalles de la orden
        $stmt = $pdo->prepare("SELECT * FROM detalles_orden_compra WHERE orden_compra_id = ?");
        $stmt->execute([$orden_id]);
        $detalles_orden = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Si no hay orden, redirigir
if (!$orden) {
    die('Orden de compra no encontrada.');
}

// Obtener requisición para datos del cliente
$stmt = $pdo->prepare("SELECT * FROM requisiciones WHERE folio = ?");
$stmt->execute([$orden['no_requisicion']]);
$requisicion = $stmt->fetch(PDO::FETCH_ASSOC);

// Valores por defecto (desde factura existente o desde orden)
$numero_factura = $factura ? $factura['numero_factura'] : 'FAC-' . date('Ymd') . '-' . str_pad($orden_id, 4, '0', STR_PAD_LEFT);
$fecha_factura = $factura ? $factura['fecha'] : date('Y-m-d');

// Datos del cliente
$cliente_nombre = $factura ? $factura['cliente_nombre'] : 'TecNM Campus Saltillo';
$cliente_direccion = $factura ? $factura['cliente_direccion'] : 'Boulevard Venustiano Carranza #2400, Col. Tecnológico, Saltillo, Coahuila, C.P. 25280';
$cliente_rfc = $factura ? $factura['cliente_rfc'] : 'TNM-850101-XXX';

// Condiciones
$condiciones = $factura ? $factura['condiciones'] : ($orden['condiciones'] ?? '');
$vendedor = $factura ? $factura['vendedor'] : ($orden['vendedor'] ?? '');
$via_embarque = $factura ? $factura['via_embarque'] : ($orden['via_embarque'] ?? '');

// Totales
$subtotal = $factura ? $factura['subtotal'] : ($orden['subtotal'] ?? 0);
$iva = $factura ? $factura['iva'] : ($orden['iva'] ?? 0);
$total = $factura ? $factura['total'] : ($orden['total'] ?? 0);

// Determinar qué detalles mostrar
$detalles_mostrar = !empty($detalles_factura) ? $detalles_factura : $detalles_orden;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $factura ? 'Editar' : 'Crear' ?> Factura — AlmacenaTec</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .form-section-title {
            font-size: 11px; font-weight: 700; text-transform: uppercase;
            letter-spacing: 1px; color: var(--c-text-muted);
            padding: 18px 0 8px; border-bottom: 1px solid var(--c-border);
            margin-bottom: 16px;
        }
        .form-section-title i { color: var(--c-primary); }
        .detalle-row {
            background: #f8faff;
            padding: 12px;
            border-radius: var(--radius-sm);
            margin-bottom: 8px;
            border: 1px solid var(--c-border);
        }
        .total-section {
            background: #f0f4f9;
            padding: 16px 20px;
            border-radius: var(--radius-sm);
            margin-top: 16px;
        }
        .total-section .total-row {
            display: flex;
            justify-content: flex-end;
            gap: 40px;
            padding: 4px 0;
            font-size: 14px;
        }
        .total-section .total-row .label { font-weight: 600; color: var(--c-text-muted); width: 120px; text-align: right; }
        .total-section .total-row .value { font-weight: 700; width: 150px; text-align: right; }
        .total-section .total-row.total-final .value { font-size: 20px; color: var(--c-primary); }
        .total-section .total-row.total-final .label { font-size: 16px; color: var(--c-text); }
        .alert-info-orden {
            background: #dbeafe;
            border: 1px solid #93c5fd;
            padding: 12px 16px;
            border-radius: var(--radius-sm);
            margin-bottom: 16px;
        }
        .alert-info-orden strong { color: var(--c-primary); }
        .btn-eliminar-detalle {
            background: rgba(220,38,38,.1);
            color: #dc2626;
            border: none;
            padding: 4px 8px;
            border-radius: 4px;
            cursor: pointer;
            transition: all .18s;
        }
        .btn-eliminar-detalle:hover {
            background: #dc2626;
            color: #fff;
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
                <h1><i class="bi bi-pencil-square me-2" style="color:var(--c-primary)"></i><?= $factura ? 'Editar' : 'Crear' ?> Factura</h1>
                <div class="topbar-breadcrumb"><a href="../home/index.php">Inicio</a> &rsaquo; <a href="facturas_lista.php">Facturas</a> &rsaquo; <?= $factura ? 'Editar' : 'Nueva' ?></div>
            </div>
            <div style="display:flex;align-items:center;gap:10px;flex-shrink:0">
                <span style="font-size:13px;color:var(--c-text-md)"><i class="bi bi-person-circle me-1"></i><?= $nombre_usuario ?></span>
                <a href="<?= BASE_PATH ?>logout.php" class="btn-accent-brand" style="padding:6px 12px;font-size:12px"><i class="bi bi-box-arrow-right me-1"></i> Salir</a>
            </div>
        </header>

        <main class="content">
            <div class="card-panel" style="max-width: 1100px; margin: 0 auto;">
                <div style="padding: 24px;">

                    <div class="alert-info-orden">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Orden de Compra:</strong> <?= htmlspecialchars($orden['no_orden_compra']) ?> &nbsp;|&nbsp;
                        <strong>Proveedor:</strong> <?= htmlspecialchars($orden['proveedor']) ?> &nbsp;|&nbsp;
                        <strong>Requisición:</strong> <?= htmlspecialchars($orden['no_requisicion']) ?>
                        <input type="hidden" name="orden_compra_id" value="<?= $orden_id ?>">
                        <input type="hidden" name="factura_id" value="<?= $factura_id ?>">
                    </div>

                    <form method="POST" action="<?= BASE_PATH ?>handlers/save_factura.php" id="formFactura">

                        <input type="hidden" name="orden_compra_id" value="<?= $orden_id ?>">
                        <input type="hidden" name="factura_id" value="<?= $factura_id ?>">

                        <!-- ============================================================ -->
                        <!-- DATOS DE LA FACTURA                                           -->
                        <!-- ============================================================ -->
                        <div class="form-section-title"><i class="bi bi-file-earmark-text me-1"></i>Datos de la Factura</div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Número de Factura <span class="text-danger">*</span></label>
                                <input type="text" name="numero_factura" class="form-control" value="<?= htmlspecialchars($numero_factura) ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Fecha <span class="text-danger">*</span></label>
                                <input type="date" name="fecha" class="form-control" value="<?= $fecha_factura ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">RFC Cliente <span class="text-danger">*</span></label>
                                <input type="text" name="cliente_rfc" class="form-control" value="<?= htmlspecialchars($cliente_rfc) ?>" required>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Nombre del Cliente <span class="text-danger">*</span></label>
                                <input type="text" name="cliente_nombre" class="form-control" value="<?= htmlspecialchars($cliente_nombre) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Dirección del Cliente <span class="text-danger">*</span></label>
                                <input type="text" name="cliente_direccion" class="form-control" value="<?= htmlspecialchars($cliente_direccion) ?>" required>
                            </div>
                        </div>

                 

                        <!-- ============================================================ -->
                        <!-- DETALLES DE LA FACTURA                                       -->
                        <!-- ============================================================ -->
                        <div class="form-section-title"><i class="bi bi-list-ul me-1"></i>Detalles de la Factura</div>
                        <p class="text-muted" style="font-size:12px;margin-bottom:12px;">
                            <i class="bi bi-info-circle"></i> Los detalles se cargan automáticamente desde la Orden de Compra. Puedes modificarlos.
                        </p>

                        <div id="detallesContainer">
                            <?php if (!empty($detalles_mostrar)): ?>
                                <?php foreach ($detalles_mostrar as $index => $det): ?>
                                <div class="detalle-row" data-index="<?= $index ?>">
                                    <div class="row g-2">
                                        <div class="col-md-2">
                                            <label class="form-label">Artículo (Código)</label>
                                            <input type="text" name="detalle_articulo[]" class="form-control" value="<?= htmlspecialchars($det['articulo_codigo'] ?? '') ?>" placeholder="Ej: PL-7-120">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Nombre y características <span class="text-danger">*</span></label>
                                            <input type="text" name="detalle_nombre[]" class="form-control" value="<?= htmlspecialchars($det['nombre_caracteristicas'] ?? '') ?>" required>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">Unidad <span class="text-danger">*</span></label>
                                            <input type="text" name="detalle_unidad[]" class="form-control" value="<?= htmlspecialchars($det['unidad_medida'] ?? '') ?>" required>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">Unidades <span class="text-danger">*</span></label>
                                            <input type="number" name="detalle_unidades[]" class="form-control detalle-cantidad" min="1" value="<?= $det['unidades'] ?? 1 ?>" required>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">Precio <span class="text-danger">*</span></label>
                                            <input type="number" name="detalle_precio[]" class="form-control detalle-precio" step="0.01" min="0" value="<?= $det['precio'] ?? 0 ?>" required>
                                        </div>
                                        <div class="col-md-1" style="display:flex;align-items:flex-end;padding-bottom:4px">
                                            <button type="button" class="btn-eliminar-detalle btn-remove-detalle" style="width:100%" onclick="removeDetalle(this)"><i class="bi bi-trash"></i></button>
                                        </div>
                                    </div>
                                    <div class="row g-2 mt-2">
                                        <div class="col-md-3 offset-md-9">
                                            <label class="form-label">Descuento</label>
                                            <input type="number" name="detalle_descuento[]" class="form-control detalle-descuento" step="0.01" min="0" value="<?= $det['descuento'] ?? 0 ?>">
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="detalle-row" data-index="0">
                                    <div class="row g-2">
                                        <div class="col-md-2">
                                            <label class="form-label">Artículo (Código)</label>
                                            <input type="text" name="detalle_articulo[]" class="form-control" placeholder="Ej: PL-7-120">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Nombre y características <span class="text-danger">*</span></label>
                                            <input type="text" name="detalle_nombre[]" class="form-control" placeholder="Descripción del artículo" required>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">Unidad <span class="text-danger">*</span></label>
                                            <input type="text" name="detalle_unidad[]" class="form-control" placeholder="Pza, Kg, Lote" required>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">Unidades <span class="text-danger">*</span></label>
                                            <input type="number" name="detalle_unidades[]" class="form-control detalle-cantidad" min="1" value="1" required>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">Precio <span class="text-danger">*</span></label>
                                            <input type="number" name="detalle_precio[]" class="form-control detalle-precio" step="0.01" min="0" value="0" required>
                                        </div>
                                        <div class="col-md-1" style="display:flex;align-items:flex-end;padding-bottom:4px">
                                            <button type="button" class="btn-eliminar-detalle btn-remove-detalle" style="width:100%" onclick="removeDetalle(this)"><i class="bi bi-trash"></i></button>
                                        </div>
                                    </div>
                                    <div class="row g-2 mt-2">
                                        <div class="col-md-3 offset-md-9">
                                            <label class="form-label">Descuento</label>
                                            <input type="number" name="detalle_descuento[]" class="form-control detalle-descuento" step="0.01" min="0" value="0">
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="mt-2">
                            <button type="button" class="btn-outline-brand" onclick="addDetalle()"><i class="bi bi-plus-lg me-1"></i> Agregar artículo</button>
                        </div>

                        <!-- ============================================================ -->
                        <!-- TOTALES                                                      -->
                        <!-- ============================================================ -->
                        <div class="total-section">
                            <div class="total-row">
                                <span class="label">SUBTOTAL:</span>
                                <span class="value" id="subtotalDisplay">$<?= number_format($subtotal ?? 0, 2) ?></span>
                            </div>
                            <div class="total-row">
                                <span class="label">IVA (16%):</span>
                                <span class="value" id="ivaDisplay">$<?= number_format($iva ?? 0, 2) ?></span>
                            </div>
                            <div class="total-row total-final">
                                <span class="label">TOTAL:</span>
                                <span class="value" id="totalDisplay">$<?= number_format($total ?? 0, 2) ?></span>
                            </div>
                            <input type="hidden" name="subtotal" id="subtotal_hidden" value="<?= $subtotal ?? 0 ?>">
                            <input type="hidden" name="iva" id="iva_hidden" value="<?= $iva ?? 0 ?>">
                            <input type="hidden" name="total" id="total_hidden" value="<?= $total ?? 0 ?>">
                        </div>

                        <!-- ============================================================ -->
                        <!-- BOTONES                                                      -->
                        <!-- ============================================================ -->
                        <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:20px">
                            <a href="facturas_lista.php" class="btn-outline-brand"><i class="bi bi-x-lg me-1"></i> Cancelar</a>
                            <button type="submit" class="btn-primary-brand"><i class="bi bi-save me-1"></i> Guardar Factura</button>
                        </div>

                    </form>
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

    function addDetalle() {
        const container = document.getElementById('detallesContainer');
        const index = container.children.length;
        const template = container.children[0].cloneNode(true);
        template.dataset.index = index;
        
        template.querySelectorAll('input').forEach(el => {
            if (el.type === 'number' && el.classList.contains('detalle-cantidad')) el.value = '1';
            else if (el.type === 'number' && el.classList.contains('detalle-precio')) el.value = '0';
            else if (el.type === 'number' && el.classList.contains('detalle-descuento')) el.value = '0';
            else if (el.type === 'text') el.value = '';
        });
        
        container.appendChild(template);
        calcularTotales();
    }

    function removeDetalle(btn) {
        const container = document.getElementById('detallesContainer');
        if (container.children.length <= 1) {
            alert('Debe haber al menos un artículo.');
            return;
        }
        btn.closest('.detalle-row').remove();
        calcularTotales();
    }

    function calcularTotales() {
        let subtotal = 0;
        const rows = document.querySelectorAll('.detalle-row');
        
        rows.forEach(row => {
            const unidades = parseFloat(row.querySelector('.detalle-cantidad')?.value) || 0;
            const precio = parseFloat(row.querySelector('.detalle-precio')?.value) || 0;
            const descuento = parseFloat(row.querySelector('.detalle-descuento')?.value) || 0;
            const importe = (unidades * precio) - descuento;
            subtotal += importe;
        });

        const iva = subtotal * 0.16;
        const total = subtotal + iva;

        document.getElementById('subtotalDisplay').textContent = '$' + subtotal.toFixed(2);
        document.getElementById('ivaDisplay').textContent = '$' + iva.toFixed(2);
        document.getElementById('totalDisplay').textContent = '$' + total.toFixed(2);
        document.getElementById('subtotal_hidden').value = subtotal.toFixed(2);
        document.getElementById('iva_hidden').value = iva.toFixed(2);
        document.getElementById('total_hidden').value = total.toFixed(2);
    }

    document.addEventListener('DOMContentLoaded', function() {
        document.addEventListener('input', function(e) {
            if (e.target.classList.contains('detalle-cantidad') || 
                e.target.classList.contains('detalle-precio') || 
                e.target.classList.contains('detalle-descuento')) {
                calcularTotales();
            }
        });
        calcularTotales();
    });
</script>
</body>
</html>