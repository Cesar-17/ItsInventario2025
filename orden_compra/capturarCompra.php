<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
$departamentos = getDepartamentos($pdo);
$unidades      = getUnidadesMedida($pdo);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Nueva Orden de Compra — AlmacenaTec</title>
  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <!-- Bootstrap + Custom CSS -->
  <link rel="stylesheet" href="../css/bootstrap.min.css">
  <link rel="stylesheet" href="../css/bootstrap-grid.min.css">
  <link rel="stylesheet" href="../css/style.css">
  <style>
    .form-section-title {
      font-size: 11px; font-weight: 700; text-transform: uppercase;
      letter-spacing: 1px; color: var(--c-text-muted);
      padding: 18px 0 8px; border-bottom: 1px solid var(--c-border);
      margin-bottom: 16px;
    }
    .form-section-title i { color: var(--c-primary); }

    .detalles-table thead th {
      background: var(--c-primary) !important;
      color: #fff !important; font-size: 11.5px !important;
      padding: 10px 12px !important;
    }
    .detalles-table td { vertical-align: middle; padding: 9px 12px !important; }

    /* Total en tiempo real */
    .total-bar {
      background: #f0f5ff;
      border: 1.5px solid rgba(11,45,107,.15);
      border-radius: var(--radius);
      padding: 12px 20px;
      display: flex; align-items: center; justify-content: space-between;
      margin-bottom: 16px;
    }
    .total-bar-label { font-size: 13px; color: var(--c-text-muted); font-weight: 600; }
    .total-bar-value { font-size: 22px; font-weight: 800; color: var(--c-primary); font-family: var(--font-brand); }
  </style>
</head>
<body>

<div class="app-layout">

  <!-- ═══════════════ SIDEBAR ═══════════════ -->
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

  <!-- ═══════════════ MAIN ═══════════════ -->
  <div class="main-wrapper" id="mainWrapper">

    <header class="topbar">
      <button class="topbar-toggle" onclick="toggleSidebar()"><i class="bi bi-list" style="font-size:22px"></i></button>
      <div class="topbar-info">
        <h1>Nueva Orden de Compra</h1>
        <div class="topbar-breadcrumb">
          <a href="../home/index.php">Inicio</a> &rsaquo;
          <a href="../orden_compra/main_ordenC.php">Orden de Compra</a> &rsaquo;
          Capturar Orden
        </div>
      </div>
      <a href="../orden_compra/main_ordenC.php" class="btn-outline-brand" style="padding:7px 14px;font-size:13px">
        <i class="bi bi-arrow-left me-1"></i> Volver
      </a>
    </header>

    <main class="content">
      <div style="max-width:960px; margin: 0 auto;">

        <div class="card-panel">
          <div class="card-panel-header">
            <h3><i class="bi bi-cart-plus me-2" style="color:var(--c-primary)"></i>Formulario de Orden de Compra</h3>
          </div>
          <div class="card-panel-body">

            <form method="post" action="<?= BASE_PATH ?>handlers/save_compra.php" id="form-compra">

              <!-- ── Datos generales ── -->
              <div class="form-section-title"><i class="bi bi-info-circle me-1"></i>Datos generales</div>

              <div class="row mb-3">
                <div class="col-md-8">
                  <label class="form-label">Proveedor: <span style="color:var(--c-accent)">*</span></label>
                  <input name="proveedor" type="text" class="form-control" placeholder="Nombre del proveedor">
                </div>
                <div class="col-md-4">
                  <label class="form-label">Fecha: <span style="color:var(--c-accent)">*</span></label>
                  <div class="date-selects">
                    <select id="dia-c" class="form-select select-rojo select-small">
                      <option value="">Día</option>
                      <?php for($i=1;$i<=31;$i++): $v=str_pad($i,2,'0',STR_PAD_LEFT); ?>
                      <option value="<?=$v?>"><?=$v?></option>
                      <?php endfor; ?>
                    </select>
                    <select id="mes-c" class="form-select select-rojo select-small">
                      <option value="">Mes</option>
                      <?php $meses=['01'=>'Ene','02'=>'Feb','03'=>'Mar','04'=>'Abr','05'=>'May','06'=>'Jun','07'=>'Jul','08'=>'Ago','09'=>'Sep','10'=>'Oct','11'=>'Nov','12'=>'Dic'];
                      foreach($meses as $k=>$v): ?><option value="<?=$k?>"><?=$v?></option><?php endforeach; ?>
                    </select>
                    <select id="anio-c" class="form-select select-rojo select-small">
                      <option value="">Año</option>
                      <?php for($y=2024;$y<=2030;$y++): ?><option value="<?=$y?>"><?=$y?></option><?php endfor; ?>
                    </select>
                  </div>
                  <input type="hidden" name="fecha" id="fecha-hidden-c" />
                </div>
              </div>

              <div class="row mb-3">
                <div class="col-md-6">
                  <label class="form-label">Factura o Remisión: <span style="color:var(--c-accent)">*</span></label>
                  <input name="factura" type="text" class="form-control" placeholder="Núm. factura o remisión">
                </div>
                <div class="col-md-6">
                  <label class="form-label">Folio: <span style="color:var(--c-accent)">*</span></label>
                  <input name="folio" type="text" class="form-control" placeholder="Folio de la orden">
                </div>
              </div>

              <div class="row mb-3">
                <div class="col-md-6">
                  <label class="form-label">Proyecto:</label>
                  <input name="proyecto" type="text" class="form-control" placeholder="Nombre del proyecto">
                </div>
                <div class="col-md-6">
                  <label class="form-label">Área:</label>
                  <input name="area" type="text" class="form-control" placeholder="Área solicitante">
                </div>
              </div>

              <!-- ── Departamento ── -->
              <div class="form-section-title"><i class="bi bi-building me-1"></i>Departamento</div>

              <div class="mb-3">
                <label class="form-label">Departamento: <span style="color:var(--c-accent)">*</span></label>
                <select name="departamento_id" class="form-select">
                  <option value="">Selecciona departamento…</option>
                  <?php foreach ($departamentos as $d): ?>
                  <option value="<?= htmlspecialchars($d['id']) ?>"><?= htmlspecialchars($d['nombre']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="mb-3">
                <label class="form-label">Jefe de Departamento (autollenado):</label>
                <input type="text" id="jefe_nombre_c" class="form-control" readonly
                  placeholder="Se autollenará al seleccionar departamento"
                  style="background:#f8faff;cursor:default">
                <input type="hidden" name="jefe_id" id="jefe_id_c">
                <div class="form-text" id="jefe_correo_c" style="font-size:12px;color:var(--c-text-muted)"></div>
              </div>

              <!-- Jefe autollenado JS (debe estar antes de los campos de detalle) -->
              <script>
                (function(){
                  const dept = document.querySelector('select[name="departamento_id"]');
                  if (!dept) return;
                  dept.addEventListener('change', function(){
                    const val = this.value;
                    document.getElementById('jefe_nombre_c').value = '';
                    document.getElementById('jefe_correo_c').textContent = '';
                    document.getElementById('jefe_id_c').value = '';
                    if (!val) return;
                    fetch('<?= BASE_PATH ?>includes/get_jefe_por_departamento.php?departamento_id=' + encodeURIComponent(val))
                      .then(r=>r.json()).then(j=>{
                        if (j&&j.ok&&j.jefe){
                          document.getElementById('jefe_nombre_c').value = j.jefe.nombre;
                          document.getElementById('jefe_correo_c').textContent = j.jefe.correo;
                          document.getElementById('jefe_id_c').value = j.jefe.id;
                        }
                      }).catch(console.error);
                  });
                })();
              </script>

              <!-- ── Descripción general ── -->
              <div class="mb-3">
                <label class="form-label">Descripción / Motivo de compra:</label>
                <textarea name="descripcion" class="form-control" rows="2"
                  placeholder="Descripción del artículo o motivo de la compra…"></textarea>
              </div>

              <!-- ── Productos ── -->
              <div class="form-section-title"><i class="bi bi-bag-plus me-1"></i>Artículos de la orden</div>

              <div class="row mb-3 align-items-end">
                <div class="col-md-3">
                  <label class="form-label">Cantidad:</label>
                  <input name="cantidad" type="number" step="0.01" class="form-control" placeholder="0">
                </div>
                <div class="col-md-3">
                  <label class="form-label">Unidad:</label>
                  <select name="unidad_medida_id" class="form-select">
                    <option value="">Selecciona…</option>
                    <?php foreach ($unidades as $u): ?>
                    <option value="<?= htmlspecialchars($u['id']) ?>"><?= htmlspecialchars($u['nombre']) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="col-md-3">
                  <label class="form-label">Precio unitario:</label>
                  <div style="position:relative">
                    <span style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:var(--c-text-muted);font-weight:600">$</span>
                    <input name="precio_unitario" type="number" step="0.01" class="form-control" placeholder="0.00" style="padding-left:24px">
                  </div>
                </div>
                <div class="col-md-3">
                  <label class="form-label">Importe:</label>
                  <div style="position:relative">
                    <span style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:var(--c-text-muted);font-weight:600">$</span>
                    <input name="importe" id="importe-c" type="number" step="0.01" class="form-control" placeholder="0.00" style="padding-left:24px">
                  </div>
                </div>
              </div>

              <div class="mb-3">
                <button type="button" class="btn-outline-brand" id="btn-add-product">
                  <i class="bi bi-plus-circle me-1"></i> Agregar artículo
                </button>
              </div>

              <!-- Total en tiempo real -->
              <div class="total-bar" id="total-bar" style="display:none">
                <span class="total-bar-label"><i class="bi bi-calculator me-1"></i>Total estimado de la orden</span>
                <span class="total-bar-value" id="total-display">$0.00</span>
              </div>

              <!-- Tabla de detalles -->
              <div class="mb-4">
                <div class="table-wrap">
                  <table class="table table-sm detalles-table" id="detallesTable">
                    <thead>
                      <tr>
                        <th>#</th>
                        <th>Descripción</th>
                        <th>Cantidad</th>
                        <th>Unidad</th>
                        <th>Precio unit.</th>
                        <th>Importe</th>
                        <th></th>
                      </tr>
                    </thead>
                    <tbody></tbody>
                  </table>
                </div>
              </div>

              <!-- Botones -->
              <div class="d-flex justify-content-end gap-3 pt-3" style="border-top:1px solid var(--c-border)">
                <a href="../orden_compra/main_ordenC.php" class="btn-outline-brand">
                  <i class="bi bi-x-lg me-1"></i> Cancelar
                </a>
                <button type="submit" class="btn-primary-brand">
                  <i class="bi bi-check-lg me-1"></i> Guardar Orden
                </button>
              </div>

            </form>

          </div>
        </div>

      </div>
    </main>
  </div>
</div>

<script src="../js/bootstrap.bundle.min.js"></script>
<script>
  function toggleSidebar(){
    const s=document.getElementById('sidebar'),o=document.getElementById('sidebarOverlay'),w=document.getElementById('mainWrapper');
    if(window.innerWidth<=1100){const open=s.classList.toggle('open');o.classList.toggle('visible',open);}
    else{s.classList.toggle('collapsed');w.classList.toggle('sidebar-hidden');}
  }
  function closeSidebar(){
    document.getElementById('sidebar').classList.remove('open');
    document.getElementById('sidebarOverlay').classList.remove('visible');
  }
</script>
<script>
  (function(){
    const f = document.getElementById('form-compra');
    const btnAdd        = document.getElementById('btn-add-product');
    const detallesTable = document.getElementById('detallesTable').querySelector('tbody');
    const totalBar      = document.getElementById('total-bar');
    const totalDisplay  = document.getElementById('total-display');
    let detalleIndex = 0;

    function buildFechaHidden(){
      const d=document.getElementById('dia-c').value;
      const m=document.getElementById('mes-c').value;
      const y=document.getElementById('anio-c').value;
      if(d&&m&&y) document.getElementById('fecha-hidden-c').value=`${y}-${m}-${d}`;
    }

    /* Recalcular total de la tabla */
    function recalcTotal(){
      let total = 0;
      detallesTable.querySelectorAll('tr').forEach(function(tr){
        const imp = parseFloat(tr.querySelector('[name="detalle_importe[]"]')?.value) || 0;
        total += imp;
      });
      if (total > 0) {
        totalBar.style.display = 'flex';
        totalDisplay.textContent = '$' + total.toFixed(2);
      } else {
        totalBar.style.display = 'none';
      }
    }

    /* Recalcular importe al cambiar cantidad o precio */
    const cantInput   = f.querySelector('[name="cantidad"]');
    const precioInput = f.querySelector('[name="precio_unitario"]');
    const importeInput = document.getElementById('importe-c');
    function recompute(){
      const qty = parseFloat(cantInput.value)||0;
      const prc = parseFloat(precioInput.value)||0;
      if (qty&&prc) importeInput.value = (qty*prc).toFixed(2);
    }
    cantInput  && cantInput.addEventListener('input', recompute);
    precioInput && precioInput.addEventListener('input', recompute);

    btnAdd.addEventListener('click', function(){
      const descripcion = f.querySelector('[name="descripcion"]').value.trim();
      const cantidad    = parseFloat(cantInput.value) || 0;
      const unidadSelect= f.querySelector('[name="unidad_medida_id"]');
      const unidadId    = unidadSelect.value ? parseInt(unidadSelect.value) : null;
      const unidadText  = unidadSelect.options[unidadSelect.selectedIndex] ? unidadSelect.options[unidadSelect.selectedIndex].text : '';
      const precio      = parseFloat(precioInput.value) || 0;
      const importe     = parseFloat(importeInput.value) || (cantidad*precio);

      if (!descripcion){ alert('Ingresa una descripción'); return; }
      if (cantidad<=0)  { alert('Ingresa una cantidad válida'); return; }
      if (!unidadId)    { alert('Selecciona una unidad'); return; }
      if (precio<=0)    { alert('Ingresa un precio unitario'); return; }
      const facturaVal = (f.querySelector('[name="factura"]')||{value:''}).value.trim();
      if (!facturaVal)  { alert('Ingresa una factura o remisión'); return; }

      detalleIndex++;
      const tr = document.createElement('tr');
      tr.dataset.idx = detalleIndex;
      tr.innerHTML = `
        <td style="font-weight:700;color:var(--c-primary)">${detalleIndex}</td>
        <td>${esc(descripcion)}<input type="hidden" name="detalle_descripcion[]" value="${esc(descripcion)}"></td>
        <td>${cantidad}<input type="hidden" name="detalle_cantidad[]" value="${cantidad}"></td>
        <td>${esc(unidadText)}<input type="hidden" name="detalle_unidad_medida_id[]" value="${unidadId}"></td>
        <td>$${precio.toFixed(2)}<input type="hidden" name="detalle_precio_unitario[]" value="${precio}"></td>
        <td style="font-weight:600">$${importe.toFixed(2)}<input type="hidden" name="detalle_importe[]" value="${importe}"></td>
        <td><button type="button" class="btn-tbl-danger btn-remove"><i class="bi bi-trash"></i></button></td>`;
      detallesTable.appendChild(tr);
      tr.querySelector('.btn-remove').addEventListener('click', function(){ tr.remove(); recalcTotal(); });

      /* Limpiar campos de detalle */
      cantInput.value=''; unidadSelect.value=''; precioInput.value=''; importeInput.value='';
      recalcTotal();
    });

    detallesTable.addEventListener('click', function(e){
      if (e.target && (e.target.classList.contains('btn-remove') || e.target.closest('.btn-remove'))){
        const tr = e.target.closest('tr');
        if (tr) { tr.remove(); recalcTotal(); }
      }
    });

    function esc(s){ return String(s).replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

    f.addEventListener('submit', async function(e){
      e.preventDefault();
      buildFechaHidden();
      const proveedor    = f.querySelector('[name="proveedor"]').value.trim();
      const fecha        = document.getElementById('fecha-hidden-c').value.trim();
      const departamento = f.querySelector('[name="departamento_id"]').value;
      if (!proveedor)    { alert('Te faltó el proveedor'); return; }
      if (!fecha)        { alert('Te faltó la fecha'); return; }
      if (!departamento) { alert('Te faltó el departamento'); return; }
      if (detallesTable.querySelectorAll('tr').length===0){ alert('Agrega al menos un artículo a la orden'); return; }

      const facturaInput = f.querySelector('[name="factura"]');
      const facturaVal   = facturaInput ? facturaInput.value.trim() : '';
      if (!facturaVal){ alert('Ingresa una factura o remisión'); return; }
      try {
      const qf = BASE_PATH + 'includes/check_duplicate.php?kind=orden_factura&value=' + encodeURIComponent(facturaVal);
        const resf = await fetch(qf);
        const jf   = await resf.json();
        if (jf&&jf.ok&&jf.exists){ alert('Esa factura ya está registrada. Verifica el número.'); return; }
      } catch(err){ console.error('Error comprobando factura:', err); }

      const folioInput = f.querySelector('[name="folio"]');
      const folioVal   = folioInput ? folioInput.value.trim() : '';
      if (!folioVal){ alert('Te faltó el folio'); return; }
      try {
        const q   = BASE_PATH + 'includes/check_duplicate.php?kind=orden_folio&value='+encodeURIComponent(folioVal);
        const res = await fetch(q);
        const json = await res.json();
        if (json&&json.ok&&json.exists){ alert('El folio ya existe. Elige otro folio.'); return; }
      } catch(err){ console.error('Error comprobando folio:', err); }

      f.submit();
    });
  })();
</script>
</body>
</html>
