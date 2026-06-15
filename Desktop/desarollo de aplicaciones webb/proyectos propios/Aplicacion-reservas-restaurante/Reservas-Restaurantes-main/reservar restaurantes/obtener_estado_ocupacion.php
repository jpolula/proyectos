<?php
/**
 * obtener_estado_ocupacion.php
 * 
 * Este script devuelve el estado de ocupación de los días disponibles para mostrar
 * colores diferentes en el calendario según el nivel de ocupación.
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
    
    // Obtener todos los días disponibles
    $stmt = $pdo->query("
        SELECT DISTINCT fecha 
        FROM dias_disponibles 
        WHERE disponible = 1 
        ORDER BY fecha
    ");
    $dias_disponibles = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Crear array para almacenar el estado de ocupación de cada día
    $estado_ocupacion = [];
    
    // Para cada día disponible, verificar el estado de ocupación
    foreach ($dias_disponibles as $fecha) {
        // Obtener todas las combinaciones de turno y zona para este día
        $stmt = $pdo->prepare("
            SELECT turno_id, zona
            FROM dias_disponibles
            WHERE fecha = ? AND disponible = 1
        ");
        $stmt->execute([$fecha]);
        $combinaciones = $stmt->fetchAll();
        
        // Inicializar estado para este día
        $estado_ocupacion[$fecha] = [
            'estado' => 'disponible', // disponible, medio_lleno, lleno
            'detalles' => []
        ];
        
        $total_combinaciones = count($combinaciones);
        $combinaciones_llenas = 0;
        $combinaciones_medio_llenas = 0;
        
        // Verificar cada combinación de turno y zona
        foreach ($combinaciones as $combinacion) {
            $turno_id = $combinacion['turno_id'];
            $zona = $combinacion['zona'];
            
            // Obtener el nombre del turno
            $stmt = $pdo->prepare("SELECT nombre FROM turnos WHERE id = ?");
            $stmt->execute([$turno_id]);
            $turno = $stmt->fetchColumn();
            
            // Obtener la capacidad máxima para esta combinación
            $stmt = $pdo->prepare("
                SELECT aforo_maximo 
                FROM capacidad 
                WHERE fecha = ? AND zona = ? AND turno_id = ?
            ");
            $stmt->execute([$fecha, $zona, $turno_id]);
            $aforo_maximo = $stmt->fetchColumn();
            
            // Si no hay configuración específica, obtener la capacidad por defecto
            if ($aforo_maximo === false) {
                $campo_capacidad = 'capacidad_' . $zona . '_' . $turno;
                $stmt = $pdo->prepare("SELECT $campo_capacidad FROM configuracion WHERE id = 1");
                $stmt->execute();
                $aforo_maximo = $stmt->fetchColumn();
                
                // Si aún no hay valor, usar un valor por defecto
                if ($aforo_maximo === false) {
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
            
            // Calcular porcentaje de ocupación
            $porcentaje_ocupacion = ($total_reservado / $aforo_maximo) * 100;
            
            // Determinar estado de esta combinación
            $estado_combinacion = 'disponible';
            if ($porcentaje_ocupacion >= 100) {
                $estado_combinacion = 'lleno';
                $combinaciones_llenas++;
            } elseif ($porcentaje_ocupacion >= 75) {
                $estado_combinacion = 'medio_lleno';
                $combinaciones_medio_llenas++;
            }
            
            // Guardar detalles de esta combinación
            $estado_ocupacion[$fecha]['detalles'][] = [
                'turno' => $turno,
                'turno_id' => $turno_id,
                'zona' => $zona,
                'aforo_maximo' => $aforo_maximo,
                'total_reservado' => $total_reservado,
                'porcentaje_ocupacion' => $porcentaje_ocupacion,
                'estado' => $estado_combinacion
            ];
        }
        
        // Determinar estado general del día
        if ($combinaciones_llenas == $total_combinaciones) {
            $estado_ocupacion[$fecha]['estado'] = 'lleno';
        } elseif ($combinaciones_llenas > 0 || $combinaciones_medio_llenas > $total_combinaciones / 2) {
            $estado_ocupacion[$fecha]['estado'] = 'medio_lleno';
        }
    }
    
    // Devolver respuesta
    echo json_encode([
        'success' => true,
        'estado_ocupacion' => $estado_ocupacion
    ]);
    
} catch (PDOException $e) {
    // Devolver error
    echo json_encode([
        'success' => false,
        'mensaje' => 'Error en el servidor: ' . $e->getMessage()
    ]);
}
?>
