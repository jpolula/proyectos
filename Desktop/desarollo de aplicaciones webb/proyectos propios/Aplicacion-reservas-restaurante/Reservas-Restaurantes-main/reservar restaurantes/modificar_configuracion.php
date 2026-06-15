<?php
// Script para modificar la tabla de configuración y añadir el campo de URL de redirección
try {
    // Conectar a la base de datos
    $pdo = new PDO("mysql:host=localhost;dbname=restaurante_reservas", "root", "", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    // Verificar si la columna ya existe
    $stmt = $pdo->prepare("SHOW COLUMNS FROM configuracion LIKE 'url_redireccion_reserva'");
    $stmt->execute();
    $columna_existe = $stmt->fetch();
    
    if (!$columna_existe) {
        // Añadir la columna si no existe
        $pdo->exec("ALTER TABLE configuracion ADD COLUMN url_redireccion_reserva VARCHAR(255) DEFAULT 'reserva_exitosa.php' COMMENT 'URL a la que redirigir tras una reserva exitosa'");
        echo "Columna 'url_redireccion_reserva' añadida correctamente a la tabla configuracion.<br>";
    } else {
        echo "La columna 'url_redireccion_reserva' ya existe en la tabla configuracion.<br>";
    }
    
    echo "Proceso completado con éxito.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
