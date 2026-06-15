<?php
// Activar la visualización de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Depuración de Checkboxes Personalizados</h1>";

try {
    // Verificar si la sesión está iniciada
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    echo "<p>Sesión iniciada correctamente.</p>";
    
    // Verificar si el usuario está autenticado
    if (!isset($_SESSION['admin_id'])) {
        echo "<p style='color:red;'>ERROR: No hay sesión de administrador activa.</p>";
        echo "<p>Redirigiendo a la página de login...</p>";
        echo "<script>setTimeout(function() { window.location.href = 'login.php'; }, 3000);</script>";
        exit;
    }
    
    echo "<p>Usuario autenticado: ID " . $_SESSION['admin_id'] . "</p>";
    
    // Verificar conexión a la base de datos
    echo "<h2>Verificando conexión a la base de datos</h2>";
    
    $pdo = new PDO('mysql:host=localhost;dbname=restaurante_reservas', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    echo "<p>Conexión a la base de datos establecida correctamente.</p>";
    
    // Verificar si existen las tablas necesarias
    echo "<h2>Verificando tablas en la base de datos</h2>";
    
    $tablas = [
        'checkboxes_personalizados',
        'reservas_checkboxes'
    ];
    
    foreach ($tablas as $tabla) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$tabla'");
        if ($stmt->rowCount() > 0) {
            echo "<p>Tabla '$tabla' encontrada.</p>";
            
            // Mostrar estructura de la tabla
            $stmt = $pdo->query("DESCRIBE $tabla");
            $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<details>";
            echo "<summary>Estructura de la tabla '$tabla'</summary>";
            echo "<table border='1' cellpadding='5'>";
            echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Clave</th><th>Predeterminado</th><th>Extra</th></tr>";
            
            foreach ($columnas as $columna) {
                echo "<tr>";
                echo "<td>" . $columna['Field'] . "</td>";
                echo "<td>" . $columna['Type'] . "</td>";
                echo "<td>" . $columna['Null'] . "</td>";
                echo "<td>" . $columna['Key'] . "</td>";
                echo "<td>" . $columna['Default'] . "</td>";
                echo "<td>" . $columna['Extra'] . "</td>";
                echo "</tr>";
            }
            
            echo "</table>";
            echo "</details>";
        } else {
            echo "<p style='color:red;'>ERROR: Tabla '$tabla' no encontrada.</p>";
            
            // Crear la tabla si no existe
            if ($tabla === 'checkboxes_personalizados') {
                echo "<p>Creando tabla 'checkboxes_personalizados'...</p>";
                
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
            } else if ($tabla === 'reservas_checkboxes') {
                echo "<p>Creando tabla 'reservas_checkboxes'...</p>";
                
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
            }
        }
    }
    
    // Verificar si hay checkboxes en la tabla
    $stmt = $pdo->query("SELECT COUNT(*) FROM checkboxes_personalizados");
    $count = $stmt->fetchColumn();
    
    echo "<p>Número de checkboxes personalizados: $count</p>";
    
    if ($count === 0) {
        echo "<p>No hay checkboxes personalizados. Insertando ejemplos...</p>";
        
        // Insertar algunos checkboxes de ejemplo
        $pdo->exec("INSERT INTO checkboxes_personalizados (texto, descripcion, activo, orden) VALUES 
        ('Acepto la política de privacidad', 'El cliente acepta nuestra política de privacidad y tratamiento de datos', TRUE, 1),
        ('Deseo recibir ofertas y promociones', 'El cliente desea recibir información sobre ofertas y promociones', TRUE, 2)");
        
        echo "<p>Checkboxes de ejemplo insertados correctamente.</p>";
    }
    
    echo "<h2>Verificación completada</h2>";
    echo "<p>Todo parece estar correcto. <a href='checkboxes_personalizados.php'>Ir a la página de checkboxes personalizados</a></p>";
    
} catch (Exception $e) {
    echo "<h2 style='color:red;'>Error detectado</h2>";
    echo "<p><strong>Mensaje:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Archivo:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Línea:</strong> " . $e->getLine() . "</p>";
    echo "<p><strong>Traza:</strong></p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>
