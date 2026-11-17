<?php
// includes/get_salidas.php
header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['usuario_id'])) { echo json_encode(['ok' => false, 'error' => 'No autenticado']); exit; }

require_once __DIR__ . '/db.php';

try {
    $stmt = $pdo->query("SELECT * FROM salidas_inventario ORDER BY id DESC");
    $salidas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($salidas as &$s) {
        // Detalles
        try {
            $stmtDet = $pdo->prepare("SELECT d.*, p.codigo_barras FROM detalles_salida d LEFT JOIN productos p ON p.id = d.producto_id WHERE d.salida_id = ?");
            $stmtDet->execute([$s['id']]);
            $s['detalles'] = $stmtDet->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) { $s['detalles'] = []; }

        // Extraer tipo y jefe de las observaciones
        $obs = $s['observaciones'] ?? '';
        
        $s['tipo_compra_orden'] = 'normal';
        if (stripos($obs, 'licitacion') !== false) $s['tipo_compra_orden'] = 'licitacion';
        elseif (stripos($obs, '5000') !== false || stripos($obs, 'partida') !== false) $s['tipo_compra_orden'] = 'partida_5000';

        // 🔥 EXTRAER EL JEFE 🔥
        if (preg_match('/Jefe:\s*([^|]+)/', $obs, $matches)) {
            $s['jefe_nombre'] = trim($matches[1]);
        } else {
            $s['jefe_nombre'] = 'No registrado';
        }

        // Departamento
        $s['departamento'] = 'Activo Fijo';
    }

    echo json_encode(['ok' => true, 'salidas' => $salidas]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
?>