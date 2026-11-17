<?php
// includes/get_orden_detalle.php
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
    $stmt = $pdo->prepare("
        SELECT 
            oc.*,
            c.id as compra_id,
            c.entregado,
            c.fecha_entrega,
            fin.id as finanza_id,
            fin.pagado,
            fin.fecha_pago,
            area.id as area_id
        FROM ordenes_compra oc
        LEFT JOIN compras c ON c.orden_compra_id = oc.id
        LEFT JOIN finanzas fin ON fin.compra_id = c.id
        LEFT JOIN area_solicitante area ON area.compra_id = c.id
        WHERE oc.id = ?
    ");
    $stmt->execute([$id]);
    $orden = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$orden) {
        echo json_encode(['ok' => false, 'error' => 'Orden no encontrada']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM detalles_orden_compra WHERE orden_compra_id = ?");
    $stmt->execute([$id]);
    $detalles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['ok' => true, 'orden' => $orden, 'detalles' => $detalles]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
?>