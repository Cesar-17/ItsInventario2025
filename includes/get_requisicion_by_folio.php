<?php
// includes/get_requisicion_by_folio.php
header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['ok' => false, 'error' => 'No autenticado']);
    exit;
}

require_once __DIR__ . '/db.php';

$folio = isset($_GET['folio']) ? trim($_GET['folio']) : '';

if (!$folio) {
    echo json_encode(['ok' => false, 'error' => 'Folio requerido']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM requisiciones WHERE folio = ?");
    $stmt->execute([$folio]);
    $requisicion = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode(['ok' => true, 'requisicion' => $requisicion]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
?>