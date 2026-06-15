<?php
/**
 * Configuración de la API para n8n
 * 
 * Este archivo contiene la configuración necesaria para la API
 * que permite la integración con n8n.
 */

// Prevenir acceso directo al archivo
if (!defined('API_ACCESS')) {
    header('HTTP/1.0 403 Forbidden');
    exit('Acceso prohibido');
}

// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_NAME', 'restaurante_reservas');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Configuración de la API
define('API_VERSION', '1.0');
define('API_DEBUG', true);
define('API_TOKEN', '6c2d4d056c436576a493230d5770769a'); // Cambia esto por un token seguro

// Configuración de CORS para permitir solicitudes desde n8n
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Content-Type: application/json; charset=UTF-8');

// Si es una solicitud OPTIONS (preflight), terminar aquí
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Función para conectar a la base de datos
function getDbConnection() {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    
    try {
        return new PDO($dsn, DB_USER, DB_PASS, $options);
    } catch (PDOException $e) {
        // En producción, registrar el error en lugar de mostrarlo
        if (API_DEBUG) {
            echo json_encode(['error' => 'Error de conexión: ' . $e->getMessage()]);
        } else {
            echo json_encode(['error' => 'Error de conexión a la base de datos']);
        }
        exit;
    }
}

// Función para validar el token de API
function validateApiToken() {
    $headers = getallheaders();
    $authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';
    
    // Verificar si el encabezado de autorización existe y tiene el formato correcto
    if (empty($authHeader) || !preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
        header('HTTP/1.0 401 Unauthorized');
        echo json_encode(['error' => 'Token de autorización no proporcionado o inválido']);
        exit;
    }
    
    $token = $matches[1];
    
    // Verificar si el token coincide con el token configurado
    if ($token !== API_TOKEN) {
        header('HTTP/1.0 401 Unauthorized');
        echo json_encode(['error' => 'Token de autorización inválido']);
        exit;
    }
    
    return true;
}

// Función para responder con JSON
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}
