<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
$unidades   = getUnidadesMedida($pdo);
$categorias = getCategoriasInventario($pdo);
$productos = getProductos($pdo);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Vale por Material — AlmacenaTec</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="../css/bootstrap.min.css">
  <link rel="stylesheet" href="../css/style.css">
  <style>
    .form-section-title {
      font-size: 11px; font-weight: 700; text-transform: uppercase;
      letter-spacing: 1px; color: var(--c-text-muted);
      padding: 18px 0 8px; border-bottom: 1px solid var(--c-border);
      margin-bottom: 16px;
    }
    .form-section-title i { color: var(--c-primary); }
    .vale-header {
      background: linear-gradient(135deg, var(--c-primary), var(--c-primary-dk));
      color: #fff;
      padding: 20px 24px;
      border-radius: var(--radius-lg) var(--radius-lg) 0 0;
    }
    .vale-header h3 {
      color: #fff;
      margin: 0;
      font-weight: 700;
    }
    .vale-header small {
      color: rgba(255,255,255,.7);
    }
    .vale-number {
      font-size: 14px;
      font-weight: 600;
      color: var(--c-gold);
    }
    .detalle-row {
      background: #f8faff;
      padding: 12px;
      border-radius: var(--radius-sm);
      margin-bottom: 8px;
      border: 1px solid var(--c-border);
    }
    .detalle-row .btn-remove {
      color: var(--c-accent);
      background: none;
      border: none;
      cursor: pointer;
    }
    .producto-info {
      font-size: 12px;
      color: var(--c-text-muted);
    }
    .total-section {
      background: #f0f4f9;
      padding: 16px 20px;
      border-radius: var(--radius-sm);
      margin-top: 16px;
    }
    .total-section .total-label {
      font-weight: 600;
      font-size: 16px;
    }
    .total-section .total-amount {
      font-size: 24px;
      font-weight: 800;
      color: var(--c-primary);
    }
    .vale-footer {
      border-top: 2px dashed var(--c-border);
      padding-top: 16px;
      margin-top: 16px;
      display: grid;
      grid-template-columns: 1fr 1fr 1fr;
      gap: 16px;
    }
    .vale-footer .field-group label {
      font-size: 11px;
      font-weight: 600;
      color: var(--c-text-muted);
      text-transform: uppercase;
      letter-spacing: .5px;
      display: block;
      margin-bottom: 4px;
    }
    .vale-footer .field-group .field-value {
      font-weight: 600;
      font-size: 14px;
      border-bottom: 1px solid var(--c-border);
      padding-bottom: 4px;
    }
    @media print {
      .no-print { display: none !important; }
      .vale-header { background: var(--c-primary) !important; -webkit-print-color-adjust: exact; }
      .total-section { background: #f0f4f9 !important; -webkit-print-color-adjust: exact; }
    }
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
        <h1>Vale por Material</h1>
        <div class="topbar-breadcrumb">
          <a href="../home/index.php">Inicio</a> &rsaquo;
          <a href="../productos/main_productos.php">Productos</a> &rsaquo; Vale por Material
        </div>
      </div>
      <div style="display:flex;align-items:center;gap:10px;flex-shrink:0">
        <span style="font-size:13px;color:var(--c-text-md)"><i class="bi bi-person-circle me-1"></i><?= htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Usuario') ?></span>
        <a href="<?= BASE_PATH ?>logout.php" class="btn-accent-brand" style="padding:6px 12px;font-size:12px"><i class="bi bi-box-arrow-right me-1"></i> Salir</a>
      </div>
    </header>

    <main class="content">

      <?php if (isset($_GET['ok'])): ?>
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> Vale generado correctamente.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
      <?php endif; ?>

      <div class="page-header">
        <h2><i class="bi bi-file-earmark-text me-2" style="color:var(--c-accent)"></i>Vale por Material</h2>
        <div style="display:flex;gap:8px;flex-wrap:wrap" class="no-print">
          <a href="../productos/main_productos.php" class="btn-outline-brand"><i class="bi bi-arrow-left me-1"></i> Volver</a>
        </div>
      </div>

      <!-- Formulario del Vale -->
      <div class="card-panel">
        <div class="vale-header">
          <h3><i class="bi bi-file-earmark-text me-2"></i>VALE POR MATERIAL</h3>
          <small>TecNM Campus Saltillo</small>
        </div>
        
        <div style="padding: 24px;">
          <form method="POST" action="<?= BASE_PATH ?>handlers/save_vale.php" id="formVale">
            
            <!-- Datos del vale -->
            <div class="row g-3 mb-4">
              <div class="col-md-3">
                <label class="form-label">Fecha <span class="text-danger">*</span></label>
                <input type="date" name="fecha" class="form-control" value="<?= date('Y-m-d') ?>" required>
              </div>
              <div class="col-md-4">
                <label class="form-label">Solicita <span class="text-danger">*</span></label>
                <input type="text" name="solicita" class="form-control" placeholder="Nombre de quien solicita" required>
              </div>
              <div class="col-md-5">
                <label class="form-label">Área donde se va a utilizar <span class="text-danger">*</span></label>
                <input type="text" name="area_uso" class="form-control" placeholder="Departamento o área" required>
              </div>
            </div>

            <div class="row g-3 mb-4">
              <div class="col-md-6">
                <label class="form-label">Descripción <span class="text-danger">*</span></label>
                <textarea name="descripcion" class="form-control" rows="2" placeholder="Descripción del material solicitado" required></textarea>
              </div>
              <div class="col-md-6">
                <label class="form-label">Autoriza <span class="text-danger">*</span></label>
                <input type="text" name="autoriza" class="form-control" placeholder="Nombre de quien autoriza" required>
              </div>
            </div>

            <!-- Detalles de productos -->
            <div class="form-section-title"><i class="bi bi-list-ul me-1"></i>Detalles del material</div>
            
            <div id="detallesContainer">
              <div class="detalle-row" data-index="0">
                <div class="row g-2">
                  <div class="col-md-5">
                    <label class="form-label">Producto</label>
                    <select name="detalle_producto_id[]" class="form-select producto-select" required>
                      <option value="">— Seleccionar —</option>
                      <?php foreach ($productos as $p): ?>
                      <option value="<?= $p['id'] ?>">
                        <?= htmlspecialchars($p['codigo_barras'] ?? '') ?> - <?= htmlspecialchars($p['descripcion']) ?>
                      </option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div class="col-md-2">
                    <label class="form-label">Cantidad</label>
                    <input type="number" name="detalle_cantidad[]" class="form-control" min="1" value="1" required>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label">Descripción adicional</label>
                    <input type="text" name="detalle_descripcion[]" class="form-control" placeholder="Detalle específico">
                  </div>
                  <div class="col-md-1" style="display:flex;align-items:flex-end;padding-bottom:4px">
                    <button type="button" class="btn-tbl-danger btn-remove-detalle" style="width:100%" onclick="removeDetalle(this)"><i class="bi bi-trash"></i></button>
                  </div>
                </div>
              </div>
            </div>

            <div class="mt-2 no-print">
              <button type="button" class="btn-outline-brand" onclick="addDetalle()"><i class="bi bi-plus-lg me-1"></i> Agregar producto</button>
            </div>

            <!-- Área de firmas -->
            <div class="vale-footer">
              <div class="field-group">
                <label>Quien solicita</label>
                <div class="field-value" id="solicitaDisplay">—</div>
              </div>
              <div class="field-group">
                <label>Autoriza</label>
                <div class="field-value" id="autorizaDisplay">—</div>
              </div>
              <div class="field-group">
                <label>Fecha</label>
                <div class="field-value" id="fechaDisplay">—</div>
              </div>
            </div>

            <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:20px" class="no-print">
              <a href="../productos/main_productos.php" class="btn-outline-brand"><i class="bi bi-x-lg me-1"></i> Cancelar</a>
              <button type="submit" class="btn-accent-brand"><i class="bi bi-file-earmark-text me-1"></i> Generar Vale</button>
            </div>
          </form>
        </div>
      </div>
    </main>
  </div>
</div>

<script src="../js/bootstrap.bundle.min.js"></script>
<script>
  function toggleSidebar(){
    const s=document.getElementById('sidebar'),o=document.getElementById('sidebarOverlay'),w=document.getElementById('mainWrapper');
    if(window.innerWidth<=1100){
      const open=s.classList.toggle('open');
      o.classList.toggle('visible',open);
    }else{
      s.classList.toggle('collapsed');
      w.classList.toggle('sidebar-hidden');
    }
  }
  function closeSidebar(){
    document.getElementById('sidebar').classList.remove('open');
    document.getElementById('sidebarOverlay').classList.remove('visible');
  }

  function addDetalle() {
    const container = document.getElementById('detallesContainer');
    const index = container.children.length;
    const template = container.children[0].cloneNode(true);
    template.dataset.index = index;
    
    // Limpiar valores
    template.querySelectorAll('input, select').forEach(el => {
      if (el.type === 'number') el.value = '1';
      else if (el.type === 'text') el.value = '';
      else if (el.tagName === 'SELECT') el.selectedIndex = 0;
    });
    
    // Actualizar labels
    template.querySelectorAll('.form-label').forEach(label => {
      const text = label.textContent.trim();
      label.textContent = text;
    });
    
    container.appendChild(template);
  }

  function removeDetalle(btn) {
    const container = document.getElementById('detallesContainer');
    if (container.children.length <= 1) {
      alert('Debe haber al menos un detalle.');
      return;
    }
    btn.closest('.detalle-row').remove();
  }

  // Actualizar display de firmas
  document.querySelectorAll('input[name="solicita"], input[name="autoriza"], input[name="fecha"]').forEach(input => {
    input.addEventListener('input', function() {
      const id = this.name + 'Display';
      const display = document.getElementById(id);
      if (display) {
        display.textContent = this.value || '—';
      }
    });
  });

  // Inicializar display
  document.addEventListener('DOMContentLoaded', function() {
    const fechaInput = document.querySelector('input[name="fecha"]');
    if (fechaInput) {
      const display = document.getElementById('fechaDisplay');
      if (display) display.textContent = fechaInput.value || '—';
    }
  });
</script>
</body>
</html>