<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
$nombre = htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Usuario');
$rol = $_SESSION['usuario_rol'] ?? 'usuario';
$rolesRestringidos = ['almacenista', 'mantenimiento'];
$soloProductos = in_array($rol, $rolesRestringidos);

// Obtener lista de activos disponibles para transferir
$activosDisponibles = [];
try {
    $stmt = $pdo->query("SELECT id, num_oficina, descripcion_bien, marca, modelo, serie, area_pertenece, resguardante FROM activos_fijos WHERE disponible = 1 AND tipo = 'entrada' ORDER BY id DESC");
    $activosDisponibles = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $activosDisponibles = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transferir Activo — AlmacenaTec</title>
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
        .tipo-header {
            background: #0B2D6B;
            color: #fff;
            padding: 16px 24px;
            border-radius: var(--radius-lg) var(--radius-lg) 0 0;
        }
        .tipo-header h3 {
            color: #fff;
            margin: 0;
        }
        .autocomplete-container {
            position: relative;
        }
        .autocomplete-list {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: #fff;
            border: 1px solid var(--c-border);
            border-radius: var(--radius-sm);
            max-height: 200px;
            overflow-y: auto;
            z-index: 1000;
            display: none;
            box-shadow: var(--shadow-md);
        }
        .autocomplete-list .item {
            padding: 8px 12px;
            cursor: pointer;
            border-bottom: 1px solid var(--c-border);
            font-size: 13px;
        }
        .autocomplete-list .item:hover {
            background: var(--c-bg);
        }
        .autocomplete-list .item .small-text {
            font-size: 11px;
            color: var(--c-text-muted);
        }
        .btn-transferir {
            background: #0B2D6B;
            color: #fff;
            border: none;
            padding: 9px 18px;
            border-radius: var(--radius-sm);
            font-size: 13.5px;
            font-weight: 600;
            cursor: pointer;
            transition: all .2s;
            text-decoration: none;
        }
        .btn-transferir:hover {
            background: #0B2D6B;
            color: #fff;
        }
        .info-activo {
            background: #f8faff;
            padding: 16px;
            border-radius: var(--radius-sm);
            border: 1px solid var(--c-border);
            margin-bottom: 16px;
        }
        .info-activo .label {
            font-size: 11px;
            color: var(--c-text-muted);
            text-transform: uppercase;
            font-weight: 600;
        }
        .info-activo .value {
            font-weight: 600;
            font-size: 15px;
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
                <h1><i class="bi bi-arrow-left-right me-2" style="color:#8b5cf6"></i>Transferir Activo Fijo</h1>
                <div class="topbar-breadcrumb"><a href="../home/index.php">Inicio</a> &rsaquo; <a href="index.php">Activo Fijo</a> &rsaquo; Transferir</div>
            </div>
            <div style="display:flex;align-items:center;gap:10px;flex-shrink:0">
                <span style="font-size:13px;color:var(--c-text-md)"><i class="bi bi-person-circle me-1"></i><?= $nombre ?></span>
                <a href="<?= BASE_PATH ?>logout.php" class="btn-accent-brand" style="padding:6px 12px;font-size:12px"><i class="bi bi-box-arrow-right me-1"></i> Salir</a>
            </div>
        </header>

        <main class="content">
            <div class="card-panel" style="max-width: 900px; margin: 0 auto;">
                <div class="tipo-header">
                    <h3><i class="bi bi-arrow-left-right me-2"></i>Transferencia de Activo Fijo</h3>
                    <small>Registra la transferencia de un activo a otro propietario o área</small>
                </div>

                <div style="padding: 24px;">
                    <form method="POST" action="<?= BASE_PATH ?>handlers/save_transferencia.php" id="formTransferencia">

                        <!-- Seleccionar activo -->
                        <div class="form-section-title"><i class="bi bi-search me-1"></i>Seleccionar activo a transferir</div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-12">
                                <label class="form-label">Buscar activo <span class="text-danger">*</span></label>
                                <div class="autocomplete-container">
                                    <input type="text" id="buscarActivo" class="form-control" placeholder="Escribe para buscar por descripción, marca, modelo, serie o número de oficina..." autocomplete="off">
                                    <div class="autocomplete-list" id="autocompleteList"></div>
                                </div>
                                <input type="hidden" name="activo_id" id="activo_id" value="">
                                <small class="text-muted">Escribe y selecciona el activo que deseas transferir</small>
                            </div>
                        </div>

                        <!-- Información del activo seleccionado -->
                        <div id="infoActivo" style="display:none;">
                            <div class="form-section-title"><i class="bi bi-info-circle me-1"></i>Información del Activo</div>
                            <div class="info-activo">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="label">Descripción</div>
                                        <div class="value" id="infoDescripcion">—</div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="label">Marca / Modelo</div>
                                        <div class="value" id="infoMarcaModelo">—</div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="label">Serie</div>
                                        <div class="value" id="infoSerie">—</div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="label">Oficina</div>
                                        <div class="value" id="infoOficina">—</div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="label">Área Actual</div>
                                        <div class="value" id="infoArea">—</div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="label">Resguardante Actual</div>
                                        <div class="value" id="infoResguardante">—</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Datos de la transferencia -->
                        <div class="form-section-title"><i class="bi bi-arrow-left-right me-1"></i>Datos de la Transferencia</div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Fecha de Transferencia <span class="text-danger">*</span></label>
                                <input type="date" name="fecha" class="form-control" value="<?= date('Y-m-d') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Motivo de Transferencia</label>
                                <input type="text" name="motivo" class="form-control" placeholder="Ej: Cambio de área, Reasignación, etc.">
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Área Destino <span class="text-danger">*</span></label>
                                <input type="text" name="area_destino" id="area_destino" class="form-control" placeholder="Ej: Sistemas, Mantenimiento, Dirección" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Resguardante Destino <span class="text-danger">*</span></label>
                                <input type="text" name="resguardante_destino" id="resguardante_destino" class="form-control" placeholder="Nombre del nuevo resguardante" required>
                            </div>
                        </div>

                        <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:20px">
                            <a href="index.php" class="btn-outline-brand"><i class="bi bi-x-lg me-1"></i> Cancelar</a>
                            <button type="submit" class="btn-transferir">
                                <i class="bi bi-arrow-left-right me-1"></i> Transferir Activo
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

<script src="../js/bootstrap.bundle.min.js"></script>
<script>
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

    // ================================================================
    // AUTOCOMPLETADO PARA TRANSFERENCIA
    // ================================================================
    const activosDisponibles = <?= json_encode($activosDisponibles) ?>;

    const inputBuscar = document.getElementById('buscarActivo');
    const listaAutocomplete = document.getElementById('autocompleteList');

    function renderizarAutocomplete(filter) {
        const filtered = activosDisponibles.filter(a => {
            const search = filter.toLowerCase();
            return (a.num_oficina || '').toLowerCase().includes(search) ||
                   (a.descripcion_bien || '').toLowerCase().includes(search) ||
                   (a.marca || '').toLowerCase().includes(search) ||
                   (a.modelo || '').toLowerCase().includes(search) ||
                   (a.serie || '').toLowerCase().includes(search) ||
                   (a.area_pertenece || '').toLowerCase().includes(search) ||
                   (a.resguardante || '').toLowerCase().includes(search);
        });

        listaAutocomplete.innerHTML = '';
        if (filtered.length === 0 || filter === '') {
            listaAutocomplete.style.display = 'none';
            return;
        }

        filtered.forEach(a => {
            const div = document.createElement('div');
            div.className = 'item';
            div.innerHTML = `
                <strong>${a.descripcion_bien || 'Sin descripción'}</strong>
                <div class="small-text">
                    Oficina: ${a.num_oficina || 'N/A'} | 
                    Marca: ${a.marca || 'N/A'} | 
                    Modelo: ${a.modelo || 'N/A'} | 
                    Serie: ${a.serie || 'N/A'}
                    ${a.area_pertenece ? `| Área: ${a.area_pertenece}` : ''}
                    ${a.resguardante ? `| Resguardante: ${a.resguardante}` : ''}
                </div>
            `;
            div.onclick = function() {
                seleccionarActivo(a);
            };
            listaAutocomplete.appendChild(div);
        });
        listaAutocomplete.style.display = 'block';
    }

    function seleccionarActivo(a) {
        document.getElementById('activo_id').value = a.id;
        document.getElementById('infoDescripcion').textContent = a.descripcion_bien || '—';
        document.getElementById('infoMarcaModelo').textContent = (a.marca || '') + ' ' + (a.modelo || '') || '—';
        document.getElementById('infoSerie').textContent = a.serie || '—';
        document.getElementById('infoOficina').textContent = a.num_oficina || '—';
        document.getElementById('infoArea').textContent = a.area_pertenece || 'Sin asignar';
        document.getElementById('infoResguardante').textContent = a.resguardante || 'Sin asignar';
        
        document.getElementById('infoActivo').style.display = 'block';
        inputBuscar.value = a.descripcion_bien || a.num_oficina || '';
        listaAutocomplete.style.display = 'none';
        document.getElementById('buscarActivo').value = a.descripcion_bien || a.num_oficina || '';
    }

    inputBuscar.addEventListener('input', function() {
        const val = this.value.trim();
        renderizarAutocomplete(val);
    });

    inputBuscar.addEventListener('blur', function() {
        setTimeout(() => {
            listaAutocomplete.style.display = 'none';
        }, 200);
    });

    inputBuscar.addEventListener('focus', function() {
        const val = this.value.trim();
        if (val !== '') renderizarAutocomplete(val);
    });

    document.getElementById('formTransferencia').addEventListener('submit', function(e) {
        const activoId = document.getElementById('activo_id').value;
        if (!activoId) {
            e.preventDefault();
            alert('Por favor, selecciona un activo de la lista para transferir.');
            inputBuscar.focus();
        }
        const areaDestino = document.getElementById('area_destino').value.trim();
        const resguardanteDestino = document.getElementById('resguardante_destino').value.trim();
        if (!areaDestino || !resguardanteDestino) {
            e.preventDefault();
            alert('Por favor, completa el área destino y el resguardante destino.');
        }
    });
</script>
</body>
</html>