<?php
// includes/get_ordenes.php
header('Content-Type: application/json; charset=utf-8');
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['usuario_id'])) { echo json_encode(['ok'=>false,'error'=>'No autenticado']); exit; }
require_once __DIR__ . '/functions.php';
try {
    // adaptamos la consulta si la columna jefe_id existe (compatibilidad con esquemas antiguos)
    $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'ordenes_compra' AND column_name = 'jefe_id'");
    $stmtCheck->execute();
    $hasJefe = (bool)$stmtCheck->fetchColumn();

    if ($hasJefe) {
        $sql = "SELECT o.id, o.folio, o.proveedor, o.fecha, o.factura_remision, o.proyecto, o.departamento_id, d.nombre AS departamento, o.jefe_id, j.nombre AS jefe_nombre, o.descripcion_general, o.estatus, o.total, o.created_at
            FROM ordenes_compra o
            LEFT JOIN departamentos d ON d.id = o.departamento_id
            LEFT JOIN jefes_departamento j ON j.id = o.jefe_id
            ORDER BY o.created_at DESC";
    } else {
        // esquema antiguo sin jefe_id
        $sql = "SELECT o.id, o.folio, o.proveedor, o.fecha, o.factura_remision, o.proyecto, o.departamento_id, d.nombre AS departamento, NULL AS jefe_id, NULL AS jefe_nombre, o.descripcion_general, o.estatus, o.total, o.created_at
            FROM ordenes_compra o
            LEFT JOIN departamentos d ON d.id = o.departamento_id
            ORDER BY o.created_at DESC";
    }
    $stmt = $pdo->query($sql);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as &$r) {
        $stmt2 = $pdo->prepare('SELECT doc.id, doc.descripcion, doc.cantidad, doc.precio_unitario, doc.importe, doc.producto_id, p.codigo_barras, p.descripcion AS producto_desc, u.nombre AS unidad FROM detalles_orden_compra doc LEFT JOIN productos p ON p.id = doc.producto_id LEFT JOIN unidades_medida u ON u.id = doc.unidad_medida_id WHERE doc.orden_compra_id = :oid');
        $stmt2->execute([':oid' => $r['id']]);
        $r['detalles'] = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    }
    echo json_encode(['ok' => true, 'ordenes' => $rows]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}

?>
