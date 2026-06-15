<?php
require_once 'functions.php';

header('Content-Type: application/json');

// Debug: Registrar los datos recibidos
error_log("POST data received: " . print_r($_POST, true));

// Validar campos requeridos
if (!isset($_POST['ip']) || !isset($_POST['service']) || !isset($_POST['device_type'])) {
    echo json_encode(['success' => false, 'error' => 'Faltan campos requeridos']);
    exit;
}

$ip = $_POST['ip'];
$service = $_POST['service'];
$device_type = $_POST['device_type'];

// Manejar las conexiones
$connections = [];
if (isset($_POST['connections']) && !empty($_POST['connections'])) {
    $connections = json_decode($_POST['connections'], true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        $connections = [];
    }
}

// Añadir el dispositivo
$result = add_ip_to_config($ip, $service, $device_type, $connections);
if ($result) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Error al guardar la configuración']);
}
