<?php
// Actualizar tabla de configuración para añadir campos de personalización
try {
    $pdo = new PDO('mysql:host=localhost;dbname=restaurante_reservas', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    // Verificar si las columnas ya existen
    $stmt = $pdo->query("SHOW COLUMNS FROM configuracion LIKE 'logo_path'");
    $logoExists = $stmt->fetch();
    
    $stmt = $pdo->query("SHOW COLUMNS FROM configuracion LIKE 'color_principal'");
    $colorExists = $stmt->fetch();
    
    $stmt = $pdo->query("SHOW COLUMNS FROM configuracion LIKE 'tipo_letra'");
    $tipoLetraExists = $stmt->fetch();
    
    // Añadir columnas si no existen
    if (!$logoExists) {
        $pdo->exec("ALTER TABLE configuracion ADD COLUMN logo_path VARCHAR(255) DEFAULT NULL");
        echo "Columna logo_path añadida correctamente.<br>";
    }
    
    if (!$colorExists) {
        $pdo->exec("ALTER TABLE configuracion ADD COLUMN color_principal VARCHAR(20) DEFAULT '#3B82F6'");
        echo "Columna color_principal añadida correctamente.<br>";
    }
    
    if (!$tipoLetraExists) {
        $pdo->exec("ALTER TABLE configuracion ADD COLUMN tipo_letra VARCHAR(100) DEFAULT 'system-ui, -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Helvetica Neue, Arial'");
        echo "Columna tipo_letra añadida correctamente.<br>";
    }
    
    echo "Actualización de la tabla completada.";
    
} catch (PDOException $e) {
    echo "Error al actualizar la tabla: " . $e->getMessage();
}
?>
