<?php
// includes/get_activos_movimientos.php
header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['usuario_id'])) { echo json_encode(['ok' => false, 'error' => 'No autenticado']); exit; }

require_once __DIR__ . '/db.php';
$rol = $_SESSION['usuario_rol'] ?? 'usuario';

try {
    $sql = "SELECT * FROM activos_fijos WHERE 1=1";
    if ($rol === 'activo_fijo') {
        $sql .= " AND tipo_compra IN ('partida_5000', 'licitacion')";
    }
    $sql .= " ORDER BY id DESC";

    $stmt = $pdo->query($sql);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['ok' => true, 'activos' => $rows]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
?>