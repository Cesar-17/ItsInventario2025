<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';


$rol = $_SESSION['usuario_rol'] ?? 'usuario';
if ($rol !== 'admin') {
    header('Location: ' . BASE_PATH . 'home/index.php?error=acceso_denegado');
    exit;
}

$departamentos = getDepartamentos($pdo);
$unidades      = getUnidadesMedida($pdo);
$productos     = getProductos($pdo);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Nueva Salida — Activo Fijo</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="../css/bootstrap.min.css">
  <link rel="stylesheet" href="../css/style.css">
  <style>
    /* ============================================================
       PALETA DE COLORES LOGIN
       ============================================================ */
    :root {
      --c-primary: #0B2D6B;
      --c-primary-dk: #071d46;
      --c-gold: #c9973a;
      --c-accent: #B81C2E;
      --c-bg: #F0F4F9;
      --c-surface: #FFFFFF;
      --c-border: #E2E8F0;
      --c-text: #1E293B;
      --c-text-md: #475569;
      --c-text-muted: #94A3B8;
      --radius-sm: 6px;
      --radius: 10px;
    }

    /* ── Título de sección ── */
    .form-section-title {
      font-size: 11px; font-weight: 700; text-transform: uppercase;
      letter-spacing: 1px; color: var(--c-text-muted);
      padding: 18px 0 8px; border-bottom: 1px solid var(--c-border);
      margin-bottom: 16px;
    }
    .form-section-title i { color: var(--c-primary); }

    /* ── Tabla de detalles ── */
    .detalles-table thead th {
      background: #0B2D6B !important;
      color: #fff !important;
      font-size: 11.5px !important;
      padding: 10px 12px !important;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    .detalles-table thead th i {
      color: #c9973a;
      margin-right: 6px;
    }
    .detalles-table td {
      vertical-align: middle; padding: 9px 12px !important;
      border-bottom: 1px solid #E2E8F0;
    }
    .detalles-table tbody tr:hover {
      background: #F0F4F9;
    }

    /* ── Barra de total ── */
    .total-bar {
      background: #0B2D6B;
      color: #fff;
      padding: 12px 20px;
      border-radius: 8px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-top: 12px;
      font-weight: 600;
    }
    .total-bar span { font-size: 20px; }
    .total-bar .label { font-size: 16px; }

    /* ── Card header ── */
    .card-header-almacenatec {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 16px 20px;
      background: #FFFFFF;
      border-bottom: 1px solid #E2E8F0;
      flex-wrap: wrap;
      gap: 12px;
    }
    .card-header-almacenatec h3 {
      font-family: 'Poppins', sans-serif;
      font-size: 15px;
      font-weight: 700;
      color: #0B2D6B;
      margin: 0;
    }
    .card-header-almacenatec h3 i {
      color: #c9973a;
      margin-right: 8px;
    }

    /* ── Jefe chip ── */
    .jefe-chip {
      background: rgba(11,45,107,.08);
      border-radius: 20px;
      padding: 6px 14px;
      font-size: 13px;
      color: #0B2D6B;
      display: none;
      margin-top: 8px;
    }

    /* ── Campos de formulario ── */
    .form-control:focus, .form-select:focus {
      border-color: #0B2D6B !important;
      box-shadow: 0 0 0 3px rgba(11,45,107,.12) !important;
    }

    /* ── Botones ── */
    .btn-primary-brand {
      background: #0B2D6B;
      color: #fff !important;
      border: none;
      padding: 9px 18px;
      border-radius: 6px;
      font-size: 13.5px;
      font-weight: 600;
      font-family: 'Poppins', sans-serif;
      cursor: pointer;
      transition: all .2s;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 6px;
    }
    .btn-primary-brand:hover {
      background: #071d46;
      color: #fff;
      transform: translateY(-1px);
      box-shadow: 0 4px 14px rgba(11,45,107,.35);
    }

    .btn-outline-brand {
      background: transparent;
      color: #0B2D6B !important;
      border: 1.5px solid #0B2D6B;
      padding: 8px 18px;
      border-radius: 6px;
      font-size: 13.5px;
      font-weight: 600;
      font-family: 'Poppins', sans-serif;
      cursor: pointer;
      transition: all .2s;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 6px;
    }
    .btn-outline-brand:hover {
      background: #0B2D6B;
      color: #fff !important;
    }

    .btn-accent-brand {
      background: #B81C2E;
      color: #fff !important;
      border: none;
      padding: 6px 12px;
      border-radius: 6px;
      font-size: 12px;
      font-weight: 600;
      cursor: pointer;
      transition: all .2s;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 4px;
    }
    .btn-accent-brand:hover {
      background: #8f1522;
      color: #fff;
    }

    /* ── Fila de agregar producto ── */
    .add-producto-row {
      background: #F0F4F9;
      padding: 14px;
      border-radius: 8px;
      border: 1px solid #E2E8F0;
    }

    /* ── Título de página ── */
    .tabla-titulo {
      font-family: 'Poppins', sans-serif;
      font-size: 18px;
      font-weight: 700;
      color: #0B2D6B;
      margin-bottom: 16px;
      display: flex;
      align-items: center;
      gap: 10px;
      padding-bottom: 10px;
      border-bottom: 3px solid #c9973a;
    }
    .tabla-titulo i {
      color: #c9973a;
      font-size: 22px;
    }
    .tabla-titulo small {
      font-size: 13px;
      font-weight: 400;
      color: #475569;
      margin-left: 8px;
    }

    /* ── Error ── */
    .form-error {
      color: #856404;
      background: #fff3cd;
      padding: 8px 12px;
      border-radius: 6px;
      margin-top: 12px;
      display: none;
      font-size: 13px;
      border-left: 4px solid #d97706;
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
    <div class="sidebar-footer"><i class="bi bi-geo-alt-fill me-1"></i> TecNM · Campus Saltillo · 2025</div>
  </aside>

  <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

  <div class="main-wrapper" id="mainWrapper">
    <header class="topbar">
      <button class="topbar-toggle" onclick="toggleSidebar()"><i class="bi bi-list" style="font-size:22px"></i></button>
      <div class="topbar-info">
        <h1>Nueva Salida</h1>
        <div class="topbar-breadcrumb">
          <a href="../home/index.php">Inicio</a> &rsaquo;
          <a href="../activo_fijo/main_activF.php">Activo Fijo</a> &rsaquo; Nueva Salida
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
        <i class="bi bi-check-circle-fill me-2"></i> Salida registrada correctamente.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
      <?php endif; ?>

      <div class="page-header">
        <h2><i class="bi bi-plus-circle me-2" style="color:#0B2D6B"></i>Registrar Salida de Activo Fijo</h2>
        <a href="../activo_fijo/main_activF.php" class="btn-outline-brand"><i class="bi bi-arrow-left me-1"></i> Volver</a>
      </div>

      <!-- Título de la tabla -->
      <div class="tabla-titulo">
        <i class="bi bi-file-earmark-text"></i>
        Nueva Salida de Activo Fijo
        <small>Registro de salida de activos del inventario</small>
      </div>

      <form method="POST" action="<?= BASE_PATH ?>handlers/save_activo.php" id="formSalida">
        <div class="card-panel">
          <div class="card-header-almacenatec">
            <h3><i class="bi bi-file-earmark-text"></i>Datos generales</h3>
          </div>
          <div style="padding:20px 24px">

            <div class="form-section-title"><i class="bi bi-info-circle me-1"></i>Identificación</div>
            <div class="row g-3 mb-3">
              <div class="col-md-4">
                <label class="form-label">Folio <small class="text-muted">(se genera automáticamente)</small></label>
                <input type="text" name="folio" id="folio" class="form-control" placeholder="Dejar vacío para auto-generar">
                <div id="folioFeedback" style="font-size:12px;margin-top:4px"></div>
              </div>
              <div class="col-md-4">
                <label class="form-label">Fecha <span class="text-danger">*</span></label>
                <input type="date" name="fecha" id="fecha" class="form-control" required>
              </div>
              <div class="col-md-4">
                <label class="form-label">Factura / Referencia</label>
                <input type="text" name="factura" class="form-control" placeholder="Ej. FAC-2025-001">
              </div>
            </div>

            <div class="form-section-title"><i class="bi bi-building me-1"></i>Destino</div>
            <div class="row g-3 mb-3">
              <div class="col-md-4">
                <label class="form-label">Departamento <span class="text-danger">*</span></label>
                <select name="departamento_id" id="departamento_id" class="form-select" required onchange="loadJefe(this.value)">
                  <option value="">— Seleccionar —</option>
                  <?php foreach ($departamentos as $dep): ?>
                  <option value="<?= $dep['id'] ?>"><?= htmlspecialchars($dep['nombre']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-4">
                <label class="form-label">Jefe de Departamento</label>
                <input type="hidden" name="jefe_id" id="jefe_id">
                <input type="text" id="jefe_nombre_display" class="form-control" placeholder="Se carga al seleccionar departamento" readonly style="background:#f8faff">
              </div>
              <div class="col-md-4">
                <label class="form-label">Proveedor <span class="text-danger">*</span></label>
                <input type="text" name="proveedor" class="form-control" placeholder="Nombre del proveedor" required>
              </div>
            </div>
            <div class="row g-3 mb-3">
              <div class="col-md-6">
                <label class="form-label">Proyecto</label>
                <input type="text" name="proyecto" class="form-control" placeholder="Nombre del proyecto (opcional)">
              </div>
              <div class="col-md-6">
                <label class="form-label">Observaciones</label>
                <input type="text" name="descripcion" class="form-control" placeholder="Notas adicionales (opcional)">
              </div>
            </div>

            <div class="form-section-title"><i class="bi bi-list-ul me-1"></i>Detalles de salida</div>

            <!-- Agregar producto -->
            <div class="row g-2 align-items-end mb-3 add-producto-row">
              <div class="col-md-3">
                <label class="form-label" style="font-size:12px;font-weight:600;color:#475569;">Producto</label>
                <select id="sel_producto" class="form-select form-select-sm">
                  <option value="">— Seleccionar —</option>
                  <?php foreach ($productos as $p): ?>
                  <option value="<?= $p['id'] ?>" data-desc="<?= htmlspecialchars($p['descripcion']) ?>" data-code="<?= htmlspecialchars($p['codigo_barras'] ?? '') ?>">
                    <?= htmlspecialchars(($p['codigo_barras'] ? $p['codigo_barras'].' — ' : '').$p['descripcion']) ?>
                  </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-3">
                <label class="form-label" style="font-size:12px;font-weight:600;color:#475569;">Descripción adicional</label>
                <input type="text" id="sel_desc" class="form-control form-control-sm" placeholder="(opcional)">
              </div>
              <div class="col-md-1">
                <label class="form-label" style="font-size:12px;font-weight:600;color:#475569;">Cantidad</label>
                <input type="number" id="sel_cant" class="form-control form-control-sm" min="0.01" step="0.01" value="1">
              </div>
              <div class="col-md-2">
                <label class="form-label" style="font-size:12px;font-weight:600;color:#475569;">Unidad</label>
                <select id="sel_unidad" class="form-select form-select-sm">
                  <option value="">— —</option>
                  <?php foreach ($unidades as $u): ?>
                  <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['nombre']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-2">
                <label class="form-label" style="font-size:12px;font-weight:600;color:#475569;">Precio unitario</label>
                <input type="number" id="sel_precio" class="form-control form-control-sm" min="0" step="0.01" value="0" placeholder="0.00">
              </div>
              <div class="col-md-1">
                <button type="button" class="btn-primary-brand w-100" onclick="addDetalle()" style="padding:7px 0;font-size:13px;"><i class="bi bi-plus-lg"></i></button>
              </div>
            </div>

            <!-- Tabla de detalles -->
            <div class="table-wrap" id="detallesWrap" style="display:none">
              <table class="table table-striped table-sm detalles-table mb-0">
                <thead>
                  <tr>
                    <th><i class="bi bi-hash"></i> #</th>
                    <th><i class="bi bi-box"></i> Producto</th>
                    <th><i class="bi bi-file-text"></i> Descripción</th>
                    <th><i class="bi bi-sort-numeric-up"></i> Cantidad</th>
                    <th><i class="bi bi-rulers"></i> Unidad</th>
                    <th><i class="bi bi-tag"></i> Precio Unit.</th>
                    <th><i class="bi bi-cash"></i> Importe</th>
                    <th></th>
                  </tr>
                </thead>
                <tbody id="detallesTbody"></tbody>
              </table>
            </div>

            <div class="total-bar" id="totalBar" style="display:none">
              <span class="label"><i class="bi bi-cash-stack me-2"></i>Total</span>
              <span id="totalDisplay">$ 0.00</span>
            </div>

            <!-- Hidden inputs para detalles -->
            <div id="detallesHidden"></div>

            <div class="form-error" id="formError"></div>

            <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:20px">
              <a href="../activo_fijo/main_activF.php" class="btn-outline-brand"><i class="bi bi-x-lg me-1"></i> Cancelar</a>
              <button type="submit" class="btn-primary-brand"><i class="bi bi-save me-1"></i> Guardar Salida</button>
            </div>
          </div>
        </div>
      </form>
    </main>
  </div>
</div>

<script src="../js/bootstrap.bundle.min.js"></script>
<script>
  // Sidebar
  function toggleSidebar(){const s=document.getElementById('sidebar'),o=document.getElementById('sidebarOverlay'),w=document.getElementById('mainWrapper');if(window.innerWidth<=1100){const open=s.classList.toggle('open');o.classList.toggle('visible',open);}else{s.classList.toggle('collapsed');w.classList.toggle('sidebar-hidden');}}
  function closeSidebar(){document.getElementById('sidebar').classList.remove('open');document.getElementById('sidebarOverlay').classList.remove('visible');}

  // Set today's date
  document.getElementById('fecha').valueAsDate = new Date();

  // Jefe autofill
  async function loadJefe(depId) {
    const inp = document.getElementById('jefe_nombre_display');
    const hid = document.getElementById('jefe_id');
    if (!depId) { inp.value = ''; hid.value = ''; return; }
    try {
      const res = await fetch('<?= BASE_PATH ?>includes/get_jefe_por_departamento.php?departamento_id=' + depId);
      const data = await res.json();
      if (data.ok && data.jefe) {
        inp.value = data.jefe.nombre;
        hid.value = data.jefe.id;
      } else {
        inp.value = 'Sin jefe registrado';
        hid.value = '';
      }
    } catch(e) { inp.value = ''; hid.value = ''; }
  }

  // Folio duplicate check
  let folioTimer;
  document.getElementById('folio').addEventListener('input', function() {
    clearTimeout(folioTimer);
    const v = this.value.trim();
    const fb = document.getElementById('folioFeedback');
    if (!v) { fb.textContent = ''; return; }
    folioTimer = setTimeout(async () => {
      try {
        const res = await fetch('<?= BASE_PATH ?>includes/check_duplicate.php?tabla=salidas_inventario&campo=folio&valor=' + encodeURIComponent(v));
        const data = await res.json();
        if (data.exists) {
          fb.style.color = '#dc3545';
          fb.textContent = 'Este folio ya existe — se generará uno nuevo al guardar.';
        } else {
          fb.style.color = '#16a34a';
          fb.textContent = 'Folio disponible.';
        }
      } catch(e) { fb.textContent = ''; }
    }, 500);
  });

  // Detalles
  const detalles = [];

  function addDetalle() {
    const selProd = document.getElementById('sel_producto');
    const opt = selProd.options[selProd.selectedIndex];
    const prodId = selProd.value;
    const prodLabel = opt && opt.value ? opt.text : '';
    const desc = document.getElementById('sel_desc').value.trim();
    const cant = parseFloat(document.getElementById('sel_cant').value) || 0;
    const selUnidad = document.getElementById('sel_unidad');
    const unidadId = selUnidad.value;
    const unidadLabel = selUnidad.options[selUnidad.selectedIndex]?.text || '';
    const precio = parseFloat(document.getElementById('sel_precio').value) || 0;

    if (!prodId) { showError('Selecciona un producto.'); return; }
    if (!unidadId) { showError('Selecciona una unidad de medida.'); return; }
    if (cant <= 0) { showError('La cantidad debe ser mayor a 0.'); return; }

    hideError();
    detalles.push({
      producto_id: prodId,
      producto_label: prodLabel,
      descripcion: desc,
      cantidad: cant,
      unidad_medida_id: unidadId,
      unidad_label: unidadLabel,
      precio_unitario: precio,
      importe: cant * precio
    });

    // Reset inputs
    selProd.value = '';
    document.getElementById('sel_desc').value = '';
    document.getElementById('sel_cant').value = '1';
    selUnidad.value = '';
    document.getElementById('sel_precio').value = '0';

    renderDetalles();
  }

  function removeDetalle(i) {
    detalles.splice(i, 1);
    renderDetalles();
  }

  function renderDetalles() {
    const tbody = document.getElementById('detallesTbody');
    const wrap = document.getElementById('detallesWrap');
    const totalBar = document.getElementById('totalBar');
    const hidden = document.getElementById('detallesHidden');

    tbody.innerHTML = '';
    hidden.innerHTML = '';

    if (detalles.length === 0) {
      wrap.style.display = 'none';
      totalBar.style.display = 'none';
      return;
    }

    wrap.style.display = '';
    totalBar.style.display = '';

    let total = 0;
    detalles.forEach((d, i) => {
      total += d.importe;
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td>${i + 1}</td>
        <td style="font-size:12px">${escHtml(d.producto_label)}</td>
        <td style="font-size:12px">${escHtml(d.descripcion)}</td>
        <td>${d.cantidad}</td>
        <td>${escHtml(d.unidad_label)}</td>
        <td>$ ${d.precio_unitario.toFixed(2)}</td>
        <td><strong>$ ${d.importe.toFixed(2)}</strong></td>
        <td><button type="button" onclick="removeDetalle(${i})" style="background:none;border:none;color:#B81C2E;cursor:pointer;font-size:16px;transition:color .2s;" onmouseover="this.style.color='#8f1522'" onmouseout="this.style.color='#B81C2E'"><i class="bi bi-trash"></i></button></td>
      `;
      tbody.appendChild(tr);

      // Hidden inputs
      hidden.innerHTML += `
        <input type="hidden" name="detalle_producto_id[]" value="${escAttr(d.producto_id)}">
        <input type="hidden" name="detalle_descripcion[]" value="${escAttr(d.descripcion)}">
        <input type="hidden" name="detalle_cantidad[]" value="${escAttr(d.cantidad)}">
        <input type="hidden" name="detalle_unidad_medida_id[]" value="${escAttr(d.unidad_medida_id)}">
        <input type="hidden" name="detalle_precio_unitario[]" value="${escAttr(d.precio_unitario)}">
        <input type="hidden" name="detalle_importe[]" value="${escAttr(d.importe)}">
      `;
    });

    document.getElementById('totalDisplay').textContent = '$ ' + total.toFixed(2);
  }

  function showError(msg) {
    const el = document.getElementById('formError');
    el.textContent = msg;
    el.style.display = 'block';
  }
  function hideError() {
    document.getElementById('formError').style.display = 'none';
  }
  function escHtml(s) { return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
  function escAttr(s) { return String(s).replace(/"/g,'&quot;'); }

  // Form submit validation
  document.getElementById('formSalida').addEventListener('submit', function(e) {
    if (detalles.length === 0) {
      e.preventDefault();
      showError('Agrega al menos un producto al detalle de la salida.');
    }
  });
</script>
</body>
</html>