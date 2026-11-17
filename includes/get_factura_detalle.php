<?php
// includes/get_factura_detalle.php
header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['ok' => false, 'error' => 'No autenticado']);
    exit;
}

require_once __DIR__ . '/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id) {
    echo json_encode(['ok' => false, 'error' => 'ID inválido']);
    exit;
}

try {
    // Obtener factura con datos de la orden
    $stmt = $pdo->prepare("
        SELECT f.*, oc.no_orden_compra, oc.no_requisicion,
               CASE 
                   WHEN f.numero_factura IS NOT NULL AND f.numero_factura != '' THEN 'emitida'
                   ELSE 'pendiente'
               END as estatus
        FROM facturas f
        LEFT JOIN ordenes_compra oc ON oc.id = f.orden_compra_id
        WHERE f.id = ?
    ");
    $stmt->execute([$id]);
    $factura = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$factura) {
        echo json_encode(['ok' => false, 'error' => 'Factura no encontrada']);
        exit;
    }

    // Obtener detalles
    $stmt = $pdo->prepare("SELECT * FROM detalles_factura WHERE factura_id = ?");
    $stmt->execute([$id]);
    $detalles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['ok' => true, 'factura' => $factura, 'detalles' => $detalles]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
?>