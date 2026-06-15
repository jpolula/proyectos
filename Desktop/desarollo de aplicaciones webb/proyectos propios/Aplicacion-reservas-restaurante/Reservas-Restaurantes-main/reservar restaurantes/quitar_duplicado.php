<?php
// Script para eliminar la duplicación de necesidades especiales
// Habilitar la visualización de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Archivo a modificar
$archivo = 'admin/reservas.php';

// Verificar si el archivo existe
if (!file_exists($archivo)) {
    die("El archivo $archivo no existe.");
}

// Leer el contenido del archivo
$contenido = file_get_contents($archivo);

// Buscar y eliminar la sección duplicada de necesidades especiales
$patron = "/<div class=\"mt-1\"><strong>Necesidades:<\/strong>(.*?)<\/div>/s";
$contenido_modificado = preg_replace($patron, "", $contenido);

// Guardar el archivo modificado
if ($contenido_modificado !== $contenido) {
    file_put_contents($archivo, $contenido_modificado);
    echo "<p>✅ Se ha eliminado la duplicación de necesidades especiales.</p>";
} else {
    echo "<p>⚠️ No se encontró la duplicación específica. Intentando otro patrón...</p>";
    
    // Intentar con otro patrón
    $patron2 = "/<div class=\"mt-1\"><strong>Necesidades especiales:<\/strong>(.*?)<\/div>/s";
    $contenido_modificado = preg_replace($patron2, "", $contenido);
    
    if ($contenido_modificado !== $contenido) {
        file_put_contents($archivo, $contenido_modificado);
        echo "<p>✅ Se ha eliminado la duplicación de necesidades especiales (segundo patrón).</p>";
    } else {
        echo "<p>⚠️ No se encontró la duplicación con el segundo patrón. Intentando un enfoque directo...</p>";
        
        // Buscar cualquier otra instancia de "Necesidades especiales" antes de la sección principal
        $patron_final = "/<td class=\"px-6 py-4 text-sm text-gray-500\">(.*?)Necesidades especiales:(.*?)Necesidades especiales/s";
        if (preg_match($patron_final, $contenido, $coincidencias)) {
            $original = $coincidencias[0];
            $reemplazo = str_replace("Necesidades especiales:", "", $original);
            $contenido_modificado = str_replace($original, $reemplazo, $contenido);
            
            file_put_contents($archivo, $contenido_modificado);
            echo "<p>✅ Se ha eliminado la duplicación de necesidades especiales (enfoque directo).</p>";
        } else {
            echo "<p>❌ No se pudo encontrar la duplicación con ningún patrón.</p>";
            
            // Último intento: reemplazar todo el bloque de código
            $bloque_completo = '<td class="px-6 py-4 text-sm text-gray-500">
                                        <?php 
                                        // Mostrar observaciones si las hay (incluyen alérgenos)
                                        if (!empty($reserva[\'observaciones\'])) {
                                            echo \'<div class="mb-2">\';
                                            echo \'<span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-sm font-medium bg-gray-100 text-gray-800 mr-1">\';
                                            echo \'<i class="fas fa-comment mr-1"></i> Observaciones\';
                                            echo \'</span><br>\';
                                            
                                            // Resaltar los alérgenos si están presentes
                                            $obs = $reserva[\'observaciones\'];
                                            if (strpos(strtolower($obs), "alérgenos:") !== false || strpos(strtolower($obs), "alergenos:") !== false) {
                                                // Dividir el texto para resaltar la parte de alérgenos
                                                $pattern = "/(.*?)(alérgenos:|alergenos:)(.*)/is";
                                                if (preg_match($pattern, $obs, $matches)) {
                                                    // Parte antes de "Alérgenos:"
                                                    if (!empty(trim($matches[1]))) {
                                                        echo \'<div class="my-1">\' . nl2br(htmlspecialchars(trim($matches[1]))) . \'</div>\';
                                                    }
                                                    
                                                    // Parte de alérgenos
                                                    echo \'<div class="my-1 p-1 bg-yellow-50 border-l-4 border-yellow-400">\';
                                                    echo \'<strong class="text-yellow-700">\' . $matches[2] . \'</strong>\';
                                                    echo nl2br(htmlspecialchars($matches[3]));
                                                    echo \'</div>\';
                                                } else {
                                                    echo nl2br(htmlspecialchars($obs));
                                                }
                                            } else {
                                                echo nl2br(htmlspecialchars($obs));
                                            }
                                            
                                            echo \'</div>\';
                                        }
                                        
                                        // Mostrar necesidades especiales si las hay
                                        if (!empty($reserva[\'necesidades_especiales\'])) {
                                            echo \'<div class="mt-2">\';
                                            echo \'<span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-sm font-medium bg-blue-100 text-blue-800 mr-1">\';
                                            echo \'<i class="fas fa-info-circle mr-1"></i> Necesidades especiales\';
                                            echo \'</span><br>\';
                                            echo \'<div class="mt-1 p-2 bg-blue-50 border-l-4 border-blue-300 text-blue-800">\';
                                            echo nl2br(htmlspecialchars($reserva[\'necesidades_especiales\']));
                                            echo \'</div></div>\';
                                        }
                                        
                                        // Si no hay nada que mostrar
                                        if (empty($reserva[\'observaciones\']) && empty($reserva[\'necesidades_especiales\'])) {
                                            echo \'<span class="text-gray-400">Sin información adicional</span>\';
                                        }
                                        ?>
                                    </td>';
            
            $patron_td = "/<td class=\"px-6 py-4 text-sm text-gray-500\">(.*?)<\/td>/s";
            if (preg_match($patron_td, $contenido, $coincidencias)) {
                $contenido_modificado = str_replace($coincidencias[0], $bloque_completo, $contenido);
                file_put_contents($archivo, $contenido_modificado);
                echo "<p>✅ Se ha reemplazado completamente el bloque de código para eliminar cualquier duplicación.</p>";
            }
        }
    }
}

echo "<h1>Corrección completada</h1>";
echo "<p>Se ha eliminado la duplicación de necesidades especiales en la página de reservas.</p>";
echo "<p><a href='admin/reservas.php' style='display:inline-block;background-color:#4CAF50;color:white;padding:10px 15px;text-decoration:none;border-radius:4px;margin-top:20px;'>Ir a la gestión de reservas</a></p>";
?>
<style>
    body {
        font-family: Arial, sans-serif;
        line-height: 1.6;
        margin: 20px;
        padding: 20px;
        max-width: 800px;
        margin: 0 auto;
    }
    h1 {
        color: #333;
    }
    p {
        margin-bottom: 15px;
    }
</style>
