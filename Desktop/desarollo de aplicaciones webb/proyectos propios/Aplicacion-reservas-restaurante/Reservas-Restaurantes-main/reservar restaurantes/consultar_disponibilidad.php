<?php
/**
 * consultar_disponibilidad.php
 * API mejorada para consultar días disponibles y verificar disponibilidad
 */

// Prevenir caché en el navegador
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header('Content-Type: application/json');

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

try {
    // Conectar a la base de datos
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // Obtener el modo de operación
    $modo = $_GET['modo'] ?? 'dias';
    
    // MODO 1: OBTENER TODOS LOS DÍAS DISPONIBLES
    if ($modo === 'dias') {
        // Verificar si la tabla dias_disponibles existe
        $stmt = $pdo->query("SHOW TABLES LIKE 'dias_disponibles'");
        $tablaExiste = $stmt->rowCount() > 0;
        
        // Si la tabla no existe, crearla
        if (!$tablaExiste) {
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS dias_disponibles (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    fecha DATE NOT NULL,
                    zona VARCHAR(50) NOT NULL,
                    turno_id INT NOT NULL,
                    disponible TINYINT(1) NOT NULL DEFAULT 1,
                    UNIQUE KEY fecha_turno_zona (fecha, turno_id, zona)
                )
            ");
            
            // También crear la tabla turnos si no existe
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
        }
        
        // Obtener todos los turnos
        $stmtTurnos = $pdo->query("SELECT id FROM turnos");
        $turnos = $stmtTurnos->fetchAll(PDO::FETCH_COLUMN);
        
        if (empty($turnos)) {
            // Si no hay turnos, insertar los básicos
            $pdo->exec("
                INSERT INTO turnos (nombre, hora_inicio, hora_fin) VALUES 
                ('mediodia', '13:00', '16:00'),
                ('noche', '20:00', '23:00')
            ");
            
            $stmtTurnos = $pdo->query("SELECT id FROM turnos");
            $turnos = $stmtTurnos->fetchAll(PDO::FETCH_COLUMN);
        }
        
        // Obtener todos los días de mayo 2025
        $fechasMayo = generarFechasMayo2025();
        
        // Comprobar si ya existen días para mayo 2025
        $stmt = $pdo->query("SELECT COUNT(*) FROM dias_disponibles WHERE fecha >= '2025-05-01' AND fecha <= '2025-05-31'");
        $diasExistentes = $stmt->fetchColumn();
        
        // Si no existen días o hay muy pocos, insertar todos los días de mayo como disponibles
        if ($diasExistentes < 10) {
            // Primero, eliminar cualquier día existente de mayo 2025 para evitar duplicados
            $pdo->exec("DELETE FROM dias_disponibles WHERE fecha >= '2025-05-01' AND fecha <= '2025-05-31'");
            
            // Luego, insertar todos los días de mayo 2025 como disponibles
            foreach ($fechasMayo as $fecha) {
                foreach ($turnos as $turno) {
                    // Insertar para zona interior (dentro)
                    $stmt = $pdo->prepare("
                        INSERT INTO dias_disponibles (fecha, turno_id, zona, disponible)
                        VALUES (?, ?, 'dentro', 1)
                    ");
                    $stmt->execute([$fecha, $turno]);
                    
                    // Insertar para terraza (fuera)
                    $stmt = $pdo->prepare("
                        INSERT INTO dias_disponibles (fecha, turno_id, zona, disponible)
                        VALUES (?, ?, 'fuera', 1)
                    ");
                    $stmt->execute([$fecha, $turno]);
                }
            }
        }
        
        // Obtener días disponibles (al menos una combinación disponible de zona+turno)
        $stmt = $pdo->query("
            SELECT DISTINCT fecha 
            FROM dias_disponibles 
            WHERE disponible = 1 
            AND fecha >= '2025-05-01' 
            AND fecha <= '2025-05-31'
            ORDER BY fecha
        ");
        
        $fechasDisponibles = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        // Si no hay días disponibles (algo raro pasó), usar todos los días de mayo
        if (empty($fechasDisponibles)) {
            $fechasDisponibles = $fechasMayo;
        }
        
        // Formatear respuesta
        $diasDisponibles = [];
        foreach ($fechasDisponibles as $fecha) {
            $diasDisponibles[] = [
                'fecha' => $fecha,
                'disponible' => true
            ];
        }
        
        // Devolver respuesta JSON
        echo json_encode([
            'success' => true,
            'dias' => $diasDisponibles,
            'timestamp' => time()
        ]);
    }
    
    // MODO 2: COMPROBAR DISPONIBILIDAD PARA UNA FECHA, ZONA Y TURNO ESPECÍFICOS
    elseif ($modo === 'disponibilidad') {
        // Obtener parámetros
        $fecha = $_GET['fecha'] ?? '';
        $zona = $_GET['zona'] ?? '';
        $turno = $_GET['turno'] ?? '';
        
        // Validar parámetros
        if (empty($fecha) || empty($zona) || empty($turno)) {
            echo json_encode([
                'success' => false,
                'mensaje' => 'Faltan parámetros requeridos'
            ]);
            exit;
        }
        
        // Obtener el ID del turno
        $stmt = $pdo->prepare("SELECT id FROM turnos WHERE nombre = ?");
        $stmt->execute([$turno]);
        $turnoId = $stmt->fetchColumn();
        
        if (!$turnoId) {
            echo json_encode([
                'success' => false,
                'mensaje' => 'Turno no válido'
            ]);
            exit;
        }
        
        // Verificar disponibilidad en la tabla dias_disponibles
        $stmt = $pdo->prepare("
            SELECT disponible 
            FROM dias_disponibles 
            WHERE fecha = ? AND zona = ? AND turno_id = ?
        ");
        $stmt->execute([$fecha, $zona, $turnoId]);
        $disponible = $stmt->fetchColumn();
        
        // Si no hay registro, verificar si hay capacidad configurada
        if ($disponible === false) {
            // Si no hay registro, asumir que está disponible por defecto
            $disponible = true;
        }
        
        // Verificar si hay bloqueos
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM bloqueos 
            WHERE fecha = ? AND zona = ? AND turno_id = ?
        ");
        $stmt->execute([$fecha, $zona, $turnoId]);
        $bloqueado = ($stmt->fetchColumn() > 0);
        
        if ($bloqueado) {
            $disponible = false;
        }
        
        // Respuesta JSON
        echo json_encode([
            'success' => true,
            'disponible' => $disponible ? true : false
        ]);
    }
    
    // MODO NO VÁLIDO
    else {
        echo json_encode([
            'success' => false,
            'mensaje' => 'Modo no válido'
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'mensaje' => 'Error: ' . $e->getMessage()
    ]);
}
?>
