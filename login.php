<?php

// login.php - Página de login

// COMENTA ESTA LÍNEA UN MOMENTO PARA PROBAR:
 require_once __DIR__ . '/includes/auth.php'; 

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['usuario_id'])) {
    header('Location: ' . BASE_PATH . 'home/index.php');
    exit;
}

require_once __DIR__ . '/includes/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Ingresa usuario y contraseña.';
    } else {
        $stmt = $pdo->prepare("SELECT id, username, password_hash, nombre, rol, departamento, activo FROM usuarios WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !$user['activo']) {
            $error = 'Usuario o contraseña incorrectos.';
        } elseif (!password_verify($password, $user['password_hash'])) {
            $error = 'Usuario o contraseña incorrectos.';
        } else {
            session_regenerate_id(true);
            $_SESSION['usuario_id']           = $user['id'];
            $_SESSION['usuario_nombre']       = $user['nombre'];
            $_SESSION['usuario_rol']          = $user['rol'];
            $_SESSION['usuario_departamento'] = $user['departamento'];

            $pdo->prepare("UPDATE usuarios SET last_login = NOW() WHERE id = ?")->execute([$user['id']]);

            header('Location: ' . BASE_PATH . 'home/index.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Iniciar sesión — AlmacenaTec</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <style>
    /* ============================================================
        ROOT VARIABLES
        ============================================================ */
    :root {
      --c-primary: #0B2D6B;
      --c-primary-dk: #071d46;
      --c-primary-lt: #1a4a9e;
      --c-gold: #c9973a;
      --c-accent: #B81C2E;
      --c-bg: #F0F4F9;
      --c-surface: #FFFFFF;
      --c-border: #E2E8F0;
      --c-text: #1E293B;
      --c-text-md: #475569;
      --c-text-muted: #94A3B8;
      --radius-sm: 8px;
      --radius: 12px;
      --radius-lg: 20px;
      --shadow: 0 4px 24px rgba(11,45,107,.12);
      --shadow-hover: 0 8px 40px rgba(11,45,107,.18);
      --transition: all .3s cubic-bezier(.4,0,.2,1);
    }

    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    html, body { height: 100%; }
    body { font-family: 'Inter', sans-serif; background: var(--c-bg); }

    /* ============================================================
        LAYOUT
        ============================================================ */
    .login-layout {
      display: grid;
      grid-template-columns: 1.1fr 1fr;
      min-height: 100vh;
    }

    /* ============================================================
        HERO PANEL
        ============================================================ */
    .login-hero {
      position: relative;
      background: linear-gradient(150deg, var(--c-primary) 0%, var(--c-primary-dk) 60%, #040f28 100%);
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: flex-start;
      padding: 60px 56px;
      overflow: hidden;
      isolation: isolate;
    }

    .login-hero::before {
      content: '';
      position: absolute;
      inset: 0;
      background: url('<?= BASE_PATH ?>media/its_fondo.jpg_large') center/cover no-repeat;
      opacity: .06;
      z-index: -1;
    }

    /* Decoraciones */
    .hero-deco {
      position: absolute;
      border-radius: 50%;
      pointer-events: none;
    }
    .hero-deco-1 {
      width: 520px;
      height: 520px;
      top: -180px;
      right: -160px;
      background: radial-gradient(circle, rgba(201,151,58,.12) 0%, transparent 70%);
    }
    .hero-deco-2 {
      width: 320px;
      height: 320px;
      bottom: -120px;
      left: -120px;
      background: radial-gradient(circle, rgba(184,28,46,.15) 0%, transparent 70%);
    }
    .hero-deco-3 {
      width: 160px;
      height: 160px;
      bottom: 120px;
      right: 40px;
      background: radial-gradient(circle, rgba(201,151,58,.08) 0%, transparent 70%);
    }
    .hero-deco-4 {
      position: absolute;
      top: 50%;
      right: 30px;
      width: 1px;
      height: 160px;
      background: linear-gradient(to bottom, transparent, rgba(255,255,255,.08), transparent);
    }

    .hero-content {
      position: relative;
      z-index: 1;
    }

    .hero-badge {
      display: inline-flex;
      align-items: center;
      gap: 10px;
      background: rgba(255,255,255,.08);
      backdrop-filter: blur(12px);
      border: 1px solid rgba(255,255,255,.12);
      border-radius: 40px;
      padding: 6px 18px 6px 16px;
      font-size: 11px;
      font-weight: 600;
      color: rgba(255,255,255,.85);
      letter-spacing: .4px;
      text-transform: uppercase;
      margin-bottom: 32px;
    }
    .hero-badge i {
      color: var(--c-gold);
      font-size: 13px;
    }

    .hero-title {
      font-family: 'Poppins', sans-serif;
      font-size: clamp(42px, 5vw, 60px);
      font-weight: 800;
      color: #fff;
      line-height: 1.05;
      margin-bottom: 16px;
      letter-spacing: -1px;
    }
    .hero-title span { color: var(--c-gold); }

    .hero-subtitle {
      font-size: 15px;
      color: rgba(255,255,255,.6);
      line-height: 1.8;
      max-width: 400px;
      margin-bottom: 40px;
      font-weight: 300;
    }

    .hero-divider {
      width: 50px;
      height: 3px;
      background: linear-gradient(90deg, var(--c-gold), var(--c-accent));
      border-radius: 2px;
      margin-bottom: 32px;
    }

    .hero-stats {
      display: flex;
      gap: 40px;
    }
    .hero-stat { text-align: left; }
    .hero-stat-num {
      font-family: 'Poppins', sans-serif;
      font-size: 24px;
      font-weight: 700;
      color: var(--c-gold);
    }
    .hero-stat-label {
      font-size: 10px;
      color: rgba(255,255,255,.4);
      text-transform: uppercase;
      letter-spacing: .6px;
      margin-top: 2px;
    }

    /* ============================================================
        FORM PANEL
        ============================================================ */
    .login-form-panel {
      background: var(--c-bg);
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      padding: 48px 40px;
    }

    .login-box {
      width: 100%;
      max-width: 400px;
    }

    .login-logo {
      display: flex;
      align-items: center;
      gap: 14px;
      margin-bottom: 32px;
    }
    .login-logo-mark {
      width: 48px;
      height: 48px;
      border-radius: 12px;
      background: var(--c-primary);
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: 'Poppins', sans-serif;
      font-weight: 800;
      font-size: 17px;
      color: #fff;
      letter-spacing: -1px;
      flex-shrink: 0;
      box-shadow: 0 4px 14px rgba(11,45,107,.25);
    }
    .login-logo-text { display: flex; flex-direction: column; }
    .login-logo-name {
      font-family: 'Poppins', sans-serif;
      font-weight: 700;
      font-size: 20px;
      color: var(--c-text);
      line-height: 1.1;
    }
    .login-logo-name span { color: var(--c-primary); }
    .login-logo-sub {
      font-size: 11px;
      color: var(--c-text-muted);
      margin-top: 1px;
    }

    .login-header {
      margin-bottom: 28px;
    }
    .login-title {
      font-family: 'Poppins', sans-serif;
      font-size: 24px;
      font-weight: 700;
      color: var(--c-text);
      margin-bottom: 4px;
    }
    .login-subtitle {
      font-size: 14px;
      color: var(--c-text-muted);
    }

    /* ── Formulario ── */
    .form-group {
      margin-bottom: 18px;
    }

    .form-label {
      font-size: 12.5px;
      font-weight: 600;
      color: var(--c-text-md);
      margin-bottom: 6px;
      display: block;
    }

    .input-icon-wrap {
      position: relative;
    }
    .input-icon-wrap .bi {
      position: absolute;
      left: 14px;
      top: 50%;
      transform: translateY(-50%);
      color: var(--c-text-muted);
      font-size: 16px;
      transition: var(--transition);
    }
    .input-icon-wrap .form-control {
      width: 100%;
      padding: 12px 16px 12px 44px;
      border: 2px solid var(--c-border);
      border-radius: var(--radius-sm);
      background: var(--c-surface);
      color: var(--c-text);
      font-size: 14px;
      font-family: 'Inter', sans-serif;
      transition: var(--transition);
      outline: none;
    }
    .input-icon-wrap .form-control:focus {
      border-color: var(--c-primary);
      box-shadow: 0 0 0 4px rgba(11,45,107,.08);
    }
    .input-icon-wrap .form-control:focus + .bi {
      color: var(--c-primary);
    }

    .input-icon-wrap .form-control.with-toggle {
      padding-right: 44px;
    }

    .toggle-pass {
      position: absolute;
      right: 14px;
      top: 50%;
      transform: translateY(-50%);
      background: none;
      border: none;
      cursor: pointer;
      color: var(--c-text-muted);
      font-size: 16px;
      padding: 0;
      transition: var(--transition);
    }
    .toggle-pass:hover { color: var(--c-text); }

    .btn-login {
      width: 100%;
      padding: 14px 20px;
      background: var(--c-primary);
      color: #fff;
      border: none;
      border-radius: var(--radius-sm);
      font-family: 'Poppins', sans-serif;
      font-size: 15px;
      font-weight: 600;
      cursor: pointer;
      transition: var(--transition);
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      margin-top: 6px;
    }
    .btn-login:hover {
      background: var(--c-primary-dk);
      transform: translateY(-2px);
      box-shadow: 0 8px 24px rgba(11,45,107,.25);
    }
    .btn-login:active {
      transform: scale(.98);
    }

    .error-box {
      background: rgba(184,28,46,.06);
      border: 1px solid rgba(184,28,46,.2);
      border-radius: var(--radius-sm);
      padding: 12px 16px;
      font-size: 13px;
      color: var(--c-accent);
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 20px;
    }
    .error-box i { font-size: 18px; flex-shrink: 0; }

    .login-divider {
      display: flex;
      align-items: center;
      gap: 16px;
      margin: 20px 0;
    }
    .login-divider::before,
    .login-divider::after {
      content: '';
      flex: 1;
      height: 1px;
      background: var(--c-border);
    }
    .login-divider span {
      font-size: 12px;
      color: var(--c-text-muted);
      font-weight: 500;
      white-space: nowrap;
    }

    .btn-microsoft {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 12px;
      width: 100%;
      padding: 12px 20px;
      background: #2F2F2F;
      color: #fff;
      border: none;
      border-radius: var(--radius-sm);
      font-size: 14px;
      font-weight: 600;
      font-family: 'Inter', sans-serif;
      cursor: pointer;
      transition: var(--transition);
      text-decoration: none;
    }
    .btn-microsoft:hover {
      background: #1a1a1a;
      transform: translateY(-2px);
      box-shadow: 0 8px 24px rgba(0,0,0,.15);
    }
    .btn-microsoft svg { flex-shrink: 0; }

    .login-footer {
      margin-top: 32px;
      font-size: 11.5px;
      color: var(--c-text-muted);
      text-align: center;
      border-top: 1px solid var(--c-border);
      padding-top: 20px;
    }
    .login-footer i { color: var(--c-accent); }

    /* ============================================================
        RESPONSIVE
        ============================================================ */
    @media (max-width: 820px) {
      .login-layout { grid-template-columns: 1fr; }
      .login-hero { display: none; }
      .login-form-panel { padding: 40px 24px; }
      .login-box { max-width: 100%; }
    }

    @media (max-width: 480px) {
      .login-logo-mark { width: 42px; height: 42px; font-size: 14px; }
      .login-logo-name { font-size: 17px; }
      .login-title { font-size: 20px; }
    }
  </style>
</head>
<body>

<div class="login-layout">

  <!-- ════════════════════════════════════════════ -->
  <!-- HERO PANEL                                  -->
  <!-- ════════════════════════════════════════════ -->
  <div class="login-hero">
    <div class="hero-deco hero-deco-1"></div>
    <div class="hero-deco hero-deco-2"></div>
    <div class="hero-deco hero-deco-3"></div>
    <div class="hero-deco-4"></div>

    <div class="hero-content">
      <div class="hero-badge">
        <i class="bi bi-geo-alt-fill"></i>
        TecNM · Campus Saltillo
      </div>

      <h1 class="hero-title">
        Almacena<span>Tec</span>
      </h1>

      <p class="hero-subtitle">
        Sistema de gestión de inventario institucional para el Tecnológico Nacional de México.
      </p>

      <div class="hero-divider"></div>

      <div class="hero-stats">
        <div class="hero-stat">
          <div class="hero-stat-num">AF</div>
          <div class="hero-stat-label">Activo Fijo</div>
        </div>
        <div class="hero-stat">
          <div class="hero-stat-num">OC</div>
          <div class="hero-stat-label">Órdenes de Compra</div>
        </div>
        <div class="hero-stat">
          <div class="hero-stat-num">INV</div>
          <div class="hero-stat-label">Inventario</div>
        </div>
      </div>
    </div>
  </div>

  <!-- ════════════════════════════════════════════ -->
  <!-- FORM PANEL                                  -->
  <!-- ════════════════════════════════════════════ -->
  <div class="login-form-panel">
    <div class="login-box">

      <div class="login-logo">
        <div class="login-logo-mark">AT</div>
        <div class="login-logo-text">
          <span class="login-logo-name">Almacena<span>Tec</span></span>
          <span class="login-logo-sub">TecNM Campus Saltillo · 2025</span>
        </div>
      </div>

      <div class="login-header">
        <h2 class="login-title">Iniciar sesión</h2>
        <p class="login-subtitle">Ingresa tus credenciales para continuar.</p>
      </div>

      <?php if ($error): ?>
      <div class="error-box">
        <i class="bi bi-exclamation-triangle-fill"></i>
        <?= htmlspecialchars($error) ?>
      </div>
      <?php endif; ?>

      <form method="post" action="">
        <div class="form-group">
          <label class="form-label" for="username">Usuario</label>
          <div class="input-icon-wrap">
            <i class="bi bi-person"></i>
            <input type="text" id="username" name="username" class="form-control"
              placeholder="Nombre de usuario" autocomplete="username"
              value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" autofocus>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label" for="password">Contraseña</label>
          <div class="input-icon-wrap">
            <i class="bi bi-lock"></i>
            <input type="password" id="password" name="password" class="form-control with-toggle"
              placeholder="••••••••" autocomplete="current-password">
            <button type="button" class="toggle-pass" onclick="togglePass()" title="Mostrar/ocultar contraseña">
              <i class="bi bi-eye" id="pass-eye"></i>
            </button>
          </div>
        </div>

        <button type="submit" class="btn-login">
          <i class="bi bi-box-arrow-in-right"></i> Ingresar
        </button>
      </form>

      <div class="login-divider">
        <span>o</span>
      </div>

      <!-- Botón Microsoft 365 -->
      <a href="microsoft_login.php" class="btn-microsoft">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 23 23">
          <path fill="#f3f3f3" d="M0 0h11v11H0z"/>
          <path fill="#f3f3f3" d="M12 0h11v11H12z"/>
          <path fill="#f3f3f3" d="M0 12h11v11H0z"/>
          <path fill="#f3f3f3" d="M12 12h11v11H12z"/>
          <path fill="#f25022" d="M1 1h4v4H1z"/>
          <path fill="#7fba00" d="M6 1h4v4H6z"/>
          <path fill="#00a4ef" d="M1 6h4v4H1z"/>
          <path fill="#ffb900" d="M6 6h4v4H6z"/>
          <path fill="#f25022" d="M13 1h4v4h-4z"/>
          <path fill="#7fba00" d="M18 1h4v4h-4z"/>
          <path fill="#00a4ef" d="M13 6h4v4h-4z"/>
          <path fill="#ffb900" d="M18 6h4v4h-4z"/>
          <path fill="#f25022" d="M1 13h4v4H1z"/>
          <path fill="#7fba00" d="M6 13h4v4H6z"/>
          <path fill="#00a4ef" d="M1 18h4v4H1z"/>
          <path fill="#ffb900" d="M6 18h4v4H6z"/>
          <path fill="#f25022" d="M13 13h4v4h-4z"/>
          <path fill="#7fba00" d="M18 13h4v4h-4z"/>
          <path fill="#00a4ef" d="M13 18h4v4h-4z"/>
          <path fill="#ffb900" d="M18 18h4v4h-4z"/>
        </svg>
        Iniciar sesión con Microsoft 365
      </a>

      <div class="login-footer">
        <i class="bi bi-shield-lock me-1"></i>
        Acceso restringido — solo personal autorizado
      </div>

    </div>
  </div>

</div>

<script>
  function togglePass() {
    const inp = document.getElementById('password');
    const eye = document.getElementById('pass-eye');
    if (inp.type === 'password') {
      inp.type = 'text';
      eye.className = 'bi bi-eye-slash';
    } else {
      inp.type = 'password';
      eye.className = 'bi bi-eye';
    }
  }
</script>

</body>
</html>