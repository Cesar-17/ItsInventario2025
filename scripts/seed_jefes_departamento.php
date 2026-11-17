<?php
// scripts/seed_jefes_departamento.php
// Script para poblar jefes_de_departamento para los departamentos existentes.
// Ejecutar desde navegador o CLI en entorno de desarrollo.

require_once __DIR__ . '/../includes/db.php';

$mapping = [
    'Almacén' => ['nombre' => 'Jefe Almacén', 'correo' => 'jefe.almacen@example.com'],
    'Compras' => ['nombre' => 'Jefe Compras', 'correo' => 'jefe.compras@example.com'],
    'Recursos Humanos' => ['nombre' => 'Jefe RH', 'correo' => 'jefe.rh@example.com'],
    'TI' => ['nombre' => 'Jefe TI', 'correo' => 'jefe.ti@example.com'],
];

$results = [];
foreach ($mapping as $deptName => $j) {
    // buscar departamento
    $stmt = $pdo->prepare('SELECT id FROM departamentos WHERE nombre = :n LIMIT 1');
    $stmt->execute([':n' => $deptName]);
    $deptId = $stmt->fetchColumn();
    if (!$deptId) {
        $results[] = "No existe departamento: $deptName";
        continue;
    }
  
    // comprobar si ya hay un jefe activo para este departamento
    $check = $pdo->prepare('SELECT id FROM jefes_departamento WHERE departamento_id = :d LIMIT 1');
    $check->execute([':d' => $deptId]);
    $existing = $check->fetchColumn();
    if ($existing) {
        // actualizar
        $up = $pdo->prepare('UPDATE jefes_departamento SET nombre = :nombre, correo = :correo, activo = 1 WHERE id = :id');
        $up->execute([':nombre' => $j['nombre'], ':correo' => $j['correo'], ':id' => $existing]);
        $results[] = "Actualizado jefe para $deptName (id=$existing)";
    } else {
        // insertar
        $ins = $pdo->prepare('INSERT INTO jefes_departamento (nombre, correo, departamento_id, activo) VALUES (:nombre, :correo, :d, 1)');
        $ins->execute([':nombre' => $j['nombre'], ':correo' => $j['correo'], ':d' => $deptId]);
        $newId = $pdo->lastInsertId();
        $results[] = "Insertado jefe para $deptName (id=$newId)";
    }
}

header('Content-Type: text/plain; charset=utf-8');
echo "Seed jefes_departamento\n";
foreach ($results as $r) echo $r . "\n";

echo "\nHecho. Revisa la tabla jefes_departamento para confirmar.";

?>
