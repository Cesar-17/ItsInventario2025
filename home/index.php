<?php
// home/index.php
require_once __DIR__ . '/../includes/auth.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_id'])) {
    header('Location:' . BASE_PATH . 'login.php');
    exit;
}

$nombre = htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Usuario');
$rol = $_SESSION['usuario_rol'] ?? 'usuario';
$departamento = $_SESSION['usuario_departamento'] ?? null;

$rolesRestringidos = ['almacenista', 'mantenimiento'];
$soloProductos = in_array($rol, $rolesRestringidos);

$esActivoFijo = ($rol === 'activo_fijo');
$esAlmacenGeneral = ($rol === 'almacen_general');
$esFinanzas = ($rol === 'finanzas');
$esJefe = esJefeDepartamento();
$esAdmin = ($rol === 'admin');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AlmacenaTec — TecNM Campus Saltillo</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../css/style.css">
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

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { height: 100%; }

        .home-layout {
            display: grid;
            grid-template-columns: 1.1fr 1fr;
            min-height: 100vh;
        }

        .home-hero {
            position: relative;
            background: var(--c-primary);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: flex-start;
            padding: 60px 56px;
            overflow: hidden;
        }
        .home-hero::before {
            content: '';
            position: absolute; inset: 0;
         background: url('<?= BASE_PATH ?>media/its_fondo.jpg_large') center/cover no-repeat;
            opacity: .08;
        }
        .hero-circle {
            position: absolute; border-radius: 50%;
            background: rgba(255,255,255,.04);
        }
        .hero-circle-1 { width: 420px; height: 420px; top: -120px; right: -120px; }
        .hero-circle-2 { width: 260px; height: 260px; bottom: -80px; left: -80px; }
        .hero-circle-3 { width: 140px; height: 140px; bottom: 160px; right: 60px; background: rgba(185,28,46,.12); }

        .hero-badge {
            position: relative;
            display: inline-flex; align-items: center; gap: 8px;
            background: rgba(255,255,255,.1);
            border: 1px solid rgba(255,255,255,.2);
            border-radius: 30px;
            padding: 5px 14px;
            font-size: 11.5px; font-weight: 600;
            color: rgba(255,255,255,.85);
            letter-spacing: .5px;
            text-transform: uppercase;
            margin-bottom: 28px;
        }
        .hero-badge i { color: var(--c-gold); font-size: 13px; }

        .hero-title {
            position: relative;
            font-family: 'Poppins', sans-serif;
            font-size: clamp(36px, 4.5vw, 52px);
            font-weight: 800;
            color: #fff;
            line-height: 1.1;
            margin-bottom: 18px;
        }
        .hero-title span { color: var(--c-gold); }

        .hero-desc {
            position: relative;
            font-size: 14.5px;
            color: rgba(255,255,255,.65);
            line-height: 1.7;
            max-width: 380px;
            margin-bottom: 40px;
        }

        .hero-stats {
            position: relative;
            display: flex; gap: 32px;
        }
        .hero-stat { text-align: left; }
        .hero-stat-num {
            font-family: 'Poppins', sans-serif;
            font-size: 26px; font-weight: 700;
            color: var(--c-gold);
        }
        .hero-stat-label {
            font-size: 11px; color: rgba(255,255,255,.5);
            text-transform: uppercase; letter-spacing: .5px;
        }

        .home-panel {
            background: var(--c-bg);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 48px 40px;
        }
        .home-box {
            width: 100%;
            max-width: 480px;
        }

        .home-logo {
            display: flex; align-items: center; gap: 12px;
            margin-bottom: 30px;
        }
        .home-logo-mark {
            width: 44px; height: 44px; border-radius: 10px;
            background: var(--c-primary);
            display: flex; align-items: center; justify-content: center;
            font-family: 'Poppins', sans-serif;
            font-weight: 800; font-size: 16px;
            color: #fff; letter-spacing: -1px;
            flex-shrink: 0;
        }
        .home-logo-text { display: flex; flex-direction: column; }
        .home-logo-name {
            font-family: 'Poppins', sans-serif;
            font-weight: 700; font-size: 18px;
            color: var(--c-text);
            line-height: 1;
        }
        .home-logo-name span { color: var(--c-primary); }
        .home-logo-sub {
            font-size: 11px; color: var(--c-text-muted);
            margin-top: 3px;
        }

        .home-title {
            font-family: 'Poppins', sans-serif;
            font-size: 22px; font-weight: 700;
            color: var(--c-text);
            margin-bottom: 4px;
        }
        .home-subtitle {
            font-size: 13.5px; color: var(--c-text-muted);
            margin-bottom: 28px;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 16px;
            background: var(--c-surface);
            padding: 12px 20px;
            border-radius: var(--radius);
            border: 1px solid var(--c-border);
            margin-bottom: 24px;
            box-shadow: 0 1px 3px rgba(0,0,0,.05);
        }
        .user-info .avatar {
            width: 40px; height: 40px;
            border-radius: 50%;
            background: var(--c-primary);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
            font-size: 18px;
            flex-shrink: 0;
        }
        .user-info .user-details { flex: 1; }
        .user-info .user-details .name { font-weight: 600; font-size: 15px; color: var(--c-text); }
        .user-info .user-details .role {
            font-size: 12px;
            color: var(--c-text-muted);
            display: inline-block;
            background: var(--c-bg);
            padding: 1px 12px;
            border-radius: 20px;
            margin-top: 2px;
        }
        .user-info .logout-btn {
            background: var(--c-accent);
            color: #fff;
            border: none;
            padding: 6px 14px;
            border-radius: var(--radius-sm);
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: background .15s;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .user-info .logout-btn:hover { background: #8f1522; }

        .module-cards {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .module-card {
            display: flex;
            align-items: center;
            gap: 14px;
            background: var(--c-surface);
            border: 1px solid var(--c-border);
            border-radius: var(--radius);
            padding: 14px 18px;
            text-decoration: none;
            color: var(--c-text);
            transition: all .2s ease;
            cursor: pointer;
        }
        .module-card:hover {
            border-color: var(--c-primary);
            box-shadow: 0 4px 16px rgba(0,0,0,.08);
            transform: translateY(-2px);
        }
        .module-card.disabled {
            opacity: 0.5;
            cursor: not-allowed;
            pointer-events: none;
        }
        .module-card.disabled:hover {
            transform: none;
            box-shadow: none;
        }
        .module-icon {
            width: 44px; height: 44px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
        }
        .module-icon.blue { background: rgba(11,45,107,.1); color: var(--c-primary); }
        .module-icon.red { background: rgba(184,28,46,.1); color: var(--c-accent); }
        .module-icon.gold { background: rgba(201,151,58,.12); color: var(--c-gold); }
        .module-icon.green { background: rgba(5,150,105,.1); color: #059669; }
        .module-icon.gray { background: rgba(100,100,100,.1); color: #999; }
        .module-icon.darkblue { background: rgba(11,45,107,.1); color: #0B2D6B; }

        .module-info { flex: 1; }
        .module-info h3 { font-size: 14px; font-weight: 600; color: var(--c-text); margin-bottom: 2px; }
        .module-info p { font-size: 12px; color: var(--c-text-muted); margin: 0; }
        .module-arrow {
            color: var(--c-text-muted);
            font-size: 18px;
            transition: transform .2s ease;
        }
        .module-card:hover .module-arrow {
            transform: translateX(4px);
            color: var(--c-primary);
        }
        .module-card .lock {
            font-size: 14px;
            color: #999;
        }

        .home-footer {
            margin-top: 32px;
            padding-top: 20px;
            border-top: 1px solid var(--c-border);
            font-size: 11px;
            color: var(--c-text-muted);
            text-align: center;
        }

        .admin-section {
            margin-top: 10px;
            padding-top: 14px;
            border-top: 1px solid rgba(0,0,0,.07);
        }
        .admin-section .admin-label {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #aaa;
            margin-bottom: 8px;
        }

        @media (max-width: 820px) {
            .home-layout { grid-template-columns: 1fr; }
            .home-hero { display: none; }
            .home-panel { padding: 32px 20px; }
            .home-box { max-width: 100%; }
        }
    </style>
</head>
<body>

<div class="home-layout">

    <!-- ══ Hero Panel ══ -->
    <div class="home-hero">
        <div class="hero-circle hero-circle-1"></div>
        <div class="hero-circle hero-circle-2"></div>
        <div class="hero-circle hero-circle-3"></div>

        <div class="hero-badge">
            <i class="bi bi-geo-alt-fill"></i> TecNM · Campus Saltillo
        </div>

        <h1 class="hero-title">Almacena<span>Tec</span></h1>
        <p class="hero-desc">
            Sistema de gestión de inventario institucional para el Tecnológico Nacional de México, Campus Saltillo.
        </p>

        <div class="hero-stats">
            <div class="hero-stat">
                <div class="hero-stat-num">AF</div>
                <div class="hero-stat-label">Activo Fijo</div>
            </div>
            <div class="hero-stat">
                <div class="hero-stat-num">AG</div>
                <div class="hero-stat-label">Almacén General</div>
            </div>
            <div class="hero-stat">
                <div class="hero-stat-num">INV</div>
                <div class="hero-stat-label">Inventario</div>
            </div>
        </div>
    </div>

    <!-- ══ Panel derecho ══ -->
    <div class="home-panel">
        <div class="home-box">

            <div class="home-logo">
                <div class="home-logo-mark">AT</div>
                <div class="home-logo-text">
                    <span class="home-logo-name">Almacena<span>Tec</span></span>
                    <span class="home-logo-sub">TecNM Campus Saltillo · 2025</span>
                </div>
            </div>

            <h2 class="home-title">Bienvenido, <?= $nombre ?> 👋</h2>
            <p class="home-subtitle">Selecciona el módulo que deseas gestionar</p>

            <!-- Información del usuario -->
            <div class="user-info">
                <div class="avatar"><?= strtoupper(substr($nombre, 0, 1)) ?></div>
                <div class="user-details">
                    <div class="name"><?= $nombre ?></div>
                    <span class="role"><?= ucfirst($rol) ?></span>
                </div>
                <a href="<?= BASE_PATH ?>logout.php" class="logout-btn">
                    <i class="bi bi-box-arrow-right"></i> Salir
                </a>
            </div>

            <!-- Módulos -->
            <div class="module-cards">

                <?php if ($esJefe): ?>
                <!-- JEFE DE DEPARTAMENTO -->
       <a href="../jefe_departamento.php" class="module-card">
                    <div class="module-icon darkblue"><i class="bi bi-building"></i></div>
                    <div class="module-info">
                        <h3>Mi Departamento</h3>
                        <p><?= htmlspecialchars($departamento ?? 'Sin departamento') ?></p>
                    </div>
                    <div class="module-arrow"><i class="bi bi-chevron-right"></i></div>
                </a>

                <?php elseif ($esFinanzas): ?>
                <!-- FINANZAS: Almacén General, Facturas, Compras -->
                <a href="../orden_compra/requisiciones.php" class="module-card">
                    <div class="module-icon darkblue"><i class="bi bi-cart3"></i></div>
                    <div class="module-info">
                        <h3>Almacén General</h3>
                        <p>Requisiciones y órdenes de compra</p>
                    </div>
                    <div class="module-arrow"><i class="bi bi-chevron-right"></i></div>
                </a>

                <a href="../orden_compra/facturas_lista.php" class="module-card">
                    <div class="module-icon darkblue"><i class="bi bi-file-earmark-text"></i></div>
                    <div class="module-info">
                        <h3>Facturas</h3>
                        <p>Lista y gestión de facturas</p>
                    </div>
                    <div class="module-arrow"><i class="bi bi-chevron-right"></i></div>
                </a>

                <a href="../orden_compra/compras.php" class="module-card">
                    <div class="module-icon darkblue"><i class="bi bi-cart-check"></i></div>
                    <div class="module-info">
                        <h3>Control de Compras</h3>
                        <p>Seguimiento y gestión de compras</p>
                    </div>
                    <div class="module-arrow"><i class="bi bi-chevron-right"></i></div>
                </a>

                <?php elseif ($esActivoFijo): ?>
                <!-- ACTIVO FIJO: Sin Almacén General -->
                <a href="../activo_fijo/index.php" class="module-card">
                    <div class="module-icon darkblue"><i class="bi bi-safe-fill"></i></div>
                    <div class="module-info">
                        <h3>Activo Fijo</h3>
                        <p>Control de entrada y salida de activos fijos</p>
                    </div>
                    <div class="module-arrow"><i class="bi bi-chevron-right"></i></div>
                </a>

                <a href="../activo_fijo/main_activF.php" class="module-card">
                    <div class="module-icon darkblue"><i class="bi bi-clipboard2-data-fill"></i></div>
                    <div class="module-info">
                        <h3>Historial de Salidas</h3>
                        <p>Registro y historial de salidas de activos</p>
                    </div>
                    <div class="module-arrow"><i class="bi bi-chevron-right"></i></div>
                </a>

                <a href="../activo_fijo/requisiciones_af.php" class="module-card">
                    <div class="module-icon darkblue"><i class="bi bi-file-earmark-text"></i></div>
                    <div class="module-info">
                        <h3>Requisiciones AF</h3>
                        <p>Solicitudes de Partida 5000 y Licitación</p>
                    </div>
                    <div class="module-arrow"><i class="bi bi-chevron-right"></i></div>
                </a>

                <a href="../orden_compra/facturas_lista.php" class="module-card">
                    <div class="module-icon darkblue"><i class="bi bi-file-earmark-text"></i></div>
                    <div class="module-info">
                        <h3>Facturas AF</h3>
                        <p>Facturas de Partida 5000 y Licitación</p>
                    </div>
                    <div class="module-arrow"><i class="bi bi-chevron-right"></i></div>
                </a>

                <?php elseif ($esAlmacenGeneral): ?>
                <!-- ALMACÉN GENERAL: Todo menos Productos -->
                <a href="../activo_fijo/index.php" class="module-card">
                    <div class="module-icon darkblue"><i class="bi bi-safe-fill"></i></div>
                    <div class="module-info">
                        <h3>Activo Fijo</h3>
                        <p>Control de entrada y salida de activos fijos</p>
                    </div>
                    <div class="module-arrow"><i class="bi bi-chevron-right"></i></div>
                </a>

                <a href="../activo_fijo/main_activF.php" class="module-card">
                    <div class="module-icon darkblue"><i class="bi bi-clipboard2-data-fill"></i></div>
                    <div class="module-info">
                        <h3>Historial de Salidas</h3>
                        <p>Registro y historial de salidas de activos</p>
                    </div>
                    <div class="module-arrow"><i class="bi bi-chevron-right"></i></div>
                </a>

                <a href="../orden_compra/requisiciones.php" class="module-card">
                    <div class="module-icon darkblue"><i class="bi bi-cart3"></i></div>
                    <div class="module-info">
                        <h3>Almacén General</h3>
                        <p>Requisiciones y órdenes de compra</p>
                    </div>
                    <div class="module-arrow"><i class="bi bi-chevron-right"></i></div>
                </a>

            

              

                <a href="../activo_fijo/buscar_activo.php" class="module-card">
                    <div class="module-icon darkblue"><i class="bi bi-search"></i></div>
                    <div class="module-info">
                        <h3>Buscar Activo (Móvil)</h3>
                        <p>Escanea o busca activos desde tu celular</p>
                    </div>
                    <div class="module-arrow"><i class="bi bi-chevron-right"></i></div>
                </a>

                <?php elseif ($rol === 'mantenimiento' || $rol === 'almacenista'): ?>
                <!-- MANTENIMIENTO / ALMACENISTA: SOLO PRODUCTOS -->
                <a href="../productos/main_productos.php" class="module-card">
                    <div class="module-icon darkblue"><i class="bi bi-box-seam-fill"></i></div>
                    <div class="module-info">
                        <h3>Productos</h3>
                        <p>Catálogo, inventario y códigos de barras</p>
                    </div>
                    <div class="module-arrow"><i class="bi bi-chevron-right"></i></div>
                </a>

                <?php else: ?>
                <!-- ADMIN / USUARIO: TODOS LOS MÓDULOS -->
                <?php if (puedeVerActivoFijo()): ?>
                <a href="../activo_fijo/index.php" class="module-card">
                    <div class="module-icon darkblue"><i class="bi bi-safe-fill"></i></div>
                    <div class="module-info">
                        <h3>Activo Fijo</h3>
                        <p>Control de entrada y salida de activos fijos</p>
                    </div>
                    <div class="module-arrow"><i class="bi bi-chevron-right"></i></div>
                </a>
                <?php endif; ?>

                <?php if (puedeVerActivoFijo()): ?>
                <a href="../activo_fijo/main_activF.php" class="module-card">
                    <div class="module-icon darkblue"><i class="bi bi-clipboard2-data-fill"></i></div>
                    <div class="module-info">
                        <h3>Historial de Salidas</h3>
                        <p>Registro y historial de salidas de activos</p>
                    </div>
                    <div class="module-arrow"><i class="bi bi-chevron-right"></i></div>
                </a>
                <?php endif; ?>

                <?php if (puedeVerAlmacenGeneral()): ?>
                <a href="../orden_compra/requisiciones.php" class="module-card">
                    <div class="module-icon darkblue"><i class="bi bi-cart3"></i></div>
                    <div class="module-info">
                        <h3>Almacén General</h3>
                        <p>Requisiciones y órdenes de compra</p>
                    </div>
                    <div class="module-arrow"><i class="bi bi-chevron-right"></i></div>
                </a>
                <?php endif; ?>

                <?php if (puedeVerFacturas()): ?>
                <a href="../orden_compra/facturas_lista.php" class="module-card">
                    <div class="module-icon darkblue"><i class="bi bi-file-earmark-text"></i></div>
                    <div class="module-info">
                        <h3>Facturas</h3>
                        <p>Lista y gestión de facturas</p>
                    </div>
                    <div class="module-arrow"><i class="bi bi-chevron-right"></i></div>
                </a>
                <?php endif; ?>

                <?php if (puedeVerCompras()): ?>
                <a href="../orden_compra/compras.php" class="module-card">
                    <div class="module-icon darkblue"><i class="bi bi-cart-check"></i></div>
                    <div class="module-info">
                        <h3>Control de Compras</h3>
                        <p>Seguimiento y gestión de compras</p>
                    </div>
                    <div class="module-arrow"><i class="bi bi-chevron-right"></i></div>
                </a>
                <?php endif; ?>

                <?php if (puedeVerProductos()): ?>
                <a href="../productos/main_productos.php" class="module-card">
                    <div class="module-icon darkblue"><i class="bi bi-box-seam-fill"></i></div>
                    <div class="module-info">
                        <h3>Productos</h3>
                        <p>Catálogo, inventario y códigos de barras</p>
                    </div>
                    <div class="module-arrow"><i class="bi bi-chevron-right"></i></div>
                </a>
                <?php endif; ?>

                <?php if (esAdmin()): ?>
                <div class="admin-section">
                    <div class="admin-label"><i class="bi bi-shield-lock me-1"></i> Administración</div>
                    <a href="../usuarios/main_usuarios.php" class="module-card" style="opacity:.85">
                        <div class="module-icon darkblue"><i class="bi bi-people-fill"></i></div>
                        <div class="module-info">
                            <h3>Gestión de Usuarios</h3>
                            <p>Crear, editar y administrar cuentas de acceso</p>
                        </div>
                        <div class="module-arrow"><i class="bi bi-chevron-right"></i></div>
                    </a>
                </div>
                <?php endif; ?>

                <?php endif; ?>

            </div>

            <div class="home-footer">
                <i class="bi bi-geo-alt-fill" style="color: var(--c-accent)"></i>
                Tecnológico Nacional de México &nbsp;·&nbsp; Campus Saltillo &nbsp;·&nbsp; 2025
            </div>

        </div>
    </div>

</div>

</body>
</html>