<?php
require_once('functions.php');

header('Content-Type: application/json');

// Obtener todas las IPs del archivo de configuración
$ips = get_ips_from_config();

// Si se especifica una IP, solo verificar esa
if (isset($_GET['ip']) && !empty($_GET['ip'])) {
    $ip = $_GET['ip'];
    if (isset($ips[$ip])) {
        $result = analyze_ip($ip);
        $response = [
            $ip => [
                'status' => $result['status'],
                'service' => $ips[$ip]['service'],
                'icon' => $ips[$ip]['icon']
            ]
        ];
        echo json_encode($response);
        exit;
    } else {
        echo json_encode(['error' => 'IP no encontrada']);
        exit;
    }
}

// Verificar todas las IPs
$response = [];
foreach ($ips as $ip => $details) {
    $result = analyze_ip($ip);
    $response[$ip] = [
        'status' => $result['status'],
        'service' => $details['service'],
        'icon' => $details['icon']
    ];
}

echo json_encode($response);
?>
