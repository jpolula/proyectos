<?php
require_once 'functions.php';

header('Content-Type: application/json');

if (!isset($_GET['ip'])) {
    echo json_encode(['success' => false, 'error' => 'Falta la IP']);
    exit;
}

$ip = $_GET['ip'];
$connections = get_device_connections($ip);

echo json_encode([
    'success' => true,
    'connections' => $connections
]);
