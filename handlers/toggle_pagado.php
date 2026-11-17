<?php
// handlers/toggle_pagado.php
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
$pagado = isset($_POST['pagado']) ? (int)$_POST['pagado'] : 0;

if (!$compra_id) {
    echo json_encode(['ok' => false, 'error' => 'ID de compra inválido']);
    exit;
}

try {
    $pdo->beginTransaction();
    
    // Buscar si ya existe el registro de finanzas para esta compra
    $stmt = $pdo->prepare("SELECT id FROM finanzas WHERE compra_id = ?");
    $stmt->execute([$compra_id]);
    $finanza = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($finanza) {
        // Si ya existe, actualizar
        $stmt = $pdo->prepare("UPDATE finanzas SET pagado = ? WHERE compra_id = ?");
        $stmt->execute([$pagado, $compra_id]);
    } else {
        // Si no existe, insertar con fecha_pago
        $fecha_pago = ($pagado == 1) ? date('Y-m-d H:i:s') : null;
        $stmt = $pdo->prepare("INSERT INTO finanzas (compra_id, pagado, fecha_pago) VALUES (?, ?, ?)");
        $stmt->execute([$compra_id, $pagado, $fecha_pago]);
    }
    
    // Actualizar también la tabla area_solicitante si existe
    try {
        $stmt = $pdo->prepare("SELECT id FROM area_solicitante WHERE compra_id = ?");
        $stmt->execute([$compra_id]);
        if ($stmt->fetch()) {
            $fecha = ($pagado == 1) ? date('Y-m-d') : null;
            $stmt = $pdo->prepare("UPDATE area_solicitante SET pagado = ?, fecha_pago = ? WHERE compra_id = ?");
            $stmt->execute([$pagado, $fecha, $compra_id]);
        }
    } catch (Exception $e) {
        // Ignorar si la tabla no existe o falla, no es crítico
    }
    
    $pdo->commit();
    echo json_encode(['ok' => true, 'mensaje' => 'Estado de pago actualizado']);
    
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Error en toggle_pagado: ' . $e->getMessage());
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
?>