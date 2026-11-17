<?php
// handlers/eliminar_vale.php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json');

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$tipo = isset($_POST['tipo']) ? $_POST['tipo'] : '';

if (!$id || !$tipo) {
    echo json_encode(['ok' => false, 'error' => 'Datos incompletos']);
    exit;
}

try {
    $pdo->beginTransaction();
    
    if ($tipo === 'herramienta') {
        // Eliminar detalles
        $stmt = $pdo->prepare("DELETE FROM detalles_vale_herramienta WHERE vale_herramienta_id = ?");
        $stmt->execute([$id]);
        
        // Eliminar vale
        $stmt = $pdo->prepare("DELETE FROM vales_herramienta WHERE id = ?");
        $stmt->execute([$id]);
        
    } elseif ($tipo === 'material') {
        // Eliminar detalles relacionados en detalles_salida
        $stmt = $pdo->prepare("DELETE FROM detalles_salida WHERE salida_id = ?");
        $stmt->execute([$id]);
        
        // Eliminar vale
        $stmt = $pdo->prepare("DELETE FROM vales_material WHERE id = ?");
        $stmt->execute([$id]);
        
    } else {
        echo json_encode(['ok' => false, 'error' => 'Tipo no válido']);
        exit;
    }
    
    $pdo->commit();
    echo json_encode(['ok' => true, 'mensaje' => 'Vale eliminado correctamente']);
    
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
?>