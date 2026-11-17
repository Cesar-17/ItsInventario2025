<?php
// handlers/save_factura.php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Método no permitido');
}

$orden_compra_id = isset($_POST['orden_compra_id']) ? (int)$_POST['orden_compra_id'] : 0;
$factura_id = isset($_POST['factura_id']) ? (int)$_POST['factura_id'] : 0;
$numero_factura = trim($_POST['numero_factura'] ?? '');
$fecha = trim($_POST['fecha'] ?? '');
$cliente_nombre = trim($_POST['cliente_nombre'] ?? '');
$cliente_direccion = trim($_POST['cliente_direccion'] ?? '');
$cliente_rfc = trim($_POST['cliente_rfc'] ?? '');
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
if (!$orden_compra_id) $errors[] = 'ID de orden inválido';
if (!$numero_factura) $errors[] = 'El número de factura es requerido';
if (!$fecha) $errors[] = 'La fecha es requerida';
if (!$cliente_nombre) $errors[] = 'El nombre del cliente es requerido';
if (!$cliente_direccion) $errors[] = 'La dirección del cliente es requerida';
if (empty($detalles_nombre)) $errors[] = 'Debe agregar al menos un artículo';

if (!empty($errors)) {
    http_response_code(400);
    echo implode('<br>', $errors);
    exit;
}

try {
    // Obtener tipo_compra de la orden
    $tipo_compra = 'normal';
    try {
        $stmt = $pdo->prepare("SELECT tipo_compra FROM ordenes_compra WHERE id = ?");
        $stmt->execute([$orden_compra_id]);
        $oc = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($oc && !empty($oc['tipo_compra'])) {
            $tipo_compra = $oc['tipo_compra'];
        }
    } catch (Exception $e) { /* ignorar */ }

    $pdo->beginTransaction();

    // Verificar duplicado
    $stmt = $pdo->prepare("SELECT id FROM facturas WHERE orden_compra_id = ? AND id != ?");
    $stmt->execute([$orden_compra_id, $factura_id]);
    if ($stmt->fetch()) {
        throw new Exception('Ya existe una factura para esta orden de compra.');
    }

    if ($factura_id > 0) {
        // Actualizar factura
        $stmt = $pdo->prepare("
            UPDATE facturas SET 
                numero_factura = ?, fecha = ?, cliente_nombre = ?, cliente_direccion = ?,
                cliente_rfc = ?, condiciones = ?, vendedor = ?, via_embarque = ?,
                subtotal = ?, iva = ?, total = ?, tipo_compra = ?
            WHERE id = ?
        ");
        $stmt->execute([
            $numero_factura, $fecha, $cliente_nombre, $cliente_direccion,
            $cliente_rfc, $condiciones, $vendedor, $via_embarque,
            $subtotal, $iva, $total, $tipo_compra, $factura_id
        ]);
        $facturaId = $factura_id;
        
        $stmt = $pdo->prepare("DELETE FROM detalles_factura WHERE factura_id = ?");
        $stmt->execute([$factura_id]);
        
    } else {
        // Crear factura nueva
        $stmt = $pdo->prepare("
            INSERT INTO facturas 
            (orden_compra_id, numero_factura, fecha, cliente_nombre, cliente_direccion,
             cliente_rfc, condiciones, vendedor, via_embarque, subtotal, iva, total, tipo_compra)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $orden_compra_id, $numero_factura, $fecha, $cliente_nombre, $cliente_direccion,
            $cliente_rfc, $condiciones, $vendedor, $via_embarque, $subtotal, $iva, $total, $tipo_compra
        ]);
        $facturaId = $pdo->lastInsertId();
    }

    // Insertar detalles
    $stmt = $pdo->prepare("
        INSERT INTO detalles_factura 
        (factura_id, articulo_codigo, nombre_caracteristicas, unidad_medida, 
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
            $facturaId, $articulo, $nombre, $unidad,
            $unidades, $precio, $descuento, $importe
        ]);
    }

    // 5. ACTUALIZAR LA COMPRA CON EL FACTURA_ID
    $stmt = $pdo->prepare("UPDATE compras SET factura_id = ? WHERE orden_compra_id = ?");
    $stmt->execute([$facturaId, $orden_compra_id]);

    $pdo->commit();

    header('Location: ' . BASE_PATH . 'orden_compra/facturas_lista.php?ok=1');
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    error_log('Error al guardar factura: ' . $e->getMessage());
    http_response_code(500);
    echo 'Error al guardar: ' . $e->getMessage();
    exit;
}
?>