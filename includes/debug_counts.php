<?php
// includes/debug_counts.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/functions.php';
try {
    $res = [];
    $stmt = $pdo->query('SELECT COUNT(*) AS c FROM salidas_inventario');
    $res['salidas'] = (int)$stmt->fetchColumn();
    $stmt = $pdo->query('SELECT COUNT(*) AS c FROM ordenes_compra');
    $res['ordenes'] = (int)$stmt->fetchColumn();
    echo json_encode(['ok' => true, 'counts' => $res]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}

?>
