<?php
require_once 'functions.php';

echo "Iniciando daemon de monitoreo...\n";

while (true) {
    // Ejecutar el monitor
    include 'monitor.php';
    
    // Cargar la configuración para obtener el intervalo actual
    $config = parse_ini_file('config.ini', true);
    $interval = isset($config['settings']['ping_interval']) ? (int)$config['settings']['ping_interval'] : 60;
    
    echo "\nEsperando {$interval} segundos para la próxima verificación...\n";
    sleep($interval);
}
