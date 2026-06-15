<?php
// Script para insertar datos de capacidad en la base de datos

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

try {
    // Conectar a la base de datos
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // Obtener IDs de los turnos
    $stmt = $pdo->query("SELECT id, nombre FROM turnos");
    $turnos = $stmt->fetchAll();
    $turnoIds = [];
    foreach ($turnos as $turno) {
        $turnoIds[$turno['nombre']] = $turno['id'];
    }
    
    if (empty($turnoIds)) {
        throw new Exception("No se encontraron turnos en la base de datos");
    }
    
    echo "<p>Turnos encontrados: " . implode(", ", array_keys($turnoIds)) . "</p>";
    
    // Generar fechas para el mes actual y el siguiente
    $fechas = [];
    $fechaActual = new DateTime();
    
    // Añadir fechas para los próximos 30 días
    for ($i = 0; $i < 30; $i++) {
        $fecha = clone $fechaActual;
        $fecha->modify("+$i days");
        $fechas[] = $fecha->format('Y-m-d');
    }
    
    // Limpiar la tabla de capacidad
    $pdo->exec("TRUNCATE TABLE capacidad");
    echo "<p>Tabla de capacidad limpiada.</p>";
    
    // Configurar capacidad para diferentes fechas, zonas y turnos
    $stmt = $pdo->prepare("
        INSERT INTO capacidad (fecha, zona, turno_id, aforo_maximo) 
        VALUES (?, ?, ?, ?)
    ");
    
    $capacidadesInsertadas = 0;
    
    // Configurar capacidad para los próximos 30 días
    foreach ($fechas as $fecha) {
        $diaSemana = date('N', strtotime($fecha)); // 1 (lunes) a 7 (domingo)
        
        // Configurar diferentes capacidades según el día de la semana
        if ($diaSemana == 5 || $diaSemana == 6 || $diaSemana == 7) {
            // Viernes, sábado y domingo - mayor capacidad
            $aforoDentroMediodia = 40;
            $aforoDentroNoche = 50;
            $aforoFueraMediodia = 30;
            $aforoFueraNoche = 35;
        } else {
            // Resto de días - capacidad normal
            $aforoDentroMediodia = 30;
            $aforoDentroNoche = 35;
            $aforoFueraMediodia = 20;
            $aforoFueraNoche = 25;
        }
        
        // Insertar capacidad para interior, turno mediodía
        $stmt->execute([
            $fecha,
            'dentro',
            $turnoIds['mediodia'],
            $aforoDentroMediodia
        ]);
        $capacidadesInsertadas++;
        
        // Insertar capacidad para interior, turno noche
        $stmt->execute([
            $fecha,
            'dentro',
            $turnoIds['noche'],
            $aforoDentroNoche
        ]);
        $capacidadesInsertadas++;
        
        // Insertar capacidad para terraza, turno mediodía
        $stmt->execute([
            $fecha,
            'fuera',
            $turnoIds['mediodia'],
            $aforoFueraMediodia
        ]);
        $capacidadesInsertadas++;
        
        // Insertar capacidad para terraza, turno noche
        $stmt->execute([
            $fecha,
            'fuera',
            $turnoIds['noche'],
            $aforoFueraNoche
        ]);
        $capacidadesInsertadas++;
    }
    
    echo "<p>Se han insertado $capacidadesInsertadas registros de capacidad.</p>";
    
    // Actualizar días disponibles
    $pdo->exec("
        INSERT INTO dias_disponibles (fecha, zona, turno_id, disponible)
        SELECT c.fecha, c.zona, c.turno_id, 
               (c.aforo_maximo > 0) as disponible
        FROM capacidad c
        ON DUPLICATE KEY UPDATE disponible = VALUES(disponible)
    ");
    
    echo "<p>Días disponibles actualizados correctamente.</p>";
    
    echo "<h1>Datos de capacidad insertados correctamente</h1>";
    echo "<p>Ya puedes comenzar a utilizar el sistema de reservas.</p>";
    echo "<p><a href='index.php'>Volver al inicio</a></p>";
    
} catch (Exception $e) {
    echo "<h1>Error</h1>";
    echo "<p>Ha ocurrido un error: " . $e->getMessage() . "</p>";
    echo "<p>Línea: " . $e->getLine() . "</p>";
}
?>
