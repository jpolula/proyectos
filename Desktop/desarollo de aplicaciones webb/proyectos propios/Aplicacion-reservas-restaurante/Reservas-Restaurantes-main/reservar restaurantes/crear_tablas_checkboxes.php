<?php
// Script para crear las tablas de checkboxes personalizados

try {
    // Conectar a la base de datos
    $pdo = new PDO('mysql:host=localhost;dbname=restaurante_reservas', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    echo "<h1>Verificando y creando tablas de checkboxes personalizados</h1>";
    
    // Verificar si la tabla checkboxes_personalizados existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'checkboxes_personalizados'");
    $tabla_checkboxes_existe = $stmt->rowCount() > 0;
    
    if (!$tabla_checkboxes_existe) {
        echo "<p>La tabla 'checkboxes_personalizados' no existe. Creándola...</p>";
        
        // Crear la tabla checkboxes_personalizados
        $pdo->exec("CREATE TABLE checkboxes_personalizados (
            id INT AUTO_INCREMENT PRIMARY KEY,
            texto VARCHAR(255) NOT NULL,
            descripcion TEXT,
            activo BOOLEAN DEFAULT TRUE,
            orden INT NOT NULL DEFAULT 0,
            fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            fecha_modificacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        
        echo "<p>Tabla 'checkboxes_personalizados' creada correctamente.</p>";
        
        // Insertar algunos checkboxes de ejemplo
        $pdo->exec("INSERT INTO checkboxes_personalizados (texto, descripcion, activo, orden) VALUES 
        ('Acepto la política de privacidad', 'El cliente acepta nuestra política de privacidad y tratamiento de datos', TRUE, 1),
        ('Deseo recibir ofertas y promociones', 'El cliente desea recibir información sobre ofertas y promociones', TRUE, 2)");
        
        echo "<p>Checkboxes de ejemplo insertados correctamente.</p>";
    } else {
        echo "<p>La tabla 'checkboxes_personalizados' ya existe.</p>";
    }
    
    // Verificar si la tabla reservas_checkboxes existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'reservas_checkboxes'");
    $tabla_reservas_checkboxes_existe = $stmt->rowCount() > 0;
    
    if (!$tabla_reservas_checkboxes_existe) {
        echo "<p>La tabla 'reservas_checkboxes' no existe. Creándola...</p>";
        
        // Crear la tabla reservas_checkboxes
        $pdo->exec("CREATE TABLE reservas_checkboxes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            reserva_id INT NOT NULL,
            checkbox_id INT NOT NULL,
            valor BOOLEAN DEFAULT FALSE,
            FOREIGN KEY (reserva_id) REFERENCES reservas(id) ON DELETE CASCADE,
            FOREIGN KEY (checkbox_id) REFERENCES checkboxes_personalizados(id) ON DELETE CASCADE,
            UNIQUE(reserva_id, checkbox_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        
        echo "<p>Tabla 'reservas_checkboxes' creada correctamente.</p>";
    } else {
        echo "<p>La tabla 'reservas_checkboxes' ya existe.</p>";
    }
    
    echo "<p>Proceso completado correctamente.</p>";
    echo "<p><a href='admin/checkboxes_personalizados.php'>Volver a la página de checkboxes personalizados</a></p>";
    
} catch (PDOException $e) {
    echo "<h1>Error</h1>";
    echo "<p>Se ha producido un error: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>
