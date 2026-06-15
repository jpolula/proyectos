<?php
// Script para reorganizar la visualización de observaciones y necesidades especiales
// Habilitar la visualización de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Configuración de la base de datos
$host = 'localhost';
$db = 'restaurante_reservas';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    // Conectar a la base de datos
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // Verificar si se creó la columna 'alergenos' y revertir si es necesario
    $stmt = $pdo->query("DESCRIBE reservas");
    $columnas = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (in_array('alergenos', $columnas)) {
        // Migrar los datos de la columna 'alergenos' de vuelta a 'observaciones'
        $stmt = $pdo->query("SELECT id, observaciones, alergenos FROM reservas WHERE alergenos IS NOT NULL AND alergenos != ''");
        $reservas_con_alergenos = $stmt->fetchAll();
        
        $reservas_actualizadas = 0;
        
        foreach ($reservas_con_alergenos as $reserva) {
            $observaciones = $reserva['observaciones'];
            $alergenos = $reserva['alergenos'];
            
            // Añadir los alérgenos a las observaciones
            if (!empty($alergenos)) {
                if (!empty($observaciones)) {
                    $nuevas_observaciones = $observaciones . "\n\nAlérgenos: " . $alergenos;
                } else {
                    $nuevas_observaciones = "Alérgenos: " . $alergenos;
                }
                
                $stmt = $pdo->prepare("UPDATE reservas SET observaciones = ? WHERE id = ?");
                $stmt->execute([$nuevas_observaciones, $reserva['id']]);
                $reservas_actualizadas++;
            }
        }
        
        echo "<p class='success'>✅ Se han actualizado $reservas_actualizadas reservas, reintegrando la información de alérgenos en la columna 'observaciones'</p>";
        
        // Eliminar la columna 'alergenos'
        $pdo->exec("ALTER TABLE reservas DROP COLUMN alergenos");
        echo "<p class='success'>✅ Se ha eliminado la columna 'alergenos' de la tabla 'reservas'</p>";
    } else {
        echo "<p class='info'>ℹ️ No existe la columna 'alergenos' en la tabla 'reservas'</p>";
    }
    
    // Modificar la visualización en la página de reservas
    $archivo_reservas = 'admin/reservas.php';
    
    if (file_exists($archivo_reservas)) {
        $contenido = file_get_contents($archivo_reservas);
        
        // Buscar la sección donde se muestran las observaciones y necesidades especiales
        $patron_observaciones = "/<td class=\"px-6 py-4 text-sm text-gray-500\">(.*?)<\/td>/s";
        preg_match($patron_observaciones, $contenido, $coincidencias);
        
        if (!empty($coincidencias)) {
            // Código original
            $codigo_original = $coincidencias[0];
            
            // Nuevo código con observaciones (incluyendo alérgenos) y necesidades especiales mejoradas
            $codigo_nuevo = '<td class="px-6 py-4 text-sm text-gray-500">
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
            
            // Reemplazar el código original por el nuevo
            $contenido_modificado = str_replace($codigo_original, $codigo_nuevo, $contenido);
            
            // Eliminar cualquier referencia a la columna 'alergenos' en la consulta SQL
            $contenido_modificado = preg_replace("/r\.\*, r\.alergenos,/", "r.*,", $contenido_modificado);
            
            // Guardar el archivo modificado
            file_put_contents($archivo_reservas, $contenido_modificado);
            echo "<p class='success'>✅ Se ha modificado el archivo de reservas para mejorar la visualización de observaciones y necesidades especiales</p>";
            
            // Modificar la visualización de la columna de personas para quitar el badge de alérgenos
            $patron_personas = "/<td class=\"px-6 py-4 whitespace-nowrap text-sm text-gray-500\">\s*<\?php echo \$reserva\['cantidad_personas'\]; \?>\s*<\?php if \(\$reserva\['tiene_alergenos'\]\): \?>(.*?)<\?php endif; \?>\s*<\/td>/s";
            preg_match($patron_personas, $contenido_modificado, $coincidencias_personas);
            
            if (!empty($coincidencias_personas)) {
                $personas_original = $coincidencias_personas[0];
                $personas_nuevo = '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo $reserva[\'cantidad_personas\']; ?>
                                    </td>';
                
                $contenido_modificado = str_replace($personas_original, $personas_nuevo, $contenido_modificado);
                file_put_contents($archivo_reservas, $contenido_modificado);
                echo "<p class='success'>✅ Se ha eliminado el badge de alérgenos de la columna de personas</p>";
            }
            
            // Simplificar el formulario de edición para quitar el campo específico de alérgenos
            $patron_formulario = "/<div class=\"sm:col-span-3\">\s*<label for=\"alergenos\"[^>]*>Alérgenos<\/label>(.*?)<\/div>\s*<div class=\"sm:col-span-3\">\s*<label for=\"observaciones\"[^>]*>Observaciones<\/label>/s";
            preg_match($patron_formulario, $contenido_modificado, $coincidencias_formulario);
            
            if (!empty($coincidencias_formulario)) {
                $formulario_original = $coincidencias_formulario[0];
                $formulario_nuevo = '<div class="sm:col-span-3">
                                <label for="observaciones" class="block text-sm font-medium text-gray-700 mb-1">
                                    Observaciones (incluir alérgenos)
                                </label>';
                
                $contenido_modificado = str_replace($formulario_original, $formulario_nuevo, $contenido_modificado);
                file_put_contents($archivo_reservas, $contenido_modificado);
                echo "<p class='success'>✅ Se ha simplificado el formulario de edición para incluir alérgenos en observaciones</p>";
            }
            
            // Actualizar la función JavaScript para inicializar el formulario
            $patron_js = "/function abrirModalEditar\((.*?)alergenos(.*?)\) \{(.*?)\}/s";
            preg_match($patron_js, $contenido_modificado, $coincidencias_js);
            
            if (!empty($coincidencias_js)) {
                $js_original = $coincidencias_js[0];
                $js_nuevo = preg_replace("/,\s*alergenos/", "", $js_original);
                $js_nuevo = preg_replace("/document\.getElementById\('alergenos'\)\.value = alergenos;\s*/", "", $js_nuevo);
                
                $contenido_modificado = str_replace($js_original, $js_nuevo, $contenido_modificado);
                file_put_contents($archivo_reservas, $contenido_modificado);
                echo "<p class='success'>✅ Se ha actualizado la función JavaScript para eliminar referencias a la columna de alérgenos</p>";
            }
            
            // Actualizar la función de procesamiento del formulario
            $patron_procesamiento = "/\\\$alergenos = isset\(\\\$_POST\['alergenos'\]\) \? \\\$_POST\['alergenos'\] : '';/";
            if (preg_match($patron_procesamiento, $contenido_modificado)) {
                $contenido_modificado = preg_replace($patron_procesamiento, "", $contenido_modificado);
                
                // Actualizar la consulta SQL
                $patron_sql = "/observaciones = \?, necesidades_especiales = \?, tiene_alergenos = \?, alergenos = \?/";
                $contenido_modificado = preg_replace($patron_sql, "observaciones = ?, necesidades_especiales = ?, tiene_alergenos = ?", $contenido_modificado);
                
                // Actualizar los parámetros de la consulta
                $patron_params = "/\\\$observaciones, \\\$necesidades_especiales, \\\$tiene_alergenos, \\\$alergenos,/";
                $contenido_modificado = preg_replace($patron_params, "\$observaciones, \$necesidades_especiales, \$tiene_alergenos,", $contenido_modificado);
                
                file_put_contents($archivo_reservas, $contenido_modificado);
                echo "<p class='success'>✅ Se ha actualizado la función de procesamiento del formulario</p>";
            }
            
            // Actualizar la inicialización del modal de edición
            $patron_modal = "/onclick=\"abrirModalEditar\((.*?)'<\?php echo addslashes\(\\\$reserva\['alergenos'\] \?\? ''\); \?>'(.*?)\)/s";
            if (preg_match($patron_modal, $contenido_modificado, $coincidencias_modal)) {
                $modal_original = $coincidencias_modal[0];
                $modal_nuevo = preg_replace("/, '<\?php echo addslashes\(\\\$reserva\['alergenos'\] \?\? ''\); \?>'/", "", $modal_original);
                
                $contenido_modificado = str_replace($modal_original, $modal_nuevo, $contenido_modificado);
                file_put_contents($archivo_reservas, $contenido_modificado);
                echo "<p class='success'>✅ Se ha actualizado la inicialización del modal de edición</p>";
            }
        } else {
            echo "<p class='error'>❌ No se encontró la sección de observaciones en el archivo de reservas</p>";
        }
    } else {
        echo "<p class='error'>❌ No se encontró el archivo de reservas</p>";
    }
    
    echo "<h1>Reorganización completada</h1>";
    echo "<p>Se ha reorganizado la visualización de observaciones y necesidades especiales según las instrucciones:</p>";
    echo "<ul>";
    echo "<li><strong>Observaciones:</strong> Incluyen la información de alérgenos (resaltados visualmente si están presentes)</li>";
    echo "<li><strong>Necesidades Especiales:</strong> Se muestran con un estilo mejorado y destacado</li>";
    echo "</ul>";
    echo "<p><a href='admin/reservas.php' style='display:inline-block;background-color:#4CAF50;color:white;padding:10px 15px;text-decoration:none;border-radius:4px;margin-top:20px;'>Ir a la gestión de reservas</a></p>";
    
} catch (PDOException $e) {
    echo "<h1>Error</h1>";
    echo "<p class='error'>❌ Error en la base de datos: " . $e->getMessage() . "</p>";
} catch (Exception $e) {
    echo "<h1>Error</h1>";
    echo "<p class='error'>❌ Error general: " . $e->getMessage() . "</p>";
}
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
        margin-top: 30px;
    }
    p {
        margin-bottom: 15px;
    }
    ul {
        margin-left: 20px;
        margin-bottom: 20px;
    }
    li {
        margin-bottom: 8px;
    }
    .success {
        color: #2e7d32;
        background-color: #e8f5e9;
        padding: 10px;
        border-radius: 4px;
        margin-bottom: 10px;
    }
    .error {
        color: #c62828;
        background-color: #ffebee;
        padding: 10px;
        border-radius: 4px;
        margin-bottom: 10px;
    }
    .info {
        color: #1565c0;
        background-color: #e3f2fd;
        padding: 10px;
        border-radius: 4px;
        margin-bottom: 10px;
    }
</style>
