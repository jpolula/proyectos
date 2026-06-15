<?php
require_once 'functions.php';

// Cargar configuración
$config = load_current_config();
$devices = $config['ips'] ?? [];
$interval = (int)($config['settings']['ping_interval'] ?? 60);

if (empty($devices)) {
    die("No hay dispositivos configurados.\n");
}

// Estado anterior
$state_file = __DIR__ . '/monitor_state.json';
$previous_states = [];

// Cargar estado si existe
if (file_exists($state_file)) {
    $previous_states = json_decode(file_get_contents($state_file), true) ?: [];
}

echo "Monitor iniciado. Intervalo: {$interval} segundos\n\n";

while (true) {
    $current_states = [];
    $changes = false;
    
    foreach ($devices as $ip => $name) {
        // Verificar estado actual
        $result = update_ping_results($ip);
        $current_states[$name] = [
            'ip' => $ip,
            'status' => $result['status']
        ];
        
        $previous = isset($previous_states[$name]) ? $previous_states[$name]['status'] : null;
        $current = $current_states[$name]['status'];
        
        // Si el estado cambió
        if ($current !== $previous) {
            $changes = true;
            
            if ($current === 'DOWN') {
                // Dispositivo caído
                $message = "❌ <b>ALERTA: Dispositivo sin respuesta</b>\n\n";
                $message .= "📍 <b>$name</b>\n";
                $message .= "   • IP: <code>$ip</code>\n";
                $message .= "   • Estado: <b>CAÍDO</b>\n";
                $message .= "   • Última verificación: " . date('d/m/Y H:i:s') . "\n";
                send_telegram_notification($message);
                
                echo "\n¡Dispositivo CAÍDO: $name ($ip)!\n";
            } 
            else if ($current === 'UP' && $previous === 'DOWN') {
                // Dispositivo recuperado
                $message = "✅ <b>RECUPERACIÓN: Dispositivo restablecido</b>\n\n";
                $message .= "📍 <b>$name</b>\n";
                $message .= "   • IP: <code>$ip</code>\n";
                $message .= "   • Estado: <b>RECUPERADO</b>\n";
                $message .= "   • Hora de recuperación: " . date('d/m/Y H:i:s') . "\n";
                send_telegram_notification($message);
                
                echo "\n¡Dispositivo RECUPERADO: $name ($ip)!\n";
            }
            
            echo "Estado anterior: " . ($previous ?? "ninguno") . "\n";
            echo "Estado actual: " . $current . "\n\n";
        }
        
        // Log en consola
        $status_text = $current === 'UP' ? 'ACTIVO' : 'CAÍDO';
        $status_icon = $current === 'UP' ? '✅' : '❌';
        echo date('Y-m-d H:i:s') . " - $status_icon $name ($ip): $status_text\n";
    }
    
    if ($changes) {
        echo "\n¡Se detectaron cambios! Guardando estado...\n";
        file_put_contents($state_file, json_encode($current_states));
        $previous_states = $current_states;
    }
    
    echo "\nEsperando {$interval} segundos...\n\n";
    sleep($interval);
    
    // Recargar configuración
    $config = load_current_config();
    $devices = $config['ips'] ?? $devices;
    $interval = (int)($config['settings']['ping_interval'] ?? $interval);
}
