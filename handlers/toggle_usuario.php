<?php
header('Content-Type: application/json; charset=utf-8');
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'admin') {
    echo json_encode(['ok' => false, 'error' => 'Acceso denegado']);
    exit;
}
require_once __DIR__ . '/../includes/db.php';

$id     = intval($_POST['id'] ?? 0);
$action = $_POST['action'] ?? '';

if (!$id || !in_array($action, ['activate','deactivate'])) {
    echo json_encode(['ok' => false, 'error' => 'Parámetros inválidos']);
    exit;
}

// No puede desactivarse a sí mismo
if ($action === 'deactivate' && $id == $_SESSION['usuario_id']) {
    echo json_encode(['ok' => false, 'error' => 'No puedes desactivar tu propia cuenta']);
    exit;
}

// No puede desactivar el último admin activo
if ($action === 'deactivate') {
    $stmt = $pdo->prepare("SELECT rol FROM usuarios WHERE id = ?");
    $stmt->execute([$id]);
    $u = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($u && $u['rol'] === 'admin') {
        $adminCount = $pdo->query("SELECT COUNT(*) FROM usuarios WHERE rol='admin' AND activo=1")->fetchColumn();
        if ($adminCount <= 1) {
            echo json_encode(['ok' => false, 'error' => 'No puedes desactivar el único administrador activo']);
            exit;
        }
    }
}

$activo = $action === 'activate' ? 1 : 0;
$stmt = $pdo->prepare("UPDATE usuarios SET activo = ? WHERE id = ?");
$stmt->execute([$activo, $id]);

echo json_encode(['ok' => true, 'activo' => $activo]);
