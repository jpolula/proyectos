<?php
/**
 * consultar_dias_disponibles.php
 * API para obtener los días disponibles para reservas y comprobar disponibilidad
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

try {
    // Conectar a la base de datos
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // Obtener el modo de operación
    $modo = $_GET['modo'] ?? 'dias';
    
    // MODO 1: OBTENER TODOS LOS DÍAS DISPONIBLES
    if ($modo === 'dias') {
        // Verificar si la tabla dias_disponibles existe
        $tablaExiste = false;
        $stmt = $pdo->query("SHOW TABLES LIKE 'dias_disponibles'");
        if ($stmt->rowCount() > 0) {
            $tablaExiste = true;
        }
        
        // Si la tabla no existe, crearla
        if (!$tablaExiste) {
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS dias_disponibles (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    fecha DATE NOT NULL,
                    zona ENUM('dentro', 'fuera') NOT NULL,
                    turno_id INT NOT NULL,
                    disponible BOOLEAN NOT NULL DEFAULT FALSE,
                    UNIQUE(fecha, zona, turno_id),
                    FOREIGN KEY (turno_id) REFERENCES turnos(id)
                )
            ");
        }
        
        // Consultar días disponibles
        // Para que un día se considere disponible, debe tener al menos una combinación
        // de zona y turno disponible
        $stmt = $pdo->query("
            SELECT DISTINCT fecha 
            FROM dias_disponibles 
            WHERE disponible = 1 
            ORDER BY fecha
        ");
        
        $fechasDisponibles = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        // Si no hay días disponibles, devolver días predeterminados (todo mayo 2025)
        if (empty($fechasDisponibles)) {
            $fechasDisponibles = generarFechasMayo2025();
            // Intentar insertarlos en la base de datos
            try {
                $pdo->beginTransaction();
                
                // Obtener todos los turnos
                $stmtTurnos = $pdo->query("SELECT id FROM turnos");
                $turnos = $stmtTurnos->fetchAll(PDO::FETCH_COLUMN);
                
                // Si no hay turnos, insertar los predeterminados
                if (empty($turnos)) {
                    $pdo->exec("
                        INSERT INTO turnos (nombre, hora_inicio, hora_fin) VALUES 
                        ('mediodia', '13:00:00', '16:00:00'),
                        ('noche', '20:00:00', '23:00:00')
                    ");
                    
                    $stmtTurnos = $pdo->query("SELECT id FROM turnos");
                    $turnos = $stmtTurnos->fetchAll(PDO::FETCH_COLUMN);
                }
                
                // Insertar días disponibles para mayo 2025
                foreach ($fechasDisponibles as $fecha) {
                    foreach ($turnos as $turnoId) {
                        foreach (['dentro', 'fuera'] as $zona) {
                            $stmt = $pdo->prepare("
                                INSERT INTO dias_disponibles (fecha, zona, turno_id, disponible)
                                VALUES (?, ?, ?, 1)
                                ON DUPLICATE KEY UPDATE disponible = 1
                            ");
                            $stmt->execute([$fecha, $zona, $turnoId]);
                        }
                    }
                }
                
                $pdo->commit();
            } catch (PDOException $e) {
                $pdo->rollBack();
                // Silenciar errores, simplemente continuar con los días predeterminados
            }
        }
        
        // Formatear la respuesta
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
        
        // Verificar disponibilidad
        $stmt = $pdo->prepare("
            SELECT disponible 
            FROM dias_disponibles 
            WHERE fecha = ? AND zona = ? AND turno_id = ?
        ");
        $stmt->execute([$fecha, $zona, $turnoId]);
        $disponible = $stmt->fetchColumn();
        
        // Si no hay registro, verificar si hay capacidad configurada
        if ($disponible === false) {
            $stmt = $pdo->prepare("
                SELECT aforo_maximo 
                FROM capacidad 
                WHERE fecha = ? AND zona = ? AND turno_id = ?
            ");
            $stmt->execute([$fecha, $zona, $turnoId]);
            $aforoMaximo = $stmt->fetchColumn();
            
            // Si hay capacidad configurada y es mayor que 0, está disponible
            $disponible = ($aforoMaximo !== false && $aforoMaximo > 0);
        }
        
        // Verificar si hay bloqueos
        $stmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM bloqueos 
            WHERE fecha = ? AND zona = ? AND turno_id = ?
        ");
        $stmt->execute([$fecha, $zona, $turnoId]);
        $bloqueado = ($stmt->fetchColumn() > 0);
        
        // Si está bloqueado, no está disponible
        if ($bloqueado) {
            $disponible = false;
        }
        
        // Verificar reservas existentes vs capacidad
        if ($disponible) {
            // Obtener capacidad máxima
            $stmt = $pdo->prepare("
                SELECT aforo_maximo 
                FROM capacidad 
                WHERE fecha = ? AND zona = ? AND turno_id = ?
            ");
            $stmt->execute([$fecha, $zona, $turnoId]);
            $aforoMaximo = $stmt->fetchColumn();
            
            // Si no hay capacidad configurada, usar un valor predeterminado
            if ($aforoMaximo === false) {
                $aforoMaximo = 20; // Valor predeterminado
            }
            
            // Obtener reservas existentes
            $stmt = $pdo->prepare("
                SELECT SUM(cantidad_personas) 
                FROM reservas 
                WHERE fecha = ? AND zona = ? AND turno_id = ?
                AND estado != 'rechazada'
            ");
            $stmt->execute([$fecha, $zona, $turnoId]);
            $reservasExistentes = $stmt->fetchColumn();
            
            if ($reservasExistentes === false) {
                $reservasExistentes = 0;
            }
            
            // Calcular disponibilidad real
            $disponibilidadRestante = $aforoMaximo - $reservasExistentes;
            
            // Devolver respuesta JSON
            echo json_encode([
                'success' => true,
                'disponible' => ($disponibilidadRestante > 0),
                'aforo_maximo' => (int)$aforoMaximo,
                'reservas_existentes' => (int)$reservasExistentes,
                'disponibilidad_restante' => (int)$disponibilidadRestante
            ]);
        } else {
            // No está disponible
            echo json_encode([
                'success' => true,
                'disponible' => false
            ]);
        }
    }
    
    // MODO NO RECONOCIDO
    else {
        echo json_encode([
            'success' => false,
            'mensaje' => 'Modo no reconocido'
        ]);
    }
} catch (PDOException $e) {
    // Devolver error
    echo json_encode([
        'success' => false,
        'mensaje' => 'Error en el servidor: ' . $e->getMessage()
    ]);
}

/**
 * Función para generar fechas de mayo 2025
 */
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
?>
