<?php
// Este script busca todos los archivos PHP en el directorio admin y añade la inclusión del header.php si no existe
$dir = __DIR__;
$files = glob($dir . '/*.php');

$count = 0;
$modified = 0;

foreach ($files as $file) {
    $count++;
    
    // Leer el contenido del archivo
    $content = file_get_contents($file);
    
    // Verificar si ya incluye el header.php
    if (strpos($content, "include_once '../includes/header.php';") === false) {
        // Buscar la posición después de la etiqueta <head>
        $headPos = strpos($content, '<head>');
        
        if ($headPos !== false) {
            // Buscar la posición del cierre de head
            $endHeadPos = strpos($content, '</head>', $headPos);
            
            if ($endHeadPos !== false) {
                // Insertar la inclusión del header.php antes del cierre de head
                $newContent = substr($content, 0, $endHeadPos);
                $newContent .= "\n    <?php include_once '../includes/header.php'; ?>\n";
                $newContent .= substr($content, $endHeadPos);
                
                // Guardar el archivo modificado
                file_put_contents($file, $newContent);
                $modified++;
                
                echo "Archivo modificado: " . basename($file) . "<br>";
            }
        }
    }
}

echo "<p>Total de archivos procesados: $count</p>";
echo "<p>Archivos modificados: $modified</p>";
?>
