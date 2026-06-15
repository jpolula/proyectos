<?php
require_once 'functions.php';

header('Content-Type: application/json');

if (isset($_POST['from_ip']) && isset($_POST['to_ip'])) {
    $from_ip = $_POST['from_ip'];
    $to_ip = $_POST['to_ip'];
    
    $result = delete_connection($from_ip, $to_ip);
    echo json_encode(['success' => $result]);
} else {
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
}
