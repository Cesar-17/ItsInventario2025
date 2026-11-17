<?php
// handlers/guardar_etiqueta.php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Método no permitido']);
    exit;
}

$activo_id  = (int)($_POST['activo_id'] ?? 0);
$base       = trim($_POST['base'] ?? '');
$anio       = trim($_POST['anio'] ?? date('Y'));
$licitacion = trim($_POST['licitacion'] ?? '');
$usuarios   = json_decode($_POST['usuarios'] ?? '[]', true);

if (!$activo_id || !$base || empty($usuarios)) {
    echo json_encode(['ok'=>false,'error'=>'Faltan datos o usuarios no asignados']);
    exit;
}

try {
    // 1. Obtener el último consecutivo GLOBAL para esta base
    $stmt = $pdo->prepare("SELECT MAX(consecutivo) FROM etiquetas_activos WHERE num_inventario_base = ?");
    $stmt->execute([$base]);
    $ultimoGlobal = (int)$stmt->fetchColumn();

    $pdo->beginTransaction();

    $stmt = $pdo->prepare("
        INSERT INTO etiquetas_activos
        (activo_id, num_inventario_completo, num_inventario_base, consecutivo, anio,
         usuario_etiqueta, area_etiqueta, licitacion)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $etiquetasGeneradas = [];
    $consecutivoActual = $ultimoGlobal;

    // 2. Iterar por cada fila de usuario
    foreach ($usuarios as $u) {
        $cant = (int)($u['cantidad'] ?? 0);
        $userName = trim($u['usuario'] ?? '');
        $userArea = trim($u['area'] ?? '');

        for ($i = 0; $i < $cant; $i++) {
            $consecutivoActual++;
            $consecutivoStr = str_pad($consecutivoActual, 4, '0', STR_PAD_LEFT);
            $completo = $base . ' - ' . $consecutivoStr . ' / ' . $anio;

            $stmt->execute([
                $activo_id,
                $completo,
                $base,
                $consecutivoActual,
                $anio,
                $userName,
                $userArea,
                $licitacion
            ]);

            $etiquetasGeneradas[] = [
                'id' => $pdo->lastInsertId(),
                'completo' => $completo,
                'consecutivo' => $consecutivoStr,
                'anio' => $anio,
                'usuario_etiqueta' => $userName,
                'area_etiqueta' => $userArea,
                'licitacion' => $licitacion
            ];
        }
    }

    // 3. Actualizar el activo principal con los datos de la primera etiqueta
    $primera = $etiquetasGeneradas[0];
    $stmt = $pdo->prepare("
        UPDATE activos_fijos SET
            num_inventario_base = ?,
            num_inventario_completo = ?,
            num_inventario_anio = ?,
            usuario_etiqueta = ?,
            area_etiqueta = ?,
            licitacion = ?,
            etiquetas_generadas = 1
        WHERE id = ?
    ");
    $stmt->execute([
        $base, $primera['completo'], $anio, $primera['usuario_etiqueta'], 
        $primera['area_etiqueta'], $licitacion, $activo_id
    ]);

    $pdo->commit();

    echo json_encode([
        'ok' => true,
        'etiquetas' => $etiquetasGeneradas
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['ok'=>false,'error'=>$e->getMessage()]);
}