<?php
// handlers/save_producto.php
// Actualizado para usar el nuevo esquema: productos(categoria_id, unidad_medida_id, stock, costo_promedio...)
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Método no permitido');
}

$descripcion = trim($_POST['descripcion'] ?? '');
$marca = trim($_POST['marca'] ?? '');
$modelo = trim($_POST['modelo'] ?? '');
$categoria_id = is_numeric($_POST['categoria_id'] ?? null) ? (int)$_POST['categoria_id'] : null;
$unidad_medida_id = is_numeric($_POST['unidad_medida_id'] ?? null) ? (int)$_POST['unidad_medida_id'] : null;
$stock_minimo = is_numeric($_POST['stock_minimo'] ?? null) ? (int)$_POST['stock_minimo'] : 0;
$costo_promedio = is_numeric($_POST['costo_promedio'] ?? null) ? (float)$_POST['costo_promedio'] : 0.0;
$cantidad = is_numeric($_POST['cantidad'] ?? null) ? max(1, (int)$_POST['cantidad']) : 1;

$errors = [];
if ($descripcion === '') $errors[] = 'Descripción es requerida.';
if (!$categoria_id) $errors[] = 'Categoría es requerida.';
if (!$unidad_medida_id) $errors[] = 'Unidad de medida es requerida.';

if (!empty($errors)) {
    http_response_code(400);
    echo implode('<br>', $errors);
    exit;
}

try {
    // determinar tipo de categoría para decidir comportamiento
    $tipo = null;
    $cat = $pdo->prepare('SELECT tipo FROM categorias_inventario WHERE id = :id LIMIT 1');
    $cat->execute([':id' => $categoria_id]);
    $c = $cat->fetch(PDO::FETCH_ASSOC);
    if ($c && isset($c['tipo'])) $tipo = $c['tipo'];

    // preparar declaración de inserción
    $stmt = $pdo->prepare("INSERT INTO productos
        (codigo_barras, descripcion, marca, modelo, categoria_id, unidad_medida_id, stock_minimo, stock_actual, costo_promedio, activo)
        VALUES
        (:codigo_barras, :descripcion, :marca, :modelo, :categoria_id, :unidad_medida_id, :stock_minimo, :stock_actual, :costo_promedio, 1)");

    // determinar letra según categoría (mismo criterio que antes)
    $letter = 'X';
    if ($categoria_id === 5000) $letter = 'A';
    elseif ($categoria_id === 2000) $letter = 'C';
    else {
        if ($tipo !== null) $letter = ($tipo === 'activo') ? 'A' : 'C';
    }

    $generated = [];

    // iniciar transacción para operaciones atómicas
    if (!$pdo->inTransaction()) $pdo->beginTransaction();

    if ($tipo === 'activo') {
        // Para activos: crear N filas (una por pieza), cada una con su propio código
        for ($i = 0; $i < $cantidad; $i++) {
            $stmt->execute([
                ':codigo_barras' => null,
                ':descripcion' => $descripcion,
                ':marca' => $marca,
                ':modelo' => $modelo,
                ':categoria_id' => $categoria_id,
                ':unidad_medida_id' => $unidad_medida_id,
                ':stock_minimo' => $stock_minimo,
                ':stock_actual' => 0,
                ':costo_promedio' => $costo_promedio,
            ]);
            $newId = $pdo->lastInsertId();
            $codigo_barras = $letter . $categoria_id . '-' . str_pad($newId, 6, '0', STR_PAD_LEFT);
            $upd = $pdo->prepare('UPDATE productos SET codigo_barras = :cb WHERE id = :id');
            $upd->execute([':cb' => $codigo_barras, ':id' => $newId]);
            $generated[] = $codigo_barras;
        }

        // commit y redirigir con la lista de códigos generados (separados por coma)
        if ($pdo->inTransaction()) $pdo->commit();
        header('Location: ' . BASE_PATH . 'productos/capturarProd.php?ok=1&barcodes=' . urlencode(implode(',', $generated)));
        exit;
    } else {
        // Para consumibles u otros tipos: sumar stock si existe producto similar, sino crear uno nuevo
        // Regla para identificar producto similar: misma descripcion, categoria_id, marca y modelo
        $find = $pdo->prepare('SELECT id, stock_actual FROM productos WHERE descripcion = :descripcion AND categoria_id = :categoria_id AND marca = :marca AND modelo = :modelo LIMIT 1');
        $find->execute([':descripcion' => $descripcion, ':categoria_id' => $categoria_id, ':marca' => $marca, ':modelo' => $modelo]);
        $existing = $find->fetch(PDO::FETCH_ASSOC);
        if ($existing) {
            // sumar stock
            $newStock = (int)$existing['stock_actual'] + $cantidad;
            $upd2 = $pdo->prepare('UPDATE productos SET stock_actual = :stock WHERE id = :id');
            $upd2->execute([':stock' => $newStock, ':id' => $existing['id']]);
            $codigo_barras = null;
            // intentar obtener codigo existente
            $cbt = $pdo->prepare('SELECT codigo_barras FROM productos WHERE id = :id LIMIT 1');
            $cbt->execute([':id' => $existing['id']]);
            $rowcb = $cbt->fetch(PDO::FETCH_ASSOC);
            if ($rowcb) $codigo_barras = $rowcb['codigo_barras'];

            if ($pdo->inTransaction()) $pdo->commit();
            header('Location: ' . BASE_PATH . 'productos/capturarProd.php?ok=1&barcode=' . urlencode($codigo_barras));
            exit;
        } else {
            // insertar nuevo producto con stock_actual = cantidad
            $stmt->execute([
                ':codigo_barras' => null,
                ':descripcion' => $descripcion,
                ':marca' => $marca,
                ':modelo' => $modelo,
                ':categoria_id' => $categoria_id,
                ':unidad_medida_id' => $unidad_medida_id,
                ':stock_minimo' => $stock_minimo,
                ':stock_actual' => $cantidad,
                ':costo_promedio' => $costo_promedio,
            ]);
            $newId = $pdo->lastInsertId();
            $codigo_barras = $letter . $categoria_id . '-' . str_pad($newId, 6, '0', STR_PAD_LEFT);
            $upd = $pdo->prepare('UPDATE productos SET codigo_barras = :cb WHERE id = :id');
            $upd->execute([':cb' => $codigo_barras, ':id' => $newId]);

            if ($pdo->inTransaction()) $pdo->commit();
            header('Location: ' . BASE_PATH . 'productos/capturarProd.php?ok=1&barcode=' . urlencode($codigo_barras));
            exit;
        }
    }
} catch (Exception $e) {
    // rollback si hay transacción activa
    try {
        if ($pdo->inTransaction()) $pdo->rollBack();
    } catch (Exception $rb) {
        error_log('Rollback failed in save_producto.php: ' . $rb->getMessage());
    }
    error_log('Insert producto error: ' . $e->getMessage());
    // mostrar detalles en desarrollo
    $env = 'production';
    $cfgFile = __DIR__ . '/../includes/config.php';
    if (file_exists($cfgFile)) {
        $cfg = include $cfgFile;
        if (is_array($cfg) && isset($cfg['APP_ENV'])) $env = $cfg['APP_ENV'];
    }
    http_response_code(500);
    if ($env !== 'production') {
        echo 'Error al guardar el producto: ' . htmlspecialchars($e->getMessage());
    } else {
        echo 'Error al guardar el producto.';
    }
}

