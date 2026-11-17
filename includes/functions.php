<?php
// includes/functions.php
require_once __DIR__ . '/db.php';

function getUnidadesMedida(PDO $pdo) {
    $stmt = $pdo->query('SELECT id, nombre, abreviatura FROM unidades_medida ORDER BY nombre');
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getCategoriasInventario(PDO $pdo) {
    $stmt = $pdo->query("SELECT id, nombre, tipo FROM categorias_inventario ORDER BY nombre");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getDepartamentos(PDO $pdo) {
    $stmt = $pdo->query("SELECT id, nombre FROM departamentos ORDER BY nombre");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getJefeByDepartamento(PDO $pdo, int $departamento_id) {
    $stmt = $pdo->prepare('SELECT id, nombre, correo FROM jefes_departamento WHERE departamento_id = :d AND activo = 1 LIMIT 1');
    $stmt->execute([':d' => $departamento_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
}

function getProductos(PDO $pdo) {
    $stmt = $pdo->query('SELECT id, codigo_barras, descripcion FROM productos WHERE activo = 1 ORDER BY descripcion');
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function tableExists(PDO $pdo, string $tableName): bool {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = :tn");
    $stmt->execute([':tn' => $tableName]);
    return (bool) $stmt->fetchColumn();
}

/**
 * Inserta en una tabla solo las columnas que existan.
 * $data es un array asociativo column => value
 */
function insertIntoTable(PDO $pdo, string $tableName, array $data) {
    // obtener columnas de la tabla
    $stmt = $pdo->prepare("SELECT COLUMN_NAME FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = :tn");
    $stmt->execute([':tn' => $tableName]);
    $cols = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

    $insertCols = array_values(array_intersect($cols, array_keys($data)));
    if (empty($insertCols)) {
        throw new Exception('No hay columnas válidas para insertar en ' . $tableName);
    }

    $placeholders = array_map(function($c){ return ':' . $c; }, $insertCols);
    $sql = sprintf("INSERT INTO %s (%s) VALUES (%s)", $tableName, implode(',', $insertCols), implode(',', $placeholders));
    $stmt2 = $pdo->prepare($sql);
    $params = [];
    foreach ($insertCols as $c) {
        $params[':' . $c] = $data[$c];
    }
    $stmt2->execute($params);
    return $pdo->lastInsertId();
}
