<?php
// Script para configurar la base de datos

// Configuración de la conexión a la base de datos
$host = 'localhost';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

try {
    // Conectar a MySQL sin seleccionar una base de datos
    $pdo = new PDO("mysql:host=$host;charset=$charset", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    
    // Leer el archivo SQL
    $sql = file_get_contents('setup_database.sql');
    
    // Ejecutar las consultas SQL
    $pdo->exec($sql);
    
    echo "<h1>¡Base de datos creada correctamente!</h1>";
    echo "<p>Se ha creado la base de datos con todas las tablas necesarias.</p>";
    echo "<p>Usuario administrador creado:</p>";
    echo "<ul>";
    echo "<li><strong>Usuario:</strong> admin</li>";
    echo "<li><strong>Contraseña:</strong> admin123</li>";
    echo "</ul>";
    echo "<p><a href='index.php' style='display:inline-block;background-color:#4CAF50;color:white;padding:10px 15px;text-decoration:none;border-radius:4px;margin-top:20px;'>Ir a la página principal</a></p>";
    
} catch (PDOException $e) {
    echo "<h1>Error</h1>";
    echo "<p>No se pudo crear la base de datos: " . $e->getMessage() . "</p>";
}
?>
