<?php
/**
 * API Endpoint: Reservas
 * 
 * Gestiona las operaciones CRUD para las reservas del restaurante.
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
        // Obtener reservas
        if ($id) {
            // Obtener una reserva específica por ID
            $stmt = $pdo->prepare("
                SELECT r.*, c.nombre, c.email, c.telefono, t.nombre AS turno_nombre 
                FROM reservas r
                JOIN clientes c ON r.cliente_id = c.id
                JOIN turnos t ON r.turno_id = t.id
                WHERE r.id = ?
            ");
            $stmt->execute([$id]);
            $reserva = $stmt->fetch();
            
            if ($reserva) {
                jsonResponse($reserva);
            } else {
                jsonResponse(['error' => 'Reserva no encontrada'], 404);
            }
        } else {
            // Obtener todas las reservas con filtros opcionales
            $query = "
                SELECT r.*, c.nombre, c.email, c.telefono, t.nombre AS turno_nombre 
                FROM reservas r
                JOIN clientes c ON r.cliente_id = c.id
                JOIN turnos t ON r.turno_id = t.id
                WHERE 1=1
            ";
            $params = [];
            
            // Filtrar por fecha
            if (isset($_GET['fecha'])) {
                $query .= " AND r.fecha = ?";
                $params[] = $_GET['fecha'];
            }
            
            // Filtrar por estado
            if (isset($_GET['estado'])) {
                $query .= " AND r.estado = ?";
                $params[] = $_GET['estado'];
            }
            
            // Filtrar por zona
            if (isset($_GET['zona'])) {
                $query .= " AND r.zona = ?";
                $params[] = $_GET['zona'];
            }
            
            // Filtrar por turno
            if (isset($_GET['turno_id'])) {
                $query .= " AND r.turno_id = ?";
                $params[] = $_GET['turno_id'];
            }
            
            // Ordenar
            $query .= " ORDER BY r.fecha DESC, r.hora ASC";
            
            // Limitar resultados
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 100;
            $query .= " LIMIT ?";
            $params[] = $limit;
            
            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            $reservas = $stmt->fetchAll();
            
            jsonResponse(['reservas' => $reservas, 'total' => count($reservas)]);
        }
        break;
        
    case 'POST':
        // Crear una nueva reserva
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Validar datos requeridos
        $requiredFields = ['nombre', 'email', 'telefono', 'fecha', 'turno_id', 'zona', 'hora', 'cantidad_personas'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                jsonResponse(['error' => "El campo '$field' es requerido"], 400);
            }
        }
        
        // Iniciar transacción
        $pdo->beginTransaction();
        
        try {
            // Verificar si el cliente ya existe
            $stmt = $pdo->prepare("SELECT id FROM clientes WHERE email = ?");
            $stmt->execute([$data['email']]);
            $cliente = $stmt->fetch();
            
            if ($cliente) {
                $cliente_id = $cliente['id'];
                
                // Actualizar datos del cliente
                $stmt = $pdo->prepare("
                    UPDATE clientes 
                    SET nombre = ?, telefono = ? 
                    WHERE id = ?
                ");
                $stmt->execute([$data['nombre'], $data['telefono'], $cliente_id]);
            } else {
                // Crear nuevo cliente
                $stmt = $pdo->prepare("
                    INSERT INTO clientes (nombre, email, telefono) 
                    VALUES (?, ?, ?)
                ");
                $stmt->execute([$data['nombre'], $data['email'], $data['telefono']]);
                $cliente_id = $pdo->lastInsertId();
            }
            
            // Verificar disponibilidad
            $stmt = $pdo->prepare("
                SELECT disponible 
                FROM dias_disponibles 
                WHERE fecha = ? AND turno_id = ? AND zona = ? AND disponible = 1
            ");
            $stmt->execute([$data['fecha'], $data['turno_id'], $data['zona']]);
            $disponible = $stmt->fetchColumn();
            
            if (!$disponible) {
                $pdo->rollBack();
                jsonResponse(['error' => 'La fecha, turno o zona seleccionada no está disponible'], 400);
            }
            
            // Verificar capacidad
            $stmt = $pdo->prepare("
                SELECT SUM(cantidad_personas) as total_reservado
                FROM reservas
                WHERE fecha = ? AND zona = ? AND turno_id = ? AND estado = 'confirmada'
            ");
            $stmt->execute([$data['fecha'], $data['zona'], $data['turno_id']]);
            $resultado = $stmt->fetch();
            $total_reservado = $resultado['total_reservado'] ?: 0;
            
            // Obtener capacidad máxima
            $stmt = $pdo->prepare("
                SELECT aforo_maximo 
                FROM capacidad 
                WHERE fecha = ? AND zona = ? AND turno_id = ?
            ");
            $stmt->execute([$data['fecha'], $data['zona'], $data['turno_id']]);
            $aforo_maximo = $stmt->fetchColumn();
            
            if ($aforo_maximo === false) {
                $campo_capacidad = 'capacidad_' . $data['zona'] . '_' . $data['turno_id'];
                $stmt = $pdo->prepare("SELECT $campo_capacidad FROM configuracion WHERE id = 1");
                $stmt->execute();
                $aforo_maximo = $stmt->fetchColumn();
                
                if ($aforo_maximo === false) {
                    $aforo_maximo = ($data['zona'] == 'dentro') ? 30 : 20;
                }
            }
            
            $aforo_disponible = $aforo_maximo - $total_reservado;
            
            if ($aforo_disponible < $data['cantidad_personas']) {
                $pdo->rollBack();
                jsonResponse(['error' => 'No hay suficiente capacidad disponible'], 400);
            }
            
            // Obtener el número máximo de personas sin aprobación
            $stmt = $pdo->prepare("SELECT max_personas_sin_aprobacion FROM configuracion WHERE id = 1");
            $stmt->execute();
            $max_personas_sin_aprobacion = $stmt->fetchColumn() ?: 8;
            
            // Determinar el estado de la reserva
            $estado = $data['cantidad_personas'] <= $max_personas_sin_aprobacion ? 'confirmada' : 'pendiente';
            
            // Crear la reserva
            $stmt = $pdo->prepare("
                INSERT INTO reservas (
                    cliente_id, fecha, zona, turno_id, hora, cantidad_personas, 
                    observaciones, necesidades_especiales, tiene_alergenos, estado
                ) VALUES (
                    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
                )
            ");
            $stmt->execute([
                $cliente_id,
                $data['fecha'],
                $data['zona'],
                $data['turno_id'],
                $data['hora'],
                $data['cantidad_personas'],
                $data['observaciones'] ?? '',
                $data['necesidades_especiales'] ?? '',
                isset($data['tiene_alergenos']) ? 1 : 0,
                $estado
            ]);
            
            $reserva_id = $pdo->lastInsertId();
            
            // Confirmar transacción
            $pdo->commit();
            
            // Obtener la reserva creada
            $stmt = $pdo->prepare("
                SELECT r.*, c.nombre, c.email, c.telefono, t.nombre AS turno_nombre 
                FROM reservas r
                JOIN clientes c ON r.cliente_id = c.id
                JOIN turnos t ON r.turno_id = t.id
                WHERE r.id = ?
            ");
            $stmt->execute([$reserva_id]);
            $reserva = $stmt->fetch();
            
            jsonResponse(['message' => 'Reserva creada correctamente', 'reserva' => $reserva], 201);
            
        } catch (PDOException $e) {
            $pdo->rollBack();
            jsonResponse(['error' => 'Error al crear la reserva: ' . $e->getMessage()], 500);
        }
        break;
        
    case 'PUT':
        // Actualizar una reserva existente
        if (!$id) {
            jsonResponse(['error' => 'Se requiere un ID de reserva'], 400);
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Verificar si la reserva existe
        $stmt = $pdo->prepare("SELECT * FROM reservas WHERE id = ?");
        $stmt->execute([$id]);
        $reserva = $stmt->fetch();
        
        if (!$reserva) {
            jsonResponse(['error' => 'Reserva no encontrada'], 404);
        }
        
        // Construir la consulta de actualización
        $updateFields = [];
        $params = [];
        
        // Campos que se pueden actualizar
        $allowedFields = [
            'fecha', 'zona', 'turno_id', 'hora', 'cantidad_personas', 
            'observaciones', 'necesidades_especiales', 'tiene_alergenos', 'estado'
        ];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updateFields[] = "$field = ?";
                $params[] = $data[$field];
            }
        }
        
        if (empty($updateFields)) {
            jsonResponse(['error' => 'No se proporcionaron campos para actualizar'], 400);
        }
        
        // Añadir el ID al final de los parámetros
        $params[] = $id;
        
        $query = "UPDATE reservas SET " . implode(', ', $updateFields) . " WHERE id = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        
        // Obtener la reserva actualizada
        $stmt = $pdo->prepare("
            SELECT r.*, c.nombre, c.email, c.telefono, t.nombre AS turno_nombre 
            FROM reservas r
            JOIN clientes c ON r.cliente_id = c.id
            JOIN turnos t ON r.turno_id = t.id
            WHERE r.id = ?
        ");
        $stmt->execute([$id]);
        $reservaActualizada = $stmt->fetch();
        
        jsonResponse(['message' => 'Reserva actualizada correctamente', 'reserva' => $reservaActualizada]);
        break;
        
    case 'DELETE':
        // Eliminar una reserva
        if (!$id) {
            jsonResponse(['error' => 'Se requiere un ID de reserva'], 400);
        }
        
        // Verificar si la reserva existe
        $stmt = $pdo->prepare("SELECT * FROM reservas WHERE id = ?");
        $stmt->execute([$id]);
        $reserva = $stmt->fetch();
        
        if (!$reserva) {
            jsonResponse(['error' => 'Reserva no encontrada'], 404);
        }
        
        // Eliminar la reserva
        $stmt = $pdo->prepare("DELETE FROM reservas WHERE id = ?");
        $stmt->execute([$id]);
        
        jsonResponse(['message' => 'Reserva eliminada correctamente']);
        break;
        
    default:
        jsonResponse(['error' => 'Método no permitido'], 405);
        break;
}
