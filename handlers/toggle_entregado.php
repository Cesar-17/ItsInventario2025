<?php
// handlers/toggle_entregado.php
header('Content-Type: application/json');

session_start();
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['ok' => false, 'error' => 'No autenticado']);
    exit;
}

require_once __DIR__ . '/../includes/db.php';

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$entregado = isset($_POST['entregado']) ? (int)$_POST['entregado'] : 0;

if (!$id) {
    echo json_encode(['ok' => false, 'error' => 'ID inválido']);
    exit;
}

try {
    $pdo->beginTransaction();
    
    // Actualizar compra
    $stmt = $pdo->prepare("UPDATE compras SET entregado = ?, fecha_entrega = ? WHERE id = ?");
    $fecha = $entregado ? date('Y-m-d') : null;
    $stmt->execute([$entregado, $fecha, $id]);
    
    // Si está entregado, crear/actualizar area_solicitante
    if ($entregado) {
        $stmt = $pdo->prepare("
            SELECT c.*, oc.no_orden_compra, f.numero_factura as no_factura, 
                   oc.area_solicitante, oc.descripcion_general as descripcion
            FROM compras c
            LEFT JOIN ordenes_compra oc ON oc.id = c.orden_compra_id
            LEFT JOIN facturas f ON f.id = c.factura_id
            WHERE c.id = ?
        ");
        $stmt->execute([$id]);
        $compra = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($compra) {
            // Asegurar que la descripción no sea NULL
            $descripcion = $compra['descripcion'] ?? '';
            if (empty($descripcion)) {
                $descripcion = 'Compra sin descripción - Orden: ' . ($compra['no_orden_compra'] ?? 'N/A');
            }
            
            $stmt = $pdo->prepare("SELECT id FROM area_solicitante WHERE compra_id = ?");
            $stmt->execute([$id]);
            if (!$stmt->fetch()) {
                $stmt = $pdo->prepare("
                    INSERT INTO area_solicitante 
                    (compra_id, orden_compra_id, factura_id, no_orden_compra, no_factura, 
                     descripcion, entregado, fecha_entrega, pagado, area_solicitante)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $id,
                    $compra['orden_compra_id'],
                    $compra['factura_id'],
                    $compra['no_orden_compra'] ?? 'N/A',
                    $compra['no_factura'] ?? 'N/A',
                    $descripcion,
                    $entregado,
                    $fecha,
                    0,
                    $compra['area_solicitante'] ?? 'No especificado'
                ]);
            } else {
                $stmt = $pdo->prepare("UPDATE area_solicitante SET entregado = ?, fecha_entrega = ?, descripcion = ? WHERE compra_id = ?");
                $stmt->execute([$entregado, $fecha, $descripcion, $id]);
            }
        }
    }
    
    $pdo->commit();
    echo json_encode(['ok' => true]);
    
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
?>