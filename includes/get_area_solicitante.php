<?php
// includes/get_area_solicitante.php
header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['ok' => false, 'error' => 'No autenticado']);
    exit;
}

require_once __DIR__ . '/db.php';

$compra_id = isset($_GET['compra_id']) ? (int)$_GET['compra_id'] : 0;

if (!$compra_id) {
    echo json_encode(['ok' => false, 'error' => 'ID inválido']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM area_solicitante WHERE compra_id = ?");
    $stmt->execute([$compra_id]);
    $area = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode(['ok' => true, 'area' => $area]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
?>