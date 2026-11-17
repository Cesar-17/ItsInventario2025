<?php
// espera_rol.php
require_once __DIR__ . '/includes/auth.php'; // Verifica la sesión

$nombre = htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Usuario');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cuenta en Revisión — AlmacenaTec</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&family=Poppins:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            display: flex; align-items: center; justify-content: center; 
            height: 100vh; background: #F0F4F9; font-family: 'Inter', sans-serif; 
        }
        .card { 
            max-width: 550px; width: 90%; padding: 50px 40px; 
            border-radius: 12px; background: #fff; text-align: center; 
            box-shadow: 0 10px 30px rgba(0,0,0,.1); border-top: 6px solid #c9973a; 
        }
        .card i { font-size: 60px; color: #0B2D6B; margin-bottom: 20px; }
        .card h2 { font-family: 'Poppins', sans-serif; color: #0B2D6B; font-weight: 800; margin-bottom: 12px; font-size: 22px; }
        .card p { color: #475569; font-size: 15px; margin-bottom: 16px; line-height: 1.5; }
        .card .btn-logout { 
            display: inline-flex; align-items: center; gap: 8px; margin-top: 20px;
            background: #0B2D6B; color: #fff; text-decoration: none;
            padding: 10px 24px; border-radius: 6px; font-weight: 600; transition: background 0.2s;
        }
        .card .btn-logout:hover { background: #071d46; }
    </style>
</head>
<body>
    <div class="card">
        <i class="bi bi-hourglass-split"></i>
        <h2>Hola, <?= $nombre ?></h2>
        <p>Tu cuenta ha sido creada correctamente y está **en espera de asignación**.</p>
        <p>El Jefe de tu departamento o el Administrador te asignará los permisos correspondientes a la brevedad.</p>
        <a href="<?= BASE_PATH ?>logout.php" class="btn-logout">
            <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
        </a>
    </div>
</body>
</html>