<?php
/**
 * Script para corregir rápidamente el problema de llaves en reservas.php
 */

// Ruta al archivo
$archivo = __DIR__ . '/admin/reservas.php';
$archivo_backup = __DIR__ . '/admin/reservas.php.bak';

// Crear copia de seguridad
if (file_exists($archivo)) {
    copy($archivo, $archivo_backup);
    echo "Se ha creado una copia de seguridad en: $archivo_backup\n";
} else {
    die("Error: No se encontró el archivo reservas.php\n");
}

// Leer el contenido del archivo
$contenido = file_get_contents($archivo);
if ($contenido === false) {
    die("Error: No se pudo leer el contenido del archivo\n");
}

// Añadir una llave de cierre al final del archivo
$contenido_corregido = $contenido . "\n?>";

// Guardar el archivo corregido
if (file_put_contents($archivo, $contenido_corregido) === false) {
    die("Error: No se pudo guardar el archivo corregido\n");
}

echo "Se ha añadido una llave de cierre al final del archivo\n";
echo "Verificando la sintaxis del archivo corregido...\n";

// Verificar la sintaxis del archivo corregido
$output = [];
$return_var = 0;
exec("php -l $archivo", $output, $return_var);

if ($return_var === 0) {
    echo "¡La sintaxis del archivo es correcta!\n";
} else {
    echo "Todavía hay errores de sintaxis en el archivo:\n";
    echo implode("\n", $output) . "\n";
    
    // Restaurar la copia de seguridad
    copy($archivo_backup, $archivo);
    echo "Se ha restaurado la copia de seguridad\n";
}
