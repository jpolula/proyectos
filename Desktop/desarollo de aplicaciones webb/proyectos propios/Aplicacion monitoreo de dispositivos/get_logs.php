<?php
require_once 'functions.php';

header('Content-Type: application/json');

// Obtener los últimos 100 logs
$logs = read_logs(100);

// Filtrar solo los logs de ping fallidos y estados críticos
$filtered_logs = array_filter($logs, function($log) {
    return strpos($log, '[ERROR]') !== false || 
           strpos($log, '[WARNING]') !== false ||
           strpos($log, 'Sin respuesta') !== false ||
           strpos($log, 'estado crítico') !== false;
});

// Ordenar los logs del más reciente al más antiguo
$filtered_logs = array_values($filtered_logs);
rsort($filtered_logs);

echo json_encode($filtered_logs);
