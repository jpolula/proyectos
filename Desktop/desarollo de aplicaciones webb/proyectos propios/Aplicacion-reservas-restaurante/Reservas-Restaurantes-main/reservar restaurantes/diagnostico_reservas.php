<?php
// Script de diagnóstico para verificar problemas con la inserción de reservas
// Habilitar la visualización de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Diagnóstico de Reservas</h1>";

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

try {
    echo "<p>Conectando a la base de datos...</p>";
    $pdo = new PDO($dsn, $user, $pass, $options);
    echo "<p style='color: green;'>Conexión exitosa a la base de datos.</p>";
    
    // Verificar estructura de la tabla reservas
    echo "<h2>Estructura de la tabla 'reservas':</h2>";
    $stmt = $pdo->query("DESCRIBE reservas");
    $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1'>";
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
    
    // Verificar si hay restricciones o triggers en la tabla reservas
    echo "<h2>Restricciones y Triggers:</h2>";
    
    try {
        $stmt = $pdo->query("SHOW CREATE TABLE reservas");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<pre>" . htmlspecialchars($result['Create Table']) . "</pre>";
    } catch (PDOException $e) {
        echo "<p style='color: red;'>Error al obtener la estructura completa: " . $e->getMessage() . "</p>";
    }
    
    // Intentar una inserción de prueba
    echo "<h2>Prueba de inserción directa:</h2>";
    
    try {
        // Primero insertar un cliente de prueba
        $stmt = $pdo->prepare("
            INSERT INTO clientes (nombre, email, telefono) 
            VALUES (?, ?, ?)
        ");
        
        $nombre_prueba = "Cliente Diagnóstico " . date('YmdHis');
        $email_prueba = "diagnostico" . date('YmdHis') . "@ejemplo.com";
        $telefono_prueba = "123456789";
        
        $stmt->execute([$nombre_prueba, $email_prueba, $telefono_prueba]);
        $cliente_id = $pdo->lastInsertId();
        
        echo "<p>Cliente de prueba insertado con ID: <strong>$cliente_id</strong></p>";
        
        // Ahora insertar una reserva de prueba directamente
        $sql = "
            INSERT INTO reservas (
                cliente_id, fecha, zona, turno_id, 
                hora, cantidad_personas, observaciones, 
                necesidades_especiales, tiene_alergenos, estado
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ";
        
        echo "<p>SQL a ejecutar:</p>";
        echo "<pre>" . htmlspecialchars($sql) . "</pre>";
        
        $stmt = $pdo->prepare($sql);
        
        $fecha_prueba = date('Y-m-d');
        $zona_prueba = 'dentro';
        $turno_id_prueba = 1; // Asumiendo que existe un turno con ID 1
        $hora_prueba = '13:00';
        $cantidad_personas_prueba = 2;
        $observaciones_prueba = 'Observaciones de diagnóstico';
        $necesidades_especiales_prueba = 'Necesidades especiales de diagnóstico';
        $tiene_alergenos_prueba = 1;
        $estado_prueba = 'confirmada';
        
        // Guardamos la información de alérgenos en observaciones ya que no hay columna 'alergenos'
        $alergenos_texto = 'Alérgenos de diagnóstico';
        if ($tiene_alergenos_prueba) {
            $observaciones_prueba .= "\nAlérgenos: $alergenos_texto";
        }
        
        $params = [
            $cliente_id, $fecha_prueba, $zona_prueba, $turno_id_prueba,
            $hora_prueba, $cantidad_personas_prueba, $observaciones_prueba,
            $necesidades_especiales_prueba, $tiene_alergenos_prueba, $estado_prueba
        ];
        
        echo "<p>Parámetros a usar:</p>";
        echo "<pre>" . print_r($params, true) . "</pre>";
        
        $resultado = $stmt->execute($params);
        
        if ($resultado) {
            $reserva_id = $pdo->lastInsertId();
            echo "<p style='color: green;'>Reserva de prueba insertada con ID: <strong>$reserva_id</strong></p>";
            
            // Verificar si la reserva se insertó correctamente
            $stmt = $pdo->prepare("SELECT * FROM reservas WHERE id = ?");
            $stmt->execute([$reserva_id]);
            $reserva = $stmt->fetch();
            
            if ($reserva) {
                echo "<p style='color: green;'>La reserva se puede recuperar correctamente de la base de datos.</p>";
                
                echo "<h3>Datos de la reserva insertada:</h3>";
                echo "<table border='1'>";
                foreach ($reserva as $campo => $valor) {
                    echo "<tr>";
                    echo "<td><strong>$campo</strong></td>";
                    echo "<td>" . htmlspecialchars($valor) . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<p style='color: red;'>No se puede recuperar la reserva de la base de datos.</p>";
            }
        } else {
            echo "<p style='color: red;'>La inserción falló sin lanzar excepción.</p>";
        }
    } catch (PDOException $e) {
        echo "<p style='color: red;'>Error PDO al insertar datos de prueba: " . $e->getMessage() . "</p>";
        echo "<p>Código de error: " . $e->getCode() . "</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>Error general al insertar datos de prueba: " . $e->getMessage() . "</p>";
    }
    
    // Verificar registros recientes
    echo "<h2>Últimas 10 reservas en la base de datos:</h2>";
    
    $stmt = $pdo->query("SELECT * FROM reservas ORDER BY id DESC LIMIT 10");
    $reservas = $stmt->fetchAll();
    
    if (count($reservas) > 0) {
        echo "<table border='1'>";
        echo "<tr>";
        foreach (array_keys($reservas[0]) as $columna) {
            echo "<th>$columna</th>";
        }
        echo "</tr>";
        
        foreach ($reservas as $reserva) {
            echo "<tr>";
            foreach ($reserva as $valor) {
                echo "<td>" . htmlspecialchars($valor ?? 'NULL') . "</td>";
            }
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p>No hay reservas en la base de datos.</p>";
    }
    
    // Verificar logs de errores de PHP
    echo "<h2>Últimas entradas del log de errores de PHP:</h2>";
    $error_log = ini_get('error_log');
    
    if ($error_log && file_exists($error_log) && is_readable($error_log)) {
        $log_content = file_get_contents($error_log);
        $lines = explode("\n", $log_content);
        $last_lines = array_slice($lines, -50); // Últimas 50 líneas
        
        echo "<pre>";
        foreach ($last_lines as $line) {
            if (strpos($line, 'reserva') !== false || strpos($line, 'Reserva') !== false) {
                echo "<span style='color: red;'>" . htmlspecialchars($line) . "</span>\n";
            } else {
                echo htmlspecialchars($line) . "\n";
            }
        }
        echo "</pre>";
    } else {
        echo "<p>No se puede acceder al archivo de log de PHP o no está configurado.</p>";
    }
    
    // Verificar permisos de usuario en la base de datos
    echo "<h2>Permisos del usuario en la base de datos:</h2>";
    try {
        $stmt = $pdo->query("SHOW GRANTS FOR CURRENT_USER()");
        $permisos = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo "<ul>";
        foreach ($permisos as $permiso) {
            echo "<li>$permiso</li>";
        }
        echo "</ul>";
    } catch (PDOException $e) {
        echo "<p style='color: red;'>Error al obtener permisos: " . $e->getMessage() . "</p>";
    }
    
    // Recomendaciones
    echo "<h2>Recomendaciones:</h2>";
    echo "<ol>";
    echo "<li>Verifica que los campos en el formulario de reserva coincidan exactamente con los campos en la tabla 'reservas'.</li>";
    echo "<li>Asegúrate de que todos los campos requeridos estén siendo enviados correctamente desde el formulario.</li>";
    echo "<li>Revisa los logs de error de PHP para identificar cualquier error específico durante la inserción.</li>";
    echo "<li>Verifica que no haya restricciones o triggers en la tabla que puedan estar bloqueando la inserción.</li>";
    echo "<li>Asegúrate de que el usuario de la base de datos tenga permisos suficientes para insertar registros.</li>";
    echo "<li>Revisa el código en confirmar_reserva.php para asegurarte de que la lógica de inserción es correcta.</li>";
    echo "</ol>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Error de conexión a la base de datos: " . $e->getMessage() . "</p>";
}
?>
