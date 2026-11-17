<?php
// handlers/save_activo_fijo.php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('Método no permitido'); }

$tipo = isset($_POST['tipo']) && in_array($_POST['tipo'], ['entrada', 'salida']) ? $_POST['tipo'] : 'entrada';

// =============================================
// SALIDA
// =============================================
if ($tipo === 'salida') {
    $activo_id = isset($_POST['activo_id']) ? (int)$_POST['activo_id'] : 0;
    if (!$activo_id) { http_response_code(400); echo 'Debes seleccionar un activo existente.'; exit; }

    try {
        $stmt = $pdo->prepare("SELECT * FROM activos_fijos WHERE id = ? AND disponible = 1 AND tipo = 'entrada'");
        $stmt->execute([$activo_id]);
        $activo = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$activo) { http_response_code(400); echo 'El activo no está disponible.'; exit; }

        $folio = 'SAL-' . date('Ymd') . '-' . rand(1000, 9999);
        $tipo_compra = trim($_POST['tipo_compra'] ?? $activo['tipo_compra'] ?? 'normal');
        $proveedor = trim($_POST['proveedor'] ?? 'N/A');
        $fecha_hoy = date('Y-m-d');
        
        $observaciones = 'Salida de activo fijo. Tipo: ' . $tipo_compra . ' | Jefe: ' . $activo['jefe_departamento'];

        // 1. Insertar en salidas_inventario
        $stmt = $pdo->prepare("
            INSERT INTO salidas_inventario 
            (folio, fecha, departamento_id, jefe_id, proveedor, observaciones, proyecto)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $folio, $fecha_hoy, 1, null, $proveedor, $observaciones, 'Activo Fijo'
        ]);

        // 2. Insertar en activos_fijos (El movimiento de salida para el Historial)
        $stmt = $pdo->prepare("
            INSERT INTO activos_fijos 
            (folio, tipo, num_oficina, fecha, jefe_departamento, descripcion_bien, 
             num_simenu, sep, etiqueta, departamento_recibe, marca, modelo, serie, disponible, tipo_compra)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $folio, 'salida', $activo['num_oficina'], $fecha_hoy, $activo['jefe_departamento'],
            $activo['descripcion_bien'], $activo['num_simenu'], $activo['sep'], $activo['etiqueta'],
            $activo['departamento_recibe'], $activo['marca'], $activo['modelo'], $activo['serie'], 0, $tipo_compra
        ]);

        // 3. Actualizar el original a NO DISPONIBLE
        $stmt = $pdo->prepare("UPDATE activos_fijos SET disponible = 0 WHERE id = ?");
        $stmt->execute([$activo_id]);

        header('Location: ' . BASE_PATH . 'activo_fijo/index.php?ok=1');
        exit;

    } catch (Exception $e) {
        http_response_code(500);
        echo 'Error al guardar salida: ' . $e->getMessage();
        exit;
    }
}

// ENTRADA (Igual)
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
$cantidad_unidades = isset($_POST['cantidad_unidades']) ? max(1, (int)$_POST['cantidad_unidades']) : 1;
$tipo_compra = trim($_POST['tipo_compra'] ?? 'normal');

$tipo_equipo = 'otro';
if (isset($_POST['tipo_pc']) && $_POST['tipo_pc'] == 'pc') $tipo_equipo = 'pc';
elseif (isset($_POST['tipo_laptop']) && $_POST['tipo_laptop'] == 'laptop') $tipo_equipo = 'laptop';
elseif (isset($_POST['tipo_aire']) && $_POST['tipo_aire'] == 'aire') $tipo_equipo = 'aire';

$memoria_ram = trim($_POST['memoria_ram'] ?? '');
$sistema_operativo = trim($_POST['sistema_operativo'] ?? '');
$tipo_disco = trim($_POST['tipo_disco'] ?? '');
$capacidad_disco = trim($_POST['capacidad_disco'] ?? '');
$anio_asignacion = !empty($_POST['anio_asignacion']) ? (int)$_POST['anio_asignacion'] : null;
$antiguedad = !empty($_POST['antiguedad']) ? (int)$_POST['antiguedad'] : null;

$aire_tipo = trim($_POST['aire_tipo'] ?? '');
$aire_capacidad = trim($_POST['aire_capacidad'] ?? '');
$aire_ubicacion = trim($_POST['aire_ubicacion'] ?? '');
$aire_tecnologia = trim($_POST['aire_tecnologia'] ?? '');
$aire_ciclo = trim($_POST['aire_ciclo'] ?? '');
$aire_funciona = isset($_POST['aire_funciona']) ? (int)$_POST['aire_funciona'] : 1;
$aire_anio_asignacion = !empty($_POST['aire_anio_asignacion']) ? (int)$_POST['aire_anio_asignacion'] : null;
$aire_antiguedad = !empty($_POST['aire_antiguedad']) ? (int)$_POST['aire_antiguedad'] : null;

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
         cantidad_unidades, tipo_equipo, memoria_ram, sistema_operativo, tipo_disco,
         capacidad_disco, anio_asignacion, antiguedad,
         aire_tipo, aire_capacidad, aire_ubicacion, aire_tecnologia, aire_ciclo,
         aire_funciona, aire_anio_asignacion, aire_antiguedad, tipo_compra)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $folio, 'entrada', $num_oficina, $fecha, $jefe_departamento, $descripcion_bien,
        $num_simenu, $sep, $etiqueta, $departamento_recibe, $marca, $modelo, $serie, $disponible,
        $cantidad_unidades, $tipo_equipo, $memoria_ram, $sistema_operativo, $tipo_disco,
        $capacidad_disco, $anio_asignacion, $antiguedad,
        $aire_tipo, $aire_capacidad, $aire_ubicacion, $aire_tecnologia, $aire_ciclo,
        $aire_funciona, $aire_anio_asignacion, $aire_antiguedad, $tipo_compra
    ]);

    $activo_id = $pdo->lastInsertId();

    header('Location: ' . BASE_PATH . 'activo_fijo/index.php?ok=1&nuevo=' . $activo_id . '&cant=' . $cantidad_unidades);
    exit;

} catch (Exception $e) {
    http_response_code(500);
    echo 'Error al guardar: ' . $e->getMessage();
    exit;
}
?>