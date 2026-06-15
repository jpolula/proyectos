<?php
// Página de diagnóstico para identificar el problema con fecha_creacion
// Esta página muestra información detallada sobre la estructura de la tabla,
// las consultas SQL y los resultados para ayudar a identificar el error

// Configuración de la conexión a la base de datos
$host = 'localhost';
$db = 'restaurante_reservas';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

// Función para mostrar resultados de consultas SQL
function mostrarResultado($titulo, $sql, $params = [], $pdo) {
    echo "<div style='margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px;'>";
    echo "<h3>$titulo</h3>";
    echo "<p><strong>SQL:</strong> <code>" . htmlspecialchars($sql) . "</code></p>";
    
    if (!empty($params)) {
        echo "<p><strong>Parámetros:</strong> <pre>" . print_r($params, true) . "</pre></p>";
    }
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        if (stripos($sql, 'SELECT') === 0) {
            $resultados = $stmt->fetchAll();
            echo "<p><strong>Número de resultados:</strong> " . count($resultados) . "</p>";
            
            if (!empty($resultados)) {
                echo "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse;'>";
                
                // Encabezados de la tabla
                echo "<tr style='background-color: #f2f2f2;'>";
                foreach (array_keys($resultados[0]) as $columna) {
                    echo "<th>" . htmlspecialchars($columna) . "</th>";
                }
                echo "</tr>";
                
                // Filas de datos
                foreach ($resultados as $fila) {
                    echo "<tr>";
                    foreach ($fila as $valor) {
                        echo "<td>" . (is_null($valor) ? "<em>NULL</em>" : htmlspecialchars($valor)) . "</td>";
                    }
                    echo "</tr>";
                }
                
                echo "</table>";
            }
        } else {
            $filas_afectadas = $stmt->rowCount();
            echo "<p><strong>Filas afectadas:</strong> $filas_afectadas</p>";
        }
        
        echo "<p style='color: green;'>✓ Consulta ejecutada correctamente</p>";
    } catch (PDOException $e) {
        echo "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    
    echo "</div>";
}

// Función para mostrar la estructura de una tabla
function mostrarEstructuraTabla($tabla, $pdo) {
    echo "<div style='margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px;'>";
    echo "<h3>Estructura de la tabla '$tabla'</h3>";
    
    try {
        $stmt = $pdo->query("DESCRIBE $tabla");
        $columnas = $stmt->fetchAll();
        
        echo "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse;'>";
        echo "<tr style='background-color: #f2f2f2;'><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Clave</th><th>Predeterminado</th><th>Extra</th></tr>";
        
        foreach ($columnas as $columna) {
            echo "<tr>";
            foreach ($columna as $valor) {
                echo "<td>" . (is_null($valor) ? "<em>NULL</em>" : htmlspecialchars($valor)) . "</td>";
            }
            echo "</tr>";
        }
        
        echo "</table>";
    } catch (PDOException $e) {
        echo "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    
    echo "</div>";
}

// Función para ejecutar una consulta SQL directamente
function ejecutarSQL($sql, $pdo) {
    try {
        $pdo->exec($sql);
        return true;
    } catch (PDOException $e) {
        echo "<p style='color: red;'>Error al ejecutar SQL: " . htmlspecialchars($e->getMessage()) . "</p>";
        return false;
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnóstico de fecha_creacion</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        h1, h2, h3 {
            color: #2c5282;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .section {
            margin-bottom: 30px;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 5px;
        }
        .actions {
            margin: 20px 0;
        }
        button, .button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            margin-right: 10px;
            text-decoration: none;
            display: inline-block;
        }
        button:hover, .button:hover {
            background-color: #45a049;
        }
        .button-warning {
            background-color: #f44336;
        }
        .button-warning:hover {
            background-color: #d32f2f;
        }
        code {
            background-color: #f5f5f5;
            padding: 2px 5px;
            border-radius: 3px;
            font-family: monospace;
        }
        pre {
            background-color: #f5f5f5;
            padding: 10px;
            border-radius: 5px;
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            padding: 8px;
            text-align: left;
            border: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
        }
        .success {
            color: green;
        }
        .error {
            color: red;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Diagnóstico de fecha_creacion</h1>
        
        <div class="section">
            <h2>Información del sistema</h2>
            <p><strong>PHP Version:</strong> <?php echo phpversion(); ?></p>
            <p><strong>PDO Drivers:</strong> <?php echo implode(', ', PDO::getAvailableDrivers()); ?></p>
            <p><strong>Fecha y hora actual:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
        </div>
        
        <?php
        try {
            // Conectar a la base de datos
            $pdo = new PDO($dsn, $user, $pass, $options);
            echo "<div class='section success'><p>✓ Conexión a la base de datos establecida correctamente</p></div>";
            
            // Mostrar la estructura de la tabla reservas
            mostrarEstructuraTabla('reservas', $pdo);
            
            // Verificar si la columna fecha_creacion existe
            $sql = "SHOW COLUMNS FROM reservas LIKE 'fecha_creacion'";
            mostrarResultado("Verificar si existe la columna fecha_creacion", $sql, [], $pdo);
            
            // Mostrar las últimas 5 reservas con su fecha_creacion
            $sql = "SELECT id, cliente_id, fecha, hora, zona, estado, fecha_creacion FROM reservas ORDER BY id DESC LIMIT 5";
            mostrarResultado("Últimas 5 reservas", $sql, [], $pdo);
            
            // Contar reservas sin fecha_creacion
            $sql = "SELECT COUNT(*) as total FROM reservas WHERE fecha_creacion IS NULL";
            mostrarResultado("Contar reservas sin fecha_creacion", $sql, [], $pdo);
            
            // Verificar la consulta que se usa en el panel de administración
            $sql = "
                SELECT r.id, r.fecha, r.hora, r.zona, r.estado, 
                       c.nombre, c.email, c.telefono, 
                       t.nombre AS turno_nombre,
                       DATE_FORMAT(r.fecha_creacion, '%d/%m/%Y %H:%i') AS fecha_hora_creacion
                FROM reservas r
                JOIN clientes c ON r.cliente_id = c.id
                JOIN turnos t ON r.turno_id = t.id
                ORDER BY r.id DESC
                LIMIT 5
            ";
            mostrarResultado("Consulta del panel de administración", $sql, [], $pdo);
            
            // Sección de acciones
            echo "<div class='section'>";
            echo "<h2>Acciones disponibles</h2>";
            
            if (isset($_GET['action'])) {
                switch ($_GET['action']) {
                    case 'add_column':
                        echo "<h3>Añadiendo columna fecha_creacion...</h3>";
                        if (ejecutarSQL("ALTER TABLE reservas ADD COLUMN fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP", $pdo)) {
                            echo "<p class='success'>✓ Columna añadida correctamente</p>";
                        }
                        break;
                    
                    case 'update_null':
                        echo "<h3>Actualizando registros sin fecha_creacion...</h3>";
                        if (ejecutarSQL("UPDATE reservas SET fecha_creacion = NOW() WHERE fecha_creacion IS NULL", $pdo)) {
                            echo "<p class='success'>✓ Registros actualizados correctamente</p>";
                        }
                        break;
                    
                    case 'modify_column':
                        echo "<h3>Modificando columna fecha_creacion...</h3>";
                        if (ejecutarSQL("ALTER TABLE reservas MODIFY COLUMN fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP", $pdo)) {
                            echo "<p class='success'>✓ Columna modificada correctamente</p>";
                        }
                        break;
                    
                    case 'test_insert':
                        echo "<h3>Probando inserción con fecha_creacion...</h3>";
                        
                        // Crear cliente de prueba
                        $pdo->exec("INSERT INTO clientes (nombre, email, telefono) VALUES ('Cliente Prueba', 'test@example.com', '123456789')");
                        $cliente_id = $pdo->lastInsertId();
                        
                        // Insertar reserva de prueba
                        $sql = "INSERT INTO reservas (cliente_id, fecha, zona, turno_id, hora, cantidad_personas, estado) 
                                VALUES (?, ?, ?, ?, ?, ?, ?)";
                        $params = [$cliente_id, '2025-12-31', 'dentro', 1, '14:00:00', 2, 'confirmada'];
                        
                        mostrarResultado("Inserción de prueba", $sql, $params, $pdo);
                        
                        // Obtener la reserva recién insertada
                        $reserva_id = $pdo->lastInsertId();
                        $sql = "SELECT id, cliente_id, fecha, hora, zona, estado, fecha_creacion FROM reservas WHERE id = ?";
                        mostrarResultado("Verificar reserva insertada", $sql, [$reserva_id], $pdo);
                        
                        // Limpiar datos de prueba
                        $pdo->exec("DELETE FROM reservas WHERE id = $reserva_id");
                        $pdo->exec("DELETE FROM clientes WHERE id = $cliente_id");
                        echo "<p>Datos de prueba eliminados.</p>";
                        break;
                }
            }
            
            echo "<div class='actions'>";
            echo "<a href='?action=add_column' class='button'>Añadir columna fecha_creacion</a>";
            echo "<a href='?action=update_null' class='button'>Actualizar registros NULL</a>";
            echo "<a href='?action=modify_column' class='button'>Modificar columna (DEFAULT CURRENT_TIMESTAMP)</a>";
            echo "<a href='?action=test_insert' class='button'>Probar inserción</a>";
            echo "</div>";
            
            echo "<a href='admin/reservas.php' class='button'>Volver al panel de administración</a>";
            echo "</div>";
            
        } catch (PDOException $e) {
            echo "<div class='section error'>";
            echo "<h2>Error de conexión</h2>";
            echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
            echo "</div>";
        }
        ?>
    </div>
</body>
</html>
