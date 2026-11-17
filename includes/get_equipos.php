<?php
// includes/get_equipos.php
header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['ok' => false, 'error' => 'No autenticado']);
    exit;
}

require_once __DIR__ . '/db.php';

try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'activos_fijos'");
    if ($stmt->rowCount() == 0) {
        echo json_encode(['ok' => true, 'equipos' => []]);
        exit;
    }

    // Obtener solo activos de tipo 'entrada' que sean equipos
    $stmt = $pdo->query("
        SELECT * FROM activos_fijos 
        WHERE tipo = 'entrada' 
        AND tipo_equipo IN ('pc', 'laptop', 'aire')
        ORDER BY id DESC
    ");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Si no hay resultados con el filtro, intentar obtener todos para depuración
    if (empty($rows)) {
        // Verificar si hay registros con tipo_equipo vacío pero con campos de aire llenos
        $stmt2 = $pdo->query("
            SELECT * FROM activos_fijos 
            WHERE tipo = 'entrada' 
            AND (aire_tipo IS NOT NULL AND aire_tipo != '')
            ORDER BY id DESC
        ");
        $rows2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);
        // Si hay registros con datos de aire pero tipo_equipo no está configurado, los incluimos
        foreach ($rows2 as $row) {
            if (!in_array($row['id'], array_column($rows, 'id'))) {
                $row['tipo_equipo'] = 'aire';
                $rows[] = $row;
            }
        }
    }
    
    echo json_encode(['ok' => true, 'equipos' => $rows]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
?>