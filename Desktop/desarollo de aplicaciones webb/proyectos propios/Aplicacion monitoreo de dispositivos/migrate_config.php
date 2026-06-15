<?php
// Leer el archivo de configuración actual
$config = parse_ini_file('config.ini', true);

// Crear una nueva estructura para las IPs
$new_ips = [];
if (isset($config['ips'])) {
    foreach ($config['ips'] as $ip => $service) {
        if (is_array($service)) {
            // Ya está en el nuevo formato
            $new_ips[$ip] = $service;
        } else {
            // Convertir al nuevo formato
            $new_ips[$ip] = [
                'service' => $service,
                'icon' => 'router' // Icono por defecto
            ];
        }
    }
}

// Actualizar la configuración
$config['ips'] = $new_ips;

// Generar el nuevo contenido del archivo
$new_content = '';
foreach ($config as $section => $values) {
    $new_content .= "[$section]\n";
    foreach ($values as $key => $value) {
        if (is_array($value)) {
            foreach ($value as $subkey => $subvalue) {
                $new_content .= "$key" . "[$subkey] = \"$subvalue\"\n";
            }
        } else {
            $new_content .= "$key = \"$value\"\n";
        }
    }
    $new_content .= "\n";
}

// Guardar el archivo
file_put_contents('config.ini', $new_content);

echo "Migración completada.\n";
?>
