<?php
// Script para crear una columna específica para alérgenos y separar esta información de las observaciones
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
    
    // 1. Verificar si la columna 'alergenos' ya existe en la tabla 'reservas'
    $stmt = $pdo->query("DESCRIBE reservas");
    $columnas = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (!in_array('alergenos', $columnas)) {
        // Añadir la columna 'alergenos' a la tabla 'reservas'
        $pdo->exec("ALTER TABLE reservas ADD COLUMN alergenos TEXT AFTER tiene_alergenos");
        echo "<p class='success'>✅ Se ha añadido la columna 'alergenos' a la tabla 'reservas'</p>";
    } else {
        echo "<p class='info'>ℹ️ La columna 'alergenos' ya existe en la tabla 'reservas'</p>";
    }
    
    // 2. Extraer información de alérgenos de la columna 'observaciones' y moverla a la columna 'alergenos'
    $stmt = $pdo->query("SELECT id, observaciones FROM reservas WHERE observaciones LIKE '%Alérgenos:%' OR observaciones LIKE '%Alergenos:%'");
    $reservas_con_alergenos = $stmt->fetchAll();
    
    $reservas_actualizadas = 0;
    
    foreach ($reservas_con_alergenos as $reserva) {
        $observaciones = $reserva['observaciones'];
        $alergenos = '';
        $nuevas_observaciones = $observaciones;
        
        // Buscar patrones de alérgenos en las observaciones
        if (preg_match('/Alérgenos:\s*(.*?)(\n|$)/i', $observaciones, $matches)) {
            $alergenos = trim($matches[1]);
            $nuevas_observaciones = trim(str_replace($matches[0], '', $observaciones));
        }
        
        // Actualizar la reserva con los alérgenos separados
        if (!empty($alergenos)) {
            $stmt = $pdo->prepare("UPDATE reservas SET alergenos = ?, observaciones = ? WHERE id = ?");
            $stmt->execute([$alergenos, $nuevas_observaciones, $reserva['id']]);
            $reservas_actualizadas++;
        }
    }
    
    echo "<p class='success'>✅ Se han actualizado $reservas_actualizadas reservas, moviendo la información de alérgenos a su propia columna</p>";
    
    // 3. Modificar la visualización en la página de reservas para mostrar alérgenos y observaciones por separado
    $archivo_reservas = 'admin/reservas.php';
    
    if (file_exists($archivo_reservas)) {
        $contenido = file_get_contents($archivo_reservas);
        
        // Buscar la sección donde se muestran las observaciones
        $patron_observaciones = "/<td class=\"px-6 py-4 text-sm text-gray-500\">(.*?)<\/td>/s";
        preg_match($patron_observaciones, $contenido, $coincidencias);
        
        if (!empty($coincidencias)) {
            // Código original
            $codigo_original = $coincidencias[0];
            
            // Nuevo código con alérgenos y observaciones separados
            $codigo_nuevo = '<td class="px-6 py-4 text-sm text-gray-500">
                                        <?php 
                                        // Mostrar alérgenos si los hay
                                        if ($reserva[\'tiene_alergenos\'] && !empty($reserva[\'alergenos\'])) {
                                            echo \'<div class="mb-2">\';
                                            echo \'<span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-sm font-medium bg-yellow-100 text-yellow-800 mr-1">\';
                                            echo \'<i class="fas fa-exclamation-triangle mr-1"></i> Alérgenos\';
                                            echo \'</span>\';
                                            echo \' \' . htmlspecialchars($reserva[\'alergenos\']);
                                            echo \'</div>\';
                                        }
                                        
                                        // Mostrar observaciones si las hay (ahora sin alérgenos)
                                        if (!empty($reserva[\'observaciones\'])) {
                                            echo \'<div class="mt-2">\';
                                            echo \'<span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-sm font-medium bg-gray-100 text-gray-800 mr-1">\';
                                            echo \'<i class="fas fa-comment mr-1"></i> Observaciones\';
                                            echo \'</span>\';
                                            echo \' \' . htmlspecialchars(substr($reserva[\'observaciones\'], 0, 100));
                                            if (strlen($reserva[\'observaciones\']) > 100) {
                                                echo \'...\';
                                            }
                                            echo \'</div>\';
                                        }
                                        
                                        // Mostrar necesidades especiales si las hay
                                        if (!empty($reserva[\'necesidades_especiales\'])) {
                                            echo \'<div class="mt-2">\';
                                            echo \'<span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-sm font-medium bg-blue-100 text-blue-800 mr-1">\';
                                            echo \'<i class="fas fa-info-circle mr-1"></i> Necesidades\';
                                            echo \'</span>\';
                                            echo \' \' . htmlspecialchars(substr($reserva[\'necesidades_especiales\'], 0, 100));
                                            if (strlen($reserva[\'necesidades_especiales\']) > 100) {
                                                echo \'...\';
                                            }
                                            echo \'</div>\';
                                        }
                                        
                                        // Si no hay nada que mostrar
                                        if (empty($reserva[\'alergenos\']) && empty($reserva[\'observaciones\']) && empty($reserva[\'necesidades_especiales\'])) {
                                            echo \'<span class="text-gray-400">Sin información adicional</span>\';
                                        }
                                        ?>
                                    </td>';
            
            // Reemplazar el código original por el nuevo
            $contenido_modificado = str_replace($codigo_original, $codigo_nuevo, $contenido);
            
            // Verificar si necesitamos añadir la columna 'alergenos' a la consulta SQL
            if (strpos($contenido, "r.alergenos") === false) {
                // Buscar la consulta SQL que obtiene las reservas
                $patron_sql = "/SELECT\s+r\.\*,\s+c\.nombre,\s+c\.email,\s+c\.telefono/";
                preg_match($patron_sql, $contenido, $coincidencias_sql);
                
                if (!empty($coincidencias_sql)) {
                    $sql_original = $coincidencias_sql[0];
                    $sql_nuevo = "SELECT r.*, r.alergenos, c.nombre, c.email, c.telefono";
                    $contenido_modificado = str_replace($sql_original, $sql_nuevo, $contenido_modificado);
                }
            }
            
            // Guardar el archivo modificado
            file_put_contents($archivo_reservas, $contenido_modificado);
            echo "<p class='success'>✅ Se ha modificado el archivo de reservas para mostrar alérgenos y observaciones por separado</p>";
        } else {
            echo "<p class='error'>❌ No se encontró la sección de observaciones en el archivo de reservas</p>";
        }
    } else {
        echo "<p class='error'>❌ No se encontró el archivo de reservas</p>";
    }
    
    // 4. Modificar el formulario de edición de reservas para separar alérgenos y observaciones
    $patron_formulario = "/<div class=\"sm:col-span-3\">\s*<label for=\"observaciones\"[^>]*>Observaciones<\/label>(.*?)<\/div>\s*<div class=\"sm:col-span-3\">\s*<label for=\"necesidades_especiales\"[^>]*>Necesidades Especiales<\/label>/s";
    preg_match($patron_formulario, $contenido, $coincidencias_formulario);
    
    if (!empty($coincidencias_formulario)) {
        $formulario_original = $coincidencias_formulario[0];
        
        $formulario_nuevo = '<div class="sm:col-span-3">
                                <label for="alergenos" class="block text-sm font-medium text-gray-700 mb-1">
                                    Alérgenos
                                </label>
                                <div class="mt-1">
                                    <textarea id="alergenos" name="alergenos" rows="2" 
                                              class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md p-2 border"></textarea>
                                </div>
                                <div class="mt-1">
                                    <label class="inline-flex items-center">
                                        <input type="checkbox" id="tiene_alergenos" name="tiene_alergenos" class="form-checkbox h-4 w-4 text-blue-600">
                                        <span class="ml-2 text-sm text-gray-600">Marcar si tiene alérgenos</span>
                                    </label>
                                </div>
                            </div>
                            
                            <div class="sm:col-span-3">
                                <label for="observaciones" class="block text-sm font-medium text-gray-700 mb-1">
                                    Observaciones
                                </label>
                                <div class="mt-1">
                                    <textarea id="observaciones" name="observaciones" rows="2" 
                                              class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md p-2 border"></textarea>
                                </div>
                            </div>
                            
                            <div class="sm:col-span-3">
                                <label for="necesidades_especiales" class="block text-sm font-medium text-gray-700 mb-1">
                                    Necesidades Especiales
                                </label>';
        
        $contenido_modificado = str_replace($formulario_original, $formulario_nuevo, $contenido_modificado);
        
        // Guardar el archivo modificado
        file_put_contents($archivo_reservas, $contenido_modificado);
        echo "<p class='success'>✅ Se ha modificado el formulario de edición para incluir un campo específico para alérgenos</p>";
    } else {
        echo "<p class='error'>❌ No se encontró el formulario de edición en el archivo de reservas</p>";
    }
    
    // 5. Modificar la función de procesamiento del formulario para guardar los alérgenos en su propia columna
    $patron_procesamiento = "/case 'modificar':(.*?)break;/s";
    preg_match($patron_procesamiento, $contenido_modificado, $coincidencias_procesamiento);
    
    if (!empty($coincidencias_procesamiento)) {
        $procesamiento_original = $coincidencias_procesamiento[0];
        
        // Buscar la parte donde se obtienen los datos del formulario
        $patron_datos = "/\\\$observaciones = isset\(\\\$_POST\['observaciones'\]\) \? \\\$_POST\['observaciones'\] : '';/";
        preg_match($patron_datos, $procesamiento_original, $coincidencias_datos);
        
        if (!empty($coincidencias_datos)) {
            $datos_original = $coincidencias_datos[0];
            $datos_nuevo = $datos_original . "\n                    \$alergenos = isset(\$_POST['alergenos']) ? \$_POST['alergenos'] : '';";
            
            $procesamiento_modificado = str_replace($datos_original, $datos_nuevo, $procesamiento_original);
            
            // Buscar la parte donde se actualiza la reserva
            $patron_update = "/UPDATE reservas \\\s+SET fecha = \?, hora = \?, turno_id = \?, zona = \?, cantidad_personas = \?, \\\s+observaciones = \?, necesidades_especiales = \?, tiene_alergenos = \?/";
            preg_match($patron_update, $procesamiento_modificado, $coincidencias_update);
            
            if (!empty($coincidencias_update)) {
                $update_original = $coincidencias_update[0];
                $update_nuevo = "UPDATE reservas \n                            SET fecha = ?, hora = ?, turno_id = ?, zona = ?, cantidad_personas = ?, \n                                observaciones = ?, necesidades_especiales = ?, tiene_alergenos = ?, alergenos = ?";
                
                $procesamiento_modificado = str_replace($update_original, $update_nuevo, $procesamiento_modificado);
                
                // Buscar la parte donde se ejecuta la consulta
                $patron_execute = "/\\\$stmt->execute\(\[(.*?)\]\)/s";
                preg_match($patron_execute, $procesamiento_modificado, $coincidencias_execute);
                
                if (!empty($coincidencias_execute)) {
                    $execute_original = $coincidencias_execute[0];
                    $execute_nuevo = str_replace("\$reserva_id", "\$alergenos, \$reserva_id", $execute_original);
                    
                    $procesamiento_modificado = str_replace($execute_original, $execute_nuevo, $procesamiento_modificado);
                    
                    // Reemplazar el procesamiento original por el modificado
                    $contenido_final = str_replace($procesamiento_original, $procesamiento_modificado, $contenido_modificado);
                    
                    // Guardar el archivo final
                    file_put_contents($archivo_reservas, $contenido_final);
                    echo "<p class='success'>✅ Se ha modificado el procesamiento del formulario para guardar los alérgenos en su propia columna</p>";
                } else {
                    echo "<p class='error'>❌ No se encontró la parte de ejecución de la consulta</p>";
                }
            } else {
                echo "<p class='error'>❌ No se encontró la consulta de actualización</p>";
            }
        } else {
            echo "<p class='error'>❌ No se encontró la parte de obtención de datos del formulario</p>";
        }
    } else {
        echo "<p class='error'>❌ No se encontró la función de procesamiento del formulario</p>";
    }
    
    // 6. Inicializar el campo de alérgenos en el formulario de edición
    $patron_inicializacion = "/abrirModalEditar\((.*?)\)/s";
    preg_match($patron_inicializacion, $contenido_modificado, $coincidencias_inicializacion);
    
    if (!empty($coincidencias_inicializacion)) {
        $inicializacion_original = $coincidencias_inicializacion[0];
        
        // Añadir el parámetro de alérgenos
        if (strpos($inicializacion_original, "alergenos") === false) {
            $inicializacion_modificada = str_replace("'<?php echo addslashes(\$reserva['necesidades_especiales'] ?? ''); ?>'", 
                                                   "'<?php echo addslashes(\$reserva['necesidades_especiales'] ?? ''); ?>', '<?php echo addslashes(\$reserva['alergenos'] ?? ''); ?>'", 
                                                   $inicializacion_original);
            
            $contenido_final = str_replace($inicializacion_original, $inicializacion_modificada, file_get_contents($archivo_reservas));
            
            // Guardar el archivo final
            file_put_contents($archivo_reservas, $contenido_final);
            echo "<p class='success'>✅ Se ha modificado la inicialización del formulario para incluir los alérgenos</p>";
        } else {
            echo "<p class='info'>ℹ️ La inicialización del formulario ya incluye los alérgenos</p>";
        }
    } else {
        echo "<p class='error'>❌ No se encontró la función de inicialización del formulario</p>";
    }
    
    // 7. Modificar la función JavaScript para inicializar el campo de alérgenos
    $patron_js = "/function abrirModalEditar\((.*?)\) \{(.*?)\}/s";
    preg_match($patron_js, file_get_contents($archivo_reservas), $coincidencias_js);
    
    if (!empty($coincidencias_js)) {
        $js_original = $coincidencias_js[0];
        
        // Verificar si ya incluye el parámetro de alérgenos
        $patron_parametros = "/function abrirModalEditar\((.*?)\) \{/";
        preg_match($patron_parametros, $js_original, $coincidencias_parametros);
        
        if (!empty($coincidencias_parametros)) {
            $parametros_original = $coincidencias_parametros[1];
            
            if (strpos($parametros_original, "alergenos") === false) {
                $parametros_nuevo = $parametros_original . ", alergenos";
                $js_modificado = str_replace($parametros_original, $parametros_nuevo, $js_original);
                
                // Añadir la inicialización del campo de alérgenos
                $patron_inicializacion_js = "/document.getElementById\('observaciones'\).value = observaciones;/";
                preg_match($patron_inicializacion_js, $js_modificado, $coincidencias_inicializacion_js);
                
                if (!empty($coincidencias_inicializacion_js)) {
                    $inicializacion_js_original = $coincidencias_inicializacion_js[0];
                    $inicializacion_js_nuevo = $inicializacion_js_original . "\n    document.getElementById('alergenos').value = alergenos;";
                    
                    $js_modificado = str_replace($inicializacion_js_original, $inicializacion_js_nuevo, $js_modificado);
                    
                    // Añadir la inicialización del checkbox de alérgenos
                    $patron_checkbox = "/document.getElementById\('tiene_alergenos'\).checked = tiene_alergenos === '1';/";
                    if (preg_match($patron_checkbox, $js_modificado)) {
                        echo "<p class='info'>ℹ️ El checkbox de alérgenos ya se inicializa correctamente</p>";
                    } else {
                        $patron_checkbox_otro = "/document.getElementById\('necesidades_especiales'\).value = necesidades_especiales;/";
                        preg_match($patron_checkbox_otro, $js_modificado, $coincidencias_checkbox);
                        
                        if (!empty($coincidencias_checkbox)) {
                            $checkbox_original = $coincidencias_checkbox[0];
                            $checkbox_nuevo = $checkbox_original . "\n    // Inicializar el checkbox de alérgenos\n    document.getElementById('tiene_alergenos').checked = tiene_alergenos === '1';";
                            
                            $js_modificado = str_replace($checkbox_original, $checkbox_nuevo, $js_modificado);
                        }
                    }
                    
                    $contenido_final = str_replace($js_original, $js_modificado, file_get_contents($archivo_reservas));
                    
                    // Guardar el archivo final
                    file_put_contents($archivo_reservas, $contenido_final);
                    echo "<p class='success'>✅ Se ha modificado la función JavaScript para inicializar el campo de alérgenos</p>";
                } else {
                    echo "<p class='error'>❌ No se encontró la inicialización del campo de observaciones en JavaScript</p>";
                }
            } else {
                echo "<p class='info'>ℹ️ La función JavaScript ya incluye el parámetro de alérgenos</p>";
            }
        } else {
            echo "<p class='error'>❌ No se encontraron los parámetros de la función JavaScript</p>";
        }
    } else {
        echo "<p class='error'>❌ No se encontró la función JavaScript para inicializar el formulario</p>";
    }
    
    echo "<h1>Implementación completada</h1>";
    echo "<p>Se ha creado una columna específica para alérgenos y se ha separado esta información de las observaciones.</p>";
    echo "<p>Ahora en la página de reservas se mostrarán:</p>";
    echo "<ul>";
    echo "<li><strong>Alérgenos:</strong> En su propia sección con un distintivo visual</li>";
    echo "<li><strong>Observaciones:</strong> Información general sobre la reserva</li>";
    echo "<li><strong>Necesidades Especiales:</strong> En su propia sección</li>";
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
