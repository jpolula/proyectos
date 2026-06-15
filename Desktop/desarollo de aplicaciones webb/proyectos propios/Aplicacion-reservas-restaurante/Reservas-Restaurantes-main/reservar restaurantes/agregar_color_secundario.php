<?php
// Script para agregar el campo color_secundario a la tabla configuracion
try {
    // Conectar a la base de datos
    $pdo = new PDO('mysql:host=localhost;dbname=restaurante_reservas', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    // Verificar si la columna ya existe
    $stmt = $pdo->query("SHOW COLUMNS FROM configuracion LIKE 'color_secundario'");
    $column_exists = $stmt->fetch();
    
    if (!$column_exists) {
        // Agregar la columna color_secundario
        $pdo->exec("ALTER TABLE configuracion ADD COLUMN color_secundario VARCHAR(20) DEFAULT '#FF9800' AFTER color_principal");
        echo "Se ha agregado la columna color_secundario a la tabla configuracion.<br>";
    } else {
        echo "La columna color_secundario ya existe en la tabla configuracion.<br>";
    }
    
    echo "Operación completada con éxito.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
