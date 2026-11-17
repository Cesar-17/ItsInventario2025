<?php
// logout.php

// 1. Incluimos el archivo donde se define BASE_PATH y las sesiones
require_once __DIR__ . '/includes/auth.php'; 

// 2. Destruimos la sesión
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $p['path'], $p['domain'], $p['secure'], $p['httponly']);
}
session_destroy();

// 3. Redirigimos usando BASE_PATH con seguridad
header('Location: ' . BASE_PATH . 'login.php');
exit;
?>