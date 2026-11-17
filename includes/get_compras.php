<?php
// includes/get_compras.php
header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['ok' => false, 'error' => 'No autenticado']);
    exit;
}

require_once __DIR__ . '/db.php';

try {
    $sql = "
        SELECT c.*, 
               oc.no_orden_compra, 
               oc.area_solicitante,
               oc.descripcion_general as descripcion,
               oc.estatus as orden_estatus,
               oc.id as orden_id,
               f.numero_factura as no_factura,
               fin.id as finanza_id,
               fin.pagado,
               fin.fecha_pago,
               area.id as area_id
        FROM compras c
        LEFT JOIN ordenes_compra oc ON oc.id = c.orden_compra_id
        LEFT JOIN facturas f ON f.id = c.factura_id
        LEFT JOIN finanzas fin ON fin.compra_id = c.id
        LEFT JOIN area_solicitante area ON area.compra_id = c.id
        ORDER BY c.id DESC
    ";
    
    $stmt = $pdo->query($sql);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['ok' => true, 'compras' => $rows]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
?>