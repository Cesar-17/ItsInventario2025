<?php
// handlers/save_orden_compra.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Método no permitido');
}

$fecha = trim($_POST['fecha'] ?? '');
$no_orden_compra = trim($_POST['no_orden_compra'] ?? '');
$proveedor = trim($_POST['proveedor'] ?? '');
$no_requisicion = trim($_POST['no_requisicion'] ?? '');
$area_solicitante = trim($_POST['area_solicitante'] ?? '');
$condiciones = trim($_POST['condiciones'] ?? '');
$vendedor = trim($_POST['vendedor'] ?? '');
$via_embarque = trim($_POST['via_embarque'] ?? '');
$subtotal = floatval($_POST['subtotal'] ?? 0);
$iva = floatval($_POST['iva'] ?? 0);
$total = floatval($_POST['total'] ?? 0);

// Detalles
$detalles_articulo = $_POST['detalle_articulo'] ?? [];
$detalles_nombre = $_POST['detalle_nombre'] ?? [];
$detalles_unidad = $_POST['detalle_unidad'] ?? [];
$detalles_unidades = $_POST['detalle_unidades'] ?? [];
$detalles_precio = $_POST['detalle_precio'] ?? [];
$detalles_descuento = $_POST['detalle_descuento'] ?? [];

$errors = [];
if (!$fecha) $errors[] = 'La fecha es requerida';
if (!$proveedor) $errors[] = 'El proveedor es requerido';
if (!$no_requisicion) $errors[] = 'El número de requisición es requerido';
if (!$area_solicitante) $errors[] = 'El área solicitante es requerida';
if (empty($detalles_nombre)) $errors[] = 'Debe agregar al menos un artículo';

if (!empty($errors)) {
    http_response_code(400);
    echo implode('<br>', $errors);
    exit;
}

// ============================================================
// VERIFICAR SI EL NO_ORDEN_COMPRA YA EXISTE
// ============================================================
if ($no_orden_compra !== '') {
    $stmtCheck = $pdo->prepare("SELECT id FROM ordenes_compra WHERE no_orden_compra = ?");
    $stmtCheck->execute([$no_orden_compra]);
    if ($stmtCheck->fetch()) {
        // Generar uno nuevo automáticamente
        $nuevoNoOrden = 'OC/' . date('Y') . '/' . rand(1000, 9999);
        // Verificar que el nuevo tampoco exista
        $intentos = 0;
        while ($intentos < 10) {
            $stmtCheck2 = $pdo->prepare("SELECT id FROM ordenes_compra WHERE no_orden_compra = ?");
            $stmtCheck2->execute([$nuevoNoOrden]);
            if (!$stmtCheck2->fetch()) break;
            $nuevoNoOrden = 'OC/' . date('Y') . '/' . rand(1000, 9999);
            $intentos++;
        }
        $no_orden_compra = $nuevoNoOrden;
    }
} else {
    // Si no se proporcionó número de orden, generar uno
    $no_orden_compra = 'OC/' . date('Y') . '/' . rand(1000, 9999);
    $intentos = 0;
    while ($intentos < 10) {
        $stmtCheck2 = $pdo->prepare("SELECT id FROM ordenes_compra WHERE no_orden_compra = ?");
        $stmtCheck2->execute([$no_orden_compra]);
        if (!$stmtCheck2->fetch()) break;
        $no_orden_compra = 'OC/' . date('Y') . '/' . rand(1000, 9999);
        $intentos++;
    }
}

try {
    // Obtener tipo_compra de la requisición
    $tipo_compra = 'normal';
    try {
        $stmt = $pdo->prepare("SELECT tipo_compra FROM requisiciones WHERE folio = ?");
        $stmt->execute([$no_requisicion]);
        $req = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($req && !empty($req['tipo_compra'])) {
            $tipo_compra = $req['tipo_compra'];
        }
    } catch (Exception $e) { /* ignorar */ }

    $pdo->beginTransaction();

    $folio = 'OC-' . date('Ymd') . '-' . rand(1000, 9999);

    // 1. Insertar orden de compra
    $stmt = $pdo->prepare("
        INSERT INTO ordenes_compra 
        (folio, fecha, proveedor, no_orden_compra, no_requisicion, area_solicitante,
         subtotal, iva, total, condiciones, vendedor, via_embarque, tipo_compra)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $folio, $fecha, $proveedor, $no_orden_compra, $no_requisicion, $area_solicitante,
        $subtotal, $iva, $total, $condiciones, $vendedor, $via_embarque, $tipo_compra
    ]);
    $ordenId = $pdo->lastInsertId();

    // 2. Insertar detalles
    $stmt = $pdo->prepare("
        INSERT INTO detalles_orden_compra 
        (orden_compra_id, articulo_codigo, nombre_caracteristicas, unidad_medida, 
         unidades, precio, descuento, importe)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    foreach ($detalles_nombre as $i => $nombre) {
        if (empty($nombre)) continue;
        
        $articulo = $detalles_articulo[$i] ?? '';
        $unidad = $detalles_unidad[$i] ?? '';
        $unidades = floatval($detalles_unidades[$i] ?? 0);
        $precio = floatval($detalles_precio[$i] ?? 0);
        $descuento = floatval($detalles_descuento[$i] ?? 0);
        $importe = ($unidades * $precio) - $descuento;
        
        $stmt->execute([
            $ordenId, $articulo, $nombre, $unidad,
            $unidades, $precio, $descuento, $importe
        ]);
    }

    // 3. CREAR REGISTRO EN COMPRAS AUTOMÁTICAMENTE
    $stmtCheck = $pdo->prepare("SELECT IS_NULLABLE FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'compras' AND column_name = 'factura_id'");
    $stmtCheck->execute();
    $facturaNullable = $stmtCheck->fetchColumn();
    
    if ($facturaNullable === 'NO') {
        $pdo->exec("ALTER TABLE compras MODIFY COLUMN factura_id INT NULL");
    }

    $stmt = $pdo->prepare("INSERT INTO compras (orden_compra_id, factura_id) VALUES (?, NULL)");
    $stmt->execute([$ordenId]);

    // 4. Actualizar estatus de la requisición
    $stmt = $pdo->prepare("UPDATE requisiciones SET estatus = 'orden_generada' WHERE folio = ?");
    $stmt->execute([$no_requisicion]);

    $pdo->commit();

    header('Location: ' . BASE_PATH . 'orden_compra/compras.php?ok=1');
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    error_log('Error al guardar orden de compra: ' . $e->getMessage());
    
    echo '<div style="padding:20px;font-family:Arial;">';
    echo '<h2 style="color:red;">Error al guardar la orden</h2>';
    echo '<p><strong>Mensaje:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<p><a href="javascript:history.back()">Volver</a></p>';
    echo '</div>';
    exit;
}
?>