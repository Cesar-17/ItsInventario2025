<?php
// includes/get_jefe_por_departamento.php
header('Content-Type: application/json; charset=utf-8');
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['usuario_id'])) { echo json_encode(['ok'=>false,'error'=>'No autenticado']); exit; }
require_once __DIR__ . '/functions.php';

$dept = isset($_GET['departamento_id']) ? (int)$_GET['departamento_id'] : 0;
if (!$dept) {
    echo json_encode(['ok' => false, 'error' => 'departamento_id requerido']);
    exit;
}

$jefe = getJefeByDepartamento($pdo, $dept);
if (!$jefe) {
    echo json_encode(['ok' => true, 'jefe' => null]);
} else {
    echo json_encode(['ok' => true, 'jefe' => $jefe]);
}

?>
