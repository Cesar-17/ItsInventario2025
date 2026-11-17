<?php
// includes/debug_ordenes.php
// Página de debug ligera para verificar si hay órdenes y detalles en la BD
require_once __DIR__ . '/functions.php';
?><!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Debug - Ordenes</title>
  <link rel="stylesheet" href="<?= BASE_PATH ?>css/bootstrap.min.css">
  <style>body{padding:16px}</style>
</head>
<body>
  <h2>Debug: Órdenes de compra</h2>
  <p>Enlace JSON: <a href="<?= BASE_PATH ?>includes/get_ordenes.php">includes/get_ordenes.php</a></p>
  <p>Contadores: <a href="<?= BASE_PATH ?>includes/debug_counts.php">includes/debug_counts.php</a></p>
  <?php
  try {
    $stmt = $pdo->query("SELECT id, folio, factura_remision, proveedor, fecha, estatus, total, created_at FROM ordenes_compra ORDER BY created_at DESC LIMIT 50");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (!$rows) {
      echo '<div class="alert alert-info">No se encontraron órdenes en la base de datos.</div>';
    } else {
      echo '<table class="table table-sm table-striped"><thead><tr><th>ID</th><th>Folio</th><th>Factura</th><th>Proveedor</th><th>Fecha</th><th>Estatus</th><th>Total</th><th>Detalles</th></tr></thead><tbody>';
      foreach ($rows as $r) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($r['id']) . '</td>';
        echo '<td>' . htmlspecialchars($r['folio']) . '</td>';
        echo '<td>' . htmlspecialchars($r['factura_remision']) . '</td>';
        echo '<td>' . htmlspecialchars($r['proveedor']) . '</td>';
        echo '<td>' . htmlspecialchars($r['fecha']) . '</td>';
        echo '<td>' . htmlspecialchars($r['estatus']) . '</td>';
        echo '<td>' . htmlspecialchars($r['total']) . '</td>';
        // detalles
        $stmt2 = $pdo->prepare('SELECT doc.id, doc.descripcion, doc.cantidad, doc.precio_unitario, doc.importe, doc.producto_id, p.codigo_barras, u.nombre AS unidad FROM detalles_orden_compra doc LEFT JOIN productos p ON p.id = doc.producto_id LEFT JOIN unidades_medida u ON u.id = doc.unidad_medida_id WHERE doc.orden_compra_id = :oid');
        $stmt2->execute([':oid' => $r['id']]);
        $det = $stmt2->fetchAll(PDO::FETCH_ASSOC);
        $detHtml = '';
        if ($det) {
          $detHtml .= '<table class="table table-sm mb-0"><thead><tr><th>#</th><th>Producto</th><th>Descripcion</th><th>Cantidad</th><th>Unidad</th><th>Precio</th><th>Importe</th></tr></thead><tbody>';
          $i=1;
          foreach ($det as $d) {
            $detHtml .= '<tr>';
            $detHtml .= '<td>' . $i++ . '</td>';
            $prod = $d['producto_id'] ? ($d['codigo_barras'] ? $d['codigo_barras'] : $d['producto_id']) : '';
            $detHtml .= '<td>' . htmlspecialchars($prod) . '</td>';
            $detHtml .= '<td>' . htmlspecialchars($d['descripcion']) . '</td>';
            $detHtml .= '<td>' . htmlspecialchars($d['cantidad']) . '</td>';
            $detHtml .= '<td>' . htmlspecialchars($d['unidad']) . '</td>';
            $detHtml .= '<td>' . htmlspecialchars($d['precio_unitario']) . '</td>';
            $detHtml .= '<td>' . htmlspecialchars($d['importe']) . '</td>';
            $detHtml .= '</tr>';
          }
          $detHtml .= '</tbody></table>';
        } else {
          $detHtml = '<em>No hay detalles</em>';
        }
        echo '<td>' . $detHtml . '</td>';
        echo '</tr>';
      }
      echo '</tbody></table>';
    }
  } catch (Exception $e) {
    echo '<div class="alert alert-danger">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
  }
  ?>
</body>
</html>
