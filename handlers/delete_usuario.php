<?php
header('Content-Type: application/json; charset=utf-8');
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'admin') {
    echo json_encode(['ok' => false, 'error' => 'Acceso denegado']);
    exit;
}
require_once __DIR__ . '/../includes/db.php';

$id = intval($_POST['id'] ?? 0);
if (!$id) { echo json_encode(['ok' => false, 'error' => 'ID inválido']); exit; }

// No puede eliminarse a sí mismo
if ($id == $_SESSION['usuario_id']) {
    echo json_encode(['ok' => false, 'error' => 'No puedes eliminar tu propia cuenta']);
    exit;
}

// No puede eliminar el último admin
$stmt = $pdo->prepare("SELECT rol FROM usuarios WHERE id = ?");
$stmt->execute([$id]);
$u = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$u) { echo json_encode(['ok' => false, 'error' => 'Usuario no encontrado']); exit; }

if ($u['rol'] === 'admin') {
    $adminCount = $pdo->query("SELECT COUNT(*) FROM usuarios WHERE rol='admin'")->fetchColumn();
    if ($adminCount <= 1) {
        echo json_encode(['ok' => false, 'error' => 'No puedes eliminar el único administrador']);
        exit;
    }
}

$pdo->prepare("DELETE FROM usuarios WHERE id = ?")->execute([$id]);
echo json_encode(['ok' => true]);
