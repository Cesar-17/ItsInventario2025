<?php
// includes/auth.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ============================================================
// DETECCIÓN AUTOMÁTICA DE RUTA BASE (Local, IP Móvil o Internet)
// ============================================================
$docRoot = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'] ?? '') ?: '');
$projectRoot = str_replace('\\', '/', realpath(dirname(__DIR__)) ?: '');

if ($docRoot !== '' && strpos($projectRoot, $docRoot) === 0) {
    $subPath = trim(substr($projectRoot, strlen($docRoot)), '/');
    define('BASE_PATH', $subPath === '' ? '/' : '/' . $subPath . '/');
} else {
    define('BASE_PATH', '/');
}

$pagina_actual = basename($_SERVER['PHP_SELF']);

// IMPORTANTE: Si NO hay sesión, pero estamos en el login, 
// dejamos que cargue el login en lugar de crear un bucle infinito.
if (!isset($_SESSION['usuario_id'])) {
    if ($pagina_actual !== 'login.php') {
        header('Location: ' . BASE_PATH . 'login.php');
        exit;
    }
    return; // Detiene la ejecución de los permisos aquí para que el formulario de login pueda renderizarse
}

$rol = $_SESSION['usuario_rol'] ?? 'usuario';

// ============================================================
// FUNCIONES DE PERMISOS
// ============================================================

function puedeVerActivoFijo() {
    global $rol;
    if (strpos($rol, 'jefe_') === 0) return false;
    return !in_array($rol, ['finanzas']);
}

function puedeVerAlmacenGeneral() {
    global $rol;
    if (strpos($rol, 'jefe_') === 0) return false;
    return in_array($rol, ['admin', 'almacen_general']);
}

function puedeVerProductos() {
    global $rol;
    if (strpos($rol, 'jefe_') === 0) return false;
    return !in_array($rol, ['activo_fijo', 'almacen_general', 'finanzas']);
}

function puedeVerCompras() {
    global $rol;
    if (strpos($rol, 'jefe_') === 0) return false;
    return in_array($rol, ['admin', 'finanzas', 'almacen_general', 'activo_fijo']);
}

function puedeVerFacturas() {
    global $rol;
    if (strpos($rol, 'jefe_') === 0) return false;
    return in_array($rol, ['admin', 'activo_fijo', 'almacen_general']);
}

function puedeCrearRequisicion() {
    global $rol;
    return in_array($rol, ['admin', 'finanzas']);
}

function puedeGenerarOrden() {
    global $rol;
    return in_array($rol, ['admin', 'almacen_general', 'activo_fijo']);
}

function puedeModificarCompras() {
    global $rol;
    if (strpos($rol, 'jefe_') === 0) return false;
    return in_array($rol, ['admin', 'finanzas']);
}

function esAdmin() {
    global $rol;
    return ($rol === 'admin');
}

function esJefeDepartamento() {
    global $rol;
    return strpos($rol, 'jefe_') === 0;
}

// ============================================================
// VALIDACIÓN DE ACCESO POR RUTA
// ============================================================
$rutaActual = $_SERVER['REQUEST_URI'];
$pagina_actual = basename($_SERVER['PHP_SELF']);

// 1. BLOQUEO PARA USUARIOS SIN ROL ASIGNADO
if ($rol === 'usuario') {
    if ($pagina_actual !== 'espera_rol.php' && $pagina_actual !== 'logout.php') {
        header('Location: ' . BASE_PATH . 'espera_rol.php');
        exit;
    }
    // Si ya está en la página de espera, dejarlo pasar
    return; 
}

// 2. JEFE DE DEPARTAMENTO
if (esJefeDepartamento()) {
    $paginasPermitidas = ['jefe_departamento.php', 'main_usuarios.php', 'home/index.php', 'index.php', 'logout.php'];
    $permitido = false;
    foreach ($paginasPermitidas as $pagina) {
        if (strpos($rutaActual, $pagina) !== false) { $permitido = true; break; }
    }
    if ($rutaActual === BASE_PATH || $rutaActual === BASE_PATH . 'index.php') { $permitido = true; }
    if (!$permitido) {
        header('Location: ' . BASE_PATH . 'jefe_departamento.php');
        exit;
    }
}

// 3. FINANZAS: puede ver requisiciones, compras, facturas y crear requisiciones
if ($rol === 'finanzas') {
    $paginasPermitidas = [
        'requisiciones.php', 
        'capturar_requisicion.php', 
        'save_requisicion.php',
        'get_requisiciones.php',
        'get_requisicion_detalle.php',
        'get_requisicion_by_folio.php',
        'compras.php', 
        'facturas_lista.php', 
        'get_facturas.php',
        'home/index.php', 
        'index.php', 
        'logout.php'
    ];
    $permitido = false;
    foreach ($paginasPermitidas as $pagina) {
        if (strpos($rutaActual, $pagina) !== false) { $permitido = true; break; }
    }
    if ($rutaActual === BASE_PATH || $rutaActual === BASE_PATH . 'index.php') { $permitido = true; }
    if (!$permitido) {
        header('Location: ' . BASE_PATH . 'home/index.php?error=acceso_denegado');
        exit;
    }
}

// 4. ACTIVO FIJO: NO puede ver Almacén General ni Compras
if ($rol === 'activo_fijo') {
    $paginasProhibidas = [
        'main_productos.php', 'capturarProd.php', 'vale_material.php', 
        'vale_herramienta.php', 'listar_vales.php', 
        'requisiciones.php', 'capturar_requisicion.php', 'save_requisicion.php',
        'compras.php'
    ];
    foreach ($paginasProhibidas as $pagina) {
        if (strpos($rutaActual, $pagina) !== false) {
            header('Location: ' . BASE_PATH . 'home/index.php?error=acceso_denegado');
            exit;
        }
    }
}

// 5. ALMACÉN GENERAL: NO puede ver Productos ni Compras (solo lectura en compras)
if ($rol === 'almacen_general') {
    $paginasProhibidas = ['main_productos.php', 'capturarProd.php', 'vale_material.php', 'vale_herramienta.php', 'listar_vales.php'];
    foreach ($paginasProhibidas as $pagina) {
        if (strpos($rutaActual, $pagina) !== false) {
            header('Location: ' . BASE_PATH . 'home/index.php?error=acceso_denegado');
            exit;
        }
    }
}

// 6. MANTENIMIENTO
if ($rol === 'mantenimiento') {
    $paginasPermitidas = ['main_productos.php', 'capturarProd.php', 'vale_material.php', 'vale_herramienta.php', 'listar_vales.php', 'home/index.php', 'index.php', 'logout.php', 'save_producto.php', 'save_vale_herramienta.php', 'save_vale.php']; 
    $permitido = false;
    foreach ($paginasPermitidas as $pagina) {
        if (strpos($rutaActual, $pagina) !== false) { $permitido = true; break; }
    }
    if ($rutaActual === BASE_PATH || $rutaActual === BASE_PATH . 'index.php') { $permitido = true; }
    if (!$permitido) {
        header('Location: ' . BASE_PATH . 'home/index.php?error=acceso_denegado');
        exit;
    }
}
?>