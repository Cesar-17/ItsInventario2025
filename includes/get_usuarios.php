<?php
// includes/get_usuarios.php
header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['ok' => false, 'error' => 'No autenticado']);
    exit;
}

require_once __DIR__ . '/db.php';

$rol_actual = $_SESSION['usuario_rol'] ?? 'usuario';

$esAdmin = ($rol_actual === 'admin');
$esJefe  = (strpos($rol_actual, 'jefe_') === 0);

if (!$esAdmin && !$esJefe) {
    echo json_encode(['ok' => false, 'error' => 'No tienes permisos']);
    exit;
}

try {
    if ($esAdmin) {
        // El Admin ve absolutamente a todos
        $stmt = $pdo->query("SELECT id, nombre, username, rol, departamento, activo, last_login FROM usuarios ORDER BY id DESC");
        $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        // Lógica para Jefes: Solo ven 'usuario' (en espera) y los roles que ellos pueden asignar
        $roles_visibles = ['usuario']; // Todos los jefes ven a los que están en espera
        if ($rol_actual === 'jefe_sistemas') {
            $roles_visibles[] = 'activo_fijo';
            $roles_visibles[] = 'almacen_general';
        } elseif ($rol_actual === 'jefe_mantenimiento') {
            $roles_visibles[] = 'mantenimiento';
        } elseif ($rol_actual === 'jefe_finanzas') { 
            $roles_visibles[] = 'finanzas';
        }

        // Preparamos la consulta con los roles permitidos
        $inQuery = implode(',', array_fill(0, count($roles_visibles), '?'));
        $stmt = $pdo->prepare("
            SELECT id, nombre, username, rol, departamento, activo, last_login 
            FROM usuarios 
            WHERE rol IN ($inQuery) 
            ORDER BY id DESC
        ");
        $stmt->execute($roles_visibles);
        $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    echo json_encode(['ok' => true, 'usuarios' => $usuarios]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
?>