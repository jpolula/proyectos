<?php
// Script para verificar la inserción de reservas

// Habilitar la visualización de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Verificación de Inserción de Reservas</h1>";

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
    // Conectar a la base de datos
    $pdo = new PDO($dsn, $user, $pass, $options);
    echo "<p style='color: green;'>Conexión a la base de datos establecida correctamente.</p>";
    
    // Verificar la estructura de la tabla reservas
    $stmt = $pdo->query("DESCRIBE reservas");
    $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>Estructura de la tabla 'reservas'</h2>";
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
    
    // Verificar si la columna alergenos existe
    $columnas_nombres = array_column($columnas, 'Field');
    if (!in_array('alergenos', $columnas_nombres)) {
        echo "<p style='color: red;'>La columna 'alergenos' no existe en la tabla 'reservas'. Esto puede causar errores al insertar reservas.</p>";
        
        // Intentar añadir la columna
        echo "<p>Intentando añadir la columna 'alergenos'...</p>";
        try {
            $pdo->exec("ALTER TABLE reservas ADD COLUMN alergenos TEXT AFTER tiene_alergenos");
            echo "<p style='color: green;'>Columna 'alergenos' añadida correctamente.</p>";
        } catch (PDOException $e) {
            echo "<p style='color: red;'>Error al añadir la columna 'alergenos': " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p style='color: green;'>La columna 'alergenos' existe en la tabla 'reservas'.</p>";
    }
    
    // Intentar insertar una reserva de prueba
    echo "<h2>Prueba de inserción de reserva</h2>";
    
    // Primero insertar un cliente de prueba
    $stmt = $pdo->prepare("
        INSERT INTO clientes (nombre, email, telefono) 
        VALUES (?, ?, ?)
    ");
    
    $nombre_prueba = "Cliente de Prueba " . date('YmdHis');
    $email_prueba = "prueba" . date('YmdHis') . "@ejemplo.com";
    $telefono_prueba = "123456789";
    
    $stmt->execute([$nombre_prueba, $email_prueba, $telefono_prueba]);
    $cliente_id = $pdo->lastInsertId();
    
    echo "<p>Cliente de prueba insertado con ID: $cliente_id</p>";
    
    // Verificar si hay turnos disponibles
    $stmt = $pdo->query("SELECT id FROM turnos LIMIT 1");
    $turno = $stmt->fetch();
    
    if ($turno) {
        $turno_id_prueba = $turno['id'];
        
        // Ahora insertar una reserva de prueba
        $stmt = $pdo->prepare("
            INSERT INTO reservas (
                cliente_id, fecha, zona, turno_id, 
                hora, cantidad_personas, observaciones, 
                necesidades_especiales, tiene_alergenos, alergenos, estado
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $fecha_prueba = date('Y-m-d');
        $zona_prueba = 'dentro';
        $hora_prueba = '13:00';
        $cantidad_personas_prueba = 2;
        $observaciones_prueba = 'Observaciones de prueba';
        $necesidades_especiales_prueba = 'Necesidades especiales de prueba';
        $tiene_alergenos_prueba = 1;
        $alergenos_prueba = 'Alérgenos de prueba';
        $estado_prueba = 'confirmada';
        
        try {
            $stmt->execute([
                $cliente_id, $fecha_prueba, $zona_prueba, $turno_id_prueba,
                $hora_prueba, $cantidad_personas_prueba, $observaciones_prueba,
                $necesidades_especiales_prueba, $tiene_alergenos_prueba, $alergenos_prueba, $estado_prueba
            ]);
            
            $reserva_id = $pdo->lastInsertId();
            echo "<p style='color: green;'>Reserva de prueba insertada con ID: $reserva_id</p>";
            
            // Verificar si la reserva se insertó correctamente
            $stmt = $pdo->prepare("SELECT * FROM reservas WHERE id = ?");
            $stmt->execute([$reserva_id]);
            $reserva = $stmt->fetch();
            
            if ($reserva) {
                echo "<p style='color: green;'>La reserva de prueba se puede recuperar correctamente.</p>";
                
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
                echo "<p style='color: red;'>No se puede recuperar la reserva de prueba.</p>";
            }
        } catch (PDOException $e) {
            echo "<p style='color: red;'>Error al insertar la reserva de prueba: " . $e->getMessage() . "</p>";
            
            // Mostrar la consulta SQL que falló
            echo "<p>Consulta SQL que falló:</p>";
            echo "<pre>
                INSERT INTO reservas (
                    cliente_id, fecha, zona, turno_id, 
                    hora, cantidad_personas, observaciones, 
                    necesidades_especiales, tiene_alergenos, alergenos, estado
                ) VALUES (
                    $cliente_id, '$fecha_prueba', '$zona_prueba', $turno_id_prueba,
                    '$hora_prueba', $cantidad_personas_prueba, '$observaciones_prueba',
                    '$necesidades_especiales_prueba', $tiene_alergenos_prueba, '$alergenos_prueba', '$estado_prueba'
                )
            </pre>";
        }
    } else {
        echo "<p style='color: red;'>No hay turnos disponibles en la base de datos. Esto es necesario para insertar una reserva.</p>";
    }
    
    // Verificar si hay días disponibles
    $stmt = $pdo->query("SELECT COUNT(*) FROM dias_disponibles WHERE disponible = 1");
    $count = $stmt->fetchColumn();
    
    echo "<h2>Verificación de días disponibles</h2>";
    echo "<p>Número de días disponibles: $count</p>";
    
    if ($count == 0) {
        echo "<p style='color: orange;'>No hay días disponibles para reservas. Esto podría ser un problema.</p>";
        
        // Intentar insertar algunos días disponibles
        echo "<p>Intentando insertar días disponibles de prueba...</p>";
        
        try {
            // Insertar días disponibles para los próximos 7 días
            $fecha_actual = new DateTime();
            for ($i = 0; $i < 7; $i++) {
                $fecha = $fecha_actual->format('Y-m-d');
                
                // Insertar para ambos turnos y zonas
                foreach ([1, 2] as $turno_id) {
                    foreach (['dentro', 'fuera'] as $zona) {
                        $stmt = $pdo->prepare("
                            INSERT INTO dias_disponibles (fecha, turno_id, zona, disponible)
                            VALUES (?, ?, ?, 1)
                            ON DUPLICATE KEY UPDATE disponible = 1
                        ");
                        $stmt->execute([$fecha, $turno_id, $zona]);
                    }
                }
                
                $fecha_actual->modify('+1 day');
            }
            
            echo "<p style='color: green;'>Días disponibles insertados correctamente.</p>";
        } catch (PDOException $e) {
            echo "<p style='color: red;'>Error al insertar días disponibles: " . $e->getMessage() . "</p>";
        }
    }
    
    echo "<h2>Conclusión</h2>";
    echo "<p>Si todos los pasos anteriores se han completado correctamente, la inserción de reservas debería funcionar. Si sigues teniendo problemas, revisa los logs de error de PHP y MySQL para obtener más información.</p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Error de conexión a la base de datos: " . $e->getMessage() . "</p>";
}
?>
