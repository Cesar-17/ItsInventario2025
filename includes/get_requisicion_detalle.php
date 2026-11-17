<?php
// includes/get_requisicion_detalle.php
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
    $stmt = $pdo->prepare("SELECT * FROM requisiciones WHERE id = ?");
    $stmt->execute([$id]);
    $requisicion = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$requisicion) {
        echo json_encode(['ok' => false, 'error' => 'Requisición no encontrada']);
        exit;
    }

    echo json_encode(['ok' => true, 'requisicion' => $requisicion]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
?>