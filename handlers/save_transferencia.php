<?php
// handlers/save_transferencia.php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Método no permitido');
}

$activo_id = isset($_POST['activo_id']) ? (int)$_POST['activo_id'] : 0;
$fecha = trim($_POST['fecha'] ?? '');
$motivo = trim($_POST['motivo'] ?? '');
$area_destino = trim($_POST['area_destino'] ?? '');
$resguardante_destino = trim($_POST['resguardante_destino'] ?? '');

if (!$activo_id || !$fecha || !$area_destino || !$resguardante_destino) {
    http_response_code(400);
    echo 'Todos los campos son requeridos.';
    exit;
}

try {
    $pdo->beginTransaction();

    // Obtener datos actuales del activo
    $stmt = $pdo->prepare("SELECT area_pertenece, resguardante FROM activos_fijos WHERE id = ? AND disponible = 1 AND tipo = 'entrada'");
    $stmt->execute([$activo_id]);
    $activo = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$activo) {
        throw new Exception('El activo no está disponible para transferir.');
    }

    $area_origen = $activo['area_pertenece'] ?? '';
    $resguardante_origen = $activo['resguardante'] ?? '';

    // Registrar transferencia en historial
    $stmt = $pdo->prepare("
        INSERT INTO transferencias 
        (activo_id, fecha, area_origen, area_destino, resguardante_origen, resguardante_destino, motivo)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$activo_id, $fecha, $area_origen, $area_destino, $resguardante_origen, $resguardante_destino, $motivo]);

    // Actualizar el activo con nueva área y resguardante
    $stmt = $pdo->prepare("UPDATE activos_fijos SET area_pertenece = ?, resguardante = ? WHERE id = ?");
    $stmt->execute([$area_destino, $resguardante_destino, $activo_id]);

    $pdo->commit();

    header('Location: ' . BASE_PATH . 'activo_fijo/index.php?transfer=ok');
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    error_log('Error al transferir: ' . $e->getMessage());
    http_response_code(500);
    echo 'Error al transferir: ' . $e->getMessage();
    exit;
}
?>