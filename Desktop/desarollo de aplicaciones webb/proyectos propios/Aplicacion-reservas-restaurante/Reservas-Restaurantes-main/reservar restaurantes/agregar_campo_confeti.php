<?php
// Script para agregar el campo mostrar_confeti a la tabla configuracion

try {
    // Conectar a la base de datos
    $pdo = new PDO('mysql:host=localhost;dbname=restaurante_reservas', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    // Verificar si la columna ya existe
    $stmt = $pdo->query("SHOW COLUMNS FROM configuracion LIKE 'mostrar_confeti'");
    $columna_existe = $stmt->rowCount() > 0;
    
    if (!$columna_existe) {
        // Agregar la columna mostrar_confeti
        $pdo->exec("ALTER TABLE configuracion ADD COLUMN mostrar_confeti BOOLEAN DEFAULT TRUE AFTER color_secundario");
        echo "Se ha agregado correctamente la columna 'mostrar_confeti' a la tabla 'configuracion'.";
    } else {
        echo "La columna 'mostrar_confeti' ya existe en la tabla 'configuracion'.";
    }
} catch (PDOException $e) {
    echo "Error al modificar la base de datos: " . $e->getMessage();
}
?>
