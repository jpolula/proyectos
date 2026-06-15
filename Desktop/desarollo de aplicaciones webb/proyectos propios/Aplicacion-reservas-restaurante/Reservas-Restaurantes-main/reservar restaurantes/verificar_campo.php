<?php
// Script para verificar si el campo personas_solicitadas existe en la tabla reservas

try {
    // Conectar a la base de datos
    $pdo = new PDO('mysql:host=localhost;dbname=restaurante_reservas', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    // Verificar si la columna existe
    $stmt = $pdo->query("SHOW COLUMNS FROM reservas LIKE 'personas_solicitadas'");
    $columna_existe = $stmt->rowCount() > 0;
    
    if ($columna_existe) {
        echo "La columna 'personas_solicitadas' existe en la tabla 'reservas'.";
    } else {
        echo "La columna 'personas_solicitadas' NO existe en la tabla 'reservas'.";
        
        // Intentar crearla
        $pdo->exec("ALTER TABLE reservas ADD COLUMN personas_solicitadas INT DEFAULT 0 AFTER cantidad_personas");
        echo "<br>Se ha agregado la columna 'personas_solicitadas' a la tabla 'reservas'.";
    }
    
    // Mostrar la estructura de la tabla
    echo "<br><br>Estructura de la tabla reservas:<br>";
    $stmt = $pdo->query("DESCRIBE reservas");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row['Field'] . " - " . $row['Type'] . "<br>";
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
