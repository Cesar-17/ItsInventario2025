<?php
// includes/get_activos.php
header('Content-Type: application/json; charset=utf-8');
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['ok' => false, 'error' => 'No autenticado']);
    exit;
}
require_once __DIR__ . '/db.php';

try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'activos_fijos'");
    if ($stmt->rowCount() == 0) {
        echo json_encode(['ok' => true, 'activos' => []]);
        exit;
    }

    // Mostrar TODOS los movimientos (entradas y salidas) para el módulo Activo Fijo
    $stmt = $pdo->query("
        SELECT * FROM activos_fijos 
        ORDER BY id DESC
    ");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['ok' => true, 'activos' => $rows]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
?>