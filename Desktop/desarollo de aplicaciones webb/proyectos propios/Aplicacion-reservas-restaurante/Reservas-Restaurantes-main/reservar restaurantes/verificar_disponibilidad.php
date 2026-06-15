<?php
/**
 * verificar_disponibilidad.php
 * 
 * Este script verifica la disponibilidad de una reserva sin guardarla en la base de datos.
 * Se utiliza para mostrar al usuario si hay disponibilidad antes de confirmar la reserva.
 */

session_start();

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

// Función para verificar la disponibilidad
function verificarDisponibilidad($fecha, $zona, $turno_id, $num_personas) {
    global $dsn, $user, $pass, $options;
    
    try {
        $pdo = new PDO($dsn, $user, $pass, $options);
        
        // Verificar disponibilidad del día
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as disponible 
            FROM dias_disponibles 
            WHERE fecha = ? AND turno_id = ? AND zona = ? AND disponible = 1
        ");
        $stmt->execute([$fecha, $turno_id, $zona]);
        $disponible = $stmt->fetchColumn() > 0;
        
        if (!$disponible) {
            return [
                'disponible' => false,
                'mensaje' => 'El día seleccionado no está disponible para reservas.',
                'aforo_maximo' => 0,
                'total_reservado' => 0,
                'aforo_disponible' => 0,
                'hay_disponibilidad' => false
            ];
        }
        
        // Obtener capacidad general de la configuración
        $stmt = $pdo->prepare("SELECT * FROM configuracion WHERE id = 1");
        $stmt->execute();
        $config = $stmt->fetch();
        
        // Determinar el campo de capacidad según turno y zona
        $stmt = $pdo->prepare("SELECT nombre FROM turnos WHERE id = ?");
        $stmt->execute([$turno_id]);
        $nombre_turno = $stmt->fetchColumn();
        
        $campo_capacidad = '';
        if ($nombre_turno == 'mediodia') {
            $campo_capacidad = ($zona == 'dentro') ? 'capacidad_dentro_mediodia' : 'capacidad_fuera_mediodia';
        } else {
            $campo_capacidad = ($zona == 'dentro') ? 'capacidad_dentro_noche' : 'capacidad_fuera_noche';
        }
        
        // Obtener el aforo máximo de la configuración
        $aforo_maximo = $config[$campo_capacidad] ?? (($zona == 'dentro') ? 30 : 20);
        
        // Obtener el número de personas ya reservadas (solo reservas confirmadas)
        $stmt = $pdo->prepare("
            SELECT SUM(cantidad_personas) as total_reservado
            FROM reservas
            WHERE fecha = ? AND zona = ? AND turno_id = ? AND estado = 'confirmada'
        ");
        $stmt->execute([$fecha, $zona, $turno_id]);
        $resultado = $stmt->fetch();
        $total_reservado = $resultado['total_reservado'] ?: 0;
        
        // Calcular aforo disponible
        $aforo_disponible = $aforo_maximo - $total_reservado;
        
        // Verificar si hay suficiente aforo disponible
        $hay_disponibilidad = $aforo_disponible >= $num_personas;
        
        return [
            'disponible' => $disponible,
            'aforo_maximo' => $aforo_maximo,
            'total_reservado' => $total_reservado,
            'aforo_disponible' => $aforo_disponible,
            'hay_disponibilidad' => $hay_disponibilidad,
            'mensaje' => $hay_disponibilidad 
                ? 'Hay disponibilidad para su reserva.' 
                : 'No hay suficiente aforo disponible para el número de personas indicado.'
        ];
        
    } catch (PDOException $e) {
        return [
            'disponible' => false,
            'mensaje' => 'Error al verificar disponibilidad: ' . $e->getMessage(),
            'aforo_maximo' => 0,
            'total_reservado' => 0,
            'aforo_disponible' => 0,
            'hay_disponibilidad' => false
        ];
    }
}

// Procesar la solicitud si es una llamada AJAX
if (isset($_POST['verificar']) && $_POST['verificar'] === 'true') {
    // Obtener datos de la solicitud
    $fecha = $_POST['fecha'] ?? '';
    $zona = $_POST['zona'] ?? '';
    $turno_id = $_POST['turno_id'] ?? '';
    $num_personas = $_POST['num_personas'] ?? 1;
    
    // Verificar disponibilidad
    $resultado = verificarDisponibilidad($fecha, $zona, $turno_id, $num_personas);
    
    // Devolver resultado como JSON
    header('Content-Type: application/json');
    echo json_encode($resultado);
    exit;
}

// Si no es una llamada AJAX, pero se han pasado parámetros en la URL
if (isset($_GET['fecha']) && isset($_GET['zona']) && isset($_GET['turno_id']) && isset($_GET['num_personas'])) {
    $fecha = $_GET['fecha'];
    $zona = $_GET['zona'];
    $turno_id = $_GET['turno_id'];
    $num_personas = $_GET['num_personas'];
    
    // Verificar disponibilidad
    $resultado = verificarDisponibilidad($fecha, $zona, $turno_id, $num_personas);
    
    // Guardar resultado en la sesión
    $_SESSION['disponibilidad_verificada'] = $resultado;
    
    // Redirigir a la página de confirmación
    header('Location: confirmar_reserva.php');
    exit;
}
?>
