<?php
// Configuración de la base de datos
$host = 'localhost';
$db = 'restaurante_reservas';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // Verificar estructura de la tabla dias_disponibles
    $stmt = $pdo->query("DESCRIBE dias_disponibles");
    echo "<h3>Estructura de la tabla dias_disponibles:</h3>";
    echo "<pre>";
    while ($row = $stmt->fetch()) {
        print_r($row);
    }
    echo "</pre>";
    
    // Verificar datos en la tabla
    $stmt = $pdo->query("SELECT * FROM dias_disponibles LIMIT 10");
    echo "<h3>Datos de ejemplo en dias_disponibles:</h3>";
    echo "<pre>";
    while ($row = $stmt->fetch()) {
        print_r($row);
    }
    echo "</pre>";
    
    // Verificar la configuración
    $stmt = $pdo->query("SELECT * FROM configuracion");
    echo "<h3>Configuración:</h3>";
    echo "<pre>";
    while ($row = $stmt->fetch()) {
        print_r($row);
    }
    echo "</pre>";
    
    // Verificar turnos
    $stmt = $pdo->query("SELECT * FROM turnos");
    echo "<h3>Turnos:</h3>";
    echo "<pre>";
    while ($row = $stmt->fetch()) {
        print_r($row);
    }
    echo "</pre>";
    
    // Verificar bloqueos
    $stmt = $pdo->query("SELECT * FROM bloqueos LIMIT 10");
    echo "<h3>Bloqueos:</h3>";
    echo "<pre>";
    while ($row = $stmt->fetch()) {
        print_r($row);
    }
    echo "</pre>";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
