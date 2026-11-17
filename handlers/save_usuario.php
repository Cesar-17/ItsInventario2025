<?php
// handlers/save_usuario.php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Método no permitido');
}

$id = (int)($_POST['id'] ?? 0);
$nombre = trim($_POST['nombre'] ?? '');
$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');
$rol_asignado = trim($_POST['rol'] ?? 'usuario');
$departamento = trim($_POST['departamento'] ?? '');
$rol_creador = $_SESSION['usuario_rol'] ?? 'usuario';

// ============================================================
// VALIDACIÓN ESTRICTA DE PERMISOS
// ============================================================
$roles_permitidos = [];
if ($rol_creador === 'admin') {
    $roles_permitidos = [
        'usuario', 'activo_fijo', 'almacen_general', 'mantenimiento', 
        'finanzas', 'jefe_sistemas', 'jefe_mantenimiento', 'jefe_finanzas', 'jefe_rh', 'admin'
    ];
} elseif ($rol_creador === 'jefe_sistemas') {
    $roles_permitidos = ['activo_fijo', 'almacen_general', 'usuario'];
} elseif ($rol_creador === 'jefe_mantenimiento') {
    $roles_permitidos = ['mantenimiento', 'usuario'];
} elseif ($rol_creador === 'jefe_finanzas') {
    $roles_permitidos = ['finanzas', 'usuario'];
} elseif ($rol_creador === 'jefe_rh') {
    $roles_permitidos = ['usuario']; 
} else {
    http_response_code(403);
    exit('No tienes permiso para gestionar usuarios.');
}

if (!in_array($rol_asignado, $roles_permitidos)) {
    http_response_code(403);
    echo 'No tienes permiso para asignar este rol.';
    exit;
}

$errors = [];
if (!$nombre) $errors[] = 'El nombre es requerido';
if (!$username) $errors[] = 'El usuario es requerido';
if ($id === 0 && !$password) $errors[] = 'La contraseña es requerida para nuevos usuarios';

if (!empty($errors)) {
    http_response_code(400);
    echo implode('<br>', $errors);
    exit;
}

try {
    // Verificar si el username ya existe
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE username = ? AND id != ?");
    $stmt->execute([$username, $id]);
    if ($stmt->fetch()) {
        header('Location: ' . BASE_PATH . 'usuarios/main_usuarios.php?error=username_duplicado');
        exit;
    }

    // Limpiar el departamento si el rol no es de jefe
    if (strpos($rol_asignado, 'jefe_') !== 0) {
        $departamento = '';
    }

    // Si el creador es un jefe, forzar el departamento a que sea el suyo
    if (strpos($rol_creador, 'jefe_') === 0 && empty($departamento)) {
        $departamento = $_SESSION['usuario_departamento'] ?? '';
    }

    if ($id > 0) {
        // ACTUALIZAR
        if ($password) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            // USAMOS password_hash EN LUGAR DE password
            $stmt = $pdo->prepare("UPDATE usuarios SET nombre=?, username=?, password_hash=?, rol=?, departamento=? WHERE id=?");
            $stmt->execute([$nombre, $username, $hash, $rol_asignado, $departamento, $id]);
        } else {
            $stmt = $pdo->prepare("UPDATE usuarios SET nombre=?, username=?, rol=?, departamento=? WHERE id=?");
            $stmt->execute([$nombre, $username, $rol_asignado, $departamento, $id]);
        }
    } else {
        // INSERTAR NUEVO
        $hash = password_hash($password, PASSWORD_DEFAULT);
        // USAMOS password_hash EN LUGAR DE password
        $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, username, password_hash, rol, departamento, activo) VALUES (?, ?, ?, ?, ?, 1)");
        $stmt->execute([$nombre, $username, $hash, $rol_asignado, $departamento]);
    }

    header('Location: ' . BASE_PATH . 'usuarios/main_usuarios.php?ok=1');
    exit;

} catch (Exception $e) {
    error_log('Error al guardar usuario: ' . $e->getMessage());
    http_response_code(500);
    echo 'Error al guardar: ' . $e->getMessage();
    exit;
}
?>