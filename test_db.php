<?php
echo "<h1>Prueba de conexión a la base de datos</h1>";

$host = 'localhost';
$db = 'almacenatec';
$user = 'root';
$pass = 'M1cr0Apl1kaz1on3z25';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    echo "<p style='color: green;'>✅ Conexión exitosa a la base de datos <strong>$db</strong></p>";
    
    // Verificar usuarios
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM usuarios");
    $row = $stmt->fetch();
    echo "<p>Total de usuarios: <strong>{$row['total']}</strong></p>";
    
    // Mostrar usuarios
    $stmt = $pdo->query("SELECT id, username, nombre, rol FROM usuarios");
    echo "<h2>Usuarios registrados:</h2>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Usuario</th><th>Nombre</th><th>Rol</th></tr>";
    while ($row = $stmt->fetch()) {
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>{$row['username']}</td>";
        echo "<td>{$row['nombre']}</td>";
        echo "<td>{$row['rol']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>