<?php
require_once 'functions.php';

header('Content-Type: application/json');

try {
    $config = parse_ini_file('config.ini', true);
    $ping_data = [];
    $history_file = 'ping_history.json';
    
    // Cargar historial existente
    $history = [];
    if (file_exists($history_file)) {
        $history = json_decode(file_get_contents($history_file), true) ?? [];
    }

    // Realizar ping a todos los dispositivos
    foreach ($config['ips'] as $ip => $service) {
        $result = ping($ip);
        $current_time = date('Y-m-d H:i:s');
        
        // Guardar resultado actual
        $ping_data[$ip] = [
            'status' => $result ? 'UP' : 'DOWN',
            'response_time' => $result ? '1ms' : 'N/A'
        ];
        
        // Actualizar historial del dispositivo
        if (!isset($history[$ip])) {
            $history[$ip] = [];
        }
        
        // Agregar nuevo ping al inicio del historial
        array_unshift($history[$ip], [
            'timestamp' => $current_time,
            'status' => $result ? 'UP' : 'DOWN'
        ]);
        
        // Mantener solo los últimos 10 pings
        $history[$ip] = array_slice($history[$ip], 0, 10);
    }

    // Guardar resultados
    file_put_contents($history_file, json_encode($history));
    file_put_contents('ping_results.json', json_encode($ping_data));

    echo json_encode([
        'current' => $ping_data,
        'history' => $history
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
