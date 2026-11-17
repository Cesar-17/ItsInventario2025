<?php
// includes/get_historial_transferencias.php
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
    $stmt = $pdo->query("
        SELECT t.*, 
               a.descripcion_bien, 
               a.marca, 
               a.modelo, 
               a.serie,
               a.num_oficina
        FROM transferencias t
        LEFT JOIN activos_fijos a ON a.id = t.activo_id
        ORDER BY t.id DESC
    ");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['ok' => true, 'transferencias' => $rows]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
?>