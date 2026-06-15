<?php
// Script para crear la base de datos y sus tablas

// Configuración de la conexión a la base de datos
$host = 'localhost';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

try {
    // Conectar a MySQL sin seleccionar una base de datos
    $pdo = new PDO("mysql:host=$host;charset=$charset", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    
    // Crear la base de datos si no existe
    $pdo->exec("CREATE DATABASE IF NOT EXISTS restaurante_reservas");
    echo "<p>Base de datos 'restaurante_reservas' creada o ya existente.</p>";
    
    // Seleccionar la base de datos
    $pdo->exec("USE restaurante_reservas");
    
    // Leer el archivo SQL
    $sql = file_get_contents(__DIR__ . '/database.sql');
    
    // Ejecutar las consultas SQL
    $pdo->exec($sql);
    
    echo "<h1>Base de datos importada correctamente</h1>";
    echo "<p>Las tablas han sido creadas con éxito.</p>";
    
    // Insertar datos básicos
    
    // Verificar si los turnos ya existen
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM turnos");
    $result = $stmt->fetch();
    
    if ($result['count'] == 0) {
        // Insertar turnos si no existen
        $pdo->exec("
            INSERT INTO turnos (nombre, hora_inicio, hora_fin) VALUES 
            ('mediodia', '13:00:00', '16:00:00'),
            ('noche', '20:00:00', '23:00:00')
        ");
        echo "<p>Turnos insertados correctamente.</p>";
    } else {
        echo "<p>Los turnos ya existen en la base de datos.</p>";
    }
    
    // Insertar configuración por defecto si no existe
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM configuracion");
    $result = $stmt->fetch();
    
    if ($result['count'] == 0) {
        $pdo->exec("
            INSERT INTO configuracion (id, max_personas_sin_aprobacion) VALUES 
            (1, 4)
        ");
        echo "<p>Configuración por defecto insertada correctamente.</p>";
    } else {
        echo "<p>La configuración ya existe en la base de datos.</p>";
    }
    
    // Insertar algunos clientes de ejemplo
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM clientes");
    $result = $stmt->fetch();
    
    if ($result['count'] == 0) {
        $pdo->exec("
            INSERT INTO clientes (nombre, email, telefono) VALUES 
            ('Juan Pérez', 'juan@example.com', '612345678'),
            ('María López', 'maria@example.com', '623456789'),
            ('Carlos Rodríguez', 'carlos@example.com', '634567890')
        ");
        echo "<p>Clientes de ejemplo insertados correctamente.</p>";
    } else {
        echo "<p>Ya existen clientes en la base de datos.</p>";
    }
    
    // Generar capacidad para los próximos 30 días
    $fechaActual = new DateTime();
    $turnoMediodia = 1; // Asumimos que el ID del turno mediodía es 1
    $turnoNoche = 2;    // Asumimos que el ID del turno noche es 2
    
    // Verificar si ya hay registros de capacidad
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM capacidad");
    $result = $stmt->fetch();
    
    if ($result['count'] == 0) {
        // Preparar la consulta para insertar capacidad
        $stmt = $pdo->prepare("
            INSERT INTO capacidad (fecha, zona, turno_id, aforo_maximo) 
            VALUES (?, ?, ?, ?)
        ");
        
        // Generar capacidad para los próximos 30 días
        for ($i = 0; $i < 30; $i++) {
            $fecha = clone $fechaActual;
            $fecha->modify("+$i days");
            $fechaStr = $fecha->format('Y-m-d');
            
            // Determinar capacidad según el día de la semana
            $diaSemana = $fecha->format('N'); // 1 (lunes) a 7 (domingo)
            
            if ($diaSemana >= 5) { // Viernes, sábado, domingo
                $aforoDentro = 40;
                $aforoFuera = 30;
            } else {
                $aforoDentro = 30;
                $aforoFuera = 20;
            }
            
            // Interior, turno mediodía
            $stmt->execute([$fechaStr, 'dentro', $turnoMediodia, $aforoDentro]);
            
            // Interior, turno noche
            $stmt->execute([$fechaStr, 'dentro', $turnoNoche, $aforoDentro]);
            
            // Terraza, turno mediodía
            $stmt->execute([$fechaStr, 'fuera', $turnoMediodia, $aforoFuera]);
            
            // Terraza, turno noche
            $stmt->execute([$fechaStr, 'fuera', $turnoNoche, $aforoFuera]);
        }
        
        echo "<p>Capacidad para los próximos 30 días insertada correctamente.</p>";
    } else {
        echo "<p>Ya existen registros de capacidad en la base de datos.</p>";
    }
    
    // Actualizar días disponibles
    $pdo->exec("
        INSERT INTO dias_disponibles (fecha, zona, turno_id, disponible)
        SELECT c.fecha, c.zona, c.turno_id, 
               (c.aforo_maximo > 0) as disponible
        FROM capacidad c
        ON DUPLICATE KEY UPDATE disponible = VALUES(disponible)
    ");
    
    echo "<p>Días disponibles actualizados correctamente.</p>";
    
    echo "<p><a href='index.php'>Volver al inicio</a></p>";
    
} catch (PDOException $e) {
    echo "<h1>Error</h1>";
    echo "<p>Ha ocurrido un error: " . $e->getMessage() . "</p>";
    echo "<p>Línea: " . $e->getLine() . "</p>";
}
?>
