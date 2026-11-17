<?php
// includes/check_duplicate.php
// Simple endpoint to check for common duplicates (folio, producto descripción)
header('Content-Type: application/json; charset=utf-8');
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['usuario_id'])) { echo json_encode(['ok'=>false,'error'=>'No autenticado']); exit; }
require_once __DIR__ . '/db.php';

$kind = $_GET['kind'] ?? '';
$value = $_GET['value'] ?? null;
if ($value === null) {
    echo json_encode(['ok' => false, 'error' => 'missing value']);
    exit;
}

$allowed = ['orden_folio', 'salida_folio', 'producto_desc', 'orden_factura'];
    if ($kind === 'orden_factura') {
        // check factura/remision in ordenes_compra
        $stmt = $pdo->prepare('SELECT COUNT(*) AS c FROM ordenes_compra WHERE factura_remision = :v');
        $stmt->execute([':v' => $value]);
        $row = $stmt->fetch();
        $exists = ($row && $row['c'] > 0);
        echo json_encode(['ok' => true, 'exists' => (bool)$exists]);
        exit;
    }
if (!in_array($kind, $allowed, true)) {
    echo json_encode(['ok' => false, 'error' => 'invalid kind']);
    exit;
}

try {
    if ($kind === 'orden_folio') {
        $stmt = $pdo->prepare('SELECT COUNT(*) AS c FROM ordenes_compra WHERE folio = :v');
        $stmt->execute([':v' => $value]);
        $row = $stmt->fetch();
        $exists = ($row && $row['c'] > 0);
        echo json_encode(['ok' => true, 'exists' => (bool)$exists]);
        exit;
    }

    if ($kind === 'salida_folio') {
        $stmt = $pdo->prepare('SELECT COUNT(*) AS c FROM salidas_inventario WHERE folio = :v');
        $stmt->execute([':v' => $value]);
        $row = $stmt->fetch();
        $exists = ($row && $row['c'] > 0);
        echo json_encode(['ok' => true, 'exists' => (bool)$exists]);
        exit;
    }

    if ($kind === 'producto_desc') {
        // simple case-insensitive match on descripción
        $stmt = $pdo->prepare('SELECT COUNT(*) AS c FROM productos WHERE LOWER(descripcion) = LOWER(:v)');
        $stmt->execute([':v' => $value]);
        $row = $stmt->fetch();
        $exists = ($row && $row['c'] > 0);
        echo json_encode(['ok' => true, 'exists' => (bool)$exists]);
        exit;
    }
} catch (Exception $e) {
    if (getenv('APP_ENV') === 'development') {
        echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
    } else {
        echo json_encode(['ok' => false, 'error' => 'server error']);
    }
    exit;
}

