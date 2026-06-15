<?php
try {
    // Conectar a la base de datos
    $pdo = new PDO("mysql:host=localhost;dbname=restaurante_reservas", "root", "", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    // Obtener la estructura de la tabla administrador
    $stmt = $pdo->query("DESCRIBE administrador");
    $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>Estructura de la tabla administrador:</h2>";
    echo "<pre>";
    print_r($columnas);
    echo "</pre>";
    
    // Obtener todos los administradores
    $stmt = $pdo->query("SELECT * FROM administrador");
    $administradores = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>Administradores actuales:</h2>";
    echo "<pre>";
    print_r($administradores);
    echo "</pre>";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
