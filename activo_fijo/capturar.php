<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

$nombre = htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Usuario');
$tipo = isset($_GET['tipo']) && in_array($_GET['tipo'], ['entrada', 'salida']) ? $_GET['tipo'] : 'entrada';
$tipoLabel = $tipo === 'entrada' ? 'Entrada' : 'Salida';
$tipoIcon = $tipo === 'entrada' ? 'bi-arrow-down-circle' : 'bi-arrow-up-circle';
$tipoColor = $tipo === 'entrada' ? '#059669' : '#dc2626';

$rol = $_SESSION['usuario_rol'] ?? 'usuario';

// 🔥 ARREGLO 1: AGREGAMOS tipo_compra A LA CONSULTA SQL 🔥
$activosDisponibles = [];
if ($tipo === 'salida') {
    try {
        $sql = "SELECT id, num_oficina, descripcion_bien, marca, modelo, serie, num_simenu, sep, etiqueta, departamento_recibe, jefe_departamento, tipo_compra FROM activos_fijos WHERE disponible = 1 AND tipo = 'entrada'";
        
        if ($rol === 'activo_fijo') {
            $sql .= " AND tipo_compra IN ('partida_5000', 'licitacion')";
        }
        
        $sql .= " ORDER BY id DESC";
        
        $stmt = $pdo->query($sql);
        $activosDisponibles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $activosDisponibles = [];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $tipoLabel ?> de Activo — AlmacenaTec</title>
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
            background: <?= $tipoColor ?>;
            color: #fff;
            padding: 16px 24px;
            border-radius: var(--radius-lg) var(--radius-lg) 0 0;
        }
        .tipo-header h3 { color: #fff; margin: 0; }
        .switch-container { display: flex; align-items: center; gap: 12px; margin-top: 8px; }
        .switch-container label { display: flex; align-items: center; gap: 8px; font-weight: 600; cursor: pointer; }
        .switch-container input[type="checkbox"] { width: 20px; height: 20px; cursor: pointer; }
        .disponible-text { font-weight: 600; }
        .disponible-text.si { color: #059669; }
        .disponible-text.no { color: #dc2626; }
        .btn-entrada {
            background: #059669; color: #fff; border: none; padding: 9px 18px;
            border-radius: var(--radius-sm); font-size: 13.5px; font-weight: 600;
            font-family: var(--font-brand); cursor: pointer; transition: all .2s; text-decoration: none;
        }
        .btn-entrada:hover { background: #047857; color: #fff; }
        .autocomplete-container { position: relative; }
        .autocomplete-list {
            position: absolute; top: 100%; left: 0; right: 0; background: #fff;
            border: 1px solid var(--c-border); border-radius: var(--radius-sm);
            max-height: 200px; overflow-y: auto; z-index: 1000; display: none; box-shadow: var(--shadow-md);
        }
        .autocomplete-list .item { padding: 8px 12px; cursor: pointer; border-bottom: 1px solid var(--c-border); font-size: 13px; }
        .autocomplete-list .item:hover { background: var(--c-bg); }
        .autocomplete-list .item .small-text { font-size: 11px; color: var(--c-text-muted); }
        .tipo-equipo { display: flex; gap: 24px; flex-wrap: wrap; padding: 12px 0; }
        .tipo-equipo label {
            display: flex; align-items: center; gap: 8px; font-weight: 600; cursor: pointer;
            padding: 8px 16px; border: 2px solid var(--c-border); border-radius: var(--radius-sm); transition: all .2s;
        }
        .tipo-equipo label:hover { border-color: var(--c-primary); }
        .tipo-equipo input[type="checkbox"] { width: 18px; height: 18px; cursor: pointer; }
        .tipo-equipo label.seleccionado { border-color: var(--c-primary); background: rgba(11,45,107,.05); }
        .campos-especificos {
            background: #f8faff; padding: 16px 20px; border-radius: var(--radius-sm);
            border: 1px solid var(--c-border); margin-top: 12px; display: none;
        }
        .campos-especificos.visible { display: block; }
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
            <?php if (!in_array($rol, ['almacenista', 'mantenimiento'])): ?>
            <a href="../activo_fijo/index.php" class="sidebar-link active"><i class="bi bi-safe-fill"></i> Activo Fijo</a>
            <a href="../activo_fijo/main_activF.php" class="sidebar-link"><i class="bi bi-clipboard2-data"></i> Historial Salidas</a>
             <a href="../orden_compra/requisiciones.php" class="sidebar-link"><i class="bi bi-cart3"></i> Almacén General</a>
            <?php endif; ?>

            <?php if ($rol === 'admin'): ?>
            <div class="nav-section" style="margin-top:8px">Administración</div>
            <a href="../usuarios/main_usuarios.php" class="sidebar-link"><i class="bi bi-people"></i> Usuarios</a>
            <?php endif; ?>
        </nav>
        <div class="sidebar-footer"><i class="bi bi-geo-alt-fill me-1"></i> TecNM · Campus Saltillo · 2025</div>
    </aside>

    <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

    <div class="main-wrapper" id="mainWrapper">
        <header class="topbar">
            <button class="topbar-toggle" onclick="toggleSidebar()"><i class="bi bi-list" style="font-size:22px"></i></button>
            <div class="topbar-info">
                <h1><i class="bi <?= $tipoIcon ?> me-2" style="color:<?= $tipoColor ?>"></i><?= $tipoLabel ?> de Activo Fijo</h1>
                <div class="topbar-breadcrumb"><a href="../home/index.php">Inicio</a> &rsaquo; <a href="index.php">Activo Fijo</a> &rsaquo; <?= $tipoLabel ?></div>
            </div>
            <div style="display:flex;align-items:center;gap:10px;flex-shrink:0">
                <span style="font-size:13px;color:var(--c-text-md)"><i class="bi bi-person-circle me-1"></i><?= $nombre ?></span>
                <a href="<?= BASE_PATH ?>logout.php" class="btn-accent-brand" style="padding:6px 12px;font-size:12px"><i class="bi bi-box-arrow-right me-1"></i> Salir</a>
            </div>
        </header>

        <main class="content">
            <div class="card-panel" style="max-width: 1000px; margin: 0 auto;">
                <div class="tipo-header">
                    <h3><i class="bi <?= $tipoIcon ?> me-2"></i><?= $tipoLabel ?> de Activo Fijo</h3>
                    <small>Formulario para registrar la <?= strtolower($tipoLabel) ?> de un activo fijo</small>
                </div>

                <div style="padding: 24px;">
                    <form method="POST" action="<?= BASE_PATH ?>handlers/save_activo_fijo.php" id="formActivo">
                        <input type="hidden" name="tipo" value="<?= $tipo ?>">

                        <?php if ($tipo === 'salida'): ?>
                        <div class="form-section-title"><i class="bi bi-search me-1"></i>Seleccionar activo a dar de salida</div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-12">
                                <label class="form-label">Buscar activo <span class="text-danger">*</span></label>
                                <div class="autocomplete-container">
                                    <input type="text" id="buscarActivo" class="form-control" placeholder="Escribe para buscar..." autocomplete="off">
                                    <div class="autocomplete-list" id="autocompleteList"></div>
                                </div>
                                <input type="hidden" name="activo_id" id="activo_id" value="">
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="form-section-title"><i class="bi bi-building me-1"></i>Datos del activo</div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Número de Oficina <span class="text-danger">*</span></label>
                                <input type="text" name="num_oficina" id="num_oficina" class="form-control" placeholder="Ej: OF-001" required <?= $tipo === 'salida' ? 'readonly' : '' ?>>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Fecha <span class="text-danger">*</span></label>
                                <input type="date" name="fecha" class="form-control" value="<?= date('Y-m-d') ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Jefe de Departamento <span class="text-danger">*</span></label>
                                <input type="text" name="jefe_departamento" id="jefe_departamento" class="form-control" placeholder="Nombre del jefe" required <?= $tipo === 'salida' ? 'readonly' : '' ?>>
                            </div>
                        </div>

                        <?php if ($tipo === 'entrada'): ?>
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Cantidad <span class="text-danger">*</span></label>
                                <input type="number" name="cantidad_unidades" id="cantidad_unidades" class="form-control"
                                       value="1" min="1" step="1" required
                                       onchange="validarCantidad(this)">
                                <small class="text-muted">Se generará una etiqueta por cada unidad.</small>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Tipo de Compra <span class="text-danger">*</span></label>
                                <select name="tipo_compra" id="tipo_compra" class="form-select" required>
                                    <option value="">Seleccionar...</option>
                                    <?php if ($rol !== 'activo_fijo'): ?>
                                        <option value="normal">Compra Regular</option>
                                    <?php endif; ?>
                                    <option value="partida_5000">Partida 5000</option>
                                    <option value="licitacion">Licitación</option>
                                </select>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if ($tipo === 'salida'): ?>
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Tipo de Compra <span class="text-danger">*</span></label>
                                <select name="tipo_compra" class="form-select" required>
                                    <option value="">Seleccionar...</option>
                                    <option value="normal">Compra Normal</option>
                                    <option value="partida_5000">Partida 5000</option>
                                    <option value="licitacion">Licitación</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Proveedor <span class="text-danger">*</span></label>
                                <input type="text" name="proveedor" id="proveedor" class="form-control" placeholder="Nombre del proveedor" required>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="row g-3 mb-3">
                            <div class="col-md-12">
                                <label class="form-label">Descripción del bien <span class="text-danger">*</span></label>
                                <textarea name="descripcion_bien" id="descripcion_bien" class="form-control" rows="2" placeholder="Descripción detallada del activo" required <?= $tipo === 'salida' ? 'readonly' : '' ?>></textarea>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Número de SIMENU</label>
                                <input type="text" name="num_simenu" id="num_simenu" class="form-control" placeholder="Ej: SIM-2025-001" <?= $tipo === 'salida' ? 'readonly' : '' ?>>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Código SEP</label>
                                <input type="text" name="sep" id="sep" class="form-control" placeholder="Ej: SEP-12345" <?= $tipo === 'salida' ? 'readonly' : '' ?>>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Etiqueta</label>
                                <input type="text" name="etiqueta" id="etiqueta" class="form-control" placeholder="Ej: ETQ-001" <?= $tipo === 'salida' ? 'readonly' : '' ?>>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Departamento que recibe <span class="text-danger">*</span></label>
                                <input type="text" name="departamento_recibe" id="departamento_recibe" class="form-control" placeholder="Ej: Mantenimiento, Sistemas, etc." required <?= $tipo === 'salida' ? 'readonly' : '' ?>>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Disponibilidad <span class="text-danger">*</span></label>
                                <div class="switch-container">
                                    <label>
                                        <input type="checkbox" name="disponible" value="1" id="disponible" checked onchange="actualizarDisponibilidad(this)" <?= $tipo === 'salida' ? 'disabled' : '' ?>>
                                        <span id="dispText" class="disponible-text si">✅ Disponible</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="form-section-title"><i class="bi bi-info-circle me-1"></i>Especificaciones técnicas</div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Marca <span class="text-danger">*</span></label>
                                <input type="text" name="marca" id="marca" class="form-control" placeholder="Ej: HP, Dell" required <?= $tipo === 'salida' ? 'readonly' : '' ?>>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Modelo <span class="text-danger">*</span></label>
                                <input type="text" name="modelo" id="modelo" class="form-control" placeholder="Ej: ProBook 450" required <?= $tipo === 'salida' ? 'readonly' : '' ?>>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Serie <span class="text-danger">*</span></label>
                                <input type="text" name="serie" id="serie" class="form-control" placeholder="Número de serie" required <?= $tipo === 'salida' ? 'readonly' : '' ?>>
                            </div>
                        </div>

                        <?php if ($tipo === 'entrada'): ?>
                        <div class="form-section-title"><i class="bi bi-cpu me-1"></i>Tipo de Equipo</div>

                        <div class="tipo-equipo">
                            <label id="lbl_pc">
                                <input type="checkbox" name="tipo_pc" value="pc" id="tipo_pc" onchange="toggleTipoEquipo()">
                                <i class="bi bi-pc-display"></i> PC de Escritorio
                            </label>
                            <label id="lbl_laptop">
                                <input type="checkbox" name="tipo_laptop" value="laptop" id="tipo_laptop" onchange="toggleTipoEquipo()">
                                <i class="bi bi-laptop"></i> Laptop
                            </label>
                            <label id="lbl_aire">
                                <input type="checkbox" name="tipo_aire" value="aire" id="tipo_aire" onchange="toggleTipoEquipo()">
                                <i class="bi bi-snow2"></i> Aire Acondicionado
                            </label>
                        </div>

                        <div class="campos-especificos" id="camposPcLaptop">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Memoria RAM</label>
                                    <input type="text" name="memoria_ram" id="memoria_ram" class="form-control" placeholder="Ej: 16 GB DDR4">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Sistema Operativo</label>
                                    <input type="text" name="sistema_operativo" id="sistema_operativo" class="form-control" placeholder="Ej: Windows 11 Pro">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Tipo de Disco</label>
                                    <select name="tipo_disco" id="tipo_disco" class="form-select">
                                        <option value="">Seleccionar...</option>
                                        <option value="HDD">HDD (Mecánico)</option>
                                        <option value="SSD">SSD (Sólido)</option>
                                        <option value="NVMe">NVMe</option>
                                        <option value="Mixto">Mixto (HDD + SSD)</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Capacidad de Disco</label>
                                    <input type="text" name="capacidad_disco" id="capacidad_disco" class="form-control" placeholder="Ej: 512 GB">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Año de Asignación</label>
                                    <input type="number" name="anio_asignacion" id="anio_asignacion" class="form-control" min="2000" max="2099" placeholder="Ej: 2024">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Antigüedad (años)</label>
                                    <input type="number" name="antiguedad" id="antiguedad" class="form-control" min="0" max="50" placeholder="Ej: 2">
                                </div>
                            </div>
                        </div>

                        <div class="campos-especificos" id="camposAire">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Tipo de Aire</label>
                                    <select name="aire_tipo" id="aire_tipo" class="form-select">
                                        <option value="">Seleccionar...</option>
                                        <option value="Minisplit">Minisplit</option>
                                        <option value="Ventana">Ventana</option>
                                        <option value="Portátil">Portátil</option>
                                        <option value="Central">Central</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Capacidad</label>
                                    <input type="text" name="aire_capacidad" id="aire_capacidad" class="form-control" placeholder="Ej: 12000 BTU">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Ubicación</label>
                                    <input type="text" name="aire_ubicacion" id="aire_ubicacion" class="form-control" placeholder="Ej: Oficina 5">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Tecnología</label>
                                    <select name="aire_tecnologia" id="aire_tecnologia" class="form-select">
                                        <option value="">Seleccionar...</option>
                                        <option value="Inverter">Inverter</option>
                                        <option value="On/Off">On/Off</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Ciclo</label>
                                    <select name="aire_ciclo" id="aire_ciclo" class="form-select">
                                        <option value="">Seleccionar...</option>
                                        <option value="Frío">Frío</option>
                                        <option value="Frío/Calor">Frío/Calor</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">¿Funciona?</label>
                                    <select name="aire_funciona" id="aire_funciona" class="form-select">
                                        <option value="1">Sí</option>
                                        <option value="0">No</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Año de Asignación</label>
                                    <input type="number" name="aire_anio_asignacion" id="aire_anio_asignacion" class="form-control" min="2000" max="2099">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Antigüedad (años)</label>
                                    <input type="number" name="aire_antiguedad" id="aire_antiguedad" class="form-control" min="0" max="50">
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:20px">
                            <a href="index.php" class="btn-outline-brand"><i class="bi bi-x-lg me-1"></i> Cancelar</a>
                            <button type="submit" class="<?= $tipo === 'entrada' ? 'btn-entrada' : 'btn-accent-brand' ?>">
                                <i class="bi bi-save me-1"></i> Guardar <?= $tipoLabel ?>
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

    function actualizarDisponibilidad(chk) {
        const txt = document.getElementById('dispText');
        if (chk.checked) {
            txt.textContent = '✅ Disponible';
            txt.className = 'disponible-text si';
        } else {
            txt.textContent = '❌ No disponible';
            txt.className = 'disponible-text no';
        }
    }

    function validarCantidad(input) {
        let v = parseInt(input.value);
        if (isNaN(v) || v < 1) input.value = 1;
    }

    function toggleTipoEquipo() {
        const pc = document.getElementById('tipo_pc');
        const laptop = document.getElementById('tipo_laptop');
        const aire = document.getElementById('tipo_aire');
        if (!pc) return;

        const lblPc = document.getElementById('lbl_pc');
        const lblLaptop = document.getElementById('lbl_laptop');
        const lblAire = document.getElementById('lbl_aire');

        if (pc.checked) { laptop.checked = false; aire.checked = false; }
        if (laptop.checked) { pc.checked = false; aire.checked = false; }
        if (aire.checked) { pc.checked = false; laptop.checked = false; }

        lblPc.classList.toggle('seleccionado', pc.checked);
        lblLaptop.classList.toggle('seleccionado', laptop.checked);
        lblAire.classList.toggle('seleccionado', aire.checked);

        document.getElementById('camposPcLaptop').classList.toggle('visible', pc.checked || laptop.checked);
        document.getElementById('camposAire').classList.toggle('visible', aire.checked);
    }

    document.addEventListener('DOMContentLoaded', function() {
        const chk = document.getElementById('disponible');
        if (chk) actualizarDisponibilidad(chk);

        // Autocomplete para salidas
        const buscar = document.getElementById('buscarActivo');
        if (buscar) {
            const lista = document.getElementById('autocompleteList');
            const activos = <?= json_encode($activosDisponibles ?? []) ?>;

            buscar.addEventListener('input', function() {
                const q = this.value.toLowerCase().trim();
                lista.innerHTML = '';
                if (!q) { lista.style.display = 'none'; return; }

                const filtrados = activos.filter(a =>
                    (a.num_oficina || '').toLowerCase().includes(q) ||
                    (a.descripcion_bien || '').toLowerCase().includes(q) ||
                    (a.serie || '').toLowerCase().includes(q) ||
                    (a.marca || '').toLowerCase().includes(q) ||
                    (a.modelo || '').toLowerCase().includes(q) ||
                    (a.num_simenu || '').toLowerCase().includes(q) ||
                    (a.etiqueta || '').toLowerCase().includes(q)
                ).slice(0, 20);

                if (filtrados.length === 0) { lista.style.display = 'none'; return; }

                filtrados.forEach(a => {
                    const div = document.createElement('div');
                    div.className = 'item';
                    div.innerHTML = `
                        <strong>${a.num_oficina || ''}</strong> — ${a.descripcion_bien || ''}
                        <div class="small-text">${a.marca || ''} ${a.modelo || ''} · Serie: ${a.serie || '—'}</div>
                    `;
                    div.addEventListener('click', function() {
                        seleccionarActivo(a);
                        lista.style.display = 'none';
                        buscar.value = `${a.num_oficina} — ${a.descripcion_bien}`;
                    });
                    lista.appendChild(div);
                });
                lista.style.display = 'block';
            });

            document.addEventListener('click', function(e) {
                if (!e.target.closest('.autocomplete-container')) {
                    lista.style.display = 'none';
                }
            });
        }
    });
    
    function seleccionarActivo(a) {
        const setVal = (id, v) => { const el = document.getElementById(id); if (el) el.value = v || ''; };
        setVal('activo_id', a.id);
        setVal('num_oficina', a.num_oficina);
        setVal('jefe_departamento', a.jefe_departamento);
        setVal('descripcion_bien', a.descripcion_bien);
        setVal('num_simenu', a.num_simenu);
        setVal('sep', a.sep);
        setVal('etiqueta', a.etiqueta);
        setVal('departamento_recibe', a.departamento_recibe);
        setVal('marca', a.marca);
        setVal('modelo', a.modelo);
        setVal('serie', a.serie);

        // 🔥 AUTOCOMPLETAR EL TIPO DE COMPRA 🔥
        const selectTipo = document.querySelector('select[name="tipo_compra"]');
        if (selectTipo && a.tipo_compra) {
            selectTipo.value = a.tipo_compra;
        }
    }
</script>
</body>
</html>