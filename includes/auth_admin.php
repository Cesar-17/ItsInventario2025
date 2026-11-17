<?php
// includes/auth_admin.php
require_once __DIR__ . '/auth.php';

$rol_actual = $_SESSION['usuario_rol'] ?? 'usuario';

// Permitir el paso si es Administrador O si es un Jefe de Departamento
$esAdmin = ($rol_actual === 'admin');
$esJefe  = (strpos($rol_actual, 'jefe_') === 0);

if (!$esAdmin && !$esJefe) {
    // Si no es ni admin ni jefe, lo regresamos al inicio con error
    header('Location: ' . BASE_PATH . 'home/index.php?error=acceso_denegado');
    exit;
}
?>