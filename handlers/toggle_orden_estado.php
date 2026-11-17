<?php
// handlers/toggle_orden_estado.php
header('Content-Type: application/json; charset=utf-8');
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['usuario_id'])) { echo json_encode(['ok'=>false,'error'=>'No autenticado']); exit; }
require_once __DIR__ . '/../includes/functions.php';
try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception('Método no permitido');
    $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $id = isset($input['id']) ? (int)$input['id'] : 0;
    $nuevo = isset($input['estatus']) ? trim($input['estatus']) : 'recibida';
    if (!$id) throw new Exception('ID inválido');

    // Some installations may not have an `updated_at` column. Detect and update accordingly.
    $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'ordenes_compra' AND column_name = 'updated_at'");
    $stmtCheck->execute();
    $hasUpdatedAt = (bool)$stmtCheck->fetchColumn();
    if ($hasUpdatedAt) {
        $stmt = $pdo->prepare('UPDATE ordenes_compra SET estatus = :e, updated_at = NOW() WHERE id = :id');
        $ok = $stmt->execute([':e' => $nuevo, ':id' => $id]);
    } else {
        $stmt = $pdo->prepare('UPDATE ordenes_compra SET estatus = :e WHERE id = :id');
        $ok = $stmt->execute([':e' => $nuevo, ':id' => $id]);
    }
    if (!$ok) throw new Exception('No se pudo actualizar la orden');
    echo json_encode(['ok' => true, 'id' => $id, 'estatus' => $nuevo]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}

?>
