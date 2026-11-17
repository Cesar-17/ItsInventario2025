<?php
// includes/get_facturas.php
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
    $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'facturas' AND column_name = 'tipo_compra'");
    $stmtCheck->execute();
    $tieneTipoCompra = (bool)$stmtCheck->fetchColumn();

    $sql = "
        SELECT f.*, 
               oc.no_orden_compra, 
               oc.no_requisicion,
               CASE 
                   WHEN f.numero_factura IS NOT NULL AND f.numero_factura != '' THEN 'emitida'
                   ELSE 'pendiente'
               END as estatus
        FROM facturas f
        LEFT JOIN ordenes_compra oc ON oc.id = f.orden_compra_id
    ";
    
    // Si es Activo Fijo y existe la columna, filtrar
    if ($rol === 'activo_fijo' && $tieneTipoCompra) {
        $sql .= " WHERE f.tipo_compra IN ('partida_5000', 'licitacion') ";
    }
    
    $sql .= " ORDER BY f.id DESC";
    
    $stmt = $pdo->query($sql);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['ok' => true, 'facturas' => $rows, 'debug' => ['rol' => $rol, 'tieneTipoCompra' => $tieneTipoCompra, 'total' => count($rows)]]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
?>