<?php
// Script para verificar si la tabla reservas tiene la columna alergenos
// y añadirla si es necesario

try {
    // Conectar a la base de datos
    $pdo = new PDO('mysql:host=localhost;dbname=restaurante_reservas', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    // Verificar si la columna alergenos existe en la tabla reservas
    $stmt = $pdo->query("SHOW COLUMNS FROM reservas LIKE 'alergenos'");
    $columna_existe = $stmt->rowCount() > 0;
    
    if (!$columna_existe) {
        // La columna no existe, añadirla
        $pdo->exec("ALTER TABLE reservas ADD COLUMN alergenos TEXT AFTER tiene_alergenos");
        echo "Se ha añadido la columna 'alergenos' a la tabla 'reservas'.<br>";
    } else {
        echo "La columna 'alergenos' ya existe en la tabla 'reservas'.<br>";
    }
    
    echo "Verificación completada con éxito.";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
