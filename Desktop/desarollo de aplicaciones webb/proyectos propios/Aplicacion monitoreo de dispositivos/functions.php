<?php
// Función para escribir en el log
function write_log($message, $type = 'ERROR') {
    $log_file = 'error_log.txt';
    $timestamp = date('Y-m-d H:i:s');
    $log_message = "[$timestamp][$type] $message\n";
    file_put_contents($log_file, $log_message, FILE_APPEND);
}

// Función para obtener las IPs del archivo de configuración
function get_ips_from_config() {
    $config_file = 'config.ini';
    if (!file_exists($config_file)) {
        write_log("Archivo de configuración no encontrado", "ERROR");
        return [];
    }
    
    $config = parse_ini_file($config_file, true);
    if ($config === false) {
        write_log("Error al leer el archivo de configuración", "ERROR");
        return [];
    }
    
    $ips = [];
    if (isset($config['ips'])) {
        foreach ($config['ips'] as $key => $value) {
            if (strpos($key, '[service]') !== false) {
                $ip = substr($key, 0, strpos($key, '['));
                if (!isset($ips[$ip])) {
                    $ips[$ip] = [
                        'service' => $value,
                        'icon' => 'router' // valor por defecto
                    ];
                } else {
                    $ips[$ip]['service'] = $value;
                }
            } elseif (strpos($key, '[icon]') !== false) {
                $ip = substr($key, 0, strpos($key, '['));
                if (!isset($ips[$ip])) {
                    $ips[$ip] = [
                        'service' => 'Desconocido',
                        'icon' => $value
                    ];
                } else {
                    $ips[$ip]['icon'] = $value;
                }
            }
        }
    } else {
        write_log("No se encontró la sección [ips] en el archivo de configuración", "WARNING");
    }
    
    return $ips;
}

// Función para realizar un ping a una IP
function ping($ip) {
    $output = [];
    $return_var = 0;
    
    exec("ping -n 1 " . escapeshellarg($ip), $output, $return_var);
    
    foreach ($output as $line) {
        if (strpos($line, "bytes=32") !== false) {
            return true;
        }
    }
    
    write_log("Ping fallido para IP: $ip - Sin respuesta", 'ERROR');
    return false;
}

// Función para calcular la disponibilidad de una IP
function calculate_availability($ip) {
    global $ping_data;
    
    if (!file_exists('ping_results.json')) {
        return 0;
    }
    
    $ping_data = json_decode(file_get_contents('ping_results.json'), true) ?? [];
    
    if (!isset($ping_data[$ip])) {
        return 0;
    }
    
    $total_pings = 1;
    $successful_pings = $ping_data[$ip]['status'] === 'UP' ? 1 : 0;
    
    return ($successful_pings / $total_pings) * 100;
}

// Función para obtener el Chat ID de Telegram
function get_telegram_chat_id($bot_token) {
    $config = parse_ini_file('config.ini', true);
    
    // Si ya tenemos el chat_id en la configuración, usarlo
    if (isset($config['telegram']['chat_id'])) {
        echo "Chat ID desde config: " . $config['telegram']['chat_id'] . "\n";
        return $config['telegram']['chat_id'];
    }
    
    echo "Chat ID desde config: no encontrado\n";
    echo "Intentando obtener Chat ID...\n";
    
    // Si no tenemos el chat_id, intentar obtenerlo de las actualizaciones
    $url = "https://api.telegram.org/bot{$bot_token}/getUpdates";
    
    echo "Obteniendo actualizaciones de Telegram...\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    
    $response = curl_exec($ch);
    
    if ($response === false) {
        $error = curl_error($ch);
        echo "Error de cURL: $error\n";
        curl_close($ch);
        return false;
    }
    
    curl_close($ch);
    
    $updates = json_decode($response, true);
    
    if (!isset($updates['ok']) || !$updates['ok']) {
        echo "Error al obtener actualizaciones de Telegram\n";
        return false;
    }
    
    // Buscar el primer chat_id disponible
    if (isset($updates['result']) && !empty($updates['result'])) {
        foreach ($updates['result'] as $update) {
            if (isset($update['message']['chat']['id'])) {
                $chat_id = $update['message']['chat']['id'];
                
                // Guardar el chat_id en la configuración
                if (isset($config['telegram'])) {
                    $config['telegram']['chat_id'] = $chat_id;
                    save_config($config);
                    echo "Chat ID guardado en config: $chat_id\n";
                }
                
                return $chat_id;
            }
        }
    }
    
    echo "No se pudo obtener el Chat ID\n";
    return false;
}

// Función para enviar notificaciones por Telegram
function send_telegram_notification($message) {
    try {
        echo "Preparando envío de notificación...\n";
        
        $bot_token = "8054210057:AAFlbFFPd_H98CXKtWjlCgImhsDSyZOSGMc";
        $chat_id = "2006319483";
        
        echo "Bot Token: $bot_token\n";
        echo "Chat ID: $chat_id\n";
        
        $url = "https://api.telegram.org/bot{$bot_token}/sendMessage";
        echo "URL: $url\n";
        
        $data = [
            'chat_id' => $chat_id,
            'text' => $message,
            'parse_mode' => 'HTML'
        ];
        
        echo "Enviando mensaje...\n";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        
        $response = curl_exec($ch);
        
        if ($response === false) {
            $error = curl_error($ch);
            echo "Error de cURL: $error\n";
            write_log("Error de cURL: $error", 'ERROR');
            curl_close($ch);
            return false;
        }
        
        curl_close($ch);
        
        $result = json_decode($response, true);
        echo "Respuesta de Telegram: " . print_r($result, true) . "\n";
        
        if (!isset($result['ok']) || !$result['ok']) {
            $error = isset($result['description']) ? $result['description'] : 'Error desconocido';
            echo "Error al enviar mensaje: $error\n";
            write_log("Error al enviar mensaje: $error", 'ERROR');
            return false;
        }
        
        echo "Mensaje enviado exitosamente\n";
        write_log("Mensaje enviado exitosamente a Telegram", 'INFO');
        return true;
    } catch (Exception $e) {
        echo "Error al enviar notificación: " . $e->getMessage() . "\n";
        write_log("Error al enviar notificación: " . $e->getMessage(), 'ERROR');
        return false;
    }
}

// Función para generar un mensaje usando OpenAI
function generate_ai_message($status, $ip, $timestamp, $additional_info = '') {
    $api_key = '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';
    
    // Preparar el prompt según el estado
    $prompt = "";
    if ($status === "DOWN") {
        $prompt = "Genera un mensaje de alerta en español, breve pero informativo, para notificar que el dispositivo {$ip} no responde al ping. Hora: {$timestamp}. {$additional_info}";
    } else {
        $prompt = "Genera un mensaje en español, breve pero informativo, para notificar que el dispositivo {$ip} ha vuelto a estar en línea. Hora: {$timestamp}. {$additional_info}";
    }
    
    $url = 'https://api.openai.com/v1/chat/completions';
    $data = [
        'model' => 'gpt-3.5-turbo',
        'messages' => [
            [
                'role' => 'system',
                'content' => 'Eres un asistente de monitoreo de red que genera mensajes de alerta concisos y profesionales. Incluye emojis relevantes.'
            ],
            [
                'role' => 'user',
                'content' => $prompt
            ]
        ],
        'max_tokens' => 150
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $api_key
    ]);
    
    // Configuración SSL
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    
    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($response === false) {
        write_log("Error al generar mensaje con OpenAI: " . $error, "ERROR");
        // Retornar mensaje por defecto si falla la API
        return $status === "DOWN" 
            ? "⚠️ <b>ALERTA</b>: El dispositivo {$ip} no responde al ping.\nHora: {$timestamp}"
            : "✅ <b>RECUPERADO</b>: El dispositivo {$ip} ha vuelto a estar en línea.\nHora: {$timestamp}";
    }
    
    $result = json_decode($response, true);
    if (isset($result['choices'][0]['message']['content'])) {
        return $result['choices'][0]['message']['content'];
    }
    
    // Retornar mensaje por defecto si no se puede procesar la respuesta
    return $status === "DOWN" 
        ? "⚠️ <b>ALERTA</b>: El dispositivo {$ip} no responde al ping.\nHora: {$timestamp}"
        : "✅ <b>RECUPERADO</b>: El dispositivo {$ip} ha vuelto a estar en línea.\nHora: {$timestamp}";
}

// Función para calcular el estado de la IP
function analyze_ip($ip) {
    global $ping_data;
    
    if (!isset($ping_data[$ip]) || !is_array($ping_data[$ip])) {
        return [
            'status' => 'DOWN',
            'percentage' => 0,
            'ping_results' => [],
            'label' => 'Critical',
            'average_response_time' => 'N/A',
            'last_ping' => '-',
            'availability' => 0
        ];
    }

    $status = $ping_data[$ip]['status'] ?? 'DOWN';
    $timestamp = $ping_data[$ip]['last_ping'] ?? '-';
    
    // Calcular disponibilidad basada en el último estado
    $percentage = $status === 'UP' ? 100 : 0;
    
    if ($percentage >= 80) {
        $label = "Good";
    } elseif ($percentage >= 60) {
        $label = "Stable";
    } else {
        $label = "Critical";
        write_log("Dispositivo en estado crítico - IP: $ip - Disponibilidad: " . number_format($percentage, 2) . "%", 'WARNING');
    }

    return [
        'status' => $status,
        'percentage' => $percentage,
        'ping_results' => [$ping_data[$ip]],
        'label' => $label,
        'average_response_time' => $ping_data[$ip]['response_time'] ?? 'N/A',
        'last_ping' => $timestamp,
        'availability' => $percentage
    ];
}

// Función para leer los logs
function read_logs($limit = 100) {
    $log_file = 'error_log.txt';
    if (!file_exists($log_file)) {
        return [];
    }
    
    $logs = file($log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $logs = array_reverse($logs); // Mostrar los más recientes primero
    return array_slice($logs, 0, $limit); // Limitar la cantidad de logs mostrados
}

function add_ip_to_config($ip, $service, $device_type, $connections = []) {
    // Si es un switch, no debe tener IP
    if ($device_type === 'switch') {
        $ip = '';
    } else if (!filter_var($ip, FILTER_VALIDATE_IP)) {
        write_log("IP inválida: $ip", "ERROR");
        return false;
    }

    $config = parse_ini_file('config.ini', true);
    if ($config === false) {
        write_log("Error al leer el archivo de configuración", "ERROR");
        return false;
    }

    if (!isset($config['ips'])) {
        $config['ips'] = [];
    }
    if (!isset($config['icons'])) {
        $config['icons'] = [];
    }
    if (!isset($config['connections'])) {
        $config['connections'] = [];
    }

    // Añadir o actualizar la IP con su servicio
    if (!empty($ip)) {
        $config['ips'][$ip] = $service;
    }

    // Añadir o actualizar el icono
    if (!empty($ip)) {
        $config['icons'][$ip] = $device_type;
    }

    // Manejar las conexiones
    if (!empty($connections) && !empty($ip)) {
        $config['connections'][$ip] = implode(',', $connections);
    }

    return save_config($config);
}

function update_device($original_ip, $new_ip = '', $service = null, $device_type = null, $connections = null) {
    $config = parse_ini_file('config.ini', true);
    if ($config === false) {
        write_log("Error al leer el archivo de configuración", "ERROR");
        return false;
    }

    // Asegurarse de que las secciones existen
    if (!isset($config['ips'])) {
        $config['ips'] = [];
    }
    if (!isset($config['icons'])) {
        $config['icons'] = [];
    }
    if (!isset($config['connections'])) {
        $config['connections'] = [];
    }

    // Si es un switch, no debe tener IP
    if ($device_type === 'switch') {
        $new_ip = '';
    }

    // Si hay una nueva IP y es diferente de la original, necesitamos actualizar todas las referencias
    if (!empty($new_ip) && $new_ip !== $original_ip) {
        // Validar la nueva IP
        if (!filter_var($new_ip, FILTER_VALIDATE_IP)) {
            write_log("IP inválida: $new_ip", "ERROR");
            return false;
        }

        // Eliminar todas las referencias a la IP original
        foreach ($config['ips'] as $key => $value) {
            if (strpos($key, $original_ip) === 0) {
                unset($config['ips'][$key]);
            }
        }
        if (isset($config['icons'][$original_ip])) {
            unset($config['icons'][$original_ip]);
        }
        if (isset($config['connections'][$original_ip])) {
            unset($config['connections'][$original_ip]);
        }

        // Actualizar las conexiones que apuntan a la IP original
        foreach ($config['connections'] as $source => $targets) {
            $targetArray = explode(',', $targets);
            if (in_array($original_ip, $targetArray)) {
                $targetArray = array_diff($targetArray, [$original_ip]);
                if (!empty($new_ip)) {
                    $targetArray[] = $new_ip;
                }
                if (!empty($targetArray)) {
                    $config['connections'][$source] = implode(',', array_unique($targetArray));
                } else {
                    unset($config['connections'][$source]);
                }
            }
        }
    }

    // Usar la IP apropiada para las actualizaciones
    $current_ip = !empty($new_ip) ? $new_ip : $original_ip;

    // Actualizar servicio si se proporciona y no es un switch
    if ($service !== null && !empty($current_ip)) {
        $config['ips'][$current_ip] = $service;
    }

    // Actualizar tipo de dispositivo si se proporciona
    if ($device_type !== null && !empty($current_ip)) {
        $config['icons'][$current_ip] = $device_type;
    }

    // Manejar las conexiones si se proporcionan
    if ($connections !== null && !empty($current_ip)) {
        // Eliminar las conexiones existentes
        if (isset($config['connections'][$current_ip])) {
            unset($config['connections'][$current_ip]);
        }
        
        // Eliminar este dispositivo de las conexiones de otros dispositivos
        foreach ($config['connections'] as $source => $targets) {
            $targetArray = explode(',', $targets);
            if (in_array($current_ip, $targetArray)) {
                $targetArray = array_diff($targetArray, [$current_ip]);
                if (!empty($targetArray)) {
                    $config['connections'][$source] = implode(',', $targetArray);
                } else {
                    unset($config['connections'][$source]);
                }
            }
        }

        // Añadir las nuevas conexiones
        if (!empty($connections)) {
            $config['connections'][$current_ip] = implode(',', $connections);
        }
    }

    return save_config($config);
}

function get_device_connections($ip) {
    $config = parse_ini_file('config.ini', true);
    if ($config === false || !isset($config['connections'])) {
        return [];
    }

    $connections = [];
    
    // Buscar conexiones donde el dispositivo es el origen
    if (isset($config['connections'][$ip])) {
        $connections = explode(',', $config['connections'][$ip]);
    }

    // También buscar conexiones donde el dispositivo es el destino
    foreach ($config['connections'] as $source => $targets) {
        if ($source === $ip) continue;
        $targetArray = explode(',', $targets);
        if (in_array($ip, $targetArray)) {
            $connections[] = $source;
        }
    }

    return array_unique($connections);
}

// Función para eliminar una IP del archivo de configuración
function delete_ip_from_config($ip) {
    $config = parse_ini_file('config.ini', true);
    if ($config === false) {
        write_log("Error al leer el archivo de configuración", "ERROR");
        return false;
    }

    // Eliminar el servicio
    unset($config['ips'][$ip . '[service]']);

    // Eliminar el icono
    unset($config['icons'][$ip]);

    // Eliminar las conexiones donde este dispositivo está involucrado
    if (isset($config['connections'])) {
        // Eliminar las conexiones de este dispositivo
        unset($config['connections'][$ip]);

        // Eliminar las conexiones hacia este dispositivo en otros dispositivos
        foreach ($config['connections'] as $sourceIp => $targetIps) {
            $targets = explode(',', $targetIps);
            if (in_array($ip, $targets)) {
                $targets = array_filter($targets, function($target) use ($ip) {
                    return $target !== $ip;
                });
                if (empty($targets)) {
                    unset($config['connections'][$sourceIp]);
                } else {
                    $config['connections'][$sourceIp] = implode(',', $targets);
                }
            }
        }
    }

    // Eliminar los resultados de ping si existen
    if (isset($config['ping_results'])) {
        unset($config['ping_results'][$ip]);
    }

    // Eliminar cualquier otra referencia al IP en otras secciones
    foreach ($config as $section => $values) {
        foreach ($values as $key => $value) {
            if (strpos($key, $ip) !== false || strpos($value, $ip) !== false) {
                unset($config[$section][$key]);
            }
        }
    }

    // Limpiar secciones vacías
    foreach ($config as $section => $values) {
        if (empty($values)) {
            unset($config[$section]);
        }
    }

    return save_config($config);
}

function initialize_config() {
    $default_config = [
        'settings' => [
            'ping_attempts' => '5',
            'ping_interval' => '60'
        ],
        'services' => [
            'DNS Cloudflare' => '#ADD8E6',
            'Cloudflare' => '#FAEBD7',
            'GitHub' => '#DCDCDC',
            'DEFAULT' => '#F8F8FF'
        ],
        'ips' => [],
        'icons' => [],
        'connections' => []
    ];

    // Cargar configuración existente si existe
    if (file_exists('config.ini')) {
        $current_config = parse_ini_file('config.ini', true);
        
        // Preservar IPs y conexiones existentes
        if (isset($current_config['ips'])) {
            $default_config['ips'] = $current_config['ips'];
        }
        if (isset($current_config['icons'])) {
            $default_config['icons'] = $current_config['icons'];
        }
        if (isset($current_config['connections'])) {
            $default_config['connections'] = $current_config['connections'];
        }
    }

    return save_config($default_config);
}

// Función para verificar la integridad del sistema
function check_system_health() {
    $health_issues = [];
    $repairs_needed = false;
    
    // Verificar archivos necesarios
    $required_files = [
        'config.ini' => 'Archivo de configuración',
        'ping_results.json' => 'Archivo de resultados de ping',
        'error_log.txt' => 'Archivo de registro de errores'
    ];
    
    foreach ($required_files as $file => $description) {
        if (!file_exists($file)) {
            $health_issues[] = "Archivo faltante: {$description} ({$file})";
            $repairs_needed = true;
        }
    }
    
    // Verificar permisos de archivos
    foreach ($required_files as $file => $description) {
        if (file_exists($file) && !is_writable($file)) {
            $health_issues[] = "Permisos incorrectos: {$description} ({$file})";
            $repairs_needed = true;
        }
    }
    
    // Verificar estructura del archivo de configuración
    if (file_exists('config.ini')) {
        $config = parse_ini_file('config.ini', true);
        if ($config === false) {
            $health_issues[] = "Archivo de configuración corrupto";
            $repairs_needed = true;
        } else {
            $required_sections = ['settings', 'services', 'ips', 'icons', 'connections', 'telegram'];
            foreach ($required_sections as $section) {
                if (!isset($config[$section])) {
                    $health_issues[] = "Sección faltante en config.ini: {$section}";
                    $repairs_needed = true;
                }
            }
        }
    }
    
    // Verificar archivo de resultados de ping
    if (file_exists('ping_results.json')) {
        $ping_data = json_decode(file_get_contents('ping_results.json'), true);
        if ($ping_data === null) {
            $health_issues[] = "Archivo de resultados de ping corrupto";
            $repairs_needed = true;
        }
    }
    
    return [
        'issues' => $health_issues,
        'needs_repair' => $repairs_needed
    ];
}

// Función para auto-reparar problemas
function auto_repair_system() {
    $repair_log = [];
    $health = check_system_health();
    
    if (!$health['needs_repair']) {
        return ['success' => true, 'message' => 'El sistema está en buen estado, no se requieren reparaciones.'];
    }
    
    // Crear archivo de configuración si falta o está corrupto
    if (!file_exists('config.ini') || !parse_ini_file('config.ini', true)) {
        initialize_config();
        $repair_log[] = "Se inicializó el archivo de configuración";
    }
    
    // Crear archivo de resultados de ping si falta o está corrupto
    if (!file_exists('ping_results.json') || json_decode(file_get_contents('ping_results.json'), true) === null) {
        file_put_contents('ping_results.json', json_encode([]));
        $repair_log[] = "Se inicializó el archivo de resultados de ping";
    }
    
    // Crear archivo de log si falta
    if (!file_exists('error_log.txt')) {
        file_put_contents('error_log.txt', '');
        $repair_log[] = "Se creó el archivo de registro de errores";
    }
    
    // Verificar y reparar estructura del archivo de configuración
    $config = parse_ini_file('config.ini', true);
    if ($config !== false) {
        $required_sections = [
            'settings' => [
                'ping_attempts' => '5',
                'ping_interval' => '60'
            ],
            'services' => [
                'DEFAULT' => '#F8F8FF'
            ],
            'ips' => [],
            'icons' => [],
            'connections' => [],
            'telegram' => [
                'bot_token' => '8054210057:AAFlbFFPd_H98CXKtWjlCgImhsDSyZOSGMc',
                'chat_id' => '2006319483'
            ]
        ];
        
        $modified = false;
        foreach ($required_sections as $section => $defaults) {
            if (!isset($config[$section])) {
                $config[$section] = $defaults;
                $modified = true;
                $repair_log[] = "Se añadió la sección faltante: {$section}";
            }
        }
        
        if ($modified) {
            save_config($config);
        }
    }
    
    // Verificar permisos de archivos
    $files_to_check = ['config.ini', 'ping_results.json', 'error_log.txt'];
    foreach ($files_to_check as $file) {
        if (file_exists($file) && !is_writable($file)) {
            chmod($file, 0666);
            $repair_log[] = "Se corrigieron los permisos del archivo: {$file}";
        }
    }
    
    // Verificar si las reparaciones fueron exitosas
    $health_after = check_system_health();
    $success = !$health_after['needs_repair'];
    
    // Notificar sobre las reparaciones
    $message = "🔧 <b>Informe de Auto-reparación</b>\n\n";
    $message .= $success ? "✅ Reparaciones completadas con éxito:\n" : "⚠️ Algunas reparaciones no pudieron completarse:\n";
    $message .= "\n- " . implode("\n- ", $repair_log);
    
    if (!empty($health_after['issues'])) {
        $message .= "\n\n❌ Problemas pendientes:\n- " . implode("\n- ", $health_after['issues']);
    }
    
    send_telegram_notification($message);
    write_log($message, $success ? 'INFO' : 'ERROR');
    
    return [
        'success' => $success,
        'repairs' => $repair_log,
        'pending_issues' => $health_after['issues']
    ];
}

// Función para monitorear y reparar el sistema periódicamente
function monitor_system_health() {
    $health = check_system_health();
    
    if ($health['needs_repair']) {
        $message = "🔍 <b>Problemas detectados en el sistema</b>\n\n";
        $message .= "Se encontraron los siguientes problemas:\n";
        $message .= "- " . implode("\n- ", $health['issues']);
        $message .= "\n\nIniciando auto-reparación...";
        
        send_telegram_notification($message);
        write_log($message, 'WARNING');
        
        return auto_repair_system();
    }
    
    return ['success' => true, 'message' => 'Sistema en buen estado'];
}

// Función para actualizar un ping y sus resultados
function update_ping_results($ip) {
    global $ping_data;
    
    // Cargar datos existentes
    if (!isset($ping_data)) {
        $ping_data = [];
        if (file_exists('ping_results.json')) {
            $ping_data = json_decode(file_get_contents('ping_results.json'), true) ?: [];
        }
    }
    
    // Realizar el ping
    $success = ping($ip);
    $timestamp = time();
    
    // Inicializar el array para esta IP si no existe
    if (!isset($ping_data[$ip])) {
        $ping_data[$ip] = [
            'history' => [],
            'last_status' => null,
            'last_change' => null,
            'last_check' => null
        ];
    }
    
    // Asegurarse de que history sea un array
    if (!isset($ping_data[$ip]['history']) || !is_array($ping_data[$ip]['history'])) {
        $ping_data[$ip]['history'] = [];
    }
    
    // Actualizar el estado
    $status = $success ? 'UP' : 'DOWN';
    $previous_status = isset($ping_data[$ip]['last_status']) ? $ping_data[$ip]['last_status'] : null;
    
    // Registrar el cambio si el estado es diferente
    if ($previous_status !== $status) {
        $ping_data[$ip]['last_change'] = $timestamp;
        
        // Generar mensaje personalizado para el cambio de estado
        if ($status === 'DOWN') {
            $message = generate_ai_message('down', $ip, $timestamp);
        } else {
            $message = generate_ai_message('up', $ip, $timestamp);
        }
        
        // Agregar al historial
        array_unshift($ping_data[$ip]['history'], [
            'status' => $status,
            'timestamp' => $timestamp,
            'message' => $message
        ]);
        
        // Mantener solo los últimos 100 registros
        if (count($ping_data[$ip]['history']) > 100) {
            array_pop($ping_data[$ip]['history']);
        }
    }
    
    // Actualizar estado y tiempo de última verificación
    $ping_data[$ip]['last_status'] = $status;
    $ping_data[$ip]['last_check'] = $timestamp;
    
    // Guardar los datos actualizados
    file_put_contents('ping_results.json', json_encode($ping_data, JSON_PRETTY_PRINT));
    
    return [
        'status' => $status,
        'timestamp' => $timestamp,
        'changed' => ($previous_status !== $status)
    ];
}

// Función para cargar la configuración
function load_current_config() {
    $config = parse_ini_file('config.ini', true);
    if ($config === false) {
        throw new Exception("Error al cargar el archivo de configuración");
    }
    return $config;
}

// Función para verificar si es momento de hacer ping
function should_check_device($ip) {
    $last_check = get_last_check_time($ip);
    $config = load_current_config();
    $interval = isset($config['settings']['ping_interval']) ? (int)$config['settings']['ping_interval'] : 60;
    
    return (time() - $last_check) >= $interval;
}

// Función para obtener el último tiempo de verificación
function get_last_check_time($ip) {
    $results_file = 'ping_results.json';
    if (!file_exists($results_file)) {
        return 0;
    }
    
    $results = json_decode(file_get_contents($results_file), true);
    if (!isset($results[$ip]['last_check'])) {
        return 0;
    }
    
    return $results[$ip]['last_check'];
}

function save_config($config) {
    // Asegurarnos de que tenemos permisos de escritura
    if (!is_writable('config.ini') && file_exists('config.ini')) {
        write_log("No hay permisos de escritura en config.ini", "ERROR");
        return false;
    }

    // Crear una copia de seguridad antes de modificar
    if (file_exists('config.ini')) {
        copy('config.ini', 'config.ini.bak');
    }

    $ini = '';
    foreach ($config as $section => $values) {
        // Solo escribir secciones que tengan valores
        if (!empty($values)) {
            $ini .= "[$section]\n";
            foreach ($values as $key => $val) {
                if (is_array($val)) {
                    // Si es un array, lo convertimos a string
                    $val = implode(',', array_filter($val));
                }
                // Solo escribir valores que no estén vacíos
                if (!empty($val)) {
                    $escaped_value = str_replace('"', '\"', (string)$val);
                    $ini .= "$key = \"$escaped_value\"\n";
                }
            }
            $ini .= "\n";
        }
    }
    
    // Escribir el archivo usando un archivo temporal
    $temp_file = 'config.ini.tmp';
    if (file_put_contents($temp_file, $ini) === false) {
        write_log("Error al escribir el archivo temporal", "ERROR");
        return false;
    }

    // Renombrar el archivo temporal al archivo final
    if (!rename($temp_file, 'config.ini')) {
        write_log("Error al renombrar el archivo temporal", "ERROR");
        unlink($temp_file);
        return false;
    }

    return true;
}
?>