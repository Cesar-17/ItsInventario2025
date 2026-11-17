<?php
// handlers/save_activo_fijo.php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Método no permitido');
}

$tipo = isset($_POST['tipo']) && in_array($_POST['tipo'], ['entrada', 'salida']) ? $_POST['tipo'] : 'entrada';

// Para SALIDA: obtener datos del activo existente
if ($tipo === 'salida') {
    $activo_id = isset($_POST['activo_id']) ? (int)$_POST['activo_id'] : 0;
    if (!$activo_id) {
        http_response_code(400);
        echo 'Debes seleccionar un activo existente.';
        exit;
    }

    try {
        $stmt = $pdo->prepare("SELECT * FROM activos_fijos WHERE id = ? AND disponible = 1 AND tipo = 'entrada'");
        $stmt->execute([$activo_id]);
        $activo = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$activo) {
            http_response_code(400);
            echo 'El activo seleccionado no está disponible o no existe.';
            exit;
        }

        $folio = 'SAL-' . date('Ymd') . '-' . rand(1000, 9999);

        $stmt = $pdo->prepare("
            INSERT INTO activos_fijos 
            (folio, tipo, num_oficina, fecha, jefe_departamento, descripcion_bien, 
             num_simenu, sep, etiqueta, departamento_recibe, marca, modelo, serie, disponible,
             tipo_equipo, memoria_ram, sistema_operativo, tipo_disco, capacidad_disco, 
             anio_asignacion, antiguedad, aire_tipo, aire_capacidad, aire_ubicacion, 
             aire_tecnologia, aire_ciclo, aire_funciona)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $folio,
            'salida',
            $activo['num_oficina'],
            date('Y-m-d'),
            $activo['jefe_departamento'],
            $activo['descripcion_bien'],
            $activo['num_simenu'],
            $activo['sep'],
            $activo['etiqueta'],
            $activo['departamento_recibe'],
            $activo['marca'],
            $activo['modelo'],
            $activo['serie'],
            0,
            $activo['tipo_equipo'],
            $activo['memoria_ram'],
            $activo['sistema_operativo'],
            $activo['tipo_disco'],
            $activo['capacidad_disco'],
            $activo['anio_asignacion'],
            $activo['antiguedad'],
            $activo['aire_tipo'],
            $activo['aire_capacidad'],
            $activo['aire_ubicacion'],
            $activo['aire_tecnologia'],
            $activo['aire_ciclo'],
            $activo['aire_funciona']
        ]);

        $stmt = $pdo->prepare("UPDATE activos_fijos SET disponible = 0 WHERE id = ?");
        $stmt->execute([$activo_id]);

        header('Location: ' . BASE_PATH . 'activo_fijo/index.php?ok=1');
        exit;

    } catch (Exception $e) {
        error_log('Error al guardar salida: ' . $e->getMessage());
        http_response_code(500);
        echo 'Error al guardar: ' . $e->getMessage();
        exit;
    }
}

// =============================================
// ENTRADA: registrar nuevo activo
// =============================================
$num_oficina = trim($_POST['num_oficina'] ?? '');
$fecha = trim($_POST['fecha'] ?? '');
$jefe_departamento = trim($_POST['jefe_departamento'] ?? '');
$descripcion_bien = trim($_POST['descripcion_bien'] ?? '');
$num_simenu = trim($_POST['num_simenu'] ?? '');
$sep = trim($_POST['sep'] ?? '');
$etiqueta = trim($_POST['etiqueta'] ?? '');
$departamento_recibe = trim($_POST['departamento_recibe'] ?? '');
$marca = trim($_POST['marca'] ?? '');
$modelo = trim($_POST['modelo'] ?? '');
$serie = trim($_POST['serie'] ?? '');
$disponible = isset($_POST['disponible']) ? 1 : 0;

// Campos de PC / Laptop
$tipo_pc = isset($_POST['tipo_pc']) ? 1 : 0;
$tipo_laptop = isset($_POST['tipo_laptop']) ? 1 : 0;
$tipo_aire = isset($_POST['tipo_aire']) ? 1 : 0;

$tipo_equipo = 'otro';
if ($tipo_pc) $tipo_equipo = 'pc';
elseif ($tipo_laptop) $tipo_equipo = 'laptop';
elseif ($tipo_aire) $tipo_equipo = 'aire';

$memoria_ram = trim($_POST['memoria_ram'] ?? '');
$sistema_operativo = trim($_POST['sistema_operativo'] ?? '');
$tipo_disco = trim($_POST['tipo_disco'] ?? '');
$capacidad_disco = trim($_POST['capacidad_disco'] ?? '');
$anio_asignacion = isset($_POST['anio_asignacion']) && $_POST['anio_asignacion'] !== '' ? (int)$_POST['anio_asignacion'] : null;
$antiguedad = isset($_POST['antiguedad']) && $_POST['antiguedad'] !== '' ? (int)$_POST['antiguedad'] : null;

// Campos de Aire Acondicionado
$aire_tipo = trim($_POST['aire_tipo'] ?? '');
$aire_capacidad = trim($_POST['aire_capacidad'] ?? '');
$aire_ubicacion = trim($_POST['aire_ubicacion'] ?? '');
$aire_tecnologia = trim($_POST['aire_tecnologia'] ?? '');
$aire_ciclo = trim($_POST['aire_ciclo'] ?? '');
$aire_funciona = isset($_POST['aire_funciona']) ? (int)$_POST['aire_funciona'] : 1;
$aire_anio_asignacion = isset($_POST['aire_anio_asignacion']) && $_POST['aire_anio_asignacion'] !== '' ? (int)$_POST['aire_anio_asignacion'] : null;
$aire_antiguedad = isset($_POST['aire_antiguedad']) && $_POST['aire_antiguedad'] !== '' ? (int)$_POST['aire_antiguedad'] : null;

$errors = [];
if (!$num_oficina) $errors[] = 'El número de oficina es requerido';
if (!$fecha) $errors[] = 'La fecha es requerida';
if (!$jefe_departamento) $errors[] = 'El jefe de departamento es requerido';
if (!$descripcion_bien) $errors[] = 'La descripción del bien es requerida';
if (!$departamento_recibe) $errors[] = 'El departamento que recibe es requerido';
if (!$marca) $errors[] = 'La marca es requerida';
if (!$modelo) $errors[] = 'El modelo es requerido';
if (!$serie) $errors[] = 'El número de serie es requerido';

if (!empty($errors)) {
    http_response_code(400);
    echo implode('<br>', $errors);
    exit;
}

try {
    $folio = 'ENT-' . date('Ymd') . '-' . rand(1000, 9999);

    $stmt = $pdo->prepare("
        INSERT INTO activos_fijos 
        (folio, tipo, num_oficina, fecha, jefe_departamento, descripcion_bien, 
         num_simenu, sep, etiqueta, departamento_recibe, marca, modelo, serie, disponible,
         tipo_equipo, memoria_ram, sistema_operativo, tipo_disco, capacidad_disco, 
         anio_asignacion, antiguedad, aire_tipo, aire_capacidad, aire_ubicacion, 
         aire_tecnologia, aire_ciclo, aire_funciona)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $folio,
        'entrada',
        $num_oficina,
        $fecha,
        $jefe_departamento,
        $descripcion_bien,
        $num_simenu,
        $sep,
        $etiqueta,
        $departamento_recibe,
        $marca,
        $modelo,
        $serie,
        $disponible,
        $tipo_equipo,
        $memoria_ram,
        $sistema_operativo,
        $tipo_disco,
        $capacidad_disco,
        $anio_asignacion,
        $antiguedad,
        $aire_tipo,
        $aire_capacidad,
        $aire_ubicacion,
        $aire_tecnologia,
        $aire_ciclo,
        $aire_funciona
    ]);

    header('Location: ' . BASE_PATH . 'activo_fijo/index.php?ok=1');
    exit;

} catch (Exception $e) {
    error_log('Error al guardar entrada: ' . $e->getMessage());
    http_response_code(500);
    echo 'Error al guardar: ' . $e->getMessage();
    exit;
}
?>