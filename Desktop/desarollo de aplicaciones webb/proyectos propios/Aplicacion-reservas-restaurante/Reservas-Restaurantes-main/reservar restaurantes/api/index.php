<?php
/**
 * Punto de entrada principal para la API de Reservas de Restaurantes
 * 
 * Esta API permite la integración con n8n para automatizar flujos de trabajo
 * relacionados con las reservas del restaurante.
 */

// Definir constante para control de acceso
define('API_ACCESS', true);

// Incluir archivo de configuración
require_once 'config.php';

// Obtener el método HTTP y la ruta solicitada
$method = $_SERVER['REQUEST_METHOD'];
$request = isset($_GET['endpoint']) ? $_GET['endpoint'] : '';

// Dividir la ruta en segmentos
$segments = explode('/', trim($request, '/'));
$resource = $segments[0] ?? '';
$id = $segments[1] ?? null;

// Validar el token de API excepto para la ruta de documentación
if ($resource !== 'docs') {
    validateApiToken();
}

// Manejar las diferentes rutas de la API
switch ($resource) {
    case 'reservas':
        require_once 'endpoints/reservas.php';
        break;
        
    case 'clientes':
        require_once 'endpoints/clientes.php';
        break;
        
    case 'disponibilidad':
        require_once 'endpoints/disponibilidad.php';
        break;
        
    case 'estadisticas':
        require_once 'endpoints/estadisticas.php';
        break;
        
    case 'docs':
        // Mostrar documentación de la API
        header('Content-Type: text/html; charset=UTF-8');
        include 'docs.php';
        exit;
        
    case '':
        // Información básica de la API
        jsonResponse([
            'name' => 'API de Reservas de Restaurantes',
            'version' => API_VERSION,
            'endpoints' => [
                'reservas' => '/api/reservas',
                'clientes' => '/api/clientes',
                'disponibilidad' => '/api/disponibilidad',
                'estadisticas' => '/api/estadisticas',
                'docs' => '/api/docs'
            ],
            'documentation' => '/api/docs'
        ]);
        break;
        
    default:
        // Recurso no encontrado
        jsonResponse(['error' => 'Recurso no encontrado'], 404);
        break;
}
