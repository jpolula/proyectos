<?php
/**
 * API Endpoint: Disponibilidad
 * 
 * Proporciona información sobre la disponibilidad de fechas, turnos y zonas para reservas.
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
        // Verificar disponibilidad
        if (isset($_GET['fecha']) && isset($_GET['turno_id']) && isset($_GET['zona'])) {
            // Verificar disponibilidad para una fecha, turno y zona específicos
            $fecha = $_GET['fecha'];
            $turno_id = (int)$_GET['turno_id'];
            $zona = $_GET['zona'];
            $num_personas = isset($_GET['num_personas']) ? (int)$_GET['num_personas'] : 1;
            
            // Verificar si el día está disponible
            $stmt = $pdo->prepare("
                SELECT disponible 
                FROM dias_disponibles 
                WHERE fecha = ? AND turno_id = ? AND zona = ?
            ");
            $stmt->execute([$fecha, $turno_id, $zona]);
            $disponible = $stmt->fetchColumn();
            
            if ($disponible === false) {
                jsonResponse([
                    'disponible' => false,
                    'mensaje' => 'La fecha, turno o zona seleccionada no está disponible'
                ]);
            }
            
            // Verificar si hay bloqueos
            $stmt = $pdo->prepare("
                SELECT COUNT(*) 
                FROM bloqueos 
                WHERE fecha = ? AND zona = ? AND turno_id = ?
            ");
            $stmt->execute([$fecha, $zona, $turno_id]);
            $bloqueado = ($stmt->fetchColumn() > 0);
            
            if ($bloqueado) {
                jsonResponse([
                    'disponible' => false,
                    'mensaje' => 'La fecha seleccionada está bloqueada para reservas'
                ]);
            }
            
            // Obtener la capacidad máxima
            $stmt = $pdo->prepare("
                SELECT aforo_maximo 
                FROM capacidad 
                WHERE fecha = ? AND zona = ? AND turno_id = ?
            ");
            $stmt->execute([$fecha, $zona, $turno_id]);
            $aforo_maximo = $stmt->fetchColumn();
            
            if ($aforo_maximo === false) {
                $campo_capacidad = 'capacidad_' . $zona . '_' . $turno_id;
                $stmt = $pdo->prepare("SELECT $campo_capacidad FROM configuracion WHERE id = 1");
                $stmt->execute();
                $aforo_maximo = $stmt->fetchColumn();
                
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
            
            // Calcular si hay suficiente aforo disponible
            $aforo_disponible = $aforo_maximo - $total_reservado;
            $hay_disponibilidad = $aforo_disponible >= $num_personas;
            
            // Obtener el número máximo de personas sin aprobación
            $stmt = $pdo->prepare("SELECT max_personas_sin_aprobacion FROM configuracion WHERE id = 1");
            $stmt->execute();
            $max_personas_sin_aprobacion = $stmt->fetchColumn() ?: 8;
            
            // Determinar si la reserva requeriría aprobación
            $requiere_aprobacion = $num_personas > $max_personas_sin_aprobacion;
            
            jsonResponse([
                'disponible' => $hay_disponibilidad,
                'aforo_maximo' => $aforo_maximo,
                'aforo_ocupado' => $total_reservado,
                'aforo_disponible' => $aforo_disponible,
                'personas_solicitadas' => $num_personas,
                'requiere_aprobacion' => $requiere_aprobacion,
                'mensaje' => $hay_disponibilidad 
                    ? 'Hay disponibilidad para ' . $num_personas . ' personas' 
                    : 'No hay suficiente aforo disponible para ' . $num_personas . ' personas'
            ]);
        } elseif (isset($_GET['mes']) && isset($_GET['anio'])) {
            // Obtener días disponibles para un mes y año específicos
            $mes = (int)$_GET['mes'];
            $anio = (int)$_GET['anio'];
            
            if ($mes < 1 || $mes > 12 || $anio < 2023 || $anio > 2030) {
                jsonResponse(['error' => 'Mes o año no válidos'], 400);
            }
            
            // Construir el rango de fechas para el mes
            $fecha_inicio = sprintf('%04d-%02d-01', $anio, $mes);
            $fecha_fin = date('Y-m-t', strtotime($fecha_inicio));
            
            // Obtener días disponibles en el rango
            $stmt = $pdo->prepare("
                SELECT DISTINCT fecha 
                FROM dias_disponibles 
                WHERE fecha BETWEEN ? AND ? AND disponible = 1
                ORDER BY fecha
            ");
            $stmt->execute([$fecha_inicio, $fecha_fin]);
            $dias_disponibles = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            // Obtener información detallada de disponibilidad
            $disponibilidad = [];
            
            foreach ($dias_disponibles as $fecha) {
                // Obtener turnos y zonas disponibles para esta fecha
                $stmt = $pdo->prepare("
                    SELECT d.turno_id, t.nombre AS turno_nombre, d.zona,
                           CASE 
                               WHEN d.zona = 'dentro' THEN 'Interior' 
                               ELSE 'Terraza' 
                           END AS zona_nombre
                    FROM dias_disponibles d
                    JOIN turnos t ON d.turno_id = t.id
                    WHERE d.fecha = ? AND d.disponible = 1
                    ORDER BY d.turno_id, d.zona
                ");
                $stmt->execute([$fecha]);
                $opciones = $stmt->fetchAll();
                
                $disponibilidad[$fecha] = [
                    'fecha' => $fecha,
                    'fecha_formateada' => date('d/m/Y', strtotime($fecha)),
                    'opciones' => $opciones
                ];
            }
            
            jsonResponse([
                'mes' => $mes,
                'anio' => $anio,
                'dias_disponibles' => $dias_disponibles,
                'disponibilidad' => $disponibilidad
            ]);
        } else {
            // Obtener información general de disponibilidad
            
            // Obtener rango de fechas disponibles
            $stmt = $pdo->query("
                SELECT MIN(fecha) as fecha_min, MAX(fecha) as fecha_max
                FROM dias_disponibles
                WHERE disponible = 1
            ");
            $rango = $stmt->fetch();
            
            // Obtener turnos disponibles
            $stmt = $pdo->query("
                SELECT id, nombre, hora_inicio, hora_fin
                FROM turnos
                ORDER BY id
            ");
            $turnos = $stmt->fetchAll();
            
            // Obtener zonas disponibles
            $zonas = [
                ['id' => 'dentro', 'nombre' => 'Interior'],
                ['id' => 'fuera', 'nombre' => 'Terraza']
            ];
            
            // Obtener configuración general
            $stmt = $pdo->query("SELECT * FROM configuracion WHERE id = 1");
            $configuracion = $stmt->fetch();
            
            jsonResponse([
                'rango_fechas' => $rango,
                'turnos' => $turnos,
                'zonas' => $zonas,
                'configuracion' => [
                    'max_personas_sin_aprobacion' => $configuracion['max_personas_sin_aprobacion'] ?? 8,
                    'capacidad_dentro_mediodia' => $configuracion['capacidad_dentro_mediodia'] ?? 30,
                    'capacidad_fuera_mediodia' => $configuracion['capacidad_fuera_mediodia'] ?? 20,
                    'capacidad_dentro_noche' => $configuracion['capacidad_dentro_noche'] ?? 30,
                    'capacidad_fuera_noche' => $configuracion['capacidad_fuera_noche'] ?? 20
                ]
            ]);
        }
        break;
        
    default:
        jsonResponse(['error' => 'Método no permitido'], 405);
        break;
}
