<?php require_once __DIR__ . '/../includes/auth.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Productos — AlmacenaTec</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="../css/bootstrap.min.css">
  <link rel="stylesheet" href="../css/bootstrap-grid.min.css">
  <link rel="stylesheet" href="../css/style.css">
  <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
  <script src="js/html5-qrcode.min.js"></script>
  <style>
    /* ============================================================
       ESTILOS UNIFICADOS - PALETA LOGIN
       ============================================================ */
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
    .table-almacenatec {
      width: 100%;
      border-collapse: collapse;
      font-size: 14px;
      background: #FFFFFF;
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 1px 3px rgba(0,0,0,.07), 0 4px 14px rgba(0,0,0,.05);
    }
    .table-almacenatec thead {
      background: #0B2D6B;
      color: #FFFFFF;
    }
    .table-almacenatec thead th {
      padding: 12px 16px;
      text-align: left;
      font-weight: 600;
      font-size: 12px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      border-bottom: 3px solid #c9973a;
      white-space: nowrap;
    }
    .table-almacenatec thead th i {
      margin-right: 6px;
      color: #c9973a;
    }
    .table-almacenatec tbody td {
      padding: 10px 16px;
      border-bottom: 1px solid #E2E8F0;
      color: #1E293B;
      vertical-align: middle;
    }
    .table-almacenatec tbody tr:hover {
      background: #F0F4F9;
    }
    .table-almacenatec tbody tr:last-child td {
      border-bottom: none;
    }
    .table-responsive-almacenatec {
      overflow-x: auto;
      border-radius: 10px;
    }
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
    .search-almacenatec {
      position: relative;
      display: inline-flex;
      align-items: center;
    }
    .search-almacenatec i {
      position: absolute;
      left: 12px;
      color: #94A3B8;
    }
    .search-almacenatec input {
      padding: 8px 12px 8px 36px;
      border: 1.5px solid #E2E8F0;
      border-radius: 8px;
      font-size: 13px;
      font-family: 'Inter', sans-serif;
      background: #FFFFFF;
      color: #1E293B;
      transition: border-color 0.2s;
      min-width: 200px;
    }
    .search-almacenatec input:focus {
      outline: none;
      border-color: #0B2D6B;
      box-shadow: 0 0 0 3px rgba(11,45,107,.1);
    }
    .badge-almacenatec {
      display: inline-block;
      padding: 4px 12px;
      border-radius: 20px;
      font-size: 11px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.3px;
    }
    .badge-almacenatec.activo {
      background: #d1fae5;
      color: #059669;
    }
    .badge-almacenatec.inactivo {
      background: #fee2e2;
      color: #dc2626;
    }

    .btn-almacenatec {
      display: inline-flex;
      align-items: center;
      gap: 4px;
      padding: 4px 10px;
      border-radius: 6px;
      font-size: 12px;
      font-weight: 600;
      border: none;
      cursor: pointer;
      transition: all 0.2s;
      text-decoration: none;
    }
    .btn-almacenatec.ver {
      background: rgba(11,45,107,.08);
      color: #0B2D6B;
    }
    .btn-almacenatec.ver:hover {
      background: #0B2D6B;
      color: #FFFFFF;
    }
    .btn-almacenatec.editar {
      background: rgba(217,119,6,.08);
      color: #d97706;
    }
    .btn-almacenatec.editar:hover {
      background: #d97706;
      color: #FFFFFF;
    }
    .btn-almacenatec.eliminar {
      background: rgba(184,28,46,.08);
      color: #B81C2E;
    }
    .btn-almacenatec.eliminar:hover {
      background: #B81C2E;
      color: #FFFFFF;
    }
    .btn-almacenatec.imprimir {
      background: rgba(11,45,107,.08);
      color: #0B2D6B;
    }
    .btn-almacenatec.imprimir:hover {
      background: #0B2D6B;
      color: #FFFFFF;
    }

    /* ── Botones de acción principales (azules) ── */
    .btn-primary-almacenatec {
      background: #0B2D6B;
      color: #FFFFFF;
      border: none;
      padding: 9px 18px;
      border-radius: 8px;
      font-size: 13.5px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.2s;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 6px;
    }
    .btn-primary-almacenatec:hover {
      background: #0B2D6B;
      color: #FFFFFF;
    }
    .btn-primary-almacenatec i {
      color: #ffff;
    }
    .btn-primary-almacenatec i:hover {
      color: #FFFFFF;
    }
    .btn-accent-almacenatec {
      background: #0B2D6B;
      color: #FFFFFF;
      border: none;
      padding: 9px 18px;
      border-radius: 8px;
      font-size: 13.5px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.2s;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 6px;
    }
    .btn-accent-almacenatec:hover {
      background: #0B2D6B;
      color: #FFFFFF;
    }
    .btn-warning-almacenatec {
      background: #0B2D6B;
      color: #FFFFFF;
      border: none;
      padding: 9px 18px;
      border-radius: 8px;
      font-size: 13.5px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.2s;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 6px;
    }
    .btn-warning-almacenatec:hover {
      background: #0B2D6B;
      color: #FFFFFF;
    }
    .btn-outline-almacenatec {
      background: #0B2D6B;
      color: #ffff;
      border: 1.5px solid #0B2D6B;
      padding: 8px 18px;
      border-radius: 8px;
      font-size: 13.5px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.2s;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 6px;
    }
    .btn-outline-almacenatec:hover {
      background: #0B2D6B;
      color: #FFFFFF;
    }

    .acciones-botones {
      display: flex;
      gap: 4px;
      flex-wrap: wrap;
      justify-content: center;
    }

    @media (max-width: 768px) {
      .table-almacenatec thead th {
        font-size: 10px;
        padding: 8px 10px;
      }
      .table-almacenatec tbody td {
        font-size: 12px;
        padding: 8px 10px;
      }
      .table-almacenatec thead th i {
        display: none;
      }
      .search-almacenatec input {
        min-width: 140px;
      }
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
        <h1><i class="bi bi-box-seam me-2" style="color:#0B2D6B"></i>Productos</h1>
        <div class="topbar-breadcrumb"><a href="../home/index.php">Inicio</a> &rsaquo; Productos</div>
      </div>
      <div style="display:flex;align-items:center;gap:10px;flex-shrink:0">
        <span style="font-size:13px;color:#475569"><i class="bi bi-person-circle me-1"></i><?= htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Usuario') ?></span>
        <a href="<?= BASE_PATH ?>logout.php" class="btn-accent-almacenatec" style="padding:6px 12px;font-size:12px"><i class="bi bi-box-arrow-right me-1"></i> Salir</a>
      </div>
    </header>

    <main class="content">
      <div class="page-header">
        <h2><i class="bi bi-box-seam me-2" style="color:#0B2D6B"></i>Productos</h2>
        <div style="display:flex;gap:8px;flex-wrap:wrap">
          <a href="capturarProd.php" class="btn-primary-almacenatec"><i class="bi bi-plus-lg me-1"></i> Capturar Producto</a>
          <a href="vale_material.php" class="btn-accent-almacenatec"><i class="bi bi-file-earmark-text me-1"></i> Vale por Material</a>
          <a href="vale_herramienta.php" class="btn-warning-almacenatec"><i class="bi bi-tools me-1"></i> Vale de Herramienta</a>
          <a href="listar_vales.php" class="btn-outline-almacenatec"><i class="bi bi-list-ul me-1"></i> Ver Vales</a>
        </div>
      </div>

      <!-- ============================================================ -->
      <!-- TÍTULO DE LA TABLA                                             -->
      <!-- ============================================================ -->
      <div class="tabla-titulo">
        <i class="bi bi-box-seam"></i>
        Historial de Productos
        <small>Catálogo de productos registrados</small>
      </div>

      <div class="card-panel">
        <div class="card-header-almacenatec">
          <h3><i class="bi bi-table"></i>Productos registrados</h3>
          <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center">
            <div class="search-almacenatec">
              <i class="bi bi-search"></i>
              <input type="text" id="searchItem" placeholder="Nombre, marca, código…">
            </div>
            <div style="display:flex;gap:6px">
              <input type="text" id="barcodeInput" class="form-control" placeholder="Escanear código…" style="min-width:170px;border:1.5px solid #E2E8F0;border-radius:8px;padding:8px 12px;font-size:13px;">
              <button class="btn-primary-almacenatec" id="btnBarcode" style="padding:7px 12px;white-space:nowrap"><i class="bi bi-upc-scan"></i> Buscar</button>
              <button class="btn-primary-almacenatec" id="openScannerBtn" style="padding:7px 12px"><i class="bi bi-camera"></i></button>
            </div>
          </div>
        </div>

        <div class="table-responsive-almacenatec">
          <table class="table-almacenatec">
            <thead>
              <tr>
                <th><i class="bi bi-hash"></i> ID</th>
                <th><i class="bi bi-tag"></i> Nombre / Código</th>
                <th><i class="bi bi-folder"></i> Categoría</th>
                <th><i class="bi bi-rulers"></i> Unidad</th>
                <th><i class="bi bi-cash"></i> Costo</th>
                <th><i class="bi bi-box"></i> Stock</th>
                <th><i class="bi bi-info-circle"></i> Estado</th>
                <th><i class="bi bi-tools"></i> Acciones</th>
              </tr>
            </thead>
            <tbody id="productosTableBody"></tbody>
          </table>
        </div>
      </div>
    </main>
  </div>
</div>

<div class="modal fade" id="modalProducto" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg"><div class="modal-content">
    <div class="modal-header" style="background:#0B2D6B;color:#fff;">
      <h5 class="modal-title" style="color:#fff;"><i class="bi bi-box-seam me-2"></i>Información del Producto</h5>
      <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter:invert(1);"></button>
    </div>
    <div class="modal-body" id="modalProductoBody"></div>
    <div class="modal-footer">
      <button type="button" id="printLabelBtn" class="btn-primary-almacenatec" style="display:none"><i class="bi bi-printer me-1"></i> Imprimir etiqueta</button>
      <button type="button" class="btn-outline-almacenatec" data-bs-dismiss="modal"><i class="bi bi-x-lg me-1"></i> Cerrar</button>
    </div>
  </div></div>
</div>

<div class="modal fade" id="scannerModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-sm modal-dialog-centered"><div class="modal-content">
    <div class="modal-header" style="background:#0B2D6B;color:#fff;">
      <h5 class="modal-title" style="color:#fff;"><i class="bi bi-camera me-2"></i>Escanear código</h5>
      <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter:invert(1);"></button>
    </div>
    <div class="modal-body"><div id="qr-reader" style="width:100%"></div><div id="qr-status" class="mt-2" style="font-size:12px;color:#94A3B8">Permite el acceso a la cámara y apunta al código de barras.</div></div>
    <div class="modal-footer"><button type="button" class="btn-outline-almacenatec" data-bs-dismiss="modal">Cerrar</button></div>
  </div></div>
</div>

<script>
  function toggleSidebar(){const s=document.getElementById('sidebar'),o=document.getElementById('sidebarOverlay'),w=document.getElementById('mainWrapper');if(window.innerWidth<=1100){const open=s.classList.toggle('open');o.classList.toggle('visible',open);}else{s.classList.toggle('collapsed');w.classList.toggle('sidebar-hidden');}}
  function closeSidebar(){document.getElementById('sidebar').classList.remove('open');document.getElementById('sidebarOverlay').classList.remove('visible');}
  async function fetchProductos(){try{const res=await fetch('<?= BASE_PATH ?>includes/get_productos.php');const data=await res.json();if(!data.ok){console.error('Error:',data.error);return;}window._productos=data.productos||[];renderTable(window._productos);}catch(err){console.error('fetchProductos:',err);}}
  function ensureHtml5Qrcode(t=8000){const src=['js/html5-qrcode.min.js','https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.8/html5-qrcode.min.js','https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js'];function tryLoad(s,p){return new Promise((res,rej)=>{if(window.Html5Qrcode)return res();const el=document.createElement('script');el.src=s;let done=false;const to=setTimeout(()=>{if(!done){done=true;rej(new Error('timeout '+s));}},p);el.onload=function(){if(done)return;done=true;clearTimeout(to);if(window.Html5Qrcode)res();else rej(new Error('not found '+s));};el.onerror=function(){if(done)return;done=true;clearTimeout(to);rej(new Error('Failed '+s));};document.head.appendChild(el);});}return new Promise(async(res,rej)=>{const p=Math.max(3000,Math.floor(t/src.length));for(const s of src){try{await tryLoad(s,p);return res();}catch(e){}}rej(new Error('All html5-qrcode sources failed'));});}
  function escapeHtml(s){if(!s&&s!==0)return'';return String(s).replace(/[&<>"]/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c]));}
  function renderTable(rows){const tbody=document.getElementById('productosTableBody');tbody.innerHTML='';for(const p of rows){const isActive=p.activo==1;const tr=document.createElement('tr');tr.innerHTML=`<td><span style="font-weight:700;color:#0B2D6B">${p.id}</span></td><td style="text-align:left;min-width:180px"><div style="font-weight:600;font-size:13.5px">${escapeHtml(p.descripcion)}</div><div style="font-size:11.5px;color:#475569">${escapeHtml(p.marca||'')} ${escapeHtml(p.modelo||'')}</div><div style="font-size:11px;color:#94A3B8;font-family:monospace">${escapeHtml(p.codigo_barras||'')}</div><div style="margin-top:5px"><svg id="barcode-row-${p.id}" width="130" height="28"></svg></div></td><td>${escapeHtml(p.categoria||'')}</td><td>${escapeHtml(p.unidad||'')}</td><td style="font-weight:600">$${p.costo_promedio}</td><td>${p.categoria_id==2000?`<span style="font-weight:700;color:#0B2D6B">${p.stock_actual}</span>`:'<span style="color:#94A3B8;font-size:12px">N/A</span>'}</td><td>${isActive?'<span class="badge-almacenatec activo"><i class="bi bi-check-circle-fill"></i> Activo</span>':'<span class="badge-almacenatec inactivo"><i class="bi bi-x-circle-fill"></i> Inactivo</span>'}</td><td><div class="acciones-botones"><button class="btn-almacenatec ver" onclick="viewProducto(${p.id})"><i class="bi bi-eye"></i></button><button class="btn-almacenatec eliminar" onclick="toggleProducto(${p.id},${p.activo})">${isActive?'<i class="bi bi-arrow-down-circle"></i>':'<i class="bi bi-arrow-up-circle"></i>'}</button><button class="btn-almacenatec imprimir" onclick="printLabelFromRow(${p.id})"><i class="bi bi-printer"></i></button></div></td>`;tbody.appendChild(tr);setTimeout(function(){try{const svg=document.getElementById('barcode-row-'+p.id);if(svg&&p.codigo_barras)JsBarcode(svg,String(p.codigo_barras),{format:'CODE128',displayValue:false,height:28,margin:0});}catch(e){}},10);}}
  function viewProducto(id){const p=(window._productos||[]).find(x=>x.id==id);if(!p)return alert('Producto no encontrado');const body=document.getElementById('modalProductoBody');body.innerHTML=`<dl class="row" style="font-size:13.5px"><dt class="col-sm-4" style="color:#94A3B8;font-weight:600">ID</dt><dd class="col-sm-8">${p.id}</dd><dt class="col-sm-4" style="color:#94A3B8;font-weight:600">Descripción</dt><dd class="col-sm-8">${escapeHtml(p.descripcion||'')}</dd><dt class="col-sm-4" style="color:#94A3B8;font-weight:600">Cód. barras</dt><dd class="col-sm-8"><span style="font-family:monospace">${escapeHtml(p.codigo_barras||'')}</span><br><svg id="barcode-svg-${p.id}" style="margin-top:8px"></svg></dd><dt class="col-sm-4" style="color:#94A3B8;font-weight:600">Marca</dt><dd class="col-sm-8">${escapeHtml(p.marca||'—')}</dd><dt class="col-sm-4" style="color:#94A3B8;font-weight:600">Modelo</dt><dd class="col-sm-8">${escapeHtml(p.modelo||'—')}</dd><dt class="col-sm-4" style="color:#94A3B8;font-weight:600">Categoría</dt><dd class="col-sm-8">${escapeHtml(p.categoria||'')}</dd><dt class="col-sm-4" style="color:#94A3B8;font-weight:600">Unidad</dt><dd class="col-sm-8">${escapeHtml(p.unidad||'')}</dd><dt class="col-sm-4" style="color:#94A3B8;font-weight:600">Costo prom.</dt><dd class="col-sm-8" style="font-weight:700;color:#0B2D6B">$${p.costo_promedio||'0.00'}</dd><dt class="col-sm-4" style="color:#94A3B8;font-weight:600">Stock mín.</dt><dd class="col-sm-8">${p.stock_minimo||'0'}</dd><dt class="col-sm-4" style="color:#94A3B8;font-weight:600">Stock actual</dt><dd class="col-sm-8">${p.categoria_id==2000?(p.stock_actual||'0'):'N/A (Activo Fijo)'}</dd><dt class="col-sm-4" style="color:#94A3B8;font-weight:600">Estado</dt><dd class="col-sm-8">${p.activo==1?'<span class="badge-almacenatec activo"><i class="bi bi-check-circle-fill"></i> Activo</span>':'<span class="badge-almacenatec inactivo"><i class="bi bi-x-circle-fill"></i> Inactivo</span>'}</dd></dl>`;const btn=document.getElementById('printLabelBtn');if(btn){btn.style.display='inline-flex';btn.onclick=function(){printLabel(p);};}new bootstrap.Modal(document.getElementById('modalProducto')).show();setTimeout(function(){try{const svg=document.getElementById('barcode-svg-'+p.id);if(svg&&p.codigo_barras)JsBarcode(svg,String(p.codigo_barras),{format:'CODE128',displayValue:true,fontSize:14});}catch(err){}},50);}
  async function toggleProducto(id,activo){const action=activo==1?'deactivate':'activate';if(activo==1&&!confirm('¿Confirmar dar de baja este producto?'))return;const form=new URLSearchParams();form.append('id',id);form.append('action',action);const res=await fetch('<?= BASE_PATH ?>handlers/toggle_producto.php',{method:'POST',body:form});const data=await res.json();if(data.ok){await fetchProductos();}else{alert('Error: '+(data.error||''));}}
  function printLabel(p){const w=window.open('','_blank','width=400,height=240');let html='<!doctype html><html><head><meta charset="utf-8"><title>Etiqueta</title><style>body{font-family:Arial,sans-serif;padding:8px;text-align:center}</style></head><body><div style="font-size:14px;font-weight:600;margin-bottom:6px">'+escapeHtml(p.descripcion||'')+'</div><svg id="print-barcode" width="300" height="60"></svg><div style="margin-top:6px;font-size:12px">'+escapeHtml(p.codigo_barras||'')+'</div></body></html>';w.document.open();w.document.write(html);w.document.close();try{const s=w.document.createElement('script');s.src='https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js';s.onload=function(){try{w.JsBarcode('#print-barcode',String(p.codigo_barras||''),{format:'CODE128',displayValue:true,fontSize:16});}catch(e){}try{w.print();}catch(e){}setTimeout(()=>{try{w.close();}catch(e){}},500);};w.document.body.appendChild(s);}catch(e){}}
  function printLabelFromRow(id){const p=(window._productos||[]).find(x=>x.id==id);if(p)printLabel(p);}
  document.addEventListener('DOMContentLoaded',function(){
    fetchProductos();
    document.getElementById('searchItem').addEventListener('input',function(){const q=this.value.toLowerCase().trim();if(!q)return renderTable(window._productos||[]);renderTable((window._productos||[]).filter(p=>(p.descripcion||'').toLowerCase().includes(q)||(p.marca||'').toLowerCase().includes(q)||(p.codigo_barras||'').toLowerCase().includes(q)));});
    document.getElementById('btnBarcode').addEventListener('click',async function(){const code=document.getElementById('barcodeInput').value.trim();if(!code)return alert('Introduce un código de barras');const res=await fetch('<?= BASE_PATH ?>includes/get_producto_por_barcode.php?barcode='+encodeURIComponent(code));const data=await res.json();if(!data.ok)return alert('Error: '+(data.error||''));if(!data.producto)return alert('Producto no encontrado');const p=data.producto;const body=document.getElementById('modalProductoBody');body.innerHTML=`<dl class="row" style="font-size:13.5px"><dt class="col-sm-3" style="color:#94A3B8">ID</dt><dd class="col-sm-9">${p.id}</dd><dt class="col-sm-3" style="color:#94A3B8">Código</dt><dd class="col-sm-9" style="font-family:monospace">${escapeHtml(p.codigo_barras)}</dd><dt class="col-sm-3" style="color:#94A3B8">Descripción</dt><dd class="col-sm-9" style="font-weight:600">${escapeHtml(p.descripcion)}</dd><dt class="col-sm-3" style="color:#94A3B8">Marca/Modelo</dt><dd class="col-sm-9">${escapeHtml(p.marca)} ${escapeHtml(p.modelo)}</dd><dt class="col-sm-3" style="color:#94A3B8">Categoría</dt><dd class="col-sm-9">${escapeHtml(p.categoria)}</dd><dt class="col-sm-3" style="color:#94A3B8">Unidad</dt><dd class="col-sm-9">${escapeHtml(p.unidad)}</dd></dl>`;const btn=document.getElementById('printLabelBtn');if(btn){btn.style.display='inline-flex';btn.onclick=function(){printLabel(p);};}new bootstrap.Modal(document.getElementById('modalProducto')).show();});
    document.getElementById('openScannerBtn').addEventListener('click',function(){openScannerModal();});
    let scanner=null;
    async function openScannerModal(){let statusEl=document.getElementById('qr-status');let loaded=false;try{await ensureHtml5Qrcode();loaded=true;}catch(e){}const modalEl=document.getElementById('scannerModal');if(!modalEl)return;statusEl&&(statusEl.textContent='Solicitando cámara...');const modal=new bootstrap.Modal(modalEl);modal.show();modalEl.addEventListener('hidden.bs.modal',function(){try{if(loaded&&scanner){scanner.stop().then(()=>{try{scanner.clear();}catch(e){}scanner=null;}).catch(()=>{scanner=null;});}}catch(e){}},{once:true});modalEl.addEventListener('shown.bs.modal',async function(){statusEl&&(statusEl.textContent='Inicializando...');if(loaded){try{scanner=new Html5Qrcode('qr-reader');}catch(e){statusEl&&(statusEl.textContent='Error: '+e);return;}Html5Qrcode.getCameras().then(cams=>{const id=cams&&cams.length?cams[0].id:null;if(!id){statusEl&&(statusEl.textContent='No se detectó cámara');return;}statusEl&&(statusEl.textContent='Apunta al código...');scanner.start(id,{fps:10,qrbox:250},(code)=>{try{const local=(window._productos||[]).find(x=>String(x.codigo_barras||'')===String(code).trim());if(local){try{if(scanner)scanner.stop().then(()=>{try{scanner.clear();}catch(e){}scanner=null;}).catch(()=>{scanner=null;});}catch(e){}modal.hide();viewProducto(local.id);return;}document.getElementById('barcodeInput').value=String(code).trim();document.getElementById('btnBarcode').click();if(scanner)scanner.stop().then(()=>{try{scanner.clear();}catch(e){}scanner=null;}).catch(()=>{scanner=null;});modal.hide();}catch(e){}},()=>{statusEl&&(statusEl.textContent='Buscando...');}).catch(err=>{statusEl&&(statusEl.textContent='Error: '+err);});}).catch(err=>{statusEl&&(statusEl.textContent='No se pudo acceder a cámaras: '+err);});}},{once:true});}
  });
</script>
<script src="../js/bootstrap.bundle.min.js"></script>
</body>
</html>