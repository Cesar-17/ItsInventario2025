<?php
// handlers/cambiar_estatus_orden.php
header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['ok' => false, 'error' => 'No autenticado']);
    exit;
}

require_once __DIR__ . '/../includes/db.php';

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$estatus = isset($_POST['estatus']) ? trim($_POST['estatus']) : '';

if (!$id || !$estatus) {
    echo json_encode(['ok' => false, 'error' => 'Datos incompletos']);
    exit;
}

$estatusPermitidos = ['pendiente', 'autorizada', 'entregada', 'cancelada'];
if (!in_array($estatus, $estatusPermitidos)) {
    echo json_encode(['ok' => false, 'error' => 'Estatus no válido']);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE ordenes_compra SET estatus = ? WHERE id = ?");
    $stmt->execute([$estatus, $id]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['ok' => true, 'mensaje' => 'Estatus actualizado']);
    } else {
        echo json_encode(['ok' => false, 'error' => 'No se encontró la orden o no hubo cambios']);
    }
} catch (Exception $e) {
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
?>