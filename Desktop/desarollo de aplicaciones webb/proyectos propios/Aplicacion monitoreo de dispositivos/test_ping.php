<?php
require_once 'functions.php';

// IP de prueba
$ip = "192.168.1.1";

echo "Probando sistema de monitoreo...\n";

// Hacer varios pings para probar las notificaciones
for ($i = 0; $i < 4; $i++) {
    echo "\nIntento " . ($i + 1) . ":\n";
    $result = update_ping_results($ip);
    print_r($result);
    sleep(2); // Esperar 2 segundos entre intentos
}

echo "\nPrueba completada.\n";
?>
