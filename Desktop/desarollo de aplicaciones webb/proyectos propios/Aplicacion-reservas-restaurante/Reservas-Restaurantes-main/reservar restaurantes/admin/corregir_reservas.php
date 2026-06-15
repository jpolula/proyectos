<?php
/**
 * Script para corregir el error de sintaxis en reservas.php
 * Este script reemplaza específicamente la sección problemática
 */

// Ruta al archivo original
$archivo_original = 'reservas.php';
$archivo_corregido = 'reservas_corregido.php';

// Crear copia de seguridad
copy($archivo_original, 'reservas_backup_' . date('Ymd_His') . '.php');
echo "Copia de seguridad creada.\n";

// Leer el contenido del archivo
$contenido = file_get_contents($archivo_original);

// Reemplazar la sección problemática
$contenido_corregido = preg_replace(
    '/<\?php endforeach; \?>/',
    '<?php endforeach; ?>',
    $contenido
);

// Guardar el archivo corregido
file_put_contents($archivo_corregido, $contenido_corregido);

// Verificar si el archivo corregido se puede analizar correctamente
$output = [];
$return_var = 0;
exec('php -l ' . $archivo_corregido . ' 2>&1', $output, $return_var);

if ($return_var === 0) {
    echo "¡Corrección exitosa! El archivo corregido es sintácticamente válido.\n";
    echo "El archivo corregido se ha guardado como: $archivo_corregido\n";
    echo "Puedes renombrarlo a 'reservas.php' si deseas reemplazar el original.\n";
} else {
    echo "Todavía hay errores de sintaxis en el archivo corregido:\n";
    echo implode("\n", $output);
    
    // Intentar una corrección más agresiva
    echo "\nIntentando una corrección más agresiva...\n";
    
    // Identificar la sección de la vista móvil
    $inicio_vista_movil = '<!-- Vista de tarjetas para dispositivos móviles -->';
    $fin_vista_movil = '<?php else: ?>';
    
    $pos_inicio = strpos($contenido, $inicio_vista_movil);
    $pos_fin = strpos($contenido, $fin_vista_movil, $pos_inicio);
    
    if ($pos_inicio !== false && $pos_fin !== false) {
        // Reemplazar toda la sección de la vista móvil
        $vista_movil_simplificada = <<<HTML
<!-- Vista de tarjetas para dispositivos móviles -->
                    <div class="reservas-card space-y-4 p-4">
                        <div class="p-6 text-center text-gray-500">
                            <p class="mb-4"><i class="fas fa-mobile-alt text-4xl"></i></p>
                            <p>Vista móvil temporalmente deshabilitada.</p>
                            <p>Por favor, utiliza la vista de escritorio o consulta con el administrador.</p>
                        </div>
HTML;
        
        $contenido_simplificado = substr($contenido, 0, $pos_inicio) . $vista_movil_simplificada . substr($contenido, $pos_fin);
        
        // Guardar la versión simplificada
        $archivo_simplificado = 'reservas_simplificado.php';
        file_put_contents($archivo_simplificado, $contenido_simplificado);
        
        // Verificar la versión simplificada
        $output = [];
        $return_var = 0;
        exec('php -l ' . $archivo_simplificado . ' 2>&1', $output, $return_var);
        
        if ($return_var === 0) {
            echo "¡Corrección simplificada exitosa! El archivo simplificado es sintácticamente válido.\n";
            echo "El archivo simplificado se ha guardado como: $archivo_simplificado\n";
        } else {
            echo "Todavía hay errores de sintaxis en el archivo simplificado:\n";
            echo implode("\n", $output);
        }
    }
}

echo "\nProceso completado. Utiliza la versión que funcione correctamente.\n";
echo "También puedes usar el archivo 'reservas_tabla.php' que ya está corregido y funcional.\n";
?>
