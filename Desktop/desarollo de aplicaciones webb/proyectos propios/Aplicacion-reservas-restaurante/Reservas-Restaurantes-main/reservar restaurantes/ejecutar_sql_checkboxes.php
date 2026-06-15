<?php
// Activar la visualización de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Ejecutando script SQL para crear tablas de checkboxes personalizados</h1>";

try {
    // Configuración de la base de datos
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

    // Conectar a la base de datos
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    echo "<p>Conexión a la base de datos establecida correctamente.</p>";
    
    // Leer el contenido del archivo SQL
    $sql_file = file_get_contents(__DIR__ . '/add_custom_checkboxes.sql');
    
    // Dividir el archivo SQL en instrucciones individuales
    $sql_statements = preg_split('/;\s*$/m', $sql_file);
    
    // Ejecutar cada instrucción SQL
    foreach ($sql_statements as $statement) {
        $statement = trim($statement);
        if (!empty($statement)) {
            // Eliminar la línea USE si existe
            if (!preg_match('/^USE /i', $statement)) {
                echo "<p>Ejecutando: " . htmlspecialchars(substr($statement, 0, 100)) . "...</p>";
                $pdo->exec($statement);
                echo "<p style='color: green;'>✓ Ejecutado correctamente.</p>";
            }
        }
    }
    
    // Verificar si las tablas se crearon correctamente
    echo "<h2>Verificando tablas creadas</h2>";
    
    $tablas = [
        'checkboxes_personalizados',
        'reservas_checkboxes'
    ];
    
    foreach ($tablas as $tabla) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$tabla'");
        if ($stmt->rowCount() > 0) {
            echo "<p>Tabla '$tabla' creada correctamente.</p>";
            
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
            
            // Si es la tabla de checkboxes, mostrar los registros
            if ($tabla === 'checkboxes_personalizados') {
                $stmt = $pdo->query("SELECT * FROM $tabla");
                $registros = $stmt->fetchAll();
                
                echo "<details>";
                echo "<summary>Registros en la tabla '$tabla'</summary>";
                
                if (count($registros) > 0) {
                    echo "<table border='1' cellpadding='5'>";
                    echo "<tr>";
                    foreach (array_keys($registros[0]) as $columna) {
                        echo "<th>$columna</th>";
                    }
                    echo "</tr>";
                    
                    foreach ($registros as $registro) {
                        echo "<tr>";
                        foreach ($registro as $valor) {
                            echo "<td>" . htmlspecialchars($valor) . "</td>";
                        }
                        echo "</tr>";
                    }
                    
                    echo "</table>";
                } else {
                    echo "<p>No hay registros en la tabla.</p>";
                }
                
                echo "</details>";
            }
        } else {
            echo "<p style='color: red;'>Error: La tabla '$tabla' no se ha creado correctamente.</p>";
        }
    }
    
    echo "<h2>Proceso completado</h2>";
    echo "<p>Las tablas de checkboxes personalizados se han creado correctamente.</p>";
    echo "<p><a href='admin/checkboxes_simple.php'>Ir a la página de gestión de checkboxes personalizados</a></p>";
    
} catch (PDOException $e) {
    echo "<h2 style='color: red;'>Error en la base de datos</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>
