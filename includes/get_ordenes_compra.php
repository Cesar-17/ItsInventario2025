<?php
// includes/get_ordenes_compra.php
header('Content-Type: application/json; charset=utf-8');
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['ok' => false, 'error' => 'No autenticado']);
    exit;
}
require_once __DIR__ . '/db.php';

$rol = $_SESSION['usuario_rol'] ?? 'usuario';

try {
    $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'ordenes_compra' AND column_name = 'tipo_compra'");
    $stmtCheck->execute();
    $tieneTipoCompra = (bool)$stmtCheck->fetchColumn();

    $sql = "SELECT oc.* FROM ordenes_compra oc";
    
    if ($rol === 'activo_fijo' && $tieneTipoCompra) {
        $sql .= " WHERE oc.tipo_compra IN ('partida_5000', 'licitacion') ";
    }
    
    $sql .= " ORDER BY oc.created_at DESC";
    
    $stmt = $pdo->query($sql);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['ok' => true, 'ordenes' => $rows]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
?>