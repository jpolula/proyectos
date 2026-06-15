<?php
// Script para verificar el valor actual de mostrar_confeti en la tabla configuracion

try {
    // Conectar a la base de datos
    $pdo = new PDO('mysql:host=localhost;dbname=restaurante_reservas', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    // Consultar el valor actual
    $stmt = $pdo->query("SELECT mostrar_confeti FROM configuracion WHERE id = 1");
    $config = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<h2>Valor actual de mostrar_confeti:</h2>";
    echo "<pre>";
    var_dump($config);
    echo "</pre>";
    
    // Actualizar el valor a TRUE para asegurarnos
    $stmt = $pdo->prepare("UPDATE configuracion SET mostrar_confeti = 1 WHERE id = 1");
    $stmt->execute();
    
    echo "<h2>Valor actualizado a TRUE</h2>";
    
    // Verificar el tipo de columna
    $stmt = $pdo->query("SHOW COLUMNS FROM configuracion LIKE 'mostrar_confeti'");
    $columna = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<h2>Información de la columna:</h2>";
    echo "<pre>";
    var_dump($columna);
    echo "</pre>";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
