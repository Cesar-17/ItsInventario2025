<?php
// handlers/toggle_producto.php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['usuario_id'])) { header('Content-Type: application/json'); echo json_encode(['ok'=>false,'error'=>'No autenticado']); exit; }
require_once __DIR__ . '/../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Método no permitido');
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$action = $_POST['action'] ?? '';
if (!$id || !in_array($action, ['deactivate','activate'])) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Parámetros inválidos']);
    exit;
}

try {
    $activo = ($action === 'activate') ? 1 : 0;
    $stmt = $pdo->prepare('UPDATE productos SET activo = :a WHERE id = :id');
    $stmt->execute([':a' => $activo, ':id' => $id]);
    echo json_encode(['ok' => true, 'id' => $id, 'activo' => $activo]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}

?>
