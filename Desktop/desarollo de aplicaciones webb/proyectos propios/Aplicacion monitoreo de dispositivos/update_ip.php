<?php
require_once 'functions.php';

header('Content-Type: application/json');

// Verificar que tenemos la IP original
if (!isset($_POST['original_ip'])) {
    echo json_encode(['success' => false, 'error' => 'Falta la IP original del dispositivo']);
    exit;
}

$original_ip = $_POST['original_ip'];
$new_ip = isset($_POST['new_ip']) ? $_POST['new_ip'] : '';
$service = isset($_POST['service']) ? $_POST['service'] : null;
$device_type = isset($_POST['device_type']) ? $_POST['device_type'] : null;
$connections = isset($_POST['connections']) ? json_decode($_POST['connections'], true) : null;

// Actualizar el dispositivo con todos los campos proporcionados
$result = update_device($original_ip, $new_ip, $service, $device_type, $connections);
if ($result) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Error al actualizar la configuración']);
}
