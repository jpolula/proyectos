<?php
require_once 'functions.php';

header('Content-Type: application/json');

if (isset($_POST['ip']) || isset($_GET['ip'])) {
    $ip = $_POST['ip'] ?? $_GET['ip'];
    $result = delete_ip_from_config($ip);
    echo json_encode(['success' => $result]);
} else {
    echo json_encode(['success' => false, 'error' => 'No IP provided']);
}
