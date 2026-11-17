<?php
// microsoft_login.php
// Este archivo redirige al usuario a Microsoft para iniciar sesión

session_start();

// Cargar configuración
$config = include 'config_microsoft.php';

// Paso 1: Construir la URL de autorización de Microsoft
$authorizationUrl = "https://login.microsoftonline.com/{$config['tenantId']}/oauth2/v2.0/authorize?" . http_build_query([
    'client_id' => $config['clientId'],
    'response_type' => 'code',
    'redirect_uri' => $config['redirectUri'],
    'scope' => $config['scopes'],
    'response_mode' => 'query'
]);

// Paso 2: Generar un estado para seguridad (evita ataques CSRF)
$_SESSION['oauth2state'] = bin2hex(random_bytes(16));
$authorizationUrl .= '&state=' . $_SESSION['oauth2state'];

// Paso 3: Redirigir al usuario a Microsoft
header('Location: ' . $authorizationUrl);
exit;