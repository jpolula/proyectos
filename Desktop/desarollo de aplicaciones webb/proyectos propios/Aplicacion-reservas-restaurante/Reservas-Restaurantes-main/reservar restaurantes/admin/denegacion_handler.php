<?php
/**
 * Manejador de denegación de reservas
 * Este archivo se incluye en reservas.php para manejar la denegación de reservas
 * y el envío de correos de notificación
 */

// Asegurarse de que las clases necesarias estén disponibles
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/Utils/DenegacionHelper.php';

use App\Utils\DenegacionHelper;

/**
 * Procesa la denegación de una reserva y envía el correo de notificación
 * 
 * @param \PDO $pdo Conexión a la base de datos
 * @param int $reserva_id ID de la reserva a denegar
 * @return array Resultado del procesamiento con mensaje y tipo de mensaje
 */
function procesar_denegacion_reserva($pdo, $reserva_id) {
    // Actualizar el estado de la reserva a rechazada
    $stmt = $pdo->prepare("UPDATE reservas SET estado = 'rechazada' WHERE id = ?");
    
    if (!$stmt->execute([$reserva_id])) {
        return [
            'mensaje' => 'Error al rechazar la reserva.',
            'tipo_mensaje' => 'error'
        ];
    }
    
    // Obtener los datos de la reserva para el correo
    $stmt = $pdo->prepare("
        SELECT r.*, c.nombre, c.email, c.telefono, t.nombre AS turno_nombre, 
               DATE_FORMAT(r.fecha, '%d/%m/%Y') AS fecha_formateada,
               TIME_FORMAT(r.hora, '%H:%i') AS hora_formateada
        FROM reservas r
        JOIN clientes c ON r.cliente_id = c.id
        JOIN turnos t ON r.turno_id = t.id
        WHERE r.id = ?
    ");
    
    $stmt->execute([$reserva_id]);
    $reserva = $stmt->fetch();
    
    if (!$reserva) {
        return [
            'mensaje' => 'Reserva rechazada correctamente, pero no se pudo obtener la información para enviar el correo.',
            'tipo_mensaje' => 'warning'
        ];
    }
    
    // Enviar correo de cancelación usando DenegacionHelper
    $resultado = DenegacionHelper::enviarCorreoDenegacion($reserva);
    
    if ($resultado['exito']) {
        return [
            'mensaje' => "Reserva rechazada correctamente y se ha enviado un correo de notificación al cliente ({$resultado['metodo']}).",
            'tipo_mensaje' => 'success'
        ];
    } else {
        // Registrar el error
        error_log("No se pudo enviar el correo de denegación a {$reserva['email']} por ningún método.");
        
        return [
            'mensaje' => 'Reserva rechazada correctamente, pero no se pudo enviar el correo de notificación.',
            'tipo_mensaje' => 'warning'
        ];
    }
}
