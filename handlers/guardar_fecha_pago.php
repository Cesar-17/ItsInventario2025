<?php
// handlers/guardar_fecha_pago.php
header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['ok' => false, 'error' => 'No autenticado']);
    exit;
}

require_once __DIR__ . '/../includes/db.php';

$compra_id = isset($_POST['compra_id']) ? (int)$_POST['compra_id'] : 0;
$fecha_pago = isset($_POST['fecha_pago']) ? trim($_POST['fecha_pago']) : '';

if (!$compra_id || !$fecha_pago) {
    echo json_encode(['ok' => false, 'error' => 'Datos incompletos']);
    exit;
}

try {
    $pdo->beginTransaction();
    
    // Verificar si existe
    $stmt = $pdo->prepare("SELECT id FROM finanzas WHERE compra_id = ?");
    $stmt->execute([$compra_id]);
    $finanza = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($finanza) {
        // Actualizar fecha
        $stmt = $pdo->prepare("UPDATE finanzas SET fecha_pago = ? WHERE compra_id = ?");
        $stmt->execute([$fecha_pago, $compra_id]);
    } else {
        // Crear registro (asumiendo pagado = 1 si ponen fecha)
        $stmt = $pdo->prepare("INSERT INTO finanzas (compra_id, pagado, fecha_pago) VALUES (?, 1, ?)");
        $stmt->execute([$compra_id, $fecha_pago]);
    }
    
    // Actualizar area_solicitante si existe
    try {
        $stmt = $pdo->prepare("UPDATE area_solicitante SET fecha_pago = ? WHERE compra_id = ?");
        $stmt->execute([$fecha_pago, $compra_id]);
    } catch (Exception $e) { /* Ignorar */ }
    
    $pdo->commit();
    echo json_encode(['ok' => true, 'mensaje' => 'Fecha guardada']);
    
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Error en guardar_fecha_pago: ' . $e->getMessage());
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
?>