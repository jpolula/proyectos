<?php
// Script para corregir el error de sintaxis en reservas.php

// Leer el contenido del archivo original
$contenido = file_get_contents('reservas.php');

// Buscar y reemplazar la sección problemática
// El problema está en la estructura de los bloques if y foreach
$contenido_corregido = str_replace(
    '<?php endforeach; // Fin del bucle de reservas en vista móvil ?>',
    '<?php endforeach; ?> <!-- Fin del bucle de reservas en vista móvil -->',
    $contenido
);

// Guardar el archivo corregido
file_put_contents('reservas_corregido.php', $contenido_corregido);

echo "Archivo corregido guardado como 'reservas_corregido.php'";
?>
