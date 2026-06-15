<?php
    require_once 'functions.php';

    header('Content-Type: application/json');

    // Obtener la lista de IPs del archivo de configuración
    $config = parse_ini_file('config.ini', true);
    $ips_to_monitor = $config['ips'] ?? [];
    $services = $config['services'] ?? [];

    // Preparar el estado de todos los dispositivos
    $status = [];
    $tableHtml = '';

    foreach ($ips_to_monitor as $ip => $service) {
        $result = analyze_ip($ip);
        $status[$ip] = $result;
        
        // Generar HTML para la fila de la tabla
        $percentage = $result['percentage'];
        $label = $result['label'];
        $average_response_time = $result['average_response_time'];
        
        // Determinar colores
        $label_color = $label === "Good" ? "bg-green-400" : ($label === "Stable" ? "bg-yellow-400" : "bg-red-400");
        $service_color = $services[$service] ?? $services["DEFAULT"];
        
        if ($percentage !== 'N/A') {
            $percentage = round($percentage, 1);
            if ($percentage > 80) {
                $response_time_percentage = "text-green-500";
            } elseif ($percentage > 60) {
                $response_time_percentage = "text-yellow-500";
            } else {
                $response_time_percentage = "text-red-500";
            }
        } else {
            $response_time_percentage = "text-gray-500";
        }
        
        if ($average_response_time !== 'N/A') {
            $average_response_time = round($average_response_time, 1);
            if ($average_response_time < 50) {
                $response_time_color = "text-green-500";
            } elseif ($average_response_time < 100) {
                $response_time_color = "text-yellow-500";
            } else {
                $response_time_color = "text-red-500";
            }
        } else {
            $response_time_color = "text-gray-500";
        }

        $tableHtml .= '<tr class="border-b border-gray-300 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-800 transition duration-300">';
        $tableHtml .= '<td class="p-3 text-center font-semibold" style="background-color:' . $service_color . '">' . htmlspecialchars($service) . '</td>';
        $tableHtml .= '<td class="p-3 text-center">' . htmlspecialchars($ip) . '</td>';
        $tableHtml .= '<td class="p-3 text-center"><span class="px-2 py-1 rounded ' . $label_color . '">' . ($result['status'] === 'UP' ? 'En línea' : 'Desconectado') . '</span></td>';
        $tableHtml .= '<td class="p-3 text-center"><span class="font-semibold ' . $response_time_percentage . '">' . $percentage . '%</span></td>';
        $tableHtml .= '<td class="p-3 text-center"><span class="font-semibold ' . $response_time_color . '">' . $average_response_time . ' ms</span></td>';
        
        // Agregar columnas de historial de ping
        foreach ($result['ping_results'] as $ping) {
            $status_color = $ping['status'] === 'UP' ? 'text-green-500' : 'text-red-500';
            $tableHtml .= '<td class="p-3 text-center">';
            $tableHtml .= '<div class="' . $status_color . ' font-semibold">' . $ping['status'] . '</div>';
            $tableHtml .= '<div class="text-xs text-gray-500">' . $ping['response_time'] . '</div>';
            $tableHtml .= '<div class="text-xs text-gray-400">' . $ping['timestamp'] . '</div>';
            $tableHtml .= '</td>';
        }
        
        $tableHtml .= '<td class="p-3 text-center">';
        $tableHtml .= '<button onclick="confirmDelete(\'' . $ip . '\');" class="text-red-500 hover:text-red-700">';
        $tableHtml .= '<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">';
        $tableHtml .= '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />';
        $tableHtml .= '</svg>';
        $tableHtml .= '</button>';
        $tableHtml .= '</td>';
        $tableHtml .= '</tr>';
    }

    echo json_encode([
        'success' => true,
        'status' => $status,
        'tableHtml' => $tableHtml
    ]);
?>
