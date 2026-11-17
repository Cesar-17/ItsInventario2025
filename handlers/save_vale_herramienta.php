<?php
// handlers/save_vale_herramienta.php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Método no permitido');
}

// Recoger datos del vale
$fecha = trim($_POST['fecha'] ?? '');
$solicita = trim($_POST['solicita'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');
$autoriza = trim($_POST['autoriza'] ?? '');

// Recoger detalles
$productos = $_POST['detalle_producto_id'] ?? [];
$cantidades = $_POST['detalle_cantidad'] ?? [];
$detalles_desc = $_POST['detalle_descripcion'] ?? [];

// Validar
$errors = [];
if (!$fecha) $errors[] = 'La fecha es requerida';
if (!$solicita) $errors[] = 'El campo "Nombre de quien solicita" es requerido';
if (!$descripcion) $errors[] = 'La descripción de los bienes es requerida';
if (!$autoriza) $errors[] = 'El campo "Nombre de quien autoriza" es requerido';

if (empty($productos) || count($productos) === 0) {
    $errors[] = 'Debe agregar al menos un producto/herramienta';
}

if (!empty($errors)) {
    http_response_code(400);
    echo implode('<br>', $errors);
    exit;
}

try {
    // Verificar que las tablas existen
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'vales_herramienta'");
    $stmt->execute();
    if (!$stmt->fetchColumn()) {
        throw new Exception('La tabla vales_herramienta no existe. Ejecuta el SQL de creación.');
    }
    
    // Generar folio del vale de herramienta
    $folio = 'HERR-' . date('Ymd') . '-' . str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
    
    // Iniciar transacción
    if (!$pdo->inTransaction()) $pdo->beginTransaction();
    
    // Insertar en vales_herramienta
    $stmt = $pdo->prepare("
        INSERT INTO vales_herramienta (folio, fecha, solicita, descripcion, autoriza, estatus)
        VALUES (?, ?, ?, ?, ?, 'pendiente')
    ");
    $stmt->execute([$folio, $fecha, $solicita, $descripcion, $autoriza]);
    $valeId = $pdo->lastInsertId();
    
    // Insertar detalles en detalles_vale_herramienta
    foreach ($productos as $i => $producto_id) {
        if (empty($producto_id)) continue;
        
        $cantidad = isset($cantidades[$i]) ? (float)$cantidades[$i] : 1;
        $detalle_desc = isset($detalles_desc[$i]) ? trim($detalles_desc[$i]) : $descripcion;
        
        // Obtener precio del producto
        $stmt = $pdo->prepare("SELECT costo_promedio FROM productos WHERE id = ?");
        $stmt->execute([$producto_id]);
        $producto = $stmt->fetch(PDO::FETCH_ASSOC);
        $precio = $producto ? $producto['costo_promedio'] : 0;
        
        // Obtener unidad de medida del producto
        $stmt = $pdo->prepare("SELECT unidad_medida_id FROM productos WHERE id = ?");
        $stmt->execute([$producto_id]);
        $prod = $stmt->fetch(PDO::FETCH_ASSOC);
        $unidad_id = $prod ? $prod['unidad_medida_id'] : 1;
        
        $importe = $cantidad * $precio;
        
        $stmt = $pdo->prepare("
            INSERT INTO detalles_vale_herramienta 
            (vale_herramienta_id, producto_id, descripcion, cantidad, unidad_medida_id, precio_unitario, importe)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$valeId, $producto_id, $detalle_desc, $cantidad, $unidad_id, $precio, $importe]);
        
        // Actualizar stock (restar)
        $stmt = $pdo->prepare("UPDATE productos SET stock_actual = stock_actual - ? WHERE id = ?");
        $stmt->execute([$cantidad, $producto_id]);
    }
    
    // Commit
    if ($pdo->inTransaction()) $pdo->commit();
    
    header('Location: ' . BASE_PATH . 'productos/vale_herramienta.php?ok=1');
    exit;
    
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log('Error al guardar vale de herramienta: ' . $e->getMessage());
    http_response_code(500);
    echo 'Error al guardar el vale de herramienta: ' . $e->getMessage();
    exit;
}
?>