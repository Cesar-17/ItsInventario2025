<?php
// handlers/eliminar_factura.php
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

if (!$id) {
    echo json_encode(['ok' => false, 'error' => 'ID inválido']);
    exit;
}

try {
    $pdo->beginTransaction();

    // Eliminar detalles
    $stmt = $pdo->prepare("DELETE FROM detalles_factura WHERE factura_id = ?");
    $stmt->execute([$id]);

    // Eliminar factura
    $stmt = $pdo->prepare("DELETE FROM facturas WHERE id = ?");
    $stmt->execute([$id]);

    $pdo->commit();

    echo json_encode(['ok' => true, 'mensaje' => 'Factura eliminada correctamente']);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
?>