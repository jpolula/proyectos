<?php
/**
 * verificar_aforo_admin.php
 * 
 * Este script verifica si hay suficiente aforo disponible para confirmar una reserva pendiente
 * desde el panel de administración.
 */

/**
 * Verifica si hay suficiente aforo disponible para confirmar una reserva
 * 
 * @param PDO $pdo Conexión a la base de datos
 * @param int $reserva_id ID de la reserva a confirmar
 * @return array Resultado de la verificación con las claves 'disponible', 'mensaje', 'aforo_disponible' y 'num_personas'
 */
function verificarAforoParaConfirmar($pdo, $reserva_id) {
    try {
        // Obtener los datos de la reserva
        $stmt = $pdo->prepare("
            SELECT r.*, t.nombre AS turno_nombre
            FROM reservas r
            JOIN turnos t ON r.turno_id = t.id
            WHERE r.id = ?
        ");
        $stmt->execute([$reserva_id]);
        $reserva = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$reserva) {
            return [
                'disponible' => false,
                'mensaje' => 'No se encontró la reserva.',
                'aforo_disponible' => 0,
                'num_personas' => 0
            ];
        }
        
        $fecha = $reserva['fecha'];
        $zona = $reserva['zona'];
        $turno_id = $reserva['turno_id'];
        $num_personas = $reserva['cantidad_personas'];
        
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
            $turno_nombre = strtolower($reserva['turno_nombre']) === 'mediodía' || 
                           strtolower($reserva['turno_nombre']) === 'mediodia' ? 'mediodia' : 'noche';
            $campo_capacidad = 'capacidad_' . $zona . '_' . $turno_nombre;
            
            $stmt = $pdo->prepare("SELECT $campo_capacidad FROM configuracion WHERE id = 1");
            $stmt->execute();
            $aforo_maximo = $stmt->fetchColumn();
            
            // Si aún no hay valor, usar un valor por defecto
            if ($aforo_maximo === false) {
                $aforo_maximo = ($zona == 'dentro') ? 30 : 20;
            }
        }
        
        // Obtener el número de personas ya reservadas (excluyendo la reserva actual)
        $stmt = $pdo->prepare("
            SELECT SUM(cantidad_personas) as total_reservado
            FROM reservas
            WHERE fecha = ? AND zona = ? AND turno_id = ? AND estado = 'confirmada' AND id != ?
        ");
        $stmt->execute([$fecha, $zona, $turno_id, $reserva_id]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        $total_reservado = $resultado['total_reservado'] ?: 0;
        
        // Calcular si hay suficiente aforo disponible
        $aforo_disponible = $aforo_maximo - $total_reservado;
        $hay_disponibilidad = $aforo_disponible >= $num_personas;
        
        return [
            'disponible' => $hay_disponibilidad,
            'mensaje' => $hay_disponibilidad 
                ? "Hay suficiente aforo disponible para confirmar la reserva."
                : "No hay suficiente aforo disponible para confirmar esta reserva.",
            'aforo_disponible' => $aforo_disponible,
            'num_personas' => $num_personas,
            'aforo_maximo' => $aforo_maximo,
            'total_reservado' => $total_reservado
        ];
        
    } catch (PDOException $e) {
        return [
            'disponible' => false,
            'mensaje' => 'Error al verificar el aforo disponible: ' . $e->getMessage(),
            'aforo_disponible' => 0,
            'num_personas' => 0
        ];
    }
}
?>
