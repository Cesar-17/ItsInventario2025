<?php
// handlers/save_requisicion.php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Método no permitido');
}

$folio = trim($_POST['folio'] ?? '');
$fecha = trim($_POST['fecha'] ?? '');
$jefe_solicitante = trim($_POST['jefe_solicitante'] ?? '');
$area_solicitante = trim($_POST['area_solicitante'] ?? '');
$fecha_solicitud = trim($_POST['fecha_solicitud'] ?? date('Y-m-d')); // NUEVO CAMPO RECIBIDO
$programa_operativo_anual = isset($_POST['programa_operativo_anual']) ? 1 : 0;
$clave_presupuestal = trim($_POST['clave_presupuestal'] ?? '');
$partida = trim($_POST['partida'] ?? '');
$cantidad = floatval($_POST['cantidad'] ?? 0);
$unidad = trim($_POST['unidad'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');
$costo_estimado_total = floatval($_POST['costo_estimado_total'] ?? 0);
$tipo_compra = isset($_POST['tipo_compra']) && in_array($_POST['tipo_compra'], ['normal', 'partida_5000', 'licitacion']) ? $_POST['tipo_compra'] : 'normal';

$errors = [];
if (!$folio) $errors[] = 'El folio es requerido';
if (!$fecha) $errors[] = 'La fecha es requerida';
if (!$jefe_solicitante) $errors[] = 'El jefe solicitante es requerido';
if (!$area_solicitante) $errors[] = 'El área solicitante es requerida';
if (!$fecha_solicitud) $errors[] = 'La fecha de solicitud es requerida'; // NUEVA VALIDACIÓN
if (!$clave_presupuestal) $errors[] = 'La clave presupuestal es requerida';
if (!$partida) $errors[] = 'La partida es requerida';
if ($cantidad <= 0) $errors[] = 'La cantidad debe ser mayor a 0';
if (!$unidad) $errors[] = 'La unidad de medida es requerida';
if (!$descripcion) $errors[] = 'La descripción es requerida';
if ($costo_estimado_total <= 0) $errors[] = 'El costo estimado debe ser mayor a 0';

if (!empty($errors)) {
    http_response_code(400);
    echo implode('<br>', $errors);
    exit;
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO requisiciones 
        (folio, fecha, jefe_solicitante, area_solicitante, fecha_solicitud, programa_operativo_anual, 
         clave_presupuestal, partida, cantidad, unidad, descripcion, costo_estimado_total, tipo_compra)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $folio, $fecha, $jefe_solicitante, $area_solicitante, $fecha_solicitud, $programa_operativo_anual,
        $clave_presupuestal, $partida, $cantidad, $unidad, $descripcion, $costo_estimado_total, $tipo_compra
    ]);

    header('Location: ' . BASE_PATH . 'orden_compra/requisiciones.php?ok=1');
    exit;

} catch (Exception $e) {
    if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
        http_response_code(400);
        echo 'El folio ya existe. Por favor usa uno diferente.';
    } else {
        http_response_code(500);
        echo 'Error al guardar: ' . $e->getMessage();
    }
    exit;
}
?>