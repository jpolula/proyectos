<?php
// Script para modificar la redirección en confirmar_reserva.php

// Ruta al archivo
$archivo = __DIR__ . '/confirmar_reserva.php';

// Leer el contenido del archivo
$contenido = file_get_contents($archivo);

// Verificar si se pudo leer el archivo
if ($contenido === false) {
    die("Error: No se pudo leer el archivo confirmar_reserva.php");
}

// Buscar y reemplazar la redirección
$buscar = 'header("Location: confirmacion.php");';
$reemplazar = '$_SESSION[\'reserva_exitosa\'] = true; // Variable para mostrar mensaje de éxito en el index
                header("Location: index.php");';

// Realizar el reemplazo
$nuevo_contenido = str_replace($buscar, $reemplazar, $contenido);

// Verificar si se realizó algún cambio
if ($contenido === $nuevo_contenido) {
    echo "<p>No se encontró el texto exacto a reemplazar. Intentando con una búsqueda más flexible...</p>";
    
    // Intentar con una expresión regular para encontrar la redirección
    $patron = '/header\s*\(\s*["\']Location:\s*confirmacion\.php["\']\s*\)\s*;/i';
    $reemplazo = '$_SESSION[\'reserva_exitosa\'] = true; // Variable para mostrar mensaje de éxito en el index
                header("Location: index.php");';
    
    $nuevo_contenido = preg_replace($patron, $reemplazo, $contenido);
    
    if ($contenido === $nuevo_contenido) {
        die("<p>Error: No se pudo encontrar la redirección a confirmacion.php</p>");
    }
}

// Guardar el nuevo contenido en el archivo
$resultado = file_put_contents($archivo, $nuevo_contenido);

if ($resultado === false) {
    die("<p>Error: No se pudo escribir en el archivo confirmar_reserva.php</p>");
}

echo "<h1>¡Éxito!</h1>";
echo "<p>La redirección ha sido modificada correctamente. Ahora después de confirmar una reserva, el usuario será redirigido a la página principal (index.php).</p>";
echo "<p><a href='index.php'>Volver al inicio</a></p>";
?>
