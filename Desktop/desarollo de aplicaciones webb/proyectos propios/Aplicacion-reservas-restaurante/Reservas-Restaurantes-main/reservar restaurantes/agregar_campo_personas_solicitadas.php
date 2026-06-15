<?php
// Script para agregar el campo personas_solicitadas a la tabla reservas

try {
    // Conectar a la base de datos
    $pdo = new PDO('mysql:host=localhost;dbname=restaurante_reservas', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    // Verificar si la columna ya existe
    $stmt = $pdo->query("SHOW COLUMNS FROM reservas LIKE 'personas_solicitadas'");
    $columna_existe = $stmt->rowCount() > 0;
    
    if (!$columna_existe) {
        // Agregar la columna personas_solicitadas
        $pdo->exec("ALTER TABLE reservas ADD COLUMN personas_solicitadas INT DEFAULT 0 AFTER cantidad_personas");
        echo "Se ha agregado correctamente la columna 'personas_solicitadas' a la tabla 'reservas'.";
    } else {
        echo "La columna 'personas_solicitadas' ya existe en la tabla 'reservas'.";
    }
} catch (PDOException $e) {
    echo "Error al modificar la base de datos: " . $e->getMessage();
}
?>
