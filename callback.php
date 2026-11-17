<?php
// callback.php
// Microsoft redirige aquí después de que el usuario inicia sesión

session_start();

// Cargar configuración
$config = include 'config_microsoft.php';

// ============================================================
// PASO 1: Verificar que no haya errores
// ============================================================
if (isset($_GET['error'])) {
    $error = $_GET['error_description'] ?? $_GET['error'];
    die('❌ Error de autenticación: ' . htmlspecialchars($error));
}

// ============================================================
// PASO 2: Verificar el estado (seguridad - evita CSRF)
// ============================================================
if (isset($_GET['state']) && isset($_SESSION['oauth2state'])) {
    if ($_GET['state'] !== $_SESSION['oauth2state']) {
        die('❌ Estado inválido. Posible ataque CSRF.');
    }
}

// ============================================================
// PASO 3: Verificar que tengamos el código de autorización
// ============================================================
if (!isset($_GET['code'])) {
    die('❌ No se recibió código de autorización.');
}

$code = $_GET['code'];

// ============================================================
// PASO 4: Intercambiar el código por un token de acceso
// ============================================================
$tokenUrl = "https://login.microsoftonline.com/{$config['tenantId']}/oauth2/v2.0/token";

$postData = http_build_query([
    'client_id' => $config['clientId'],
    'client_secret' => $config['clientSecret'],
    'code' => $code,
    'redirect_uri' => $config['redirectUri'],
    'grant_type' => 'authorization_code',
    'scope' => $config['scopes']
]);

// Iniciar cURL para obtener el token
$ch = curl_init($tokenUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/x-www-form-urlencoded'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Verificar que la petición fue exitosa
if ($httpCode !== 200) {
    die('❌ Error al obtener token: ' . $response);
}

$tokenData = json_decode($response, true);
if (!isset($tokenData['access_token'])) {
    die('❌ No se recibió access_token');
}

$accessToken = $tokenData['access_token'];

// ============================================================
// PASO 5: Obtener los datos del usuario desde Microsoft Graph
// ============================================================
$userUrl = 'https://graph.microsoft.com/v1.0/me';

$ch = curl_init($userUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $accessToken,
    'Content-Type: application/json'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    die('❌ Error al obtener datos del usuario: ' . $response);
}

$userData = json_decode($response, true);

// ============================================================
// PASO 6: Obtener los datos importantes del usuario
// ============================================================
$email = $userData['mail'] ?? $userData['userPrincipalName'] ?? null;
$nombre = $userData['givenName'] ?? $userData['displayName'] ?? 'Usuario';
$apellido = $userData['surname'] ?? '';
$microsoftId = $userData['id'];

// ============================================================
// PASO 7: Buscar o crear el usuario en tu base de datos
// ============================================================
require_once __DIR__ . '/includes/db.php';

// Verificar si el usuario ya existe por su email
$stmt = $pdo->prepare("SELECT id, nombre, rol FROM usuarios WHERE username = ?");
$stmt->execute([$email]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if ($usuario) {
    // El usuario existe, actualizar datos si es necesario
    $usuario_id = $usuario['id'];
    $usuario_nombre = $usuario['nombre'];
    $usuario_rol = $usuario['rol'];
    
    // Actualizar último login
    $stmt = $pdo->prepare("UPDATE usuarios SET last_login = NOW() WHERE id = ?");
    $stmt->execute([$usuario_id]);
    
} else {
    // El usuario no existe, crearlo
    $username = $email;
    $nombreCompleto = $nombre . ' ' . $apellido;
    
    // Generar una contraseña aleatoria (no se usará, pero la BD la requiere)
    $password_hash = password_hash(bin2hex(random_bytes(16)), PASSWORD_BCRYPT);
    
    $stmt = $pdo->prepare("
        INSERT INTO usuarios (username, password_hash, nombre, rol, activo) 
        VALUES (?, ?, ?, 'usuario', 1)
    ");
    $stmt->execute([$username, $password_hash, $nombreCompleto]);
    
    $usuario_id = $pdo->lastInsertId();
    $usuario_nombre = $nombreCompleto;
    $usuario_rol = 'usuario';
}

// ============================================================
// PASO 8: Crear la sesión del usuario
// ============================================================
$_SESSION['usuario_id'] = $usuario_id;
$_SESSION['usuario_nombre'] = $usuario_nombre;
$_SESSION['usuario_rol'] = $usuario_rol;
$_SESSION['usuario_email'] = $email;
$_SESSION['auth_provider'] = 'microsoft';

// ============================================================
// PASO 9: Redirigir al panel principal
// ============================================================
header('Location: ' . BASE_PATH . 'home/index.php');
exit;