<?php
// handlers/save_compra.php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Método no permitido');
}

$orden_compra_id = isset($_POST['orden_compra_id']) ? (int)$_POST['orden_compra_id'] : 0;
$factura_id = isset($_POST['factura_id']) ? (int)$_POST['factura_id'] : 0;

if (!$orden_compra_id || !$factura_id) {
    http_response_code(400);
    echo 'Debes seleccionar una Orden de Compra y una Factura.';
    exit;
}

try {
    // Verificar duplicado
    $stmt = $pdo->prepare("SELECT id FROM compras WHERE orden_compra_id = ? AND factura_id = ?");
    $stmt->execute([$orden_compra_id, $factura_id]);
    if ($stmt->fetch()) {
        throw new Exception('Ya existe una compra con esa Orden de Compra y Factura.');
    }
    
    // Insertar compra
    $stmt = $pdo->prepare("INSERT INTO compras (orden_compra_id, factura_id) VALUES (?, ?)");
    $stmt->execute([$orden_compra_id, $factura_id]);
    
    header('Location: ' . BASE_PATH . 'orden_compra/compras.php?ok=1');
    exit;
    
} catch (Exception $e) {
    http_response_code(500);
    echo 'Error al guardar: ' . $e->getMessage();
    exit;
}
?>