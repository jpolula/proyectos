<?php
/**
 * Script para desactivar automáticamente las reservas de días pasados
 * 
 * Este script puede ser ejecutado manualmente o programado para ejecutarse
 * automáticamente mediante una tarea cron o el programador de tareas de Windows.
 * 
 * NOTA: Las reservas ya no se eliminan, solo se desactivan para mantener un historial.
 */

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
    
    // Guardar en archivo de log
    file_put_contents(__DIR__ . '/logs/limpieza_reservas.log', $log_message, FILE_APPEND);
    
    // Mostrar en la salida si se ejecuta en modo CLI
    if (php_sapi_name() === 'cli') {
        echo $log_message;
    }
}

// Crear directorio de logs si no existe
if (!is_dir(__DIR__ . '/logs')) {
    mkdir(__DIR__ . '/logs', 0755, true);
}

// Obtener la fecha actual
$fecha_actual = date('Y-m-d');

try {
    // Conexión a la base de datos
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // Iniciar transacción
    $pdo->beginTransaction();
    
    // Contar cuántas reservas se van a desactivar
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM reservas WHERE fecha < ? AND estado != 'archivada'");
    $stmt->execute([$fecha_actual]);
    $total_reservas = $stmt->fetchColumn();
    
    if ($total_reservas > 0) {
        // Desactivar reservas de días pasados cambiando su estado a 'archivada'
        $stmt = $pdo->prepare("UPDATE reservas SET estado = 'archivada' WHERE fecha < ? AND estado != 'archivada'");
        $stmt->execute([$fecha_actual]);
        $reservas_desactivadas = $stmt->rowCount();
        
        // Confirmar la transacción
        $pdo->commit();
        
        log_message("Se han archivado $reservas_desactivadas reservas de días anteriores a $fecha_actual");
    } else {
        // No hay reservas para desactivar
        $pdo->commit();
        log_message("No se encontraron reservas de días anteriores a $fecha_actual para archivar");
    }
    
    // Mostrar mensaje de éxito
    if (php_sapi_name() !== 'cli') {
        echo "<div style='background-color: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 20px;'>";
        echo "<h3>Archivado de reservas completado</h3>";
        echo "<p>Se han archivado $reservas_desactivadas reservas de días anteriores a $fecha_actual</p>";
        echo "<p>Estas reservas ahora están disponibles en el historial de reservas.</p>";
        echo "<p><a href='admin/index.php'>Volver al panel de administración</a></p>";
        echo "</div>";
    }
    
} catch (PDOException $e) {
    // Revertir la transacción en caso de error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    $error_message = "Error al archivar reservas pasadas: " . $e->getMessage();
    log_message($error_message);
    
    // Mostrar mensaje de error
    if (php_sapi_name() !== 'cli') {
        echo "<div style='background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 20px;'>";
        echo "<h3>Error al archivar reservas</h3>";
        echo "<p>$error_message</p>";
        echo "<p><a href='admin/index.php'>Volver al panel de administración</a></p>";
        echo "</div>";
    }
}
