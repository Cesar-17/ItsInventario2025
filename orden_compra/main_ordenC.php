<?php require_once __DIR__ . '/../includes/auth.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Orden de Compra — AlmacenaTec</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="../css/bootstrap.min.css">
  <link rel="stylesheet" href="../css/bootstrap-grid.min.css">
  <link rel="stylesheet" href="../css/style.css">
  <script src="https://cdn.jsdelivr.net/npm/html2pdf.js@0.9.3/dist/html2pdf.bundle.min.js"></script>
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
      <button class="topbar-toggle" onclick="toggleSidebar()" title="Menú">
        <i class="bi bi-list" style="font-size:22px"></i>
      </button>
      <div class="topbar-info">
        <h1>Orden de Compra</h1>
        <div class="topbar-breadcrumb">
          <a href="../home/index.php">Inicio</a> &rsaquo; Orden de Compra
        </div>
      </div>
      <div style="display:flex;align-items:center;gap:10px;flex-shrink:0">
        <span style="font-size:13px;color:var(--c-text-md)">
          <i class="bi bi-person-circle me-1"></i>
          <?= htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Usuario') ?>
        </span>
        <a href="<?= BASE_PATH ?>logout.php" class="btn-accent-brand" style="padding:6px 12px;font-size:12px">
          <i class="bi bi-box-arrow-right me-1"></i> Salir
        </a>
      </div>
    </header>

    <main class="content">

      <div class="page-header">
        <h2><i class="bi bi-cart3 me-2" style="color:var(--c-primary)"></i>Historial de Compras</h2>
        <button class="btn-primary-brand" onclick="location.href='../orden_compra/capturarCompra.php'">
          <i class="bi bi-plus-lg"></i> Nueva Orden
        </button>
      </div>

      <div class="card-panel">
        <div class="card-panel-header">
          <h3><i class="bi bi-table me-2" style="color:var(--c-primary)"></i>Órdenes registradas</h3>
          <div class="search-wrap" style="min-width:240px">
            <i class="bi bi-search"></i>
            <input type="text" id="searchItem" class="form-control" placeholder="Buscar por no. orden o proveedor…">
          </div>
        </div>
        <div class="table-wrap">
          <table class="table table-striped table-hover text-center mb-0">
            <thead>
              <tr>
                <th>ID</th><th>No. Orden</th><th>Fecha</th><th>Proveedor</th>
                <th>Estado</th><th>Total</th><th>Acciones</th>
              </tr>
            </thead>
            <tbody id="productosTableBody"></tbody>
          </table>
        </div>
      </div>

    </main>
  </div>
</div>

<!-- Modal: detalle de orden -->
<div class="modal fade" id="modalOrden" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-file-earmark-text me-2"></i>Detalle de orden</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body" id="modalOrdenBody"></div>
      <div class="modal-footer" id="modalOrdenFooter"></div>
    </div>
  </div>
</div>

<script src="../js/bootstrap.bundle.min.js"></script>
<script>
  function toggleSidebar(){
    const sidebar=document.getElementById('sidebar'),overlay=document.getElementById('sidebarOverlay'),wrapper=document.getElementById('mainWrapper');
    if(window.innerWidth<=1100){const open=sidebar.classList.toggle('open');overlay.classList.toggle('visible',open);}
    else{sidebar.classList.toggle('collapsed');wrapper.classList.toggle('sidebar-hidden');}
  }
  function closeSidebar(){document.getElementById('sidebar').classList.remove('open');document.getElementById('sidebarOverlay').classList.remove('visible');}

  function getOrCreateErrorDiv(){
    let d=document.getElementById('fetchError');
    if(!d){d=document.createElement('div');d.id='fetchError';d.style.cssText='margin:10px 0;color:#856404;background:#fff3cd;padding:8px 12px;border-radius:6px;display:none;font-size:13px;';const c=document.querySelector('.card-panel');if(c)c.insertBefore(d,c.firstChild);}
    return d;
  }

  async function fetchOrdenes(){
    const errDiv=getOrCreateErrorDiv();
    try{
      const res=await fetch('<?= BASE_PATH ?>includes/get_ordenes.php');
      const text=await res.text();let data;
      try{data=JSON.parse(text);}catch(ex){errDiv.textContent='Respuesta no JSON: '+text.substring(0,1000);console.error(text);return;}
      if(!res.ok){errDiv.textContent='Error HTTP '+res.status;return;}
      if(!data.ok){errDiv.textContent='Error: '+(data.error||'respuesta ok=false');return;}
      errDiv.textContent='';window._ordenes=data.ordenes||[];renderOrdenes(window._ordenes);
    }catch(err){errDiv.textContent='Error al obtener órdenes: '+err.message;}
  }

  function renderOrdenes(rows){
    const tbody=document.getElementById('productosTableBody');const errDiv=getOrCreateErrorDiv();tbody.innerHTML='';
    if(!rows||rows.length===0){errDiv.textContent='No hay órdenes para mostrar.';errDiv.style.display='block';return;}
    errDiv.textContent='';errDiv.style.display='none';
    for(const o of rows){
      const tr=document.createElement('tr');const numero=o.numero||o.folio||'';const estado=o.estado||o.estatus||'';
      const canMark=!(estado&&estado.toLowerCase().includes('recib'));const isRecv=!canMark;
      tr.innerHTML=`<td><span style="font-weight:700;color:var(--c-primary)">${o.id}</span></td>
        <td><span style="font-family:var(--font-brand);font-weight:700">${numero}</span></td>
        <td><small style="color:var(--c-text-md)">${o.fecha}</small></td>
        <td>${o.proveedor||''}</td>
        <td>${isRecv?'<span class="badge-received"><i class="bi bi-check-circle-fill"></i> Recibida</span>':'<span class="badge-pending"><i class="bi bi-clock-fill"></i> Pendiente</span>'}</td>
        <td style="font-weight:700;color:var(--c-primary)">${o.total?'$'+o.total:''}</td>
        <td><div style="display:flex;gap:5px;justify-content:center;flex-wrap:wrap">
          <button class="btn-tbl-view" onclick="viewOrden(${o.id})"><i class="bi bi-eye"></i> Ver</button>
          ${canMark?`<button class="btn-tbl-success" onclick="markReceived(${o.id},this)"><i class="bi bi-check-lg"></i> Recibida</button>`:''}
        </div></td>`;
      tbody.appendChild(tr);
    }
  }

  function viewOrden(id){
    const o=(window._ordenes||[]).find(x=>x.id==id);if(!o)return alert('Orden no encontrada');
    const body=document.getElementById('modalOrdenBody');const numero2=o.numero||o.folio||'';const estado2=o.estado||o.estatus||'';
    const isRecv=estado2&&String(estado2).toLowerCase().includes('recib');
    let html=`<div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px">
      <div style="background:#f8faff;padding:12px 14px;border-radius:8px;border-left:3px solid var(--c-primary)">
        <div style="font-size:11px;font-weight:700;color:var(--c-text-muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px">No. Orden</div>
        <div style="font-weight:800;font-size:16px;color:var(--c-primary)">${numero2||'—'}</div>
      </div>
      <div style="background:#f8faff;padding:12px 14px;border-radius:8px;border-left:3px solid var(--c-gold)">
        <div style="font-size:11px;font-weight:700;color:var(--c-text-muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px">Fecha</div>
        <div style="font-weight:600;font-size:15px">${o.fecha||'—'}</div>
      </div></div>
      <table style="width:100%;font-size:13px;margin-bottom:12px">
        <tr><td style="padding:5px 0;color:var(--c-text-muted);width:130px">Proveedor</td><td style="font-weight:600">${o.proveedor||'—'}</td></tr>
        <tr><td style="padding:5px 0;color:var(--c-text-muted)">Estado</td>
            <td>${isRecv?'<span class="badge-received"><i class="bi bi-check-circle-fill"></i> Recibida</span>':'<span class="badge-pending"><i class="bi bi-clock-fill"></i> Pendiente</span>'}</td></tr>
        <tr><td style="padding:5px 0;color:var(--c-text-muted)">Observaciones</td><td>${o.descripcion_general||o.observaciones||'—'}</td></tr>
        <tr><td style="padding:5px 0;color:var(--c-text-muted)">Total</td><td style="font-weight:700;color:var(--c-primary);font-size:15px">${o.total?'$'+o.total:'—'}</td></tr>
      </table>`;
    if(o.detalles&&o.detalles.length){
      html+=`<hr style="border-color:var(--c-border)"><h6 style="font-weight:700;margin-bottom:8px"><i class="bi bi-list-ul me-1"></i>Detalles</h6>
        <div style="overflow-x:auto"><table class="table table-sm table-striped">
          <thead><tr><th>#</th><th>Producto</th><th>Descripción</th><th>Cant.</th><th>Unidad</th><th>Precio</th><th>Importe</th></tr></thead><tbody>`;
      let idx=1;
      for(const d of o.detalles){const precio=d.precio||d.precio_unitario||'';html+=`<tr><td>${idx++}</td><td>${d.producto_id?(d.codigo_barras||d.producto_id):''}</td><td>${d.descripcion}</td><td>${d.cantidad}</td><td>${d.unidad||''}</td><td>${precio}</td><td>${d.importe||''}</td></tr>`;}
      html+='</tbody></table></div>';
    }
    body.innerHTML=html;
    const footer=document.getElementById('modalOrdenFooter');
    const canMarkInline=!(estado2&&String(estado2).toLowerCase().includes('recib'));
    footer.innerHTML=`<button type="button" class="btn-outline-brand" data-bs-dismiss="modal"><i class="bi bi-x-lg me-1"></i> Cerrar</button>
      <button type="button" class="btn-primary-brand" id="btnDownloadPdfOrden"><i class="bi bi-file-earmark-pdf me-1"></i> Generar PDF</button>`;
    new bootstrap.Modal(document.getElementById('modalOrden')).show();

    document.getElementById('btnDownloadPdfOrden').onclick=async function(){
      const numeroSafe=(numero2||'orden').replace(/[^a-z0-9-_]/gi,'_');const filename=`orden_${numeroSafe}_${o.fecha||''}.pdf`;
      const pdfEl=document.createElement('div');
      pdfEl.style.cssText='font-family:Arial,Helvetica,sans-serif;color:#222;position:relative;min-height:297mm;padding:20mm;box-sizing:border-box;';
      pdfEl.innerHTML=`<img src="../media/hoja_membretada_final.jpg" style="position:absolute;top:0;left:0;width:100%;height:100%;z-index:-1;" />
<div style="background:rgba(255,255,255,.9);padding:10mm;border-radius:5px;margin-top:60mm;">
  <div style="text-align:center;margin-bottom:12px"><h2 style="margin:0;color:#0B2D6B">AlmacenaTec</h2>
  <div style="font-size:12px;color:#666">Orden de Compra — TecNM Campus Saltillo</div>
  <div style="margin-top:10px;font-size:13px;color:#333"><strong>No. Orden:</strong> ${numero2} &nbsp;·&nbsp; <strong>Fecha:</strong> ${o.fecha||''}</div>
  <hr style="border:none;border-top:1px solid #ddd;margin:10px 0"></div>
  <div style="display:flex;justify-content:space-between;gap:8px;flex-wrap:wrap;font-size:13px;margin-bottom:8px">
    <div><strong>Proveedor:</strong> ${o.proveedor||''}</div><div><strong>Jefe:</strong> ${o.jefe_nombre||o.jefe||''}</div>
  </div>`;
      if(o.detalles&&o.detalles.length){
        let rows='';let idx=1;
        for(const d of o.detalles){const precio=d.precio||d.precio_unitario||'';rows+=`<tr style="border-bottom:1px solid #eee"><td style="padding:6px">${idx++}</td><td style="padding:6px">${d.producto_id?(d.codigo_barras||d.producto_id):''}</td><td style="padding:6px">${d.descripcion||''}</td><td style="padding:6px;text-align:right">${d.cantidad||''}</td><td style="padding:6px">${d.unidad||''}</td><td style="padding:6px;text-align:right">${precio}</td><td style="padding:6px;text-align:right">${d.importe||''}</td></tr>`;}
        pdfEl.innerHTML+=`<table style="width:100%;border-collapse:collapse;margin-top:10px;font-size:13px"><thead><tr style="background:#0B2D6B;color:#fff"><th style="padding:8px">#</th><th style="padding:8px">Producto</th><th style="padding:8px">Descripción</th><th style="padding:8px;text-align:right">Cantidad</th><th style="padding:8px">Unidad</th><th style="padding:8px;text-align:right">Precio</th><th style="padding:8px;text-align:right">Importe</th></tr></thead><tbody>${rows}</tbody></table>`;
      }
      pdfEl.innerHTML+=`<p style="margin-top:14px;font-size:14px"><strong>Total: $${o.total||''}</strong></p></div>`;
      async function ensureHtml2Pdf(){if(typeof html2pdf!=='undefined')return;return new Promise((resolve,reject)=>{const s=document.createElement('script');s.src='https://unpkg.com/html2pdf.js@0.9.3/dist/html2pdf.bundle.min.js';s.onload=()=>resolve();s.onerror=()=>reject(new Error('no se pudo cargar html2pdf'));document.head.appendChild(s);});}
      try{await ensureHtml2Pdf();await html2pdf().set({margin:10,filename,image:{type:'jpeg',quality:0.98},html2canvas:{scale:2,useCORS:false,allowTaint:true},jsPDF:{unit:'mm',format:'a4',orientation:'portrait'}}).from(pdfEl).save();}
      catch(err){const w=window.open('','_blank');const st='<style>body{font-family:Arial,sans-serif;margin:20px}table{width:100%;border-collapse:collapse;margin-top:10px}th,td{border:1px solid #444;padding:6px}</style>';let p='<!doctype html><html><head><meta charset="utf-8"><title>'+filename+'</title>'+st+'</head><body>'+pdfEl.innerHTML;p+='<script>setTimeout(function(){window.print();},250);<\/script></body></html>';w.document.write(p);w.document.close();}
    };
  }

  async function markReceived(id,btn){
    if(!confirm('¿Marcar la orden como recibida?'))return;
    try{
      if(btn)btn.disabled=true;
      const res=await fetch('<?= BASE_PATH ?>handlers/toggle_orden_estado.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({id,estatus:'recibida'})});
      const j=await res.json();if(!j.ok)throw new Error(j.error||'Error desconocido');
      const o=(window._ordenes||[]).find(x=>x.id==id);if(o)o.estatus=j.estatus||'recibida';
      renderOrdenes(window._ordenes||[]);
    }catch(err){alert('Error: '+err.message);if(btn)btn.disabled=false;}
  }

  document.addEventListener('DOMContentLoaded',function(){
    fetchOrdenes();
    document.getElementById('searchItem').addEventListener('input',function(){
      const q=this.value.toLowerCase().trim();
      if(!q)return renderOrdenes(window._ordenes||[]);
      renderOrdenes((window._ordenes||[]).filter(x=>(x.numero||'').toLowerCase().includes(q)||(x.proveedor||'').toLowerCase().includes(q)));
    });
  });
</script>
</body>
</html>
