<?php
// handlers/get_siguiente_consecutivo.php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
header('Content-Type: application/json; charset=utf-8');

$base = trim($_GET['base'] ?? '');
if ($base === '') {
    echo json_encode(['ok' => false, 'error' => 'Parámetro "base" vacío']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT MAX(CAST(num_inventario_consecutivo AS UNSIGNED)) AS ultimo
        FROM activos_fijos
        WHERE num_inventario_base = ?
    ");
    $stmt->execute([$base]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $siguiente = (int)($row['ultimo'] ?? 0) + 1;

    echo json_encode([
        'ok'          => true,
        'consecutivo' => str_pad($siguiente, 4, '0', STR_PAD_LEFT)
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}