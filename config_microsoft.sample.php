<?php
// config_microsoft.sample.php
// Copy this file to config_microsoft.php and fill in your Azure credentials.

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];

$isLocal = ($host === 'localhost' || $host === '127.0.0.1');
$baseUri = $isLocal ? $protocol . $host . '/ItsInventario2025/' : $protocol . $host . '/';

return [
    // === CREDENCIALES DE MICROSOFT (Cambiar por las reales en local) ===
    'tenantId' => 'TU_TENANT_ID_AQUI',        
    'clientId' => 'TU_CLIENT_ID_AQUI',        
    'clientSecret' => 'TU_CLIENT_SECRET_AQUI', 
    // ===========================================
    
    'redirectUri' => $baseUri . 'callback.php',
    'scopes' => 'openid profile email User.Read'
];