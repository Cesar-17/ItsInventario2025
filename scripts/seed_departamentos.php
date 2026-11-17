<?php
// scripts/seed_departamentos.php
// Ejecutar en el navegador para poblar la tabla `departamentos` con valores por defecto (solo en desarrollo).
require_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json; charset=utf-8');

$seed = [
    ['nombre' => 'Compras', 'area' => 'Administración'],
    ['nombre' => 'Almacén', 'area' => 'Operaciones'],
    ['nombre' => 'Recursos Humanos', 'area' => 'Administración'],
    ['nombre' => 'TI', 'area' => 'Sistemas'],
];

try {
    $pdo->beginTransaction();
    $stmt = $pdo->prepare('INSERT INTO departamentos (nombre, area, activo) VALUES (:nombre, :area, 1) ON DUPLICATE KEY UPDATE nombre = VALUES(nombre)');
    foreach ($seed as $r) {
        $stmt->execute([':nombre' => $r['nombre'], ':area' => $r['area']]);
    }
    $pdo->commit();
    echo json_encode(['ok' => true, 'inserted' => count($seed)]);
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
