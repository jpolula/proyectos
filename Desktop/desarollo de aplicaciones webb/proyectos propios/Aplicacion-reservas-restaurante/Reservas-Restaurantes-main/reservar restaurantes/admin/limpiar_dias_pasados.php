<?php
/**
 * Script para desactivar automáticamente los días pasados en el calendario
 * y archivar las reservas de esos días
 * 
 * NOTA: Las reservas ya no se eliminan, solo se archivan para mantener un historial.
 */

// Incluir el sistema de notificaciones
require_once 'notificaciones.php';

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

// Función para registrar mensajes de log
function log_message($message) {
    $date = date('Y-m-d H:i:s');
    $log_message = "[$date] $message" . PHP_EOL;
    
    // Crear directorio de logs si no existe
    $log_dir = __DIR__ . '/../logs';
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0755, true);
    }
    
    // Guardar en archivo de log
    file_put_contents($log_dir . '/limpieza_dias_pasados.log', $log_message, FILE_APPEND);
    
    // Mostrar en la salida si se ejecuta en modo CLI
    if (php_sapi_name() === 'cli') {
        echo $log_message;
    }
}

try {
    // Conexión a la base de datos
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // Obtener la fecha actual
    $fecha_actual = date('Y-m-d');
    
    // Iniciar transacción
    $pdo->beginTransaction();
    
    // 1. Obtener todos los días disponibles que son anteriores a la fecha actual
    $stmt = $pdo->prepare("
        SELECT DISTINCT fecha 
        FROM dias_disponibles 
        WHERE fecha < ? AND disponible = 1
    ");
    $stmt->execute([$fecha_actual]);
    $dias_pasados = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $dias_desactivados = 0;
    $reservas_archivadas = 0;
    
    if (!empty($dias_pasados)) {
        // 2. Para cada día pasado, desactivarlo y eliminar sus reservas
        foreach ($dias_pasados as $dia) {
            // Desactivar el día en todas las zonas y turnos
            $stmt = $pdo->prepare("
                UPDATE dias_disponibles 
                SET disponible = 0 
                WHERE fecha = ?
            ");
            $stmt->execute([$dia]);
            $dias_desactivados += $stmt->rowCount();
            
            // Archivar las reservas de ese día (cambiar estado a 'archivada')
            $stmt = $pdo->prepare("
                UPDATE reservas 
                SET estado = 'archivada' 
                WHERE fecha = ? AND estado != 'archivada'
            ");
            $stmt->execute([$dia]);
            $reservas_archivadas += $stmt->rowCount();
            
            log_message("Día desactivado: $dia - Reservas archivadas: " . $stmt->rowCount());
        }
        
        // Confirmar la transacción
        $pdo->commit();
        
        log_message("Proceso completado: $dias_desactivados días desactivados, $reservas_archivadas reservas archivadas");
        
        // Añadir notificación al panel de administración
        if ($dias_desactivados > 0 || $reservas_archivadas > 0) {
            $mensaje = "Se han desactivado $dias_desactivados días pasados y archivado $reservas_archivadas reservas antiguas. Estas reservas ahora están disponibles en el historial.";
            agregar_notificacion($mensaje, 'success');
        }
        
        // Retornar información para uso en otros scripts
        return [
            'dias_desactivados' => $dias_desactivados,
            'reservas_archivadas' => $reservas_archivadas,
            'dias_procesados' => $dias_pasados
        ];
    } else {
        // No hay días pasados que estén activos
        $pdo->commit();
        log_message("No se encontraron días pasados disponibles para desactivar");
        
        // Retornar información para uso en otros scripts
        return [
            'dias_desactivados' => 0,
            'reservas_archivadas' => 0,
            'dias_procesados' => []
        ];
    }
} catch (PDOException $e) {
    // Revertir la transacción en caso de error
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    $error_message = "Error al desactivar días pasados: " . $e->getMessage();
    log_message($error_message);
    
    // Añadir notificación de error al panel de administración
    agregar_notificacion("Error al desactivar días pasados: " . $e->getMessage(), 'danger');
    
    // Retornar información para uso en otros scripts
    return [
        'dias_desactivados' => 0,
        'reservas_archivadas' => 0,
        'error' => $error_message
    ];
}
