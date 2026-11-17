<?php
require_once __DIR__ . '/../includes/auth.php';
$nombre = htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Usuario');
$rol = $_SESSION['usuario_rol'] ?? 'usuario';
$rolesRestringidos = ['almacenista', 'mantenimiento'];
$soloProductos = in_array($rol, $rolesRestringidos);
$esAdmin = ($rol === 'admin');
$esFinanzas = ($rol === 'finanzas');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Requisición — AlmacenaTec</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .form-section-title {
            font-size: 11px; font-weight: 700; text-transform: uppercase;
            letter-spacing: 1px; color: #94A3B8;
            padding: 18px 0 8px; border-bottom: 1px solid #E2E8F0;
            margin-bottom: 16px;
        }
        .form-section-title i { color: #0B2D6B; }

        .btn-azul-almacenatec {
            background: #0B2D6B;
            color: #FFFFFF;
            border: none;
            padding: 9px 18px;
            border-radius: 6px;
            font-size: 13.5px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-family: 'Poppins', sans-serif;
        }
        .btn-azul-almacenatec:hover {
            background: #071d46;
            color: #FFFFFF;
            transform: translateY(-1px);
            box-shadow: 0 4px 14px rgba(11,45,107,.35);
        }

        .btn-outline-almacenatec {
            background: transparent;
            color: #0B2D6B;
            border: 1.5px solid #0B2D6B;
            padding: 7px 17px;
            border-radius: 6px;
            font-size: 13.5px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-family: 'Poppins', sans-serif;
        }
        .btn-outline-almacenatec:hover {
            background: #0B2D6B;
            color: #FFFFFF;
        }

        .tipo-compra-card {
            border: 2px solid #E2E8F0;
            border-radius: 10px;
            padding: 16px;
            cursor: pointer;
            transition: all 0.2s;
            text-align: center;
            height: 100%;
        }
        .tipo-compra-card:hover {
            border-color: #0B2D6B;
            background: #F0F4F9;
        }
        .tipo-compra-card.seleccionado {
            border-color: #0B2D6B;
            background: #F0F4F9;
            box-shadow: 0 4px 14px rgba(11,45,107,.15);
        }
        .tipo-compra-card i {
            font-size: 28px;
            color: #0B2D6B;
            display: block;
            margin-bottom: 8px;
        }
        .tipo-compra-card h5 {
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            font-weight: 700;
            color: #1E293B;
            margin-bottom: 4px;
        }
        .tipo-compra-card p {
            font-size: 12px;
            color: #94A3B8;
            margin: 0;
        }
        .tipo-compra-card input[type="radio"] {
            display: none;
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
            <?php if (!$soloProductos): ?>

    <?php if ($rol !== 'finanzas'): ?>
            <a href="../activo_fijo/index.php" class="sidebar-link"><i class="bi bi-safe-fill"></i> Activo Fijo</a>
<?php endif; ?>

 <?php if ($rol !== 'finanzas'): ?>
            <a href="../activo_fijo/main_activF.php" class="sidebar-link"><i class="bi bi-clipboard2-data"></i> Historial Salidas</a>
<?php endif; ?>

            <a href="../orden_compra/requisiciones.php" class="sidebar-link"><i class="bi bi-cart3"></i> Almacén General</a>

              <?php if ($rol !== 'finanzas'): ?>
            <a href="../orden_compra/facturas_lista.php" class="sidebar-link"><i class="bi bi-file-earmark-text"></i> Facturas</a>
             <?php endif; ?>
            <a href="../orden_compra/compras.php" class="sidebar-link active"><i class="bi bi-cart-check"></i> Compras</a>
            <?php endif; ?>

              <?php if ($rol !== 'finanzas'): ?>
            <a href="../productos/main_productos.php" class="sidebar-link"><i class="bi bi-box-seam"></i> Productos</a>
             <?php endif; ?>
            
            
            <?php if ($esAdmin): ?>
            <div class="nav-section" style="margin-top:8px">Administración</div>
            <a href="../usuarios/main_usuarios.php" class="sidebar-link"><i class="bi bi-people"></i> Usuarios</a>
            <?php endif; ?>
        </nav>
        <div class="sidebar-footer"><i class="bi bi-geo-alt-fill me-1"></i> TecNM · Campus Saltillo · 2026</div>
    </aside>

    <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

    <div class="main-wrapper" id="mainWrapper">
        <header class="topbar">
            <button class="topbar-toggle" onclick="toggleSidebar()"><i class="bi bi-list" style="font-size:22px"></i></button>
            <div class="topbar-info">
                <h1><i class="bi bi-file-earmark-text me-2" style="color:#0B2D6B"></i>Nueva Requisición</h1>
                <div class="topbar-breadcrumb"><a href="../home/index.php">Inicio</a> &rsaquo; <a href="requisiciones.php">Almacén General</a> &rsaquo; Nueva Requisición</div>
            </div>
            <div style="display:flex;align-items:center;gap:10px;flex-shrink:0">
                <span style="font-size:13px;color:#475569"><i class="bi bi-person-circle me-1"></i><?= $nombre ?></span>
                <a href="<?= BASE_PATH ?>logout.php" class="btn-accent-brand" style="padding:6px 12px;font-size:12px"><i class="bi bi-box-arrow-right me-1"></i> Salir</a>
            </div>
        </header>

        <main class="content">
            <div class="card-panel" style="max-width: 900px; margin: 0 auto;">
                <div style="padding: 24px;">
                    <form method="POST" action="<?= BASE_PATH ?>handlers/save_requisicion.php" id="formRequisicion">

                        <div class="form-section-title"><i class="bi bi-file-earmark-text me-1"></i>Datos de la Requisición</div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Fecha <span class="text-danger">*</span></label>
                                <input type="date" name="fecha" class="form-control" value="<?= date('Y-m-d') ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Folio <span class="text-danger">*</span></label>
                                <input type="text" name="folio" class="form-control" placeholder="Ej: REQ/IP/CC/2026/0044" required>
                                <small class="text-muted">Formato: REQ/IP/CC/2026/0044</small>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Partida <span class="text-danger">*</span></label>
                                <input type="text" name="partida" class="form-control" placeholder="Ej: 24601" required>
                            </div>
                        </div>

                        <!-- ============================================================ -->
                        <!-- CHECK LIST: TIPO DE COMPRA                                    -->
                        <!-- ============================================================ -->
                        <div class="form-section-title"><i class="bi bi-tags me-1"></i>Tipo de Compra</div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="tipo-compra-card seleccionado" id="card_normal" onclick="seleccionarTipo('normal')">
                                    <input type="radio" name="tipo_compra" value="normal" checked>
                                    <i class="bi bi-check-circle"></i>
                                    <h5>Compra Normal</h5>
                                    <p>Compra regular</p>
                                </label>
                            </div>
                            <div class="col-md-4">
                                <label class="tipo-compra-card" id="card_partida_5000" onclick="seleccionarTipo('partida_5000')">
                                    <input type="radio" name="tipo_compra" value="partida_5000">
                                    <i class="bi bi-cash-stack"></i>
                                    <h5>Partida 5000</h5>
                                    <p>Bienes muebles e inmuebles</p>
                                </label>
                            </div>
                            <div class="col-md-4">
                                <label class="tipo-compra-card" id="card_licitacion" onclick="seleccionarTipo('licitacion')">
                                    <input type="radio" name="tipo_compra" value="licitacion">
                                    <i class="bi bi-file-earmark-check"></i>
                                    <h5>Licitación</h5>
                                    <p>Proceso de licitación</p>
                                </label>
                            </div>
                        </div>

                        <!-- CAMPOS SEPARADOS (Jefe, Fecha, Área) -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Nombre y firma del Jefe Solicitante <span class="text-danger">*</span></label>
                                <input type="text" name="jefe_solicitante" class="form-control" placeholder="Nombre completo" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Fecha Solicitada <span class="text-danger">*</span></label>
                                <input type="date" name="fecha_solicitud" class="form-control" value="<?= date('Y-m-d') ?>" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Área Solicitante <span class="text-danger">*</span></label>
                                <input type="text" name="area_solicitante" class="form-control" placeholder="Ej: Sistemas" required>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-12">
                                <div class="form-check">
                                    <input type="checkbox" name="programa_operativo_anual" value="1" class="form-check-input" id="poa">
                                    <label class="form-check-label" for="poa">
                                        ¿Están completados los bienes en el Programa Operativo Anual?
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Clave Presupuestal <span class="text-danger">*</span></label>
                                <input type="text" name="clave_presupuestal" class="form-control" placeholder="Ej: 2530-5-E023-1.5.8.30-1" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Unidad de Medida <span class="text-danger">*</span></label>
                                <input type="text" name="unidad" class="form-control" placeholder="Ej: Pza, Kg, Lote" required>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Cantidad <span class="text-danger">*</span></label>
                                <input type="number" name="cantidad" class="form-control" step="0.01" min="0.01" value="1" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Costo Estimado Total + IVA <span class="text-danger">*</span></label>
                                <input type="number" name="costo_estimado_total" class="form-control" step="0.01" min="0.01" value="0.00" required>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-12">
                                <label class="form-label">Descripción <span class="text-danger">*</span></label>
                                <textarea name="descripcion" class="form-control" rows="3" placeholder="Descripción detallada de los bienes o servicios solicitados" required></textarea>
                            </div>
                        </div>

                        <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:20px">
                            <a href="requisiciones.php" class="btn-outline-almacenatec"><i class="bi bi-x-lg me-1"></i> Cancelar</a>
                            <button type="submit" class="btn-azul-almacenatec"><i class="bi bi-save me-1"></i> Guardar Requisición</button>
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
        const s = document.getElementById('sidebar'), o = document.getElementById('sidebarOverlay'), w = document.getElementById('mainWrapper');
        if (window.innerWidth <= 1100) { const open = s.classList.toggle('open'); o.classList.toggle('visible', open); }
        else { s.classList.toggle('collapsed'); w.classList.toggle('sidebar-hidden'); }
    }
    function closeSidebar() { document.getElementById('sidebar').classList.remove('open'); document.getElementById('sidebarOverlay').classList.remove('visible'); }

    function seleccionarTipo(tipo) {
        document.querySelectorAll('.tipo-compra-card').forEach(card => card.classList.remove('seleccionado'));
        document.getElementById('card_' + tipo).classList.add('seleccionado');
        document.querySelectorAll('input[name="tipo_compra"]').forEach(radio => {
            radio.checked = (radio.value === tipo);
        });
    }
</script>
</body>
</html>