<?php
/**
 * Ejemplo de uso de la API de Reservas de Restaurante
 * 
 * Este archivo muestra ejemplos de cómo usar la API con PHP
 */

// Token de la API - Reemplaza esto con tu token actual
$apiToken = 'tu_token_secreto_aqui';

// URL base de la API
$apiBaseUrl = 'http://localhost/reservar%20restaurantes/api';

// Función para hacer peticiones a la API
function callApi($endpoint, $method = 'GET', $data = null) {
    global $apiToken, $apiBaseUrl;
    
    $url = $apiBaseUrl . '/' . $endpoint;
    $ch = curl_init();
    
    // Configurar opciones de cURL
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $apiToken,
        'Content-Type: application/json'
    ]);
    
    // Configurar método y datos para POST/PUT
    if ($method === 'POST' || $method === 'PUT') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
    } else if ($method !== 'GET') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    }
    
    // Ejecutar la petición
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    // Verificar errores
    if (curl_errno($ch)) {
        echo 'Error cURL: ' . curl_error($ch);
        return null;
    }
    
    curl_close($ch);
    
    // Devolver la respuesta como array asociativo
    return [
        'code' => $httpCode,
        'data' => json_decode($response, true)
    ];
}

// Ejemplo 1: Obtener todas las reservas
echo "<h2>Ejemplo 1: Obtener todas las reservas</h2>";
$reservas = callApi('reservas');
if ($reservas && $reservas['code'] === 200) {
    echo "<pre>";
    print_r($reservas['data']);
    echo "</pre>";
} else {
    echo "Error al obtener reservas: Código " . ($reservas ? $reservas['code'] : 'desconocido');
    echo "<pre>";
    print_r($reservas['data'] ?? 'Sin datos');
    echo "</pre>";
}

// Ejemplo 2: Obtener disponibilidad para una fecha específica
echo "<h2>Ejemplo 2: Comprobar disponibilidad</h2>";
$fecha = date('Y-m-d', strtotime('+1 day')); // Mañana
$disponibilidad = callApi("disponibilidad?fecha={$fecha}&turno_id=1&zona=interior");
if ($disponibilidad && $disponibilidad['code'] === 200) {
    echo "<pre>";
    print_r($disponibilidad['data']);
    echo "</pre>";
} else {
    echo "Error al obtener disponibilidad: Código " . ($disponibilidad ? $disponibilidad['code'] : 'desconocido');
    echo "<pre>";
    print_r($disponibilidad['data'] ?? 'Sin datos');
    echo "</pre>";
}

// Ejemplo 3: Crear una nueva reserva (comentado para evitar crear reservas de prueba accidentalmente)
/*
echo "<h2>Ejemplo 3: Crear una nueva reserva</h2>";
$nuevaReserva = [
    'cliente_id' => 1, // ID de un cliente existente
    'fecha' => $fecha,
    'turno_id' => 1,
    'zona' => 'interior',
    'hora' => '13:30',
    'num_personas' => 2,
    'observaciones' => 'Reserva de prueba desde API',
    'necesidades_especiales' => '',
    'alergenos' => ''
];
$resultado = callApi('reservas', 'POST', $nuevaReserva);
if ($resultado && $resultado['code'] === 201) {
    echo "Reserva creada correctamente:";
    echo "<pre>";
    print_r($resultado['data']);
    echo "</pre>";
} else {
    echo "Error al crear reserva: Código " . ($resultado ? $resultado['code'] : 'desconocido');
    echo "<pre>";
    print_r($resultado['data'] ?? 'Sin datos');
    echo "</pre>";
}
*/

// Ejemplo 4: Obtener estadísticas
echo "<h2>Ejemplo 4: Obtener estadísticas</h2>";
$estadisticas = callApi('estadisticas?periodo=mes');
if ($estadisticas && $estadisticas['code'] === 200) {
    echo "<pre>";
    print_r($estadisticas['data']);
    echo "</pre>";
} else {
    echo "Error al obtener estadísticas: Código " . ($estadisticas ? $estadisticas['code'] : 'desconocido');
    echo "<pre>";
    print_r($estadisticas['data'] ?? 'Sin datos');
    echo "</pre>";
}
