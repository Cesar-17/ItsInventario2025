<?php require_once __DIR__ . '/../includes/auth.php'; $esAdmin = ($_SESSION['usuario_rol'] ?? '') === 'admin'; ?>

<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Historial de Salidas</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="../css/bootstrap.min.css">
  <link rel="stylesheet" href="../css/style.css">
  <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/html2pdf.js@0.9.3/dist/html2pdf.bundle.min.js"></script>
  <style>
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
    .tabla-titulo { font-family: 'Poppins', sans-serif; font-size: 18px; font-weight: 700; color: #0B2D6B; margin-bottom: 16px; display: flex; align-items: center; gap: 10px; padding-bottom: 10px; border-bottom: 3px solid #c9973a; }
    .tabla-titulo i { color: #c9973a; font-size: 22px; }
    .tabla-titulo small { font-size: 13px; font-weight: 400; color: #475569; margin-left: 8px; }
    .card-header-almacenatec { display: flex; justify-content: space-between; align-items: center; padding: 16px 20px; background: #FFFFFF; border-bottom: 1px solid #E2E8F0; flex-wrap: wrap; gap: 12px; }
    .card-header-almacenatec h3 { font-family: 'Poppins', sans-serif; font-size: 15px; font-weight: 700; color: #0B2D6B; margin: 0; }
    .card-header-almacenatec h3 i { color: #c9973a; margin-right: 8px; }
    .search-almacenatec { position: relative; display: inline-flex; align-items: center; }
    .search-almacenatec i { position: absolute; left: 12px; color: #94A3B8; }
    .search-almacenatec input { padding: 8px 12px 8px 36px; border: 1.5px solid #E2E8F0; border-radius: 8px; font-size: 13px; font-family: 'Inter', sans-serif; background: #FFFFFF; color: #1E293B; transition: border-color 0.2s; min-width: 200px; }
    .search-almacenatec input:focus { outline: none; border-color: #0B2D6B; box-shadow: 0 0 0 3px rgba(11,45,107,.1); }
    .table-almacenatec { width: 100%; border-collapse: collapse; font-size: 14px; background: #FFFFFF; }
    .table-almacenatec thead { background: #0B2D6B; color: #FFFFFF; }
    .table-almacenatec thead th { padding: 12px 16px; text-align: center; font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 3px solid #c9973a; }
    .table-almacenatec thead th i { margin-right: 6px; color: #c9973a; }
    .table-almacenatec tbody td { padding: 10px 16px; border-bottom: 1px solid #E2E8F0; color: #1E293B; vertical-align: middle; text-align: center; }
    .table-almacenatec tbody tr:hover { background: #F0F4F9; }
    .table-almacenatec tbody tr:last-child td { border-bottom: none; }
    .table-responsive-almacenatec { overflow-x: auto; }
    .badge-almacen { display: inline-block; padding: 3px 12px; border-radius: 20px; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .3px; }
    .badge-almacen.general { background: #dbeafe; color: #0b2d6b; }
    .badge-almacen.activo { background: #d1fae5; color: #059669; }
    .badge-almacen.mantenimiento { background: #fef3c7; color: #d97706; }
    .filtros-almacenatec { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; padding: 12px 20px; background: #F0F4F9; border-bottom: 1px solid #E2E8F0; }
    .filtros-almacenatec .btn-filtro { padding: 5px 16px; border-radius: 20px; border: 1.5px solid #E2E8F0; background: #fff; font-size: 12px; font-weight: 600; cursor: pointer; transition: all 0.2s; color: #475569; }
    .filtros-almacenatec .btn-filtro:hover { border-color: #0B2D6B; color: #0B2D6B; }
    .filtros-almacenatec .btn-filtro.active { background: #0B2D6B; color: #fff; border-color: #0B2D6B; }
    .filtros-almacenatec .btn-filtro.active-general { background: #0b2d6b; color: #fff; border-color: #0b2d6b; }
    .filtros-almacenatec .btn-filtro.active-activo { background: #059669; color: #fff; border-color: #059669; }
    .filtros-almacenatec .btn-filtro.active-mantenimiento { background: #d97706; color: #fff; border-color: #d97706; }
    .btn-almacenatec { display: inline-flex; align-items: center; gap: 4px; padding: 4px 10px; border-radius: 6px; font-size: 12px; font-weight: 600; border: none; cursor: pointer; transition: all 0.2s; text-decoration: none; }
    .btn-almacenatec.ver { background: rgba(11,45,107,.08); color: #0B2D6B; }
    .btn-almacenatec.ver:hover { background: #0B2D6B; color: #FFFFFF; }
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
    
    <?php elseif ($_SESSION['usuario_rol'] === 'almacen_general'): ?>
    <a href="../activo_fijo/index.php" class="sidebar-link"><i class="bi bi-safe-fill"></i> Activo Fijo</a>
    <a href="../activo_fijo/main_activF.php" class="sidebar-link active"><i class="bi bi-clipboard2-data"></i> Historial Salidas</a>
    <a href="../orden_compra/requisiciones.php" class="sidebar-link"><i class="bi bi-cart3"></i> Almacén General</a>
   
    <?php elseif ($_SESSION['usuario_rol'] === 'activo_fijo'): ?>
    <a href="../activo_fijo/index.php" class="sidebar-link"><i class="bi bi-safe-fill"></i> Activo Fijo</a>
    <a href="../activo_fijo/main_activF.php" class="sidebar-link active"><i class="bi bi-clipboard2-data"></i> Historial Salidas</a>
    <a href="../activo_fijo/requisiciones_af.php" class="sidebar-link"><i class="bi bi-cart3"></i> Almacén General</a>
    
    <?php elseif ($_SESSION['usuario_rol'] === 'almacenista' || $_SESSION['usuario_rol'] === 'mantenimiento'): ?>
    <a href="../productos/main_productos.php" class="sidebar-link"><i class="bi bi-box-seam"></i> Productos</a>
    
    <?php else: ?>
    <!-- Admin y Usuario: todos los módulos -->
    <a href="../activo_fijo/index.php" class="sidebar-link"><i class="bi bi-safe-fill"></i> Activo Fijo</a>
    <a href="../activo_fijo/main_activF.php" class="sidebar-link active"><i class="bi bi-clipboard2-data"></i> Historial Salidas</a>
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
        <h1>Historial de Salidas</h1>
        <div class="topbar-breadcrumb"><a href="../home/index.php">Inicio</a> &rsaquo; Historial de Salidas</div>
      </div>
      <div style="display:flex;align-items:center;gap:10px;flex-shrink:0">
        <span style="font-size:13px;color:var(--c-text-md)"><i class="bi bi-person-circle me-1"></i><?= htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Usuario') ?></span>
        <a href="<?= BASE_PATH ?>logout.php" class="btn-accent-brand" style="padding:6px 12px;font-size:12px"><i class="bi bi-box-arrow-right me-1"></i> Salir</a>
      </div>
    </header>

    <main class="content">
      <div class="page-header">
        <h2><i class="bi bi-clipboard2-data me-2" style="color:var(--c-primary)"></i>Historial de Salidas</h2>
         <?php if ($esAdmin): ?>
        <button class="btn-primary-brand" onclick="location.href='../activo_fijo/capturarAFijo.php'"><i class="bi bi-plus-lg"></i> Nueva Salida</button>
         <?php endif; ?>
      </div>

      <div class="tabla-titulo">
        <i class="bi bi-clipboard2-data"></i>
        Registros de Salidas
        <small>Historial completo de salidas de activos</small>
      </div>

      <div class="card-panel">
        <div class="card-header-almacenatec">
          <h3><i class="bi bi-table"></i>Lista de Salidas</h3>
          <div class="search-almacenatec">
            <i class="bi bi-search"></i>
            <input type="text" id="searchItem" class="form-control" placeholder="Buscar por folio, proveedor, departamento…">
          </div>
        </div>

        <div class="filtros-almacenatec">
          <span style="font-size:12px;font-weight:600;color:#475569;text-transform:uppercase;letter-spacing:.5px">
            <i class="bi bi-funnel me-1"></i>Filtrar por almacén:
          </span>
          <button class="btn-filtro active" data-filtro="todos" onclick="filtrarPorAlmacen('todos', this)">Todos</button>
          <button class="btn-filtro" data-filtro="activo" onclick="filtrarPorAlmacen('activo', this)">Activo Fijo</button>
          <button class="btn-filtro" data-filtro="general" onclick="filtrarPorAlmacen('general', this)">General</button>
          <button class="btn-filtro" data-filtro="mantenimiento" onclick="filtrarPorAlmacen('mantenimiento', this)">Mantenimiento</button>
        </div>

        <div class="table-responsive-almacenatec">
          <table class="table-almacenatec">
            <thead>
              <tr>
                <th><i class="bi bi-hash"></i> ID</th>
                <th><i class="bi bi-file-earmark-text"></i> Folio</th>
                <th><i class="bi bi-calendar"></i> Fecha</th>
                <th><i class="bi bi-archive"></i> Almacén</th>
                <th><i class="bi bi-tags"></i> Tipo Compra</th>
                <th><i class="bi bi-building"></i> Departamento</th>
                <th><i class="bi bi-person"></i> Jefe</th>
                <th><i class="bi bi-truck"></i> Proveedor</th>
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

<!-- Modal: detalle de salida -->
<div class="modal fade" id="modalSalida" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header" style="background:#0B2D6B;color:#fff;">
        <h5 class="modal-title" style="color:#fff;"><i class="bi bi-file-earmark-text me-2"></i>Detalle de salida</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter:invert(1);"></button>
      </div>
      <div class="modal-body" id="modalSalidaBody"></div>
      <div class="modal-footer">
        <button type="button" class="btn-primary-brand" id="btnDownloadPdf"><i class="bi bi-file-earmark-pdf me-1"></i> Descargar PDF</button>
        <button type="button" class="btn-outline-brand" data-bs-dismiss="modal"><i class="bi bi-x-lg me-1"></i> Cerrar</button>
      </div>
    </div>
  </div>
</div>

<script src="../js/bootstrap.bundle.min.js"></script>
<script>
  // ── Variables globales ──
  let todasLasSalidas = [];
  let filtroActual = 'todos';
  const userRole = "<?= $_SESSION['usuario_rol'] ?? 'usuario' ?>"; // Obtenemos el rol actual

  // ── Sidebar ──
  function toggleSidebar() {
    const s = document.getElementById('sidebar');
    const o = document.getElementById('sidebarOverlay');
    const w = document.getElementById('mainWrapper');
    if (window.innerWidth <= 1100) {
      const open = s.classList.toggle('open');
      o.classList.toggle('visible', open);
    } else {
      s.classList.toggle('collapsed');
      w.classList.toggle('sidebar-hidden');
    }
  }
  function closeSidebar() {
    document.getElementById('sidebar').classList.remove('open');
    document.getElementById('sidebarOverlay').classList.remove('visible');
  }

  // ── Helper: error div ──
  function getOrCreateErrorDiv() {
    let d = document.getElementById('fetchError');
    if (!d) {
      d = document.createElement('div');
      d.id = 'fetchError';
      d.style.cssText = 'margin:10px 0;color:#856404;background:#fff3cd;padding:8px 12px;border-radius:6px;display:none;font-size:13px;';
      const c = document.querySelector('.card-panel');
      if (c) c.insertBefore(d, c.firstChild);
    }
    return d;
  }

  // ── Obtener el almacén ──
  function getAlmacen(s) {
    const texto = (s.observaciones || '').toLowerCase() + ' ' + (s.proyecto || '').toLowerCase();
    if (texto.includes('mantenimiento') || texto.includes('herramienta')) return 'mantenimiento';
    if (texto.includes('general') || texto.includes('consumible') || texto.includes('material')) return 'general';
    if (texto.includes('activo') || texto.includes('fijo')) return 'activo';
    
    const dept = (s.departamento || '').toLowerCase();
    if (dept.includes('mantenimiento')) return 'mantenimiento';
    if (dept.includes('general')) return 'general';
    return 'activo';
  }

  // ── NUEVO: Detectar el tipo de compra ──
  function getTipoCompra(s) {
    const texto = (s.observaciones || '').toLowerCase() + ' ' + (s.proyecto || '').toLowerCase();
    if (texto.includes('licitacion') || texto.includes('licitación')) return 'licitacion';
    if (texto.includes('5000') || texto.includes('partida')) return 'partida_5000';
    return 'normal';
  }

  function getAlmacenLabel(s) {
    const tipo = getAlmacen(s);
    const labels = {
      'activo': { label: 'Activo Fijo', class: 'activo' },
      'general': { label: 'General', class: 'general' },
      'mantenimiento': { label: 'Mantenimiento', class: 'mantenimiento' }
    };
    return labels[tipo] || labels['activo'];
  }

  // ── Fetch salidas ──
  async function fetchSalidas() {
    const errDiv = getOrCreateErrorDiv();
    try {
      const res = await fetch('<?= BASE_PATH ?>includes/get_salidas.php');
      const text = await res.text();
      let data;
      try { data = JSON.parse(text); } catch(e) {
        errDiv.style.display = 'block';
        errDiv.textContent = 'Respuesta no JSON: ' + text.substring(0, 1000);
        return;
      }
      if (!res.ok || !data.ok) {
        errDiv.style.display = 'block';
        errDiv.textContent = 'Error: ' + (data && data.error ? data.error : res.status);
        return;
      }
      errDiv.style.display = 'none';
      todasLasSalidas = data.salidas || [];
      aplicarFiltros();
    } catch(err) {
      const d = getOrCreateErrorDiv();
      d.style.display = 'block';
      d.textContent = 'Error: ' + (err && err.message ? err.message : String(err));
    }
  }

  function getJefeName(obj) {
    if (!obj) return '';
    return obj.jefe_nombre || obj.jefe || obj.jefe_name || (obj.jefe_id ? 'ID:' + obj.jefe_id : '') || '';
  }

  // ── Renderizar tabla ──
  function renderSalidas(rows) {
    const tbody = document.getElementById('productosTableBody');
    tbody.innerHTML = '';
    if (!rows || rows.length === 0) {
      const d = getOrCreateErrorDiv();
      d.style.display = 'block';
      d.textContent = 'No hay registros de salidas para mostrar.';
      return;
    }
    for (const s of rows) {
      const almacenInfo = getAlmacenLabel(s);
      const tipoCompra = getTipoCompra(s);
      
      // Pintamos el tipo de compra con colores
      let tipoHtml = `<span class="badge-almacen" style="background:#e2e8f0;color:#334155;">NORMAL</span>`;
      if (tipoCompra === 'partida_5000') tipoHtml = `<span class="badge-almacen" style="background:#dbeafe;color:#0B2D6B;">PARTIDA 5000</span>`;
      if (tipoCompra === 'licitacion') tipoHtml = `<span class="badge-almacen" style="background:#d1fae5;color:#059669;">LICITACIÓN</span>`;

      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td><span style="font-weight:600;color:#0B2D6B">${s.id}</span></td>
        <td><span style="font-family:var(--font-brand);font-weight:600">${s.folio || ''}</span></td>
        <td><small style="color:#475569">${s.fecha || ''}</small></td>
        <td><span class="badge-almacen ${almacenInfo.class}">${almacenInfo.label}</span></td>
        <td>${tipoHtml}</td>
        <td>${s.departamento || ''}</td>
        <td>${getJefeName(s)}</td>
        <td>${s.proveedor || ''}</td>
        <td>
          <button class="btn-almacenatec ver" onclick="viewSalida(${s.id})">
            <i class="bi bi-eye"></i> Ver
          </button>
        </td>
      `;
      tbody.appendChild(tr);
    }
  }

  // ── Filtrar por almacén ──
  function filtrarPorAlmacen(tipo, btn) {
    filtroActual = tipo;
    document.querySelectorAll('.btn-filtro').forEach(b => b.classList.remove('active', 'active-general', 'active-activo', 'active-mantenimiento'));
    if (btn) {
      btn.classList.add('active');
      if (tipo === 'general') btn.classList.add('active-general');
      else if (tipo === 'activo') btn.classList.add('active-activo');
      else if (tipo === 'mantenimiento') btn.classList.add('active-mantenimiento');
    }
    aplicarFiltros();
  }

   function aplicarFiltros() {
    const busqueda = (document.getElementById('searchItem').value || '').toLowerCase().trim();
    let filtradas = [...todasLasSalidas];

    //  FILTRO ESTRICTO PARA ACTIVO FIJO
    if (userRole === 'activo_fijo') {
        filtradas = filtradas.filter(s => {
            return s.tipo_compra_orden === 'partida_5000' || s.tipo_compra_orden === 'licitacion';
        });
    }

    if (filtroActual !== 'todos') {
      filtradas = filtradas.filter(s => getAlmacen(s) === filtroActual);
    }

    if (busqueda) {
      filtradas = filtradas.filter(s =>
        (s.folio || '').toLowerCase().includes(busqueda) ||
        (s.proveedor || '').toLowerCase().includes(busqueda) ||
        (s.departamento || '').toLowerCase().includes(busqueda) ||
        (s.observaciones || '').toLowerCase().includes(busqueda)
      );
    }

    renderSalidas(filtradas);
  }

  // ── Ver detalle de salida ──
  function viewSalida(id) {
    const s = todasLasSalidas.find(x => x.id == id);
    if (!s) return alert('Salida no encontrada');
    const body = document.getElementById('modalSalidaBody');
    const almacenInfo = getAlmacenLabel(s);
    const tipoCompra = getTipoCompra(s).replace('_', ' ').toUpperCase();

    let html = `
      <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;margin-bottom:16px">
        <div style="background:#F0F4F9;padding:12px 14px;border-radius:8px;border-left:3px solid #0B2D6B">
          <div style="font-size:11px;font-weight:700;color:#94A3B8;text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px">Folio</div>
          <div style="font-weight:700;font-size:16px;color:#0B2D6B">${s.folio || '—'}</div>
        </div>
        <div style="background:#F0F4F9;padding:12px 14px;border-radius:8px;border-left:3px solid #c9973a">
          <div style="font-size:11px;font-weight:700;color:#94A3B8;text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px">Fecha</div>
          <div style="font-weight:600;font-size:15px">${s.fecha || '—'}</div>
        </div>
        <div style="background:#F0F4F9;padding:12px 14px;border-radius:8px;border-left:3px solid ${almacenInfo.class === 'activo' ? '#059669' : almacenInfo.class === 'general' ? '#0b2d6b' : '#d97706'}">
          <div style="font-size:11px;font-weight:700;color:#94A3B8;text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px">Almacén</div>
          <div><span class="badge-almacen ${almacenInfo.class}">${almacenInfo.label}</span></div>
        </div>
      </div>
      <table style="width:100%;font-size:13px;margin-bottom:12px">
        <tr><td style="padding:5px 0;color:#94A3B8;width:140px">Tipo Compra</td><td style="font-weight:600">${tipoCompra}</td></tr>
        <tr><td style="padding:5px 0;color:#94A3B8">Departamento</td><td style="font-weight:600">${s.departamento || '—'}</td></tr>
        <tr><td style="padding:5px 0;color:#94A3B8">Jefe</td><td style="font-weight:600">${getJefeName(s) || '—'}</td></tr>
        <tr><td style="padding:5px 0;color:#94A3B8">Proveedor</td><td>${s.proveedor || '—'}</td></tr>
        <tr><td style="padding:5px 0;color:#94A3B8">Observaciones</td><td>${s.observaciones || '—'}</td></tr>
      </table>`;
      
    if (s.detalles && s.detalles.length) {
      html += `<hr style="border-color:#E2E8F0"><h6 style="font-weight:700;margin-bottom:8px"><i class="bi bi-list-ul me-1"></i>Detalles</h6><div style="overflow-x:auto"><table class="table table-sm table-striped"><thead><tr><th>Producto</th><th>Descripción</th><th>Cantidad</th><th>Unidad</th><th>Importe</th></tr></thead><tbody>`;
      for (const d of s.detalles) {
        html += `<tr><td>${d.producto_id ? (d.codigo_barras || d.producto_id) : ''}</td><td>${d.descripcion || ''}</td><td>${d.cantidad || ''}</td><td>${d.unidad || ''}</td><td>${d.importe || ''}</td></tr>`;
      }
      html += `</tbody></table></div>`;
    }
    body.innerHTML = html;
    new bootstrap.Modal(document.getElementById('modalSalida')).show();

    // Botón PDF
    document.getElementById('btnDownloadPdf').onclick = async function() {
      const folioSafe = (s.folio || 'salida').replace(/[^a-z0-9-_]/gi, '_');
      const filename = `salida_${folioSafe}_${s.fecha || ''}.pdf`;
      const pdfEl = document.createElement('div');
      pdfEl.style.cssText = 'font-family:Arial,Helvetica,sans-serif;color:#222;position:relative;min-height:297mm;padding:20mm;box-sizing:border-box;';
      pdfEl.innerHTML = `
        <img src="../media/hoja_membretada_final.jpg" style="position:absolute;top:0;left:0;width:100%;height:100%;z-index:-1;" />
        <div style="background:rgba(255,255,255,.9);padding:10mm;border-radius:5px;margin-top:60mm;">
          <div style="text-align:center;margin-bottom:12px">
            <h2 style="margin:0;color:#0B2D6B">AlmacenaTec</h2>
            <div style="font-size:12px;color:#666">Historial de Salidas — TecNM Campus Saltillo</div>
            <div style="margin-top:10px;font-size:13px;color:#333"><strong>Folio:</strong> ${s.folio || ''} &nbsp;·&nbsp; <strong>Fecha:</strong> ${s.fecha || ''}</div>
            <div style="margin-top:4px;font-size:12px"><span class="badge-almacen ${almacenInfo.class}">${almacenInfo.label}</span></div>
            <hr style="border:none;border-top:1px solid #ddd;margin:10px 0">
          </div>
          <div style="margin-bottom:8px;display:flex;gap:12px;flex-wrap:wrap;font-size:13px;color:#333">
            <div><strong>Tipo Compra:</strong> ${tipoCompra}</div>
            <div><strong>Departamento:</strong> ${s.departamento || ''}</div>
            <div><strong>Jefe:</strong> ${getJefeName(s)}</div>
          </div>
      `;
      if (s.detalles && s.detalles.length) {
        let rows = '';
        for (const d of s.detalles) {
          rows += `<tr style="border-bottom:1px solid #eee"><td style="padding:6px">${d.producto_id ? (d.codigo_barras || d.producto_id) : ''}</td><td style="padding:6px">${d.descripcion || ''}</td><td style="padding:6px;text-align:right">${d.cantidad || ''}</td><td style="padding:6px">${d.unidad || ''}</td><td style="padding:6px;text-align:right">${d.importe || ''}</td></tr>`;
        }
        pdfEl.innerHTML += `<table style="width:100%;border-collapse:collapse;margin-top:10px;font-size:13px"><thead><tr style="background:#0B2D6B;color:#fff"><th style="padding:8px">Producto</th><th style="padding:8px">Descripción</th><th style="padding:8px;text-align:right">Cantidad</th><th style="padding:8px">Unidad</th><th style="padding:8px;text-align:right">Importe</th></tr></thead><tbody>${rows}</tbody></table>`;
      }
      pdfEl.innerHTML += '</div>';
      try {
        if (typeof html2pdf !== 'undefined') {
          await html2pdf().set({
            margin: 10,
            filename,
            image: { type: 'jpeg', quality: 0.98 },
            html2canvas: { scale: 2, useCORS: false, allowTaint: true },
            jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
          }).from(pdfEl).save();
          return;
        }
      } catch(err) { console.error('html2pdf failed', err); }
      const w = window.open('', '_blank');
      const st = '<style>body{font-family:Arial,sans-serif;margin:20px}table{width:100%;border-collapse:collapse;margin-top:10px}th,td{border:1px solid #444;padding:6px}</style>';
      let p = '<!doctype html><html><head><meta charset="utf-8"><title>' + filename + '</title>' + st + '</head><body>' + pdfEl.innerHTML;
      p += '<script>setTimeout(function(){window.print();},250);<\/script></body></html>';
      w.document.open();
      w.document.write(p);
      w.document.close();
    };
  }

  // ── Event listeners ──
  document.addEventListener('DOMContentLoaded', function() {
    fetchSalidas();
    document.getElementById('searchItem').addEventListener('input', function() {
      aplicarFiltros();
    });
  });
</script>
</body>
</html>