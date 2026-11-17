<?php
// includes/db.php
// Loads DB credentials from environment variables first, then falls back to includes/config.php

// Try to get from environment (useful for production containers/servers)
$DB_HOST = getenv('DB_HOST') !== false ? getenv('DB_HOST') : null;
$DB_NAME = getenv('DB_NAME') !== false ? getenv('DB_NAME') : null;
$DB_USER = getenv('DB_USER') !== false ? getenv('DB_USER') : null;
$DB_PASS = getenv('DB_PASS') !== false ? getenv('DB_PASS') : null;

// Fallback to includes/config.php if present
if (!$DB_HOST || !$DB_NAME || !$DB_USER) {
    $configFile = __DIR__ . '/config.php';
    if (file_exists($configFile)) {
        $cfg = include $configFile;
        if (is_array($cfg)) {
            $DB_HOST = $DB_HOST ?: ($cfg['DB_HOST'] ?? '127.0.0.1');
            $DB_NAME = $DB_NAME ?: ($cfg['DB_NAME'] ?? 'ItsInventario');
            $DB_USER = $DB_USER ?: ($cfg['DB_USER'] ?? 'root');
            $DB_PASS = $DB_PASS ?: ($cfg['DB_PASS'] ?? '');
        }
    }
}

// Final sensible defaults for local development
$DB_HOST = $DB_HOST ?: '127.0.0.1';
$DB_NAME = $DB_NAME ?: 'ItsInventario';
$DB_USER = $DB_USER ?: 'root';
$DB_PASS = $DB_PASS ?: '';

$dsn = "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS, $options);
} catch (PDOException $e) {
    error_log('DB connection error: ' . $e->getMessage());
    if (getenv('APP_ENV') === 'production') {
        // don't show details in production
        http_response_code(500);
        echo "Error de conexión a la base de datos.";
    } else {
        // helpful message in development only
        echo "DB connection error: " . htmlspecialchars($e->getMessage());
    }
    exit;
}
