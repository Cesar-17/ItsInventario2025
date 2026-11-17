<?php
// includes/get_producto_por_barcode.php
header('Content-Type: application/json; charset=utf-8');
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['usuario_id'])) { echo json_encode(['ok'=>false,'error'=>'No autenticado']); exit; }
require_once __DIR__ . '/functions.php';

$barcode = trim($_GET['barcode'] ?? '');
if ($barcode === '') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'barcode requerido']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT p.id, p.codigo_barras, p.descripcion, p.marca, p.modelo, p.costo_promedio, p.stock_actual, p.stock_minimo, p.activo, p.categoria_id, c.nombre AS categoria, u.nombre AS unidad
        FROM productos p
        LEFT JOIN categorias_inventario c ON c.id = p.categoria_id
        LEFT JOIN unidades_medida u ON u.id = p.unidad_medida_id
        WHERE p.codigo_barras = :cb LIMIT 1");
    $stmt->execute([':cb' => $barcode]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        echo json_encode(['ok' => true, 'producto' => null]);
    } else {
        echo json_encode(['ok' => true, 'producto' => $row]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}

?>
