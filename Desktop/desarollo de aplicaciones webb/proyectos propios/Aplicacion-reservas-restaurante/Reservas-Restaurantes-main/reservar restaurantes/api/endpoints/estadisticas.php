<?php
/**
 * API Endpoint: Estadísticas
 * 
 * Proporciona estadísticas y análisis de datos sobre las reservas del restaurante.
 */

// Prevenir acceso directo al archivo
if (!defined('API_ACCESS')) {
    header('HTTP/1.0 403 Forbidden');
    exit('Acceso prohibido');
}

// Obtener conexión a la base de datos
$pdo = getDbConnection();

// Manejar diferentes métodos HTTP
switch ($method) {
    case 'GET':
        // Obtener estadísticas
        
        // Determinar el rango de fechas
        $fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : date('Y-m-d', strtotime('-30 days'));
        $fecha_fin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : date('Y-m-d');
        
        // Estadísticas generales
        $stats = [
            'periodo' => [
                'fecha_inicio' => $fecha_inicio,
                'fecha_fin' => $fecha_fin
            ]
        ];
        
        // Total de reservas
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as total,
                   SUM(CASE WHEN estado = 'confirmada' THEN 1 ELSE 0 END) as confirmadas,
                   SUM(CASE WHEN estado = 'pendiente' THEN 1 ELSE 0 END) as pendientes,
                   SUM(CASE WHEN estado = 'rechazada' THEN 1 ELSE 0 END) as rechazadas
            FROM reservas
            WHERE fecha BETWEEN ? AND ?
        ");
        $stmt->execute([$fecha_inicio, $fecha_fin]);
        $stats['total_reservas'] = $stmt->fetch();
        
        // Total de personas
        $stmt = $pdo->prepare("
            SELECT SUM(cantidad_personas) as total_personas,
                   AVG(cantidad_personas) as promedio_personas_por_reserva
            FROM reservas
            WHERE fecha BETWEEN ? AND ? AND estado = 'confirmada'
        ");
        $stmt->execute([$fecha_inicio, $fecha_fin]);
        $stats['personas'] = $stmt->fetch();
        
        // Distribución por turno
        $stmt = $pdo->prepare("
            SELECT t.nombre as turno, COUNT(*) as total
            FROM reservas r
            JOIN turnos t ON r.turno_id = t.id
            WHERE r.fecha BETWEEN ? AND ? AND r.estado = 'confirmada'
            GROUP BY r.turno_id
            ORDER BY total DESC
        ");
        $stmt->execute([$fecha_inicio, $fecha_fin]);
        $stats['por_turno'] = $stmt->fetchAll();
        
        // Distribución por zona
        $stmt = $pdo->prepare("
            SELECT 
                zona,
                CASE 
                    WHEN zona = 'dentro' THEN 'Interior' 
                    ELSE 'Terraza' 
                END AS zona_nombre,
                COUNT(*) as total
            FROM reservas
            WHERE fecha BETWEEN ? AND ? AND estado = 'confirmada'
            GROUP BY zona
            ORDER BY total DESC
        ");
        $stmt->execute([$fecha_inicio, $fecha_fin]);
        $stats['por_zona'] = $stmt->fetchAll();
        
        // Distribución por día de la semana
        $stmt = $pdo->prepare("
            SELECT 
                DAYOFWEEK(fecha) as dia_numero,
                CASE DAYOFWEEK(fecha)
                    WHEN 1 THEN 'Domingo'
                    WHEN 2 THEN 'Lunes'
                    WHEN 3 THEN 'Martes'
                    WHEN 4 THEN 'Miércoles'
                    WHEN 5 THEN 'Jueves'
                    WHEN 6 THEN 'Viernes'
                    WHEN 7 THEN 'Sábado'
                END as dia_semana,
                COUNT(*) as total
            FROM reservas
            WHERE fecha BETWEEN ? AND ? AND estado = 'confirmada'
            GROUP BY dia_numero
            ORDER BY dia_numero
        ");
        $stmt->execute([$fecha_inicio, $fecha_fin]);
        $stats['por_dia_semana'] = $stmt->fetchAll();
        
        // Distribución por mes
        $stmt = $pdo->prepare("
            SELECT 
                MONTH(fecha) as mes_numero,
                CASE MONTH(fecha)
                    WHEN 1 THEN 'Enero'
                    WHEN 2 THEN 'Febrero'
                    WHEN 3 THEN 'Marzo'
                    WHEN 4 THEN 'Abril'
                    WHEN 5 THEN 'Mayo'
                    WHEN 6 THEN 'Junio'
                    WHEN 7 THEN 'Julio'
                    WHEN 8 THEN 'Agosto'
                    WHEN 9 THEN 'Septiembre'
                    WHEN 10 THEN 'Octubre'
                    WHEN 11 THEN 'Noviembre'
                    WHEN 12 THEN 'Diciembre'
                END as mes_nombre,
                COUNT(*) as total
            FROM reservas
            WHERE fecha BETWEEN ? AND ? AND estado = 'confirmada'
            GROUP BY mes_numero
            ORDER BY mes_numero
        ");
        $stmt->execute([$fecha_inicio, $fecha_fin]);
        $stats['por_mes'] = $stmt->fetchAll();
        
        // Horas más populares
        $stmt = $pdo->prepare("
            SELECT 
                hora,
                COUNT(*) as total
            FROM reservas
            WHERE fecha BETWEEN ? AND ? AND estado = 'confirmada'
            GROUP BY hora
            ORDER BY total DESC
            LIMIT 5
        ");
        $stmt->execute([$fecha_inicio, $fecha_fin]);
        $stats['horas_populares'] = $stmt->fetchAll();
        
        // Clientes más frecuentes
        $stmt = $pdo->prepare("
            SELECT 
                c.id,
                c.nombre,
                c.email,
                COUNT(*) as total_reservas
            FROM reservas r
            JOIN clientes c ON r.cliente_id = c.id
            WHERE r.fecha BETWEEN ? AND ? AND r.estado = 'confirmada'
            GROUP BY r.cliente_id
            ORDER BY total_reservas DESC
            LIMIT 10
        ");
        $stmt->execute([$fecha_inicio, $fecha_fin]);
        $stats['clientes_frecuentes'] = $stmt->fetchAll();
        
        // Ocupación por día
        if (isset($_GET['detalle_diario']) && $_GET['detalle_diario'] == 'true') {
            $stmt = $pdo->prepare("
                SELECT 
                    fecha,
                    COUNT(*) as total_reservas,
                    SUM(cantidad_personas) as total_personas
                FROM reservas
                WHERE fecha BETWEEN ? AND ? AND estado = 'confirmada'
                GROUP BY fecha
                ORDER BY fecha
            ");
            $stmt->execute([$fecha_inicio, $fecha_fin]);
            $stats['ocupacion_diaria'] = $stmt->fetchAll();
        }
        
        jsonResponse($stats);
        break;
        
    default:
        jsonResponse(['error' => 'Método no permitido'], 405);
        break;
}
