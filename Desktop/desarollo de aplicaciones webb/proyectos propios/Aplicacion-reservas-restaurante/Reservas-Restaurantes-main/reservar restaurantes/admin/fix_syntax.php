<?php
/**
 * Script para corregir el error de sintaxis en reservas.php
 */

// Leer el contenido del archivo original
$archivo = 'reservas.php';
$contenido = file_get_contents($archivo);

// Buscar y reemplazar la sección problemática
// El problema está en el comentario dentro del bloque PHP
$contenido_corregido = str_replace(
    '<?php endforeach; ?><!-- Fin del bucle de reservas en vista móvil -->', 
    '<?php endforeach; ?>', 
    $contenido
);

// Guardar el archivo corregido
file_put_contents('reservas.php', $contenido_corregido);

echo "Archivo corregido exitosamente.\n";

// Verificar si el archivo se puede analizar correctamente
$output = [];
$return_var = 0;
exec('php -l reservas.php 2>&1', $output, $return_var);

if ($return_var === 0) {
    echo "El archivo ahora es sintácticamente válido.\n";
} else {
    echo "Todavía hay errores de sintaxis en el archivo:\n";
    echo implode("\n", $output);
}
?>
