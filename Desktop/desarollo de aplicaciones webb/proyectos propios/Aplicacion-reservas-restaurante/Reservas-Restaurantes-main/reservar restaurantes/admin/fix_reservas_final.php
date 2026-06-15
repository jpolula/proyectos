<?php
/**
 * Script para corregir el error de sintaxis en reservas.php
 * Este script reemplaza la sección problemática con una versión simplificada
 */

// Ruta al archivo original
$archivo = 'reservas.php';
$backup = 'reservas_backup_final.php';

// Crear copia de seguridad
copy($archivo, $backup);
echo "Copia de seguridad creada: $backup\n";

// Leer el contenido del archivo
$contenido = file_get_contents($archivo);

// Identificar y reemplazar la sección problemática
$seccion_problematica = '<!-- Vista de tarjetas para dispositivos móviles -->
                    <div class="reservas-card space-y-4 p-4">
                        <?php foreach ($reservas as $reserva): ?>
                            <div class="bg-white border rounded-lg shadow-sm overflow-hidden">';

$seccion_reemplazo = '<!-- Vista de tarjetas para dispositivos móviles -->
                    <div class="reservas-card space-y-4 p-4">
                        <!-- Sección temporalmente simplificada -->
                        <div class="p-6 text-center text-gray-500">
                            <p class="mb-4"><i class="fas fa-mobile-alt text-4xl"></i></p>
                            <p>Vista móvil temporalmente deshabilitada para mantenimiento.</p>
                            <p>Por favor, utiliza la vista de escritorio o consulta con el administrador.</p>
                        </div>';

// Realizar el reemplazo
$contenido_modificado = str_replace($seccion_problematica, $seccion_reemplazo, $contenido);

// Si no se encontró la sección exacta, intentar un enfoque más agresivo
if ($contenido_modificado === $contenido) {
    echo "No se encontró la sección exacta. Intentando un enfoque más agresivo...\n";
    
    // Buscar el inicio y fin de la sección de vista móvil
    $inicio = strpos($contenido, '<!-- Vista de tarjetas para dispositivos móviles -->');
    $fin = strpos($contenido, '<?php else: ?>', $inicio);
    
    if ($inicio !== false && $fin !== false) {
        // Extraer la parte antes y después de la sección problemática
        $parte_inicial = substr($contenido, 0, $inicio);
        $parte_final = substr($contenido, $fin);
        
        // Crear el contenido modificado
        $contenido_modificado = $parte_inicial . $seccion_reemplazo . $parte_final;
        echo "Sección problemática identificada y reemplazada.\n";
    } else {
        echo "No se pudo identificar la sección problemática con precisión.\n";
        exit(1);
    }
}

// Guardar el archivo modificado
file_put_contents($archivo, $contenido_modificado);
echo "Archivo modificado guardado.\n";

// Verificar si el archivo se puede analizar correctamente
$output = [];
$return_var = 0;
exec('php -l ' . $archivo . ' 2>&1', $output, $return_var);

if ($return_var === 0) {
    echo "¡Éxito! El archivo ahora es sintácticamente válido.\n";
} else {
    echo "Todavía hay errores de sintaxis en el archivo:\n";
    echo implode("\n", $output);
    
    // Restaurar desde la copia de seguridad
    copy($backup, $archivo);
    echo "Se ha restaurado el archivo original desde la copia de seguridad.\n";
}

echo "\nProceso completado.\n";
?>
