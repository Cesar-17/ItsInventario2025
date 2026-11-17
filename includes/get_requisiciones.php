<?php
// includes/get_requisiciones.php
header('Content-Type: application/json; charset=utf-8');
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['ok' => false, 'error' => 'No autenticado']);
    exit;
}
require_once __DIR__ . '/db.php';

$rol = $_SESSION['usuario_rol'] ?? 'usuario';

try {
    // Verificar si existe la columna tipo_compra
    $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'requisiciones' AND column_name = 'tipo_compra'");
    $stmtCheck->execute();
    $tieneTipoCompra = (bool)$stmtCheck->fetchColumn();

    $sql = "SELECT * FROM requisiciones";
    
    // Si es Activo Fijo, solo ve Partida 5000 o Licitación
    if ($rol === 'activo_fijo' && $tieneTipoCompra) {
        $sql .= " WHERE tipo_compra IN ('partida_5000', 'licitacion') ";
    }
    
    $sql .= " ORDER BY id DESC";
    
    $stmt = $pdo->query($sql);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['ok' => true, 'requisiciones' => $rows]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
?>