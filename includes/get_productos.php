<?php
// includes/get_productos.php
header('Content-Type: application/json; charset=utf-8');
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['usuario_id'])) { echo json_encode(['ok'=>false,'error'=>'No autenticado']); exit; }
require_once __DIR__ . '/functions.php';

try {
    $stmt = $pdo->query("SELECT p.id, p.codigo_barras, p.descripcion, p.marca, p.modelo, p.costo_promedio, p.stock_actual, p.stock_minimo, p.activo, p.categoria_id, c.nombre AS categoria, p.unidad_medida_id AS unidad_medida_id, u.nombre AS unidad
        FROM productos p
        LEFT JOIN categorias_inventario c ON c.id = p.categoria_id
        LEFT JOIN unidades_medida u ON u.id = p.unidad_medida_id
        ORDER BY p.id DESC");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['ok' => true, 'productos' => $rows]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}

?>
