<?php
/**
 * consultar_dias_disponibles.php
 * 
 * Este script proporciona los días disponibles para reservas.
 * Puede ser llamado en diferentes modos:
 * - modo=dias: Devuelve todos los días disponibles
 * - modo=disponibilidad: Comprueba disponibilidad para una fecha, zona y turno específicos
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

// Timestamp para evitar caché
$timestamp = time();

try {
    // Conectar a la base de datos
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // Obtener el modo de consulta
    $modo = $_GET['modo'] ?? 'dias';
    
    // MODO 1: OBTENER TODOS LOS DÍAS DISPONIBLES
    if ($modo === 'dias') {
        // Verificar si existe la tabla dias_disponibles
        $stmt = $pdo->query("SHOW TABLES LIKE 'dias_disponibles'");
        $tablaDiasDisponiblesExiste = $stmt->rowCount() > 0;
        
        if (!$tablaDiasDisponiblesExiste) {
            // Si la tabla no existe, devolver todas las fechas de mayo 2025
            $fechasMayo = generarFechasMayo2025();
            $dias = array_map(function($fecha) {
                return ['fecha' => $fecha];
            }, $fechasMayo);
            
            echo json_encode([
                'success' => true,
                'dias' => $dias,
                'mensaje' => 'Usando fechas predeterminadas (tabla no existe)',
                'timestamp' => $timestamp
            ]);
            exit;
        }
        
        // Consultar días disponibles de mayo 2025
        // Seleccionar los días que están marcados como disponibles en la tabla
        try {
            $stmt = $pdo->prepare("
                SELECT DISTINCT fecha 
                FROM dias_disponibles 
                WHERE disponible = 1 
                AND fecha >= '2025-05-01' 
                AND fecha <= '2025-05-31'
                ORDER BY fecha
            ");
            $stmt->execute();
            $dias = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Si no hay días disponibles, usar fechas de respaldo
            if (empty($dias)) {
                $fechasMayo = generarFechasMayo2025();
                $dias = array_map(function($fecha) {
                    return ['fecha' => $fecha];
                }, $fechasMayo);
            }
            
            echo json_encode([
                'success' => true,
                'dias' => $dias,
                'timestamp' => $timestamp
            ]);
        } catch (PDOException $e) {
            // Si hay un error en la consulta (por ejemplo, columna incorrecta)
            // Usar todas las fechas de mayo como respaldo
            $fechasMayo = generarFechasMayo2025();
            $dias = array_map(function($fecha) {
                return ['fecha' => $fecha];
            }, $fechasMayo);
            
            echo json_encode([
                'success' => true,
                'dias' => $dias,
                'mensaje' => 'Error en consulta: ' . $e->getMessage(),
                'timestamp' => $timestamp
            ]);
        }
    }
    // MODO 2: COMPROBAR DISPONIBILIDAD PARA UNA FECHA, TURNO Y ZONA ESPECÍFICOS
    else if ($modo === 'disponibilidad') {
        $fecha = $_GET['fecha'] ?? '';
        $turno = $_GET['turno'] ?? '';
        $zona = $_GET['zona'] ?? '';
        
        if (empty($fecha) || empty($turno) || empty($zona)) {
            echo json_encode([
                'success' => false,
                'mensaje' => 'Faltan parámetros requeridos',
                'timestamp' => $timestamp
            ]);
            exit;
        }
        
        // Verificar disponibilidad (simulación)
        // En un sistema real, aquí consultarías la base de datos
        $disponible = true;
        
        echo json_encode([
            'success' => true,
            'disponible' => $disponible,
            'timestamp' => $timestamp
        ]);
    }
    // MODO NO VÁLIDO
    else {
        echo json_encode([
            'success' => false,
            'mensaje' => 'Modo no válido',
            'timestamp' => $timestamp
        ]);
    }
} catch (PDOException $e) {
    // Si hay un error de conexión, usar todas las fechas de mayo como respaldo
    $fechasMayo = generarFechasMayo2025();
    $dias = array_map(function($fecha) {
        return ['fecha' => $fecha];
    }, $fechasMayo);
    
    echo json_encode([
        'success' => true,
        'dias' => $dias,
        'mensaje' => 'Error de base de datos: ' . $e->getMessage(),
        'timestamp' => $timestamp
    ]);
}
