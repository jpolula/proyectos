<?php
require_once 'functions.php';

header('Content-Type: application/json');

try {
    // Validar datos de entrada
    $oldIp = $_POST['oldIp'] ?? '';
    $newIp = $_POST['ip'] ?? '';
    $service = $_POST['service'] ?? '';
    $icon = $_POST['icon'] ?? '';
    $connections = $_POST['connections'] ?? [];

    if (empty($oldIp) || empty($newIp) || empty($service) || empty($icon)) {
        throw new Exception('Todos los campos son requeridos');
    }

    if (!filter_var($newIp, FILTER_VALIDATE_IP)) {
        throw new Exception('La dirección IP no es válida');
    }

    // Cargar configuración actual
    $config = parse_ini_file('config.ini', true);
    
    // Asegurarse de que existan todas las secciones necesarias
    $required_sections = ['settings', 'ips', 'services', 'icons', 'connections'];
    foreach ($required_sections as $section) {
        if (!isset($config[$section])) {
            $config[$section] = [];
        }
    }

    // Establecer valores por defecto si no existen
    if (!isset($config['settings']['ping_attempts'])) {
        $config['settings']['ping_attempts'] = "5";
    }
    if (!isset($config['settings']['ping_interval'])) {
        $config['settings']['ping_interval'] = "60";
    }
    if (!isset($config['services']['DEFAULT'])) {
        $config['services']['DEFAULT'] = "#F8F8FF";
    }
    
    // Verificar si la nueva IP ya existe (si es diferente a la antigua)
    if ($oldIp !== $newIp && isset($config['ips'][$newIp])) {
        throw new Exception('La nueva dirección IP ya existe');
    }

    // Actualizar la IP y el servicio
    if ($oldIp !== $newIp) {
        unset($config['ips'][$oldIp]);
        if (isset($config['icons'][$oldIp])) {
            unset($config['icons'][$oldIp]);
        }
    }
    
    $config['ips'][$newIp] = $service;
    $config['icons'][$newIp] = $icon;
    
    // Limpiar conexiones antiguas si cambió la IP
    if ($oldIp !== $newIp) {
        foreach ($config['connections'] as $key => $value) {
            if (strpos($key, $oldIp) === 0) {
                unset($config['connections'][$key]);
            }
        }
    }

    // Guardar nuevas conexiones
    foreach ($connections as $targetIp) {
        $config['connections']["{$newIp}_to_{$targetIp}"] = "1";
    }

    // Guardar la configuración actualizada
    $ini = "";
    foreach ($config as $section => $values) {
        // Asegurarse de que las secciones principales estén en el orden correcto
        $ini .= "[$section]\n";
        foreach ($values as $key => $val) {
            // Escapar cualquier carácter especial en el valor
            $escaped_value = str_replace('"', '\"', $val);
            $ini .= "$key = \"$escaped_value\"\n";
        }
        $ini .= "\n";
    }

    if (file_put_contents('config.ini', $ini) === false) {
        throw new Exception('Error al guardar la configuración');
    }

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
