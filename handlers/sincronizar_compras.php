<?php
// handlers/sincronizar_compras.php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json');

try {
    $pdo->beginTransaction();

    // Obtener todas las órdenes de compra que tienen factura asociada
    $stmt = $pdo->query("
        SELECT oc.id as orden_id, f.id as factura_id, oc.no_orden_compra, f.numero_factura, 
               oc.area_solicitante, oc.descripcion_general as descripcion
        FROM ordenes_compra oc
        INNER JOIN facturas f ON f.orden_compra_id = oc.id
        LEFT JOIN compras c ON c.orden_compra_id = oc.id AND c.factura_id = f.id
        WHERE c.id IS NULL
    ");
    
    $ordenesSinCompra = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $insertados = 0;

    foreach ($ordenesSinCompra as $item) {
        $stmt = $pdo->prepare("
            INSERT INTO compras (orden_compra_id, factura_id, entregado, fecha_entrega)
            VALUES (?, ?, 0, NULL)
        ");
        $stmt->execute([$item['orden_id'], $item['factura_id']]);
        $insertados++;
    }

    $pdo->commit();

    echo json_encode([
        'ok' => true,
        'mensaje' => "Se sincronizaron $insertados compras correctamente.",
        'insertados' => $insertados
    ]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
?>