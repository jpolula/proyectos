<?php
// Activar la visualización de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Función para mostrar información de depuración
function debug_var($var, $name = null) {
    echo "<div style='margin: 10px; padding: 10px; border: 1px solid #ccc; background-color: #f8f8f8;'>";
    if ($name) {
        echo "<strong>$name:</strong> ";
    }
    echo "<pre>";
    var_dump($var);
    echo "</pre>";
    echo "</div>";
}

echo "<h1>Depuración detallada de errores</h1>";

try {
    // Verificar si la sesión está iniciada
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    echo "<h2>Información de sesión</h2>";
    debug_var($_SESSION, "Contenido de la sesión");
    
    // Verificar conexión a la base de datos
    echo "<h2>Verificando conexión a la base de datos</h2>";
    
    $pdo = new PDO('mysql:host=localhost;dbname=restaurante_reservas', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    echo "<p>Conexión a la base de datos establecida correctamente.</p>";
    
    // Verificar todas las tablas en la base de datos
    echo "<h2>Tablas en la base de datos</h2>";
    
    $stmt = $pdo->query("SHOW TABLES");
    $tablas = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    debug_var($tablas, "Tablas existentes");
    
    // Verificar si existen las tablas necesarias
    $tablas_necesarias = [
        'administrador',
        'clientes',
        'turnos',
        'reservas',
        'capacidad',
        'bloqueos',
        'configuracion',
        'checkboxes_personalizados',
        'reservas_checkboxes'
    ];
    
    echo "<h2>Verificación de tablas necesarias</h2>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Tabla</th><th>Estado</th></tr>";
    
    foreach ($tablas_necesarias as $tabla) {
        $existe = in_array($tabla, $tablas);
        echo "<tr>";
        echo "<td>$tabla</td>";
        echo "<td style='color: " . ($existe ? "green" : "red") . ";'>" . ($existe ? "Existe" : "No existe") . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    // Verificar si existe el archivo auth.php
    echo "<h2>Verificación de archivos requeridos</h2>";
    
    $archivos = [
        'auth.php',
        'notificaciones.php',
        'limpiar_dias_pasados.php'
    ];
    
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Archivo</th><th>Estado</th></tr>";
    
    foreach ($archivos as $archivo) {
        $existe = file_exists(__DIR__ . '/' . $archivo);
        echo "<tr>";
        echo "<td>$archivo</td>";
        echo "<td style='color: " . ($existe ? "green" : "red") . ";'>" . ($existe ? "Existe" : "No existe") . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    // Verificar si la función agregar_notificacion existe
    echo "<h2>Verificación de funciones requeridas</h2>";
    
    $funciones = [
        'agregar_notificacion',
        'mostrar_notificaciones'
    ];
    
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Función</th><th>Estado</th></tr>";
    
    foreach ($funciones as $funcion) {
        $existe = function_exists($funcion);
        echo "<tr>";
        echo "<td>$funcion</td>";
        echo "<td style='color: " . ($existe ? "green" : "red") . ";'>" . ($existe ? "Existe" : "No existe") . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    // Intentar incluir los archivos requeridos
    echo "<h2>Intentando incluir archivos requeridos</h2>";
    
    try {
        echo "<p>Intentando incluir auth.php...</p>";
        require_once 'auth.php';
        echo "<p style='color: green;'>auth.php incluido correctamente.</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>Error al incluir auth.php: " . $e->getMessage() . "</p>";
    }
    
    try {
        echo "<p>Intentando incluir notificaciones.php...</p>";
        require_once 'notificaciones.php';
        echo "<p style='color: green;'>notificaciones.php incluido correctamente.</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>Error al incluir notificaciones.php: " . $e->getMessage() . "</p>";
    }
    
    // Crear las tablas si no existen
    echo "<h2>Creando tablas faltantes</h2>";
    
    if (!in_array('checkboxes_personalizados', $tablas)) {
        echo "<p>Creando tabla 'checkboxes_personalizados'...</p>";
        
        try {
            $pdo->exec("CREATE TABLE checkboxes_personalizados (
                id INT AUTO_INCREMENT PRIMARY KEY,
                texto VARCHAR(255) NOT NULL,
                descripcion TEXT,
                activo BOOLEAN DEFAULT TRUE,
                orden INT NOT NULL DEFAULT 0,
                fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                fecha_modificacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            
            echo "<p style='color: green;'>Tabla 'checkboxes_personalizados' creada correctamente.</p>";
            
            // Insertar algunos checkboxes de ejemplo
            $pdo->exec("INSERT INTO checkboxes_personalizados (texto, descripcion, activo, orden) VALUES 
            ('Acepto la política de privacidad', 'El cliente acepta nuestra política de privacidad y tratamiento de datos', TRUE, 1),
            ('Deseo recibir ofertas y promociones', 'El cliente desea recibir información sobre ofertas y promociones', TRUE, 2)");
            
            echo "<p style='color: green;'>Checkboxes de ejemplo insertados correctamente.</p>";
        } catch (Exception $e) {
            echo "<p style='color: red;'>Error al crear tabla 'checkboxes_personalizados': " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p>La tabla 'checkboxes_personalizados' ya existe.</p>";
    }
    
    if (!in_array('reservas_checkboxes', $tablas)) {
        echo "<p>Creando tabla 'reservas_checkboxes'...</p>";
        
        try {
            $pdo->exec("CREATE TABLE reservas_checkboxes (
                id INT AUTO_INCREMENT PRIMARY KEY,
                reserva_id INT NOT NULL,
                checkbox_id INT NOT NULL,
                valor BOOLEAN DEFAULT FALSE,
                FOREIGN KEY (reserva_id) REFERENCES reservas(id) ON DELETE CASCADE,
                FOREIGN KEY (checkbox_id) REFERENCES checkboxes_personalizados(id) ON DELETE CASCADE,
                UNIQUE(reserva_id, checkbox_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            
            echo "<p style='color: green;'>Tabla 'reservas_checkboxes' creada correctamente.</p>";
        } catch (Exception $e) {
            echo "<p style='color: red;'>Error al crear tabla 'reservas_checkboxes': " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p>La tabla 'reservas_checkboxes' ya existe.</p>";
    }
    
    // Verificar el contenido del archivo checkboxes_personalizados.php
    echo "<h2>Contenido de checkboxes_personalizados.php</h2>";
    
    $archivo = __DIR__ . '/checkboxes_personalizados.php';
    if (file_exists($archivo)) {
        echo "<p>El archivo existe. Mostrando las primeras 50 líneas:</p>";
        
        $contenido = file_get_contents($archivo);
        $lineas = explode("\n", $contenido);
        $primeras_lineas = array_slice($lineas, 0, 50);
        
        echo "<pre style='background-color: #f8f8f8; padding: 10px; border: 1px solid #ccc;'>";
        foreach ($primeras_lineas as $i => $linea) {
            echo ($i + 1) . ": " . htmlspecialchars($linea) . "\n";
        }
        echo "</pre>";
    } else {
        echo "<p style='color: red;'>El archivo no existe.</p>";
    }
    
    echo "<h2>Verificación completada</h2>";
    echo "<p>Intenta acceder nuevamente a la página de checkboxes personalizados: <a href='checkboxes_personalizados.php'>checkboxes_personalizados.php</a></p>";
    
} catch (Exception $e) {
    echo "<h2 style='color:red;'>Error detectado</h2>";
    echo "<p><strong>Mensaje:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Archivo:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Línea:</strong> " . $e->getLine() . "</p>";
    echo "<p><strong>Traza:</strong></p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>
