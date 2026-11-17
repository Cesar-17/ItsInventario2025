<?php
// handlers/cambiar_estatus_vale.php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json');

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$tipo = isset($_POST['tipo']) ? $_POST['tipo'] : '';
$estatus = isset($_POST['estatus']) ? $_POST['estatus'] : '';

if (!$id || !$tipo || !$estatus) {
    echo json_encode(['ok' => false, 'error' => 'Datos incompletos']);
    exit;
}

$estatusPermitidos = ['pendiente', 'autorizado', 'entregado', 'cancelado'];
if (!in_array($estatus, $estatusPermitidos)) {
    echo json_encode(['ok' => false, 'error' => 'Estatus no válido']);
    exit;
}

try {
    if ($tipo === 'herramienta') {
        $stmt = $pdo->prepare("UPDATE vales_herramienta SET estatus = ? WHERE id = ?");
    } elseif ($tipo === 'material') {
        $stmt = $pdo->prepare("UPDATE vales_material SET estatus = ? WHERE id = ?");
    } else {
        echo json_encode(['ok' => false, 'error' => 'Tipo no válido']);
        exit;
    }
    
    $stmt->execute([$estatus, $id]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['ok' => true, 'mensaje' => 'Estatus actualizado']);
    } else {
        echo json_encode(['ok' => false, 'error' => 'No se encontró el vale o no hubo cambios']);
    }
} catch (Exception $e) {
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
?>