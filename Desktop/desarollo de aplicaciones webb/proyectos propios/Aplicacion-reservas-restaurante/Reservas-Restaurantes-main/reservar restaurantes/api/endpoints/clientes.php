<?php
/**
 * API Endpoint: Clientes
 * 
 * Gestiona las operaciones CRUD para los clientes del restaurante.
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
        // Obtener clientes
        if ($id) {
            // Obtener un cliente específico por ID
            $stmt = $pdo->prepare("
                SELECT c.*, 
                       (SELECT COUNT(*) FROM reservas WHERE cliente_id = c.id) as total_reservas
                FROM clientes c
                WHERE c.id = ?
            ");
            $stmt->execute([$id]);
            $cliente = $stmt->fetch();
            
            if ($cliente) {
                // Obtener las últimas reservas del cliente
                $stmt = $pdo->prepare("
                    SELECT r.*, t.nombre AS turno_nombre 
                    FROM reservas r
                    JOIN turnos t ON r.turno_id = t.id
                    WHERE r.cliente_id = ?
                    ORDER BY r.fecha DESC, r.hora ASC
                    LIMIT 5
                ");
                $stmt->execute([$id]);
                $reservas = $stmt->fetchAll();
                
                $cliente['ultimas_reservas'] = $reservas;
                
                jsonResponse($cliente);
            } else {
                jsonResponse(['error' => 'Cliente no encontrado'], 404);
            }
        } else {
            // Obtener todos los clientes con filtros opcionales
            $query = "SELECT * FROM clientes WHERE 1=1";
            $params = [];
            
            // Filtrar por nombre
            if (isset($_GET['nombre'])) {
                $query .= " AND nombre LIKE ?";
                $params[] = '%' . $_GET['nombre'] . '%';
            }
            
            // Filtrar por email
            if (isset($_GET['email'])) {
                $query .= " AND email LIKE ?";
                $params[] = '%' . $_GET['email'] . '%';
            }
            
            // Filtrar por teléfono
            if (isset($_GET['telefono'])) {
                $query .= " AND telefono LIKE ?";
                $params[] = '%' . $_GET['telefono'] . '%';
            }
            
            // Ordenar
            $query .= " ORDER BY nombre ASC";
            
            // Limitar resultados
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 100;
            $query .= " LIMIT ?";
            $params[] = $limit;
            
            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            $clientes = $stmt->fetchAll();
            
            jsonResponse(['clientes' => $clientes, 'total' => count($clientes)]);
        }
        break;
        
    case 'POST':
        // Crear un nuevo cliente
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Validar datos requeridos
        $requiredFields = ['nombre', 'email', 'telefono'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                jsonResponse(['error' => "El campo '$field' es requerido"], 400);
            }
        }
        
        // Verificar si el email ya existe
        $stmt = $pdo->prepare("SELECT id FROM clientes WHERE email = ?");
        $stmt->execute([$data['email']]);
        $existingClient = $stmt->fetch();
        
        if ($existingClient) {
            jsonResponse(['error' => 'Ya existe un cliente con este email'], 400);
        }
        
        // Crear el cliente
        $stmt = $pdo->prepare("
            INSERT INTO clientes (nombre, email, telefono) 
            VALUES (?, ?, ?)
        ");
        $stmt->execute([
            $data['nombre'],
            $data['email'],
            $data['telefono']
        ]);
        
        $cliente_id = $pdo->lastInsertId();
        
        // Obtener el cliente creado
        $stmt = $pdo->prepare("SELECT * FROM clientes WHERE id = ?");
        $stmt->execute([$cliente_id]);
        $cliente = $stmt->fetch();
        
        jsonResponse(['message' => 'Cliente creado correctamente', 'cliente' => $cliente], 201);
        break;
        
    case 'PUT':
        // Actualizar un cliente existente
        if (!$id) {
            jsonResponse(['error' => 'Se requiere un ID de cliente'], 400);
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Verificar si el cliente existe
        $stmt = $pdo->prepare("SELECT * FROM clientes WHERE id = ?");
        $stmt->execute([$id]);
        $cliente = $stmt->fetch();
        
        if (!$cliente) {
            jsonResponse(['error' => 'Cliente no encontrado'], 404);
        }
        
        // Verificar si el nuevo email ya existe (si se está cambiando)
        if (isset($data['email']) && $data['email'] !== $cliente['email']) {
            $stmt = $pdo->prepare("SELECT id FROM clientes WHERE email = ? AND id != ?");
            $stmt->execute([$data['email'], $id]);
            $existingClient = $stmt->fetch();
            
            if ($existingClient) {
                jsonResponse(['error' => 'Ya existe otro cliente con este email'], 400);
            }
        }
        
        // Construir la consulta de actualización
        $updateFields = [];
        $params = [];
        
        // Campos que se pueden actualizar
        $allowedFields = ['nombre', 'email', 'telefono'];
        
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
        
        $query = "UPDATE clientes SET " . implode(', ', $updateFields) . " WHERE id = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        
        // Obtener el cliente actualizado
        $stmt = $pdo->prepare("SELECT * FROM clientes WHERE id = ?");
        $stmt->execute([$id]);
        $clienteActualizado = $stmt->fetch();
        
        jsonResponse(['message' => 'Cliente actualizado correctamente', 'cliente' => $clienteActualizado]);
        break;
        
    case 'DELETE':
        // Eliminar un cliente
        if (!$id) {
            jsonResponse(['error' => 'Se requiere un ID de cliente'], 400);
        }
        
        // Verificar si el cliente existe
        $stmt = $pdo->prepare("SELECT * FROM clientes WHERE id = ?");
        $stmt->execute([$id]);
        $cliente = $stmt->fetch();
        
        if (!$cliente) {
            jsonResponse(['error' => 'Cliente no encontrado'], 404);
        }
        
        // Verificar si el cliente tiene reservas
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM reservas WHERE cliente_id = ?");
        $stmt->execute([$id]);
        $reservasCount = $stmt->fetchColumn();
        
        if ($reservasCount > 0) {
            jsonResponse(['error' => 'No se puede eliminar el cliente porque tiene reservas asociadas'], 400);
        }
        
        // Eliminar el cliente
        $stmt = $pdo->prepare("DELETE FROM clientes WHERE id = ?");
        $stmt->execute([$id]);
        
        jsonResponse(['message' => 'Cliente eliminado correctamente']);
        break;
        
    default:
        jsonResponse(['error' => 'Método no permitido'], 405);
        break;
}
