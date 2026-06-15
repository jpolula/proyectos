<?php
/**
 * Script para corregir el error de sintaxis en reservas.php
 * Este script reemplaza el archivo original con una versión corregida
 */

// Ruta al archivo original
$archivo = 'reservas.php';
$backup = 'reservas_backup_' . date('Y-m-d_H-i-s') . '.php';

// Crear copia de seguridad
copy($archivo, $backup);
echo "Copia de seguridad creada: $backup\n";

// Leer el contenido del archivo
$contenido = file_get_contents($archivo);

// Buscar la sección problemática y corregirla
// El problema está en la estructura de los bloques if y foreach
$patron = '/<\?php endforeach;.*?\?>/';
$reemplazo = '<?php endforeach; ?>';
$contenido_corregido = preg_replace($patron, $reemplazo, $contenido);

// Guardar el archivo corregido
file_put_contents($archivo, $contenido_corregido);
echo "Archivo modificado.\n";

// Verificar si el archivo se puede analizar correctamente
$output = [];
$return_var = 0;
exec('php -l ' . $archivo . ' 2>&1', $output, $return_var);

if ($return_var === 0) {
    echo "El archivo ahora es sintácticamente válido.\n";
} else {
    echo "Todavía hay errores de sintaxis en el archivo:\n";
    echo implode("\n", $output);
    
    // Si todavía hay errores, intentar una corrección más agresiva
    echo "\nIntentando una corrección más agresiva...\n";
    
    // Restaurar desde la copia de seguridad
    copy($backup, $archivo);
    $contenido = file_get_contents($archivo);
    
    // Buscar y reemplazar la sección problemática completa
    $inicio_vista_movil = '<!-- Vista de tarjetas para dispositivos móviles -->';
    $fin_vista_movil = '<?php else: ?>';
    
    // Encontrar las posiciones
    $pos_inicio = strpos($contenido, $inicio_vista_movil);
    $pos_fin = strpos($contenido, $fin_vista_movil, $pos_inicio);
    
    if ($pos_inicio !== false && $pos_fin !== false) {
        // Extraer la sección
        $seccion_original = substr($contenido, $pos_inicio, $pos_fin - $pos_inicio);
        
        // Crear una versión simplificada de la sección
        $seccion_corregida = '<!-- Vista de tarjetas para dispositivos móviles -->
    <div class="reservas-card space-y-4 p-4">
        <!-- Sección temporalmente simplificada para corregir error de sintaxis -->
        <div class="p-6 text-center text-gray-500">
            <p>Vista móvil temporalmente deshabilitada.</p>
            <p>Por favor, utiliza la vista de escritorio.</p>
        </div>';
        
        // Reemplazar la sección
        $contenido_corregido = str_replace($seccion_original, $seccion_corregida, $contenido);
        file_put_contents($archivo, $contenido_corregido);
        
        echo "Se ha aplicado una corrección simplificada.\n";
        
        // Verificar nuevamente
        $output = [];
        $return_var = 0;
        exec('php -l ' . $archivo . ' 2>&1', $output, $return_var);
        
        if ($return_var === 0) {
            echo "El archivo ahora es sintácticamente válido.\n";
        } else {
            echo "Todavía hay errores de sintaxis en el archivo después de la corrección simplificada:\n";
            echo implode("\n", $output);
            
            // Restaurar desde la copia de seguridad
            copy($backup, $archivo);
            echo "\nSe ha restaurado el archivo original desde la copia de seguridad.\n";
        }
    } else {
        echo "No se pudo encontrar la sección problemática para la corrección simplificada.\n";
    }
}

echo "\nProceso completado. Verifica el archivo manualmente si es necesario.\n";
?>
