<?php
// Mostrar errores para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Actualización de la base de datos para personalización y capacidad</h1>";

try {
    // Conectar a la base de datos
    $pdo = new PDO('mysql:host=localhost;dbname=restaurante_reservas', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    echo "<p>Conexión a la base de datos: <strong style='color:green'>EXITOSA</strong></p>";
    
    // Verificar si las columnas de personalización existen
    $stmt = $pdo->query("SHOW COLUMNS FROM configuracion LIKE 'logo_path'");
    $logo_path_exists = $stmt->rowCount() > 0;
    
    $stmt = $pdo->query("SHOW COLUMNS FROM configuracion LIKE 'logo_url'");
    $logo_url_exists = $stmt->rowCount() > 0;
    
    $stmt = $pdo->query("SHOW COLUMNS FROM configuracion LIKE 'tipo_letra'");
    $tipo_letra_exists = $stmt->rowCount() > 0;
    
    // Verificar si las columnas de capacidad existen
    $stmt = $pdo->query("SHOW COLUMNS FROM configuracion LIKE 'capacidad_dentro_mediodia'");
    $capacidad_dentro_mediodia_exists = $stmt->rowCount() > 0;
    
    $stmt = $pdo->query("SHOW COLUMNS FROM configuracion LIKE 'capacidad_fuera_mediodia'");
    $capacidad_fuera_mediodia_exists = $stmt->rowCount() > 0;
    
    $stmt = $pdo->query("SHOW COLUMNS FROM configuracion LIKE 'capacidad_dentro_noche'");
    $capacidad_dentro_noche_exists = $stmt->rowCount() > 0;
    
    $stmt = $pdo->query("SHOW COLUMNS FROM configuracion LIKE 'capacidad_fuera_noche'");
    $capacidad_fuera_noche_exists = $stmt->rowCount() > 0;
    
    // Verificar si las columnas de configuración de correo existen
    $stmt = $pdo->query("SHOW COLUMNS FROM configuracion LIKE 'email_remitente'");
    $email_remitente_exists = $stmt->rowCount() > 0;
    
    $stmt = $pdo->query("SHOW COLUMNS FROM configuracion LIKE 'email_password'");
    $email_password_exists = $stmt->rowCount() > 0;
    
    $stmt = $pdo->query("SHOW COLUMNS FROM configuracion LIKE 'email_host'");
    $email_host_exists = $stmt->rowCount() > 0;
    
    $stmt = $pdo->query("SHOW COLUMNS FROM configuracion LIKE 'email_puerto'");
    $email_puerto_exists = $stmt->rowCount() > 0;
    
    $stmt = $pdo->query("SHOW COLUMNS FROM configuracion LIKE 'email_seguridad'");
    $email_seguridad_exists = $stmt->rowCount() > 0;
    
    $stmt = $pdo->query("SHOW COLUMNS FROM configuracion LIKE 'email_nombre_remitente'");
    $email_nombre_remitente_exists = $stmt->rowCount() > 0;
    
    // Verificar si la columna notificaciones_admin existe
    $stmt = $pdo->query("SHOW COLUMNS FROM configuracion LIKE 'notificaciones_admin'");
    $notificaciones_admin_exists = $stmt->rowCount() > 0;
    
    // Verificar si la columna url_redireccion_reserva existe
    $stmt = $pdo->query("SHOW COLUMNS FROM configuracion LIKE 'url_redireccion_reserva'");
    $url_redireccion_reserva_exists = $stmt->rowCount() > 0;
    
    // Actualizar la estructura de la tabla
    $alteraciones = [];
    
    // Columnas de personalización
    if (!$logo_path_exists && $logo_url_exists) {
        // Renombrar logo_url a logo_path
        $pdo->exec("ALTER TABLE configuracion CHANGE logo_url logo_path VARCHAR(255)");
        $alteraciones[] = "Columna 'logo_url' renombrada a 'logo_path'";
    } else if (!$logo_path_exists && !$logo_url_exists) {
        // Añadir logo_path si no existe
        $pdo->exec("ALTER TABLE configuracion ADD logo_path VARCHAR(255) AFTER notificar_admin");
        $alteraciones[] = "Columna 'logo_path' añadida";
    }
    
    if (!$tipo_letra_exists) {
        // Añadir tipo_letra si no existe
        $pdo->exec("ALTER TABLE configuracion ADD tipo_letra VARCHAR(255) DEFAULT 'system-ui, -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Helvetica Neue, Arial' AFTER color_secundario");
        $alteraciones[] = "Columna 'tipo_letra' añadida";
    }
    
    // Columnas de capacidad
    if (!$capacidad_dentro_mediodia_exists) {
        $pdo->exec("ALTER TABLE configuracion ADD capacidad_dentro_mediodia INT NOT NULL DEFAULT 30 AFTER notificar_admin");
        $alteraciones[] = "Columna 'capacidad_dentro_mediodia' añadida";
    }
    
    if (!$capacidad_fuera_mediodia_exists) {
        $pdo->exec("ALTER TABLE configuracion ADD capacidad_fuera_mediodia INT NOT NULL DEFAULT 20 AFTER capacidad_dentro_mediodia");
        $alteraciones[] = "Columna 'capacidad_fuera_mediodia' añadida";
    }
    
    if (!$capacidad_dentro_noche_exists) {
        $pdo->exec("ALTER TABLE configuracion ADD capacidad_dentro_noche INT NOT NULL DEFAULT 35 AFTER capacidad_fuera_mediodia");
        $alteraciones[] = "Columna 'capacidad_dentro_noche' añadida";
    }
    
    if (!$capacidad_fuera_noche_exists) {
        $pdo->exec("ALTER TABLE configuracion ADD capacidad_fuera_noche INT NOT NULL DEFAULT 25 AFTER capacidad_dentro_noche");
        $alteraciones[] = "Columna 'capacidad_fuera_noche' añadida";
    }
    
    // Columna notificaciones_admin
    if (!$notificaciones_admin_exists) {
        $pdo->exec("ALTER TABLE configuracion ADD notificaciones_admin ENUM('todas', 'pendientes', 'ninguna') DEFAULT 'pendientes' AFTER notificar_admin");
        $alteraciones[] = "Columna 'notificaciones_admin' añadida";
    }
    
    // Columnas de configuración de correo
    if (!$email_remitente_exists) {
        $pdo->exec("ALTER TABLE configuracion ADD email_remitente VARCHAR(100) AFTER capacidad_fuera_noche");
        $alteraciones[] = "Columna 'email_remitente' añadida";
    }
    
    if (!$email_password_exists) {
        $pdo->exec("ALTER TABLE configuracion ADD email_password VARCHAR(255) AFTER email_remitente");
        $alteraciones[] = "Columna 'email_password' añadida";
    }
    
    if (!$email_host_exists) {
        $pdo->exec("ALTER TABLE configuracion ADD email_host VARCHAR(100) DEFAULT 'smtp.gmail.com' AFTER email_password");
        $alteraciones[] = "Columna 'email_host' añadida";
    }
    
    if (!$email_puerto_exists) {
        $pdo->exec("ALTER TABLE configuracion ADD email_puerto INT DEFAULT 587 AFTER email_host");
        $alteraciones[] = "Columna 'email_puerto' añadida";
    }
    
    if (!$email_seguridad_exists) {
        $pdo->exec("ALTER TABLE configuracion ADD email_seguridad ENUM('tls', 'ssl') DEFAULT 'tls' AFTER email_puerto");
        $alteraciones[] = "Columna 'email_seguridad' añadida";
    }
    
    if (!$email_nombre_remitente_exists) {
        $pdo->exec("ALTER TABLE configuracion ADD email_nombre_remitente VARCHAR(100) AFTER email_seguridad");
        $alteraciones[] = "Columna 'email_nombre_remitente' añadida";
    }
    
    // Columna url_redireccion_reserva
    if (!$url_redireccion_reserva_exists) {
        $pdo->exec("ALTER TABLE configuracion ADD url_redireccion_reserva VARCHAR(255) DEFAULT 'reserva_exitosa.php' COMMENT 'URL a la que redirigir tras una reserva exitosa' AFTER tipo_letra");
        $alteraciones[] = "Columna 'url_redireccion_reserva' añadida";
    }
    
    // Añadir valores por defecto a las columnas de título y subtítulo si no los tienen
    $pdo->exec("ALTER TABLE configuracion MODIFY titulo_principal VARCHAR(100) DEFAULT 'Sistema de Reservas'");
    $pdo->exec("ALTER TABLE configuracion MODIFY subtitulo VARCHAR(200) DEFAULT 'Reserve su mesa fácilmente'");
    $alteraciones[] = "Valores por defecto añadidos a 'titulo_principal' y 'subtitulo'";
    
    // Verificar si hay datos en la tabla de configuración
    $stmt = $pdo->query("SELECT COUNT(*) FROM configuracion");
    $count = $stmt->fetchColumn();
    
    if ($count == 0) {
        // Insertar configuración por defecto si no hay datos
        $pdo->exec("INSERT INTO configuracion (id, max_personas_sin_aprobacion, email_activo, notificar_admin, notificaciones_admin, capacidad_dentro_mediodia, capacidad_fuera_mediodia, capacidad_dentro_noche, capacidad_fuera_noche, email_remitente, email_password, email_host, email_puerto, email_seguridad, email_nombre_remitente, logo_path, titulo_principal, subtitulo, color_principal, color_secundario, tipo_letra, url_redireccion_reserva) 
                   VALUES (1, 4, FALSE, 'pendientes', 'pendientes', 30, 20, 35, 25, '', '', 'smtp.gmail.com', 587, 'tls', '', '', 'Sistema de Reservas', 'Reserve su mesa fácilmente', '#4A5568', '#38B2AC', 'system-ui, -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Helvetica Neue, Arial', 'reserva_exitosa.php')");
        $alteraciones[] = "Configuración por defecto insertada";
    } else {
        // Actualizar los valores de capacidad si no existen
        $stmt = $pdo->query("SELECT capacidad_dentro_mediodia, capacidad_fuera_mediodia, capacidad_dentro_noche, capacidad_fuera_noche FROM configuracion WHERE id = 1");
        $config = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($config['capacidad_dentro_mediodia'] === null) {
            $pdo->exec("UPDATE configuracion SET capacidad_dentro_mediodia = 30 WHERE id = 1");
            $alteraciones[] = "Valor por defecto añadido a 'capacidad_dentro_mediodia'";
        }
        
        if ($config['capacidad_fuera_mediodia'] === null) {
            $pdo->exec("UPDATE configuracion SET capacidad_fuera_mediodia = 20 WHERE id = 1");
            $alteraciones[] = "Valor por defecto añadido a 'capacidad_fuera_mediodia'";
        }
        
        if ($config['capacidad_dentro_noche'] === null) {
            $pdo->exec("UPDATE configuracion SET capacidad_dentro_noche = 35 WHERE id = 1");
            $alteraciones[] = "Valor por defecto añadido a 'capacidad_dentro_noche'";
        }
        
        if ($config['capacidad_fuera_noche'] === null) {
            $pdo->exec("UPDATE configuracion SET capacidad_fuera_noche = 25 WHERE id = 1");
            $alteraciones[] = "Valor por defecto añadido a 'capacidad_fuera_noche'";
        }
    }
    
    // Crear directorio de uploads si no existe
    $upload_dir = __DIR__ . '/uploads/';
    if (!file_exists($upload_dir)) {
        if (mkdir($upload_dir, 0777, true)) {
            echo "<p>Directorio de uploads creado: <strong style='color:green'>OK</strong></p>";
        } else {
            echo "<p>Error al crear directorio de uploads: <strong style='color:red'>ERROR</strong></p>";
        }
    } else {
        echo "<p>Directorio de uploads: <strong style='color:green'>YA EXISTE</strong></p>";
    }
    
    // Mostrar las alteraciones realizadas
    if (count($alteraciones) > 0) {
        echo "<h2>Alteraciones realizadas:</h2>";
        echo "<ul>";
        foreach ($alteraciones as $alteracion) {
            echo "<li><strong style='color:green'>✓</strong> " . htmlspecialchars($alteracion) . "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>No se realizaron alteraciones en la estructura de la base de datos.</p>";
    }
    
    echo "<h2>Estado actual de la tabla de configuración:</h2>";
    
    // Mostrar la estructura actual de la tabla
    $stmt = $pdo->query("DESCRIBE configuracion");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' cellpadding='5' cellspacing='0'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Clave</th><th>Valor por defecto</th></tr>";
    
    foreach ($columns as $column) {
        echo "<tr>";
        foreach ($column as $key => $value) {
            echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
        }
        echo "</tr>";
    }
    
    echo "</table>";
    
    // Mostrar los datos actuales
    $stmt = $pdo->query("SELECT * FROM configuracion");
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($data) > 0) {
        echo "<h2>Datos actuales:</h2>";
        
        echo "<table border='1' cellpadding='5' cellspacing='0'>";
        
        // Encabezados de la tabla
        echo "<tr>";
        foreach (array_keys($data[0]) as $header) {
            echo "<th>" . htmlspecialchars($header) . "</th>";
        }
        echo "</tr>";
        
        // Datos
        foreach ($data as $row) {
            echo "<tr>";
            foreach ($row as $value) {
                echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
            }
            echo "</tr>";
        }
        
        echo "</table>";
    }
    
    echo "<p>La base de datos ha sido actualizada correctamente para soportar todas las funcionalidades de personalización.</p>";
    
} catch (PDOException $e) {
    echo "<p>Error: <strong style='color:red'>" . htmlspecialchars($e->getMessage()) . "</strong></p>";
}

// Añadir enlaces para volver
echo "<div style='margin-top: 20px;'>";
echo "<a href='admin/personalizacion.php' style='display: inline-block; margin-right: 10px; padding: 10px 15px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 4px;'>Ir a Personalización</a>";
echo "<a href='admin/login.php' style='display: inline-block; padding: 10px 15px; background-color: #2196F3; color: white; text-decoration: none; border-radius: 4px;'>Ir a Login</a>";
echo "</div>";
?>
