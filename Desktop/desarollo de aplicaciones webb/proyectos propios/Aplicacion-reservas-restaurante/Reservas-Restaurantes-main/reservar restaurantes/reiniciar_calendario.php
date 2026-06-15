<?php
/**
 * reiniciar_calendario.php
 * 
 * Script para reiniciar completamente el calendario y solucionar problemas de visualización.
 * Marca todos los días de mayo 2025 como disponibles para todas las zonas y todos los turnos.
 */

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

// Función para generar fechas de mayo 2025
function generarFechasMayo2025() {
    $fechas = [];
    $inicio = new DateTime('2025-05-01');
    $fin = new DateTime('2025-05-31');
    $intervalo = new DateInterval('P1D');
    $periodo = new DatePeriod($inicio, $intervalo, $fin);
    
    foreach ($periodo as $fecha) {
        $fechas[] = $fecha->format('Y-m-d');
    }
    
    // Añadir el último día (que no se incluye en el periodo)
    $fechas[] = '2025-05-31';
    
    return $fechas;
}

// Iniciar salida HTML
echo '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reiniciar Calendario</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen p-8">
    <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-md p-8">
        <h1 class="text-2xl font-bold mb-6">Reiniciando Calendario</h1>';

try {
    // Conectar a la base de datos
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    echo '<p>✅ Conexión a la base de datos establecida correctamente.</p>';
    
    // Verificar si la tabla dias_disponibles existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'dias_disponibles'");
    $tablaExiste = $stmt->rowCount() > 0;
    
    if ($tablaExiste) {
        echo '<p>✅ Tabla dias_disponibles encontrada.</p>';
    } else {
        echo '<p>⚠️ Tabla dias_disponibles no encontrada. Creándola...</p>';
        
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS dias_disponibles (
                id INT AUTO_INCREMENT PRIMARY KEY,
                fecha DATE NOT NULL,
                turno_id INT NOT NULL,
                zona VARCHAR(50) NOT NULL,
                disponible TINYINT(1) NOT NULL DEFAULT 1,
                UNIQUE KEY fecha_turno_zona (fecha, turno_id, zona)
            )
        ");
        
        echo '<p>✅ Tabla dias_disponibles creada correctamente.</p>';
    }
    
    // Verificar si la tabla turnos existe y tiene datos
    $stmt = $pdo->query("SELECT id, nombre FROM turnos");
    $turnos = $stmt->fetchAll();
    
    if (empty($turnos)) {
        echo '<p>⚠️ No se encontraron turnos en la base de datos. Creando turnos predeterminados...</p>';
        
        // Crear la tabla turnos si no existe
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS turnos (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nombre VARCHAR(50) NOT NULL,
                hora_inicio TIME NOT NULL,
                hora_fin TIME NOT NULL
            )
        ");
        
        // Insertar turnos básicos
        $pdo->exec("
            INSERT INTO turnos (nombre, hora_inicio, hora_fin) VALUES 
            ('mediodia', '13:00', '16:00'),
            ('noche', '20:00', '23:00')
        ");
        
        // Obtener los turnos nuevamente
        $stmt = $pdo->query("SELECT id, nombre FROM turnos");
        $turnos = $stmt->fetchAll();
        
        echo '<p>✅ Turnos predeterminados creados correctamente.</p>';
    } else {
        echo '<p>✅ Se encontraron '.count($turnos).' turnos en la base de datos.</p>';
    }
    
    // Borrar todos los días disponibles de mayo 2025
    $stmt = $pdo->prepare("DELETE FROM dias_disponibles WHERE fecha BETWEEN ? AND ?");
    $stmt->execute(['2025-05-01', '2025-05-31']);
    $diasBorrados = $stmt->rowCount();
    
    echo '<p>✅ Se eliminaron '.$diasBorrados.' registros antiguos de días disponibles.</p>';
    
    // Generar todos los días de mayo 2025
    $fechasMayo = generarFechasMayo2025();
    
    echo '<p>⏳ Generando disponibilidad para '.count($fechasMayo).' días...</p>';
    
    // Insertar todos los días como disponibles
    $insertados = 0;
    
    foreach ($fechasMayo as $fecha) {
        foreach ($turnos as $turno) {
            // Insertar para zona interior (dentro)
            $stmt = $pdo->prepare("
                INSERT INTO dias_disponibles (fecha, turno_id, zona, disponible)
                VALUES (?, ?, 'dentro', 1)
            ");
            $stmt->execute([$fecha, $turno['id']]);
            $insertados++;
            
            // Insertar para terraza (fuera)
            $stmt = $pdo->prepare("
                INSERT INTO dias_disponibles (fecha, turno_id, zona, disponible)
                VALUES (?, ?, 'fuera', 1)
            ");
            $stmt->execute([$fecha, $turno['id']]);
            $insertados++;
        }
    }
    
    echo '<p>✅ Se han insertado '.$insertados.' registros de días disponibles.</p>';
    
    // Verificar cuántos días distintos son ahora disponibles
    $stmt = $pdo->query("
        SELECT COUNT(DISTINCT fecha) 
        FROM dias_disponibles 
        WHERE disponible = 1 
        AND fecha BETWEEN '2025-05-01' AND '2025-05-31'
    ");
    $numDiasDisponibles = $stmt->fetchColumn();
    
    echo '<p>✅ Ahora hay '.$numDiasDisponibles.' días disponibles para mayo 2025.</p>';
    
    // Mensaje de éxito final
    echo '<div class="mt-8 p-4 bg-green-100 border border-green-500 rounded-md">
        <h2 class="text-xl font-semibold text-green-800 mb-2">Calendario reiniciado correctamente</h2>
        <p class="mb-4">Todos los días de mayo 2025 han sido marcados como disponibles para todas las zonas y todos los turnos.</p>
        <div class="flex gap-4">
            <a href="reserva.php" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded transition-colors">Ir a Reservas</a>
            <a href="test_calendario.html" class="inline-block bg-gray-600 hover:bg-gray-700 text-white font-medium py-2 px-4 rounded transition-colors">Probar Calendario</a>
        </div>
    </div>';
    
} catch (PDOException $e) {
    echo '<p class="error">❌ Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<p class="error">❌ En la línea: ' . $e->getLine() . '</p>';
}

// Cerrar HTML
echo '</div></body></html>';
?>
