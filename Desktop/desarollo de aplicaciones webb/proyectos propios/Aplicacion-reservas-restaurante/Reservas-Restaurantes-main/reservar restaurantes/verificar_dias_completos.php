<?php
/**
 * verificar_dias_completos.php
 * 
 * Este script verifica qué días están completamente ocupados y devuelve un array
 * con las fechas que no tienen disponibilidad para el número de personas solicitado.
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
    // Obtener parámetros
    $num_personas = isset($_GET['num_personas']) ? (int)$_GET['num_personas'] : 1;
    
    // Conectar a la base de datos
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // Obtener todos los días disponibles
    $stmt = $pdo->query("
        SELECT DISTINCT fecha 
        FROM dias_disponibles 
        WHERE disponible = 1 
        ORDER BY fecha
    ");
    $dias_disponibles = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Obtener todos los turnos
    $stmt = $pdo->query("SELECT id, nombre FROM turnos");
    $turnos = $stmt->fetchAll();
    
    // Obtener la configuración general
    $stmt = $pdo->query("SELECT * FROM configuracion WHERE id = 1");
    $config = $stmt->fetch();
    
    // Inicializar array para días sin disponibilidad
    $dias_sin_disponibilidad = [];
    
    // Verificar cada día disponible
    foreach ($dias_disponibles as $fecha) {
        $dia_completo = true; // Asumimos que el día está completo hasta que se demuestre lo contrario
        
        // Verificar cada combinación de turno y zona
        foreach ($turnos as $turno) {
            $turno_id = $turno['id'];
            $zonas = ['dentro', 'fuera'];
            
            foreach ($zonas as $zona) {
                // Verificar si hay bloqueos
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM bloqueos WHERE fecha = ? AND zona = ? AND turno_id = ?");
                $stmt->execute([$fecha, $zona, $turno_id]);
                $bloqueado = ($stmt->fetchColumn() > 0);
                
                if ($bloqueado) {
                    continue; // Si está bloqueado, pasar a la siguiente combinación
                }
                
                // Verificar si la zona y turno están disponibles para este día
                $stmt = $pdo->prepare("
                    SELECT disponible 
                    FROM dias_disponibles 
                    WHERE fecha = ? AND turno_id = ? AND zona = ?
                ");
                $stmt->execute([$fecha, $turno_id, $zona]);
                $disponible = $stmt->fetchColumn();
                
                if (!$disponible) {
                    continue; // Si no está disponible, pasar a la siguiente combinación
                }
                
                // Obtener la capacidad máxima para esa fecha, zona y turno
                $stmt = $pdo->prepare("
                    SELECT aforo_maximo 
                    FROM capacidad 
                    WHERE fecha = ? AND zona = ? AND turno_id = ?
                ");
                $stmt->execute([$fecha, $zona, $turno_id]);
                $aforo_maximo = $stmt->fetchColumn();
                
                // Si no hay configuración específica, obtener la capacidad por defecto
                if ($aforo_maximo === false) {
                    $turno_nombre = $turno['nombre'];
                    $campo_capacidad = 'capacidad_' . $zona . '_' . $turno_nombre;
                    
                    if (isset($config[$campo_capacidad])) {
                        $aforo_maximo = $config[$campo_capacidad];
                    } else {
                        // Usar valores por defecto si no hay configuración
                        $aforo_maximo = ($zona == 'dentro') ? 30 : 20;
                    }
                }
                
                // Obtener el número de personas ya reservadas
                $stmt = $pdo->prepare("
                    SELECT SUM(cantidad_personas) as total_reservado
                    FROM reservas
                    WHERE fecha = ? AND zona = ? AND turno_id = ? AND estado = 'confirmada'
                ");
                $stmt->execute([$fecha, $zona, $turno_id]);
                $resultado = $stmt->fetch();
                $total_reservado = $resultado['total_reservado'] ?: 0;
                
                // Calcular si hay suficiente aforo disponible
                $aforo_disponible = $aforo_maximo - $total_reservado;
                
                // Si hay disponibilidad para el número de personas solicitado en cualquier combinación,
                // entonces el día no está completamente ocupado
                if ($aforo_disponible >= $num_personas) {
                    $dia_completo = false;
                    break 2; // Salir de ambos bucles (zonas y turnos)
                }
            }
        }
        
        // Si después de verificar todas las combinaciones, el día sigue considerándose completo,
        // añadirlo a la lista de días sin disponibilidad
        if ($dia_completo) {
            $dias_sin_disponibilidad[] = $fecha;
        }
    }
    
    // Devolver la lista de días sin disponibilidad
    echo json_encode([
        'success' => true,
        'dias_sin_disponibilidad' => $dias_sin_disponibilidad
    ]);
    
} catch (PDOException $e) {
    // Devolver error
    echo json_encode([
        'success' => false,
        'mensaje' => 'Error en el servidor: ' . $e->getMessage(),
        'dias_sin_disponibilidad' => []
    ]);
}
?>
