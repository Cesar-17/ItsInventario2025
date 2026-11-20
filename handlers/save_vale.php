<?php
// handlers/save_vale.php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Método no permitido');
}

// Recoger datos del vale
$fecha       = trim($_POST['fecha'] ?? '');
$solicita    = trim($_POST['solicita'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');
$area_uso    = trim($_POST['area_uso'] ?? '');
$autoriza    = trim($_POST['autoriza'] ?? '');

// Recoger detalles
$productos     = $_POST['detalle_producto_id'] ?? [];
$cantidades    = $_POST['detalle_cantidad'] ?? [];
$detalles_desc = $_POST['detalle_descripcion'] ?? [];

// Validar
$errors = [];
if (!$fecha) $errors[] = 'La fecha es requerida';
if (!$solicita) $errors[] = 'El campo "Solicita" es requerido';
if (!$descripcion) $errors[] = 'La descripción es requerida';
if (!$area_uso) $errors[] = 'El área de uso es requerida';
if (!$autoriza) $errors[] = 'El campo "Autoriza" es requerido';

if (empty($productos) || !is_array($productos) || count($productos) === 0) {
    $errors[] = 'Debe agregar al menos un producto';
}

if (!empty($errors)) {
    http_response_code(400);
    echo implode('<br>', $errors);
    exit;
}

try {
    // Verificar que existan las tablas necesarias
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM information_schema.tables
        WHERE table_schema = DATABASE()
          AND table_name IN ('vales_material', 'detalles_vale_material')
    ");
    $stmt->execute();
    if ((int)$stmt->fetchColumn() < 2) {
        throw new Exception('Faltan las tablas vales_material o detalles_vale_material. Ejecuta el SQL de creación.');
    }

    // Generar folio del vale
    $folio = 'VALE-' . date('Ymd') . '-' . str_pad((string)random_int(0, 9999), 4, '0', STR_PAD_LEFT);

    // Iniciar transacción
    if (!$pdo->inTransaction()) {
        $pdo->beginTransaction();
    }

    // Insertar en vales_material
    $stmtVale = $pdo->prepare("
        INSERT INTO vales_material (folio, fecha, solicita, descripcion, area_uso, autoriza, estatus)
        VALUES (?, ?, ?, ?, ?, ?, 'pendiente')
    ");
    $stmtVale->execute([$folio, $fecha, $solicita, $descripcion, $area_uso, $autoriza]);
    $valeId = (int)$pdo->lastInsertId();

    if ($valeId <= 0) {
        throw new Exception('No se pudo obtener el ID del vale registrado.');
    }

    // Preparar consultas para detalles y actualización de stock
    $stmtProd = $pdo->prepare("SELECT costo_promedio, unidad_medida_id FROM productos WHERE id = ? LIMIT 1");
    $stmtDetalle = $pdo->prepare("
        INSERT INTO detalles_vale_material
        (vale_material_id, producto_id, descripcion, cantidad, unidad_medida_id, precio_unitario, importe)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmtStock = $pdo->prepare("UPDATE productos SET stock_actual = stock_actual - ? WHERE id = ?");

    $insertados = 0;

    foreach ($productos as $i => $producto_id) {
        if (empty($producto_id) || !is_numeric($producto_id)) {
            continue;
        }

        $productoIdInt = (int)$producto_id;
        $cantidad = (isset($cantidades[$i]) && is_numeric($cantidades[$i]))
            ? max(0.01, (float)$cantidades[$i])
            : 1.0;

        $detalle_desc = isset($detalles_desc[$i]) && trim($detalles_desc[$i]) !== ''
            ? trim($detalles_desc[$i])
            : $descripcion;

        $stmtProd->execute([$productoIdInt]);
        $prod = $stmtProd->fetch(PDO::FETCH_ASSOC);
        if (!$prod) {
            throw new Exception("El producto con ID {$productoIdInt} no existe.");
        }

        $precio    = isset($prod['costo_promedio']) ? (float)$prod['costo_promedio'] : 0.0;
        $unidad_id = !empty($prod['unidad_medida_id']) ? (int)$prod['unidad_medida_id'] : 1;
        $importe   = $cantidad * $precio;

        $stmtDetalle->execute([
            $valeId,
            $productoIdInt,
            $detalle_desc,
            $cantidad,
            $unidad_id,
            $precio,
            $importe
        ]);

        $stmtStock->execute([$cantidad, $productoIdInt]);
        $insertados++;
    }

    if ($insertados === 0) {
        throw new Exception('Debe seleccionar al menos un producto válido.');
    }

    if ($pdo->inTransaction()) {
        $pdo->commit();
    }

    header('Location: ' . BASE_PATH . 'productos/vale_material.php?ok=1');
    exit;

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Error al guardar vale: ' . $e->getMessage());
    http_response_code(500);
    echo 'Error al guardar el vale: ' . htmlspecialchars($e->getMessage());
    exit;
}