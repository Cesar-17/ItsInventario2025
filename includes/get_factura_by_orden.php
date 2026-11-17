<?php
// includes/get_factura_by_orden.php
header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['ok' => false, 'error' => 'No autenticado']);
    exit;
}

require_once __DIR__ . '/db.php';

$orden_id = isset($_GET['orden_id']) ? (int)$_GET['orden_id'] : 0;

if (!$orden_id) {
    echo json_encode(['ok' => false, 'error' => 'ID de orden inválido']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM facturas WHERE orden_compra_id = ?");
    $stmt->execute([$orden_id]);
    $factura = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode(['ok' => true, 'factura' => $factura]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
?>