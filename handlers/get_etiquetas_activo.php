<?php
// handlers/get_etiquetas_activo.php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
header('Content-Type: application/json; charset=utf-8');

$activo_id = (int)($_GET['activo_id'] ?? 0);
if (!$activo_id) {
    echo json_encode(['ok'=>false,'error'=>'Falta activo_id']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT id, num_inventario_completo, consecutivo, anio, 
               usuario_etiqueta, area_etiqueta, licitacion
        FROM etiquetas_activos
        WHERE activo_id = ?
        ORDER BY consecutivo ASC
    ");
    $stmt->execute([$activo_id]);
    $etiquetas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['ok'=>true,'etiquetas'=>$etiquetas]);
} catch (Exception $e) {
    echo json_encode(['ok'=>false,'error'=>$e->getMessage()]);
}