<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

$nombre = htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Usuario');
$rol = $_SESSION['usuario_rol'] ?? 'usuario';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#0B2D6B">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <title>Buscar Activo — AlmacenaTec</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
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
            --radius: 12px;
            --radius-sm: 8px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; -webkit-tap-highlight-color: transparent; }
        body {
            font-family: 'Inter', -apple-system, sans-serif;
            background: var(--c-bg);
            color: var(--c-text);
            min-height: 100vh;
            padding-bottom: 20px;
        }

        /* ── Header móvil ── */
        .mobile-header {
            background: linear-gradient(135deg, var(--c-primary), var(--c-primary-dk));
            color: #fff;
            padding: 16px 16px 20px;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 12px rgba(0,0,0,.15);
        }
        .mobile-header .top-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 14px;
        }
        .mobile-header .logo {
            display: flex;
            align-items: center;
            gap: 8px;
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
            font-size: 16px;
        }
        .mobile-header .logo-mark {
            width: 32px; height: 32px;
            border-radius: 8px;
            background: var(--c-accent);
            display: flex; align-items: center; justify-content: center;
            font-size: 12px; font-weight: 800;
        }
        .mobile-header .user-info {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            color: rgba(255,255,255,.8);
        }
        .mobile-header .back-btn {
            background: rgba(255,255,255,.1);
            border: none;
            color: #fff;
            width: 34px; height: 34px;
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            text-decoration: none;
            font-size: 16px;
        }
        .mobile-header h1 {
            font-family: 'Poppins', sans-serif;
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 14px;
            color: #fff;
        }

        /* ── Barra de búsqueda ── */
        .search-bar {
            display: flex;
            gap: 8px;
        }
        .search-input-wrap {
            flex: 1;
            position: relative;
        }
        .search-input-wrap i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--c-text-muted);
            font-size: 16px;
        }
        .search-input-wrap input {
            width: 100%;
            padding: 14px 44px 14px 44px;
            border: none;
            border-radius: var(--radius);
            font-size: 15px;
            font-family: 'Inter', sans-serif;
            background: rgba(255,255,255,.95);
            color: var(--c-text);
            outline: none;
        }
        .search-input-wrap input::placeholder {
            color: var(--c-text-muted);
        }
        .search-input-wrap .clear-btn {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--c-text-muted);
            font-size: 16px;
            cursor: pointer;
            padding: 4px;
            display: none;
        }
        .search-input-wrap .clear-btn.visible {
            display: block;
        }

        /* ── Filtros rápidos ── */
        .filtros-rapidos {
            display: flex;
            gap: 6px;
            padding: 12px 16px;
            overflow-x: auto;
            background: var(--c-surface);
            border-bottom: 1px solid var(--c-border);
            -webkit-overflow-scrolling: touch;
        }
        .filtros-rapidos::-webkit-scrollbar { display: none; }
        .filtros-rapidos .chip {
            padding: 7px 14px;
            border-radius: 20px;
            border: 1.5px solid var(--c-border);
            background: #fff;
            font-size: 12.5px;
            font-weight: 600;
            color: var(--c-text-md);
            white-space: nowrap;
            cursor: pointer;
            transition: all .2s;
        }
        .filtros-rapidos .chip.active {
            background: var(--c-primary);
            color: #fff;
            border-color: var(--c-primary);
        }

        /* ── Resultados ── */
        .resultados {
            padding: 16px;
        }
        .resultado-count {
            font-size: 12.5px;
            color: var(--c-text-muted);
            margin-bottom: 12px;
            padding-left: 4px;
        }

        .activo-card {
            background: var(--c-surface);
            border-radius: var(--radius);
            padding: 16px;
            margin-bottom: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,.06);
            cursor: pointer;
            transition: all .2s;
            border: 1px solid var(--c-border);
        }
        .activo-card:active {
            transform: scale(.98);
            background: #F8FAFC;
        }
        .activo-card .card-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 10px;
            gap: 10px;
        }
        .activo-card .card-title {
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
            font-size: 15px;
            color: var(--c-text);
            line-height: 1.3;
            flex: 1;
        }
        .activo-card .badge-disp {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            white-space: nowrap;
            flex-shrink: 0;
        }
        .activo-card .badge-disp.disponible {
            background: #d1fae5;
            color: #059669;
        }
        .activo-card .badge-disp.no-disponible {
            background: #fee2e2;
            color: #dc2626;
        }
        .activo-card .card-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            font-size: 12.5px;
            color: var(--c-text-md);
            margin-bottom: 10px;
        }
        .activo-card .card-info .item {
            display: flex;
            align-items: center;
            gap: 6px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .activo-card .card-info .item i {
            color: var(--c-gold);
            font-size: 13px;
            flex-shrink: 0;
        }
        .activo-card .card-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 10px;
            border-top: 1px solid var(--c-border);
        }
        .activo-card .folio {
            font-family: monospace;
            font-size: 12px;
            font-weight: 700;
            color: var(--c-primary);
        }
        .activo-card .ver-mas {
            font-size: 12px;
            font-weight: 600;
            color: var(--c-primary);
            display: flex;
            align-items: center;
            gap: 4px;
        }

        /* ── Estado vacío ── */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--c-text-muted);
        }
        .empty-state i {
            font-size: 64px;
            display: block;
            margin-bottom: 16px;
            color: #E2E8F0;
        }
        .empty-state h3 {
            font-family: 'Poppins', sans-serif;
            font-size: 16px;
            color: var(--c-text-md);
            margin-bottom: 6px;
        }
        .empty-state p {
            font-size: 13px;
        }

        /* ── Modal detalle (bottom sheet móvil) ── */
        .modal-detalle-mobile {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,.5);
            z-index: 1000;
            display: none;
            align-items: flex-end;
            backdrop-filter: blur(2px);
        }
        .modal-detalle-mobile.visible {
            display: flex;
        }
        .modal-detalle-mobile .sheet {
            background: #fff;
            width: 100%;
            max-height: 90vh;
            border-radius: 20px 20px 0 0;
            overflow-y: auto;
            animation: slideUp .3s ease;
        }
        @keyframes slideUp {
            from { transform: translateY(100%); }
            to { transform: translateY(0); }
        }
        .sheet-header {
            background: var(--c-primary);
            color: #fff;
            padding: 16px 20px;
            border-radius: 20px 20px 0 0;
            position: sticky;
            top: 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .sheet-header h3 {
            font-family: 'Poppins', sans-serif;
            font-size: 16px;
            font-weight: 700;
            color: #fff;
        }
        .sheet-header .close-btn {
            background: rgba(255,255,255,.15);
            border: none;
            color: #fff;
            width: 32px; height: 32px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 16px;
            cursor: pointer;
        }
        .sheet-body {
            padding: 20px;
        }
        .detalle-row {
            display: flex;
            padding: 10px 0;
            border-bottom: 1px solid #F0F4F9;
        }
        .detalle-row:last-child { border-bottom: none; }
        .detalle-row .label {
            font-weight: 600;
            color: var(--c-text-md);
            width: 130px;
            flex-shrink: 0;
            font-size: 12.5px;
        }
        .detalle-row .value {
            color: var(--c-text);
            font-size: 13px;
            flex: 1;
            word-break: break-word;
        }
        .detalle-section {
            font-family: 'Poppins', sans-serif;
            font-size: 11px;
            font-weight: 700;
            color: var(--c-gold);
            text-transform: uppercase;
            letter-spacing: .5px;
            margin-top: 16px;
            margin-bottom: 6px;
            padding-top: 12px;
            border-top: 2px solid #F0F4F9;
        }
        .barcode-container {
            text-align: center;
            padding: 16px;
            background: #F0F4F9;
            border-radius: 8px;
            margin: 12px 0;
        }

        /* ── Loader ── */
        .loader {
            text-align: center;
            padding: 40px;
            color: var(--c-text-muted);
        }
        .loader .spinner {
            width: 32px; height: 32px;
            border: 3px solid #E2E8F0;
            border-top-color: var(--c-primary);
            border-radius: 50%;
            animation: spin .8s linear infinite;
            margin: 0 auto 12px;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>

<!-- ══ HEADER ══ -->
<header class="mobile-header">
    <div class="top-row">
        <a href="index.php" class="back-btn"><i class="bi bi-arrow-left"></i></a>
        <div class="logo">
            <div class="logo-mark">AT</div>
            <span>AlmacenaTec</span>
        </div>
        <div class="user-info">
            <i class="bi bi-person-circle"></i>
            <span><?= $nombre ?></span>
        </div>
    </div>
    <h1><i class="bi bi-search me-2"></i>Buscar Activo</h1>

    <div class="search-bar">
        <div class="search-input-wrap">
            <i class="bi bi-search"></i>
            <input type="text" id="inputBuscar" placeholder="Código, oficina, descripción, serie…" autocomplete="off">
            <button class="clear-btn" id="clearBtn" onclick="limpiarBusqueda()">
                <i class="bi bi-x-circle-fill"></i>
            </button>
        </div>
    </div>
</header>

<!-- ══ FILTROS RÁPIDOS ══ -->
<div class="filtros-rapidos">
    <button class="chip active" data-filtro="todos" onclick="filtrar('todos', this)">Todos</button>
    <button class="chip" data-filtro="disponible" onclick="filtrar('disponible', this)">✅ Disponibles</button>
    <button class="chip" data-filtro="no_disponible" onclick="filtrar('no_disponible', this)">❌ No disponibles</button>
    <button class="chip" data-filtro="pc" onclick="filtrar('pc', this)">💻 PC</button>
    <button class="chip" data-filtro="laptop" onclick="filtrar('laptop', this)">🖥️ Laptop</button>
    <button class="chip" data-filtro="aire" onclick="filtrar('aire', this)">❄️ Aire</button>
</div>

<!-- ══ RESULTADOS ══ -->
<main class="resultados">
    <div class="resultado-count" id="resultadoCount"></div>
    <div id="resultadosContainer">
        <div class="loader">
            <div class="spinner"></div>
            Cargando activos...
        </div>
    </div>
</main>

<!-- ══ MODAL DETALLE (BOTTOM SHEET) ══ -->
<div class="modal-detalle-mobile" id="modalDetalle" onclick="cerrarDetalle(event)">
    <div class="sheet" onclick="event.stopPropagation()">
        <div class="sheet-header">
            <h3><i class="bi bi-info-circle me-2"></i>Detalle del Activo</h3>
            <button class="close-btn" onclick="cerrarDetalle()">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <div class="sheet-body" id="detalleBody"></div>
    </div>
</div>

<script>
    let todosLosActivos = [];
    let activosFiltrados = [];
    let filtroActual = 'todos';

    // ──────────────────────────────────────────────────────────────
    // CARGAR ACTIVOS
    // ──────────────────────────────────────────────────────────────
    async function cargarActivos() {
        try {
            const res = await fetch('<?= BASE_PATH ?>includes/get_activos_inventario.php');
            const data = await res.json();
            if (!data.ok) throw new Error(data.error);
            todosLosActivos = data.activos || [];
            aplicarFiltros();
        } catch (err) {
            console.error(err);
            document.getElementById('resultadosContainer').innerHTML = `
                <div class="empty-state">
                    <i class="bi bi-exclamation-triangle"></i>
                    <h3>Error al cargar</h3>
                    <p>${err.message}</p>
                </div>`;
        }
    }

    // ──────────────────────────────────────────────────────────────
    // FILTRAR
    // ──────────────────────────────────────────────────────────────
    function filtrar(tipo, btn) {
        filtroActual = tipo;
        document.querySelectorAll('.filtros-rapidos .chip').forEach(c => c.classList.remove('active'));
        if (btn) btn.classList.add('active');
        aplicarFiltros();
    }

    function aplicarFiltros() {
        const query = document.getElementById('inputBuscar').value.toLowerCase().trim();
        activosFiltrados = todosLosActivos;

        // Filtro por tipo
        if (filtroActual === 'disponible') {
            activosFiltrados = activosFiltrados.filter(a => a.disponible == 1);
        } else if (filtroActual === 'no_disponible') {
            activosFiltrados = activosFiltrados.filter(a => a.disponible == 0);
        } else if (filtroActual === 'pc') {
            activosFiltrados = activosFiltrados.filter(a => a.tipo_equipo === 'pc');
        } else if (filtroActual === 'laptop') {
            activosFiltrados = activosFiltrados.filter(a => a.tipo_equipo === 'laptop');
        } else if (filtroActual === 'aire') {
            activosFiltrados = activosFiltrados.filter(a => a.tipo_equipo === 'aire');
        }

        // Filtro por búsqueda de texto
        if (query) {
            activosFiltrados = activosFiltrados.filter(a =>
                (a.codigo_barras || '').toLowerCase().includes(query) ||
                (a.folio || '').toLowerCase().includes(query) ||
                (a.num_oficina || '').toLowerCase().includes(query) ||
                (a.descripcion_bien || '').toLowerCase().includes(query) ||
                (a.marca || '').toLowerCase().includes(query) ||
                (a.modelo || '').toLowerCase().includes(query) ||
                (a.serie || '').toLowerCase().includes(query) ||
                (a.num_simenu || '').toLowerCase().includes(query) ||
                (a.sep || '').toLowerCase().includes(query) ||
                (a.etiqueta || '').toLowerCase().includes(query) ||
                (a.resguardante || '').toLowerCase().includes(query) ||
                (a.area_pertenece || '').toLowerCase().includes(query)
            );
        }

        renderResultados();
    }

    // ──────────────────────────────────────────────────────────────
    // RENDER RESULTADOS
    // ──────────────────────────────────────────────────────────────
    function renderResultados() {
        const container = document.getElementById('resultadosContainer');
        const countDiv = document.getElementById('resultadoCount');

        if (activosFiltrados.length === 0) {
            countDiv.textContent = '';
            container.innerHTML = `
                <div class="empty-state">
                    <i class="bi bi-search"></i>
                    <h3>Sin resultados</h3>
                    <p>No se encontraron activos con esos criterios.</p>
                </div>`;
            return;
        }

        countDiv.textContent = `${activosFiltrados.length} resultado${activosFiltrados.length !== 1 ? 's' : ''}`;

        let html = '';
        for (const a of activosFiltrados) {
            const disp = a.disponible == 1;
            const tipoIcon = a.tipo_equipo === 'pc' ? '💻' : 
                            a.tipo_equipo === 'laptop' ? '🖥️' : 
                            a.tipo_equipo === 'aire' ? '❄️' : '📦';

            html += `
                <div class="activo-card" onclick="verDetalle(${a.id})">
                    <div class="card-top">
                        <div class="card-title">${tipoIcon} ${escapeHtml(a.descripcion_bien || 'Sin descripción')}</div>
                        <span class="badge-disp ${disp ? 'disponible' : 'no-disponible'}">
                            <i class="bi ${disp ? 'bi-check-circle-fill' : 'bi-x-circle-fill'}"></i>
                            ${disp ? 'Disponible' : 'No disp.'}
                        </span>
                    </div>
                    <div class="card-info">
                        <div class="item"><i class="bi bi-building"></i>${escapeHtml(a.num_oficina || 'N/A')}</div>
                        <div class="item"><i class="bi bi-tag"></i>${escapeHtml(a.marca || 'N/A')}</div>
                        <div class="item"><i class="bi bi-hdd"></i>${escapeHtml(a.modelo || 'N/A')}</div>
                        <div class="item"><i class="bi bi-upc"></i>${escapeHtml(a.serie || 'N/A')}</div>
                    </div>
                    <div class="card-footer">
                        <span class="folio">${escapeHtml(a.folio || a.codigo_barras || '')}</span>
                        <span class="ver-mas">
                            Ver más <i class="bi bi-chevron-right"></i>
                        </span>
                    </div>
                </div>
            `;
        }
        container.innerHTML = html;
    }

    // ──────────────────────────────────────────────────────────────
    // VER DETALLE
    // ──────────────────────────────────────────────────────────────
    function verDetalle(id) {
        const a = todosLosActivos.find(x => x.id == id);
        if (!a) return;

        let html = '';
        const disp = a.disponible == 1;

        html += `<div class="detalle-row"><div class="label">Estado</div><div class="value">
            <span class="badge-disp ${disp ? 'disponible' : 'no-disponible'}" style="font-size:12px;padding:4px 12px;border-radius:20px;display:inline-flex;align-items:center;gap:4px;">
                <i class="bi ${disp ? 'bi-check-circle-fill' : 'bi-x-circle-fill'}"></i>
                ${disp ? 'Disponible' : 'No disponible'}
            </span>
        </div></div>`;

        html += `<div class="detalle-section">Identificación</div>`;
        html += row('Folio', a.folio);
        html += row('Código de barras', a.codigo_barras);
        html += row('Núm. Oficina', a.num_oficina);
        html += row('Fecha registro', a.fecha);
        html += row('SIMENU', a.num_simenu);
        html += row('Código SEP', a.sep);
        html += row('Etiqueta', a.etiqueta);

        html += `<div class="detalle-section">Descripción</div>`;
        html += row('Descripción', a.descripcion_bien);
        html += row('Marca', a.marca);
        html += row('Modelo', a.modelo);
        html += row('Serie', a.serie);

        html += `<div class="detalle-section">Asignación</div>`;
        html += row('Área pertenece', a.area_pertenece);
        html += row('Departamento recibe', a.departamento_recibe);
        html += row('Resguardante', a.resguardante);
        html += row('Jefe departamento', a.jefe_departamento);

        // Especificaciones técnicas (si aplica)
        if (a.tipo_equipo === 'pc' || a.tipo_equipo === 'laptop') {
            html += `<div class="detalle-section">Especificaciones PC/Laptop</div>`;
            html += row('Memoria RAM', a.memoria_ram);
            html += row('Sistema Operativo', a.sistema_operativo);
            html += row('Tipo de Disco', a.tipo_disco);
            html += row('Capacidad Disco', a.capacidad_disco);
            html += row('Año Asignación', a.anio_asignacion);
            html += row('Antigüedad', a.antiguedad ? a.antiguedad + ' años' : null);
        }

        if (a.tipo_equipo === 'aire') {
            html += `<div class="detalle-section">Especificaciones Aire</div>`;
            html += row('Tipo', a.aire_tipo);
            html += row('Capacidad', a.aire_capacidad);
            html += row('Ubicación', a.aire_ubicacion);
            html += row('Tecnología', a.aire_tecnologia);
            html += row('Ciclo', a.aire_ciclo);
            html += row('Funciona', a.aire_funciona == 1 ? '✅ Sí' : '❌ No');
            html += row('Año Asignación', a.anio_asignacion);
            html += row('Antigüedad', a.antiguedad ? a.antiguedad + ' años' : null);
        }

        // Código de barras
        if (a.codigo_barras) {
            html += `<div class="barcode-container">
                <svg id="barcode-detalle"></svg>
                <div style="font-family:monospace;font-size:12px;margin-top:6px;font-weight:600;">${escapeHtml(a.codigo_barras)}</div>
            </div>`;
        }

        document.getElementById('detalleBody').innerHTML = html;
        document.getElementById('modalDetalle').classList.add('visible');
        document.body.style.overflow = 'hidden';

        // Renderizar código de barras
        if (a.codigo_barras) {
            setTimeout(() => {
                try {
                    JsBarcode('#barcode-detalle', a.codigo_barras, {
                        format: 'CODE128',
                        width: 2,
                        height: 60,
                        displayValue: false,
                        margin: 0
                    });
                } catch (e) { console.error(e); }
            }, 50);
        }
    }

    function cerrarDetalle(e) {
        document.getElementById('modalDetalle').classList.remove('visible');
        document.body.style.overflow = '';
    }

    function row(label, value) {
        if (!value && value !== 0) return '';
        return `<div class="detalle-row"><div class="label">${label}</div><div class="value">${escapeHtml(value)}</div></div>`;
    }

    function escapeHtml(text) {
        if (!text && text !== 0) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // ──────────────────────────────────────────────────────────────
    // LIMPIAR BÚSQUEDA
    // ──────────────────────────────────────────────────────────────
    function limpiarBusqueda() {
        document.getElementById('inputBuscar').value = '';
        document.getElementById('clearBtn').classList.remove('visible');
        aplicarFiltros();
        document.getElementById('inputBuscar').focus();
    }

    // ──────────────────────────────────────────────────────────────
    // INICIALIZAR
    // ──────────────────────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', () => {
        cargarActivos();

        const inputBuscar = document.getElementById('inputBuscar');
        const clearBtn = document.getElementById('clearBtn');

        // Búsqueda en tiempo real con debounce
        let timeout;
        inputBuscar.addEventListener('input', () => {
            clearTimeout(timeout);
            timeout = setTimeout(aplicarFiltros, 200);
            // Mostrar/ocultar botón de limpiar
            if (inputBuscar.value.length > 0) {
                clearBtn.classList.add('visible');
            } else {
                clearBtn.classList.remove('visible');
            }
        });
    });
</script>
</body>
</html>