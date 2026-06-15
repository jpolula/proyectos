<?php
// Script para insertar datos de prueba en la base de datos

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
    
    // Obtener IDs de los turnos
    $stmt = $pdo->query("SELECT id, nombre FROM turnos");
    $turnos = $stmt->fetchAll();
    $turnoIds = [];
    foreach ($turnos as $turno) {
        $turnoIds[$turno['nombre']] = $turno['id'];
    }
    
    // Generar fechas para el mes actual y el siguiente
    $fechas = [];
    $fechaActual = new DateTime();
    
    // Añadir fechas para los próximos 30 días
    for ($i = 0; $i < 30; $i++) {
        $fecha = clone $fechaActual;
        $fecha->modify("+$i days");
        $fechas[] = $fecha->format('Y-m-d');
    }
    
    // Configurar capacidad para diferentes fechas, zonas y turnos
    $stmt = $pdo->prepare("
        INSERT INTO capacidad (fecha, zona, turno_id, aforo_maximo) 
        VALUES (:fecha, :zona, :turno_id, :aforo_maximo)
        ON DUPLICATE KEY UPDATE aforo_maximo = :aforo_maximo
    ");
    
    $capacidadesInsertadas = 0;
    
    // Configurar capacidad para los próximos 30 días (solo para los primeros 5 días para simplificar)
    for ($i = 0; $i < 5; $i++) {
        $fecha = $fechas[$i];
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
            'fecha' => $fecha,
            'zona' => 'dentro',
            'turno_id' => $turnoIds['mediodia'],
            'aforo_maximo' => $aforoDentroMediodia
        ]);
        $capacidadesInsertadas++;
        
        // Insertar capacidad para interior, turno noche
        $stmt->execute([
            'fecha' => $fecha,
            'zona' => 'dentro',
            'turno_id' => $turnoIds['noche'],
            'aforo_maximo' => $aforoDentroNoche
        ]);
        $capacidadesInsertadas++;
        
        // Insertar capacidad para terraza, turno mediodía
        $stmt->execute([
            'fecha' => $fecha,
            'zona' => 'fuera',
            'turno_id' => $turnoIds['mediodia'],
            'aforo_maximo' => $aforoFueraMediodia
        ]);
        $capacidadesInsertadas++;
        
        // Insertar capacidad para terraza, turno noche
        $stmt->execute([
            'fecha' => $fecha,
            'zona' => 'fuera',
            'turno_id' => $turnoIds['noche'],
            'aforo_maximo' => $aforoFueraNoche
        ]);
        $capacidadesInsertadas++;
    }
    
    echo "<p>Se han insertado o actualizado $capacidadesInsertadas registros de capacidad.</p>";
    
    // Insertar algunos clientes de ejemplo
    $clientesInsertados = 0;
    $clienteIds = [];
    
    // Verificar si ya existen clientes
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM clientes");
    $result = $stmt->fetch();
    
    if ($result['count'] == 0) {
        // Insertar clientes si no existen
        $stmt = $pdo->prepare("
            INSERT INTO clientes (nombre, email, telefono) 
            VALUES (:nombre, :email, :telefono)
        ");
        
        $clientes = [
            ['nombre' => 'Juan Pérez', 'email' => 'juan@example.com', 'telefono' => '612345678'],
            ['nombre' => 'María López', 'email' => 'maria@example.com', 'telefono' => '623456789'],
            ['nombre' => 'Carlos Rodríguez', 'email' => 'carlos@example.com', 'telefono' => '634567890']
        ];
        
        foreach ($clientes as $cliente) {
            $stmt->execute([
                'nombre' => $cliente['nombre'],
                'email' => $cliente['email'],
                'telefono' => $cliente['telefono']
            ]);
            $clienteIds[] = $pdo->lastInsertId();
            $clientesInsertados++;
        }
        
        echo "<p>Se han insertado $clientesInsertados clientes de ejemplo.</p>";
    } else {
        // Obtener IDs de los clientes existentes
        $stmt = $pdo->query("SELECT id FROM clientes LIMIT 3");
        while ($row = $stmt->fetch()) {
            $clienteIds[] = $row['id'];
        }
        echo "<p>Se utilizarán los clientes existentes para las reservas.</p>";
    }
    
    // Verificar si hay suficientes clientes para las reservas
    if (count($clienteIds) >= 3) {
        // Actualizar días disponibles en la tabla dias_disponibles
        $stmt = $pdo->query("
            INSERT INTO dias_disponibles (fecha, zona, turno_id, disponible)
            SELECT c.fecha, c.zona, c.turno_id, 
                   (c.aforo_maximo > 0) as disponible
            FROM capacidad c
            ON DUPLICATE KEY UPDATE disponible = VALUES(disponible)
        ");
        
        echo "<p>Días disponibles actualizados correctamente.</p>";
        
        echo "<h1>Datos de prueba insertados correctamente</h1>";
        echo "<p>Ya puedes comenzar a utilizar el sistema de reservas.</p>";
        echo "<p><a href='index.php'>Volver al inicio</a></p>";
    } else {
        echo "<p>No hay suficientes clientes para crear reservas de ejemplo.</p>";
    }
    
} catch (PDOException $e) {
    echo "<h1>Error</h1>";
    echo "<p>Ha ocurrido un error al insertar los datos: " . $e->getMessage() . "</p>";
    echo "<p>Línea: " . $e->getLine() . "</p>";
}
?>
