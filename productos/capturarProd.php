<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
$unidades   = getUnidadesMedida($pdo);
$categorias = getCategoriasInventario($pdo);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Capturar Producto — AlmacenaTec</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="../css/bootstrap.min.css">
  <link rel="stylesheet" href="../css/style.css">
  <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
  <style>
    .form-section-title {
      font-size: 11px; font-weight: 700; text-transform: uppercase;
      letter-spacing: 1px; color: var(--c-text-muted);
      padding: 18px 0 8px; border-bottom: 1px solid var(--c-border);
      margin-bottom: 16px;
    }
    .form-section-title i { color: var(--c-primary); }
    .tipo-badge {
      display: inline-block; padding: 3px 10px; border-radius: 20px;
      font-size: 11.5px; font-weight: 600; margin-top: 6px;
    }
    .tipo-badge.activo     { background: rgba(11,45,107,.1);  color: var(--c-primary); }
    .tipo-badge.consumible { background: rgba(22,163,74,.1);  color: var(--c-success); }
    #barcodeSection { background:#f8faff; border-radius:10px; padding:20px; border:1px solid var(--c-border); margin-top:20px; }
    .barcode-card { text-align:center; padding:16px; background:#fff; border-radius:8px; border:1px solid var(--c-border); }
  </style>
</head>
<body>
<div class="app-layout">

  <aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
      <div class="sidebar-logo">
        <div class="logo-mark">AT</div>
        <div class="logo-text">
          <span class="logo-name">AlmacenaTec</span>
          <span class="logo-sub">TecNM Saltillo</span>
        </div>
      </div>
    </div>
<nav class="sidebar-nav">
    <div class="nav-section">Módulos</div>
    <a href="../home/index.php" class="sidebar-link"><i class="bi bi-house-door"></i> Inicio</a>
    
    <?php if (esJefeDepartamento()): ?>
    <a href="../jefe_departamento.php" class="sidebar-link"><i class="bi bi-building"></i> Mi Departamento</a>
    
    <?php elseif ($_SESSION['usuario_rol'] === 'finanzas'): ?>
    <a href="../orden_compra/compras.php" class="sidebar-link"><i class="bi bi-cart-check"></i> Compras</a>
    
    <?php elseif ($_SESSION['usuario_rol'] === 'activo_fijo' || $_SESSION['usuario_rol'] === 'almacen_general'): ?>
    <a href="../activo_fijo/index.php" class="sidebar-link"><i class="bi bi-safe-fill"></i> Activo Fijo</a>
    <a href="../activo_fijo/main_activF.php" class="sidebar-link"><i class="bi bi-clipboard2-data"></i> Historial Salidas</a>
    <a href="../orden_compra/requisiciones.php" class="sidebar-link"><i class="bi bi-cart3"></i> Almacén General</a>
    <a href="../orden_compra/facturas_lista.php" class="sidebar-link"><i class="bi bi-file-earmark-text"></i> Facturas</a>
    
    <?php elseif ($_SESSION['usuario_rol'] === 'almacenista' || $_SESSION['usuario_rol'] === 'mantenimiento'): ?>
    <a href="../productos/main_productos.php" class="sidebar-link"><i class="bi bi-box-seam"></i> Productos</a>
    
    <?php else: ?>
    <!-- Admin y Usuario: todos los módulos -->
    <a href="../activo_fijo/index.php" class="sidebar-link"><i class="bi bi-safe-fill"></i> Activo Fijo</a>
    <a href="../activo_fijo/main_activF.php" class="sidebar-link"><i class="bi bi-clipboard2-data"></i> Historial Salidas</a>
    <a href="../orden_compra/requisiciones.php" class="sidebar-link"><i class="bi bi-cart3"></i> Almacén General</a>
    <a href="../orden_compra/facturas_lista.php" class="sidebar-link"><i class="bi bi-file-earmark-text"></i> Facturas</a>
    <a href="../orden_compra/compras.php" class="sidebar-link"><i class="bi bi-cart-check"></i> Compras</a>
    <a href="../productos/main_productos.php" class="sidebar-link"><i class="bi bi-box-seam"></i> Productos</a>
    <?php if (isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'admin'): ?>
    <div class="nav-section" style="margin-top:8px">Administración</div>
    <a href="../usuarios/main_usuarios.php" class="sidebar-link"><i class="bi bi-people"></i> Usuarios</a>
    <?php endif; ?>
    <?php endif; ?>
</nav>
    <div class="sidebar-footer"><i class="bi bi-geo-alt-fill me-1"></i> TecNM · Campus Saltillo · 2026</div>
  </aside>

  <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

  <div class="main-wrapper" id="mainWrapper">
    <header class="topbar">
      <button class="topbar-toggle" onclick="toggleSidebar()"><i class="bi bi-list" style="font-size:22px"></i></button>
      <div class="topbar-info">
        <h1>Capturar Producto</h1>
        <div class="topbar-breadcrumb">
          <a href="../home/index.php">Inicio</a> &rsaquo;
          <a href="../productos/main_productos.php">Productos</a> &rsaquo; Capturar
        </div>
      </div>
      <div style="display:flex;align-items:center;gap:10px;flex-shrink:0">
        <span style="font-size:13px;color:var(--c-text-md)"><i class="bi bi-person-circle me-1"></i><?= htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Usuario') ?></span>
        <a href="<?= BASE_PATH ?>logout.php" class="btn-accent-brand" style="padding:6px 12px;font-size:12px"><i class="bi bi-box-arrow-right me-1"></i> Salir</a>
      </div>
    </header>

    <main class="content">

      <?php
      $barcodes = [];
      $barcode  = '';
      if (isset($_GET['ok'])) {
          if (!empty($_GET['barcodes'])) {
              $barcodes = explode(',', urldecode($_GET['barcodes']));
          } elseif (!empty($_GET['barcode'])) {
              $barcode = urldecode($_GET['barcode']);
          }
      }
      ?>

      <?php if (isset($_GET['ok'])): ?>
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> Producto registrado correctamente.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>

      <?php if (!empty($barcodes) || $barcode): ?>
      <div id="barcodeSection">
        <h5 style="font-weight:700;margin-bottom:14px"><i class="bi bi-upc-scan me-2" style="color:var(--c-primary)"></i>Códigos de barras generados</h5>
        <div class="row g-3" id="barcodeGrid">
          <?php
          $allCodes = !empty($barcodes) ? $barcodes : [$barcode];
          foreach ($allCodes as $cb): if (!$cb) continue; ?>
          <div class="col-6 col-md-3">
            <div class="barcode-card">
              <svg class="barcode-svg" data-code="<?= htmlspecialchars($cb) ?>"></svg>
              <div style="font-size:12px;margin-top:6px;font-weight:600"><?= htmlspecialchars($cb) ?></div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <div style="margin-top:14px;display:flex;gap:8px">
          <button class="btn-primary-brand" onclick="printBarcodes()"><i class="bi bi-printer me-1"></i> Imprimir etiquetas</button>
        </div>
      </div>
      <?php endif; ?>
      <?php endif; ?>

      <div class="page-header" style="margin-top:<?= isset($_GET['ok']) ? '16px' : '0' ?>">
        <h2><i class="bi bi-plus-circle me-2" style="color:var(--c-primary)"></i>Nuevo Producto</h2>
        <a href="../productos/main_productos.php" class="btn-outline-brand"><i class="bi bi-arrow-left me-1"></i> Volver</a>
      </div>

      <form method="POST" action="<?= BASE_PATH ?>handlers/save_producto.php" id="formProducto">
        <div class="card-panel">
          <div class="card-panel-header">
            <h3><i class="bi bi-box-seam me-2" style="color:var(--c-primary)"></i>Datos del producto</h3>
          </div>
          <div style="padding:20px 24px">

            <div class="form-section-title"><i class="bi bi-tag me-1"></i>Clasificación</div>
            <div class="row g-3 mb-3">
              <div class="col-md-6">
                <label class="form-label">Categoría <span class="text-danger">*</span></label>
                <select name="categoria_id" id="categoria_id" class="form-select" required onchange="updateTipoBadge(this)">
                  <option value="">— Seleccionar categoría —</option>
                  <?php foreach ($categorias as $cat): ?>
                  <option value="<?= $cat['id'] ?>" data-tipo="<?= htmlspecialchars($cat['tipo'] ?? '') ?>">
                    <?= htmlspecialchars($cat['nombre']) ?>
                  </option>
                  <?php endforeach; ?>
                </select>
                <div id="tipoBadge"></div>
              </div>
              <div class="col-md-6">
                <label class="form-label">Unidad de medida <span class="text-danger">*</span></label>
                <select name="unidad_medida_id" class="form-select" required>
                  <option value="">— Seleccionar —</option>
                  <?php foreach ($unidades as $u): ?>
                  <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['nombre']) ?> (<?= htmlspecialchars($u['abreviatura']) ?>)</option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>

            <div class="form-section-title"><i class="bi bi-info-circle me-1"></i>Descripción</div>
            <div class="row g-3 mb-3">
              <div class="col-md-12">
                <label class="form-label">Descripción <span class="text-danger">*</span></label>
                <input type="text" name="descripcion" class="form-control" placeholder="Descripción completa del producto" required maxlength="255">
              </div>
              <div class="col-md-6">
                <label class="form-label">Marca</label>
                <input type="text" name="marca" class="form-control" placeholder="Marca del producto" maxlength="100">
              </div>
              <div class="col-md-6">
                <label class="form-label">Modelo</label>
                <input type="text" name="modelo" class="form-control" placeholder="Modelo / referencia" maxlength="100">
              </div>
            </div>

            <div class="form-section-title"><i class="bi bi-graph-up me-1"></i>Inventario y costo</div>
            <div class="row g-3 mb-3">
              <div class="col-md-4">
                <label class="form-label">Cantidad inicial</label>
                <input type="number" name="cantidad" class="form-control" min="1" value="1">
                <small class="text-muted">Activos: se crean N registros individuales. Consumibles: se suma al stock.</small>
              </div>
              <div class="col-md-4">
                <label class="form-label">Stock mínimo</label>
                <input type="number" name="stock_minimo" class="form-control" min="0" value="0">
              </div>
              <div class="col-md-4">
                <label class="form-label">Costo promedio</label>
                <div class="input-group">
                  <span class="input-group-text">$</span>
                  <input type="number" name="costo_promedio" class="form-control" min="0" step="0.01" value="0.00">
                </div>
              </div>
            </div>

            <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:20px">
              <a href="../productos/main_productos.php" class="btn-outline-brand"><i class="bi bi-x-lg me-1"></i> Cancelar</a>
              <button type="submit" class="btn-primary-brand"><i class="bi bi-save me-1"></i> Guardar Producto</button>
            </div>
          </div>
        </div>
      </form>
    </main>
  </div>
</div>

<script src="../js/bootstrap.bundle.min.js"></script>
<script>
  function toggleSidebar(){const s=document.getElementById('sidebar'),o=document.getElementById('sidebarOverlay'),w=document.getElementById('mainWrapper');if(window.innerWidth<=1100){const open=s.classList.toggle('open');o.classList.toggle('visible',open);}else{s.classList.toggle('collapsed');w.classList.toggle('sidebar-hidden');}}
  function closeSidebar(){document.getElementById('sidebar').classList.remove('open');document.getElementById('sidebarOverlay').classList.remove('visible');}

  function updateTipoBadge(sel) {
    const opt = sel.options[sel.selectedIndex];
    const tipo = opt ? opt.dataset.tipo : '';
    const badge = document.getElementById('tipoBadge');
    if (!tipo) { badge.innerHTML = ''; return; }
    const label = tipo === 'activo' ? 'Activo Fijo' : 'Consumible';
    badge.innerHTML = `<span class="tipo-badge ${tipo}">${label}</span>`;
  }

  // Render barcodes after load
  document.addEventListener('DOMContentLoaded', function() {
    if (typeof JsBarcode !== 'undefined') {
      document.querySelectorAll('.barcode-svg[data-code]').forEach(function(el) {
        try {
          JsBarcode(el, el.dataset.code, {format:'CODE128',width:2,height:50,displayValue:false});
        } catch(e) { console.error('Barcode error', e); }
      });
    }
  });

  function printBarcodes() {
    const section = document.getElementById('barcodeSection');
    if (!section) return;
    const w = window.open('', '_blank');
    w.document.write(`<!doctype html><html><head><meta charset="utf-8"><title>Etiquetas</title>
      <style>body{font-family:Arial,sans-serif;margin:10px}.grid{display:flex;flex-wrap:wrap;gap:10px}.card{border:1px solid #ccc;padding:10px;text-align:center;width:160px;border-radius:6px}svg{max-width:100%}p{margin:4px 0;font-size:11px;font-weight:600}@media print{button{display:none}}</style>
      </head><body><div class="grid">`);
    document.querySelectorAll('.barcode-svg[data-code]').forEach(function(el) {
      const code = el.dataset.code;
      w.document.write(`<div class="card"><svg id="bc_${code.replace(/[^a-z0-9]/gi,'_')}"></svg><p>${code}</p></div>`);
    });
    w.document.write(`</div><script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"><\/script>
      <script>document.addEventListener('DOMContentLoaded',function(){document.querySelectorAll('svg[id^="bc_"]').forEach(function(el){var id=el.id;var code=id.replace('bc_','').replace(/_/g,'');try{JsBarcode(el,el.closest('.card').querySelector('p').textContent,{format:'CODE128',width:2,height:50,displayValue:false});}catch(e){}});setTimeout(function(){window.print();},600);});<\/script>
      </body></html>`);
    w.document.close();
  }
</script>
</body>
</html>
