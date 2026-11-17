<?php
require_once __DIR__ . '/../includes/auth.php';
$nombre = htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Usuario');
$rol = $_SESSION['usuario_rol'] ?? 'usuario';
$rolesRestringidos = ['almacenista', 'mantenimiento'];
$soloProductos = in_array($rol, $rolesRestringidos);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sincronizar Compras — AlmacenaTec</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
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
                <h1><i class="bi bi-arrow-repeat me-2" style="color:var(--c-primary)"></i>Sincronizar Compras</h1>
                <div class="topbar-breadcrumb"><a href="../home/index.php">Inicio</a> &rsaquo; <a href="requisiciones.php">Almacén General</a> &rsaquo; Sincronizar</div>
            </div>
            <div style="display:flex;align-items:center;gap:10px;flex-shrink:0">
                <span style="font-size:13px;color:var(--c-text-md)"><i class="bi bi-person-circle me-1"></i><?= $nombre ?></span>
                <a href="<?= BASE_PATH ?>logout.php" class="btn-accent-brand" style="padding:6px 12px;font-size:12px"><i class="bi bi-box-arrow-right me-1"></i> Salir</a>
            </div>
        </header>

        <main class="content">
            <div class="card-panel" style="max-width: 600px; margin: 40px auto;">
                <div style="padding: 30px; text-align: center;">
                    <h3><i class="bi bi-arrow-repeat me-2" style="color:var(--c-primary)"></i>Sincronizar Compras</h3>
                    <p class="text-muted" style="margin-top: 10px;">
                        Este proceso creará registros en la tabla <strong>compras</strong> a partir de las órdenes de compra y facturas existentes.
                    </p>
                    
                    <div id="resultado" style="margin-top: 20px;">
                        <button class="btn-primary-brand" onclick="sincronizar()">
                            <i class="bi bi-arrow-repeat me-1"></i> Sincronizar Ahora
                        </button>
                    </div>

                    <div id="mensaje" style="margin-top: 20px; display: none;"></div>

                    <div style="margin-top: 20px;">
                        <a href="compras.php" class="btn-outline-brand"><i class="bi bi-arrow-left me-1"></i> Volver a Compras</a>
                    </div>
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

    function sincronizar() {
        const btn = document.querySelector('.btn-primary-brand');
        const mensaje = document.getElementById('mensaje');
        
        btn.disabled = true;
        btn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i> Sincronizando...';
        mensaje.style.display = 'block';
        mensaje.innerHTML = '<div class="alert alert-info">Procesando... Por favor espera.</div>';

        fetch('<?= BASE_PATH ?>handlers/sincronizar_compras.php')
            .then(r => r.json())
            .then(data => {
                if (data.ok) {
                    mensaje.innerHTML = `<div class="alert alert-success">
                        <i class="bi bi-check-circle-fill me-2"></i> ${data.mensaje}
                    </div>`;
                    // Recargar la página después de 2 segundos para mostrar los cambios
                    setTimeout(() => {
                        window.location.href = 'compras.php';
                    }, 2000);
                } else {
                    mensaje.innerHTML = `<div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i> Error: ${data.error}
                    </div>`;
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-arrow-repeat me-1"></i> Sincronizar Ahora';
                }
            })
            .catch(err => {
                mensaje.innerHTML = `<div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> Error de conexión: ${err.message}
                </div>`;
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-arrow-repeat me-1"></i> Sincronizar Ahora';
            });
    }
</script>
</body>
</html>