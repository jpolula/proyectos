<?php
// Script para visualizar los correos generados

// Habilitar la visualización de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Función para listar archivos HTML en un directorio
function listarArchivosHTML($directorio) {
    if (!is_dir($directorio)) {
        return [];
    }
    
    $archivos = glob($directorio . '/*.html');
    rsort($archivos); // Ordenar por fecha (más reciente primero)
    return $archivos;
}

// Directorios donde buscar correos
$directorios = [
    __DIR__ . '/emails_sent',
    __DIR__ . '/emails',
    __DIR__ . '/correos',
    sys_get_temp_dir() . '/emails_sent',
    sys_get_temp_dir() . '/restaurante_emails',
    sys_get_temp_dir()
];

// Verificar si se solicita ver un correo específico
if (isset($_GET['file']) && !empty($_GET['file'])) {
    $file = $_GET['file'];
    
    // Verificar que el archivo existe y es seguro
    if (file_exists($file) && strpos($file, '.html') !== false) {
        // Mostrar el contenido del correo
        echo file_get_contents($file);
        exit;
    } else {
        echo "<h1>Error</h1>";
        echo "<p>El archivo solicitado no existe o no es válido.</p>";
        echo "<p><a href='ver_correos.php'>Volver a la lista de correos</a></p>";
        exit;
    }
}

// Título de la página
echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Visor de Correos - Sistema de Reservas</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; color: #333; }
        h1 { color: #2c3e50; }
        .container { max-width: 1200px; margin: 0 auto; }
        .directory { margin-bottom: 30px; border: 1px solid #ddd; border-radius: 5px; overflow: hidden; }
        .directory-header { background-color: #f5f5f5; padding: 10px 15px; border-bottom: 1px solid #ddd; }
        .directory-content { padding: 15px; }
        .email-list { list-style: none; padding: 0; }
        .email-list li { padding: 8px 0; border-bottom: 1px solid #eee; }
        .email-list li:last-child { border-bottom: none; }
        .email-list a { text-decoration: none; color: #3498db; }
        .email-list a:hover { text-decoration: underline; }
        .email-date { color: #7f8c8d; font-size: 0.9em; margin-left: 10px; }
        .alert { padding: 15px; margin-bottom: 20px; border: 1px solid transparent; border-radius: 4px; }
        .alert-info { color: #31708f; background-color: #d9edf7; border-color: #bce8f1; }
        .alert-warning { color: #8a6d3b; background-color: #fcf8e3; border-color: #faebcc; }
        .btn { display: inline-block; padding: 6px 12px; margin-bottom: 0; font-size: 14px; font-weight: 400; line-height: 1.42857143; text-align: center; white-space: nowrap; vertical-align: middle; cursor: pointer; background-image: none; border: 1px solid transparent; border-radius: 4px; text-decoration: none; }
        .btn-primary { color: #fff; background-color: #337ab7; border-color: #2e6da4; }
        .btn-primary:hover { background-color: #286090; border-color: #204d74; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>Visor de Correos - Sistema de Reservas</h1>
        <p>Esta herramienta muestra los correos generados por el sistema de reservas.</p>";

// Información del sistema
echo "<div class='alert alert-info'>
    <h3>Información del sistema</h3>
    <p><strong>Directorio temporal:</strong> " . sys_get_temp_dir() . "</p>
    <p><strong>Directorio del proyecto:</strong> " . __DIR__ . "</p>
</div>";

// Buscar correos en todos los directorios posibles
$correos_encontrados = false;

foreach ($directorios as $directorio) {
    $archivos = listarArchivosHTML($directorio);
    
    echo "<div class='directory'>";
    echo "<div class='directory-header'>";
    echo "<h2>Directorio: " . htmlspecialchars($directorio) . "</h2>";
    echo "<p>Estado: " . (is_dir($directorio) ? "Existe" : "No existe") . " | ";
    echo "Permisos: " . (is_writable($directorio) ? "Escribible" : "No escribible") . "</p>";
    echo "</div>";
    
    echo "<div class='directory-content'>";
    if (!empty($archivos)) {
        $correos_encontrados = true;
        echo "<ul class='email-list'>";
        foreach ($archivos as $archivo) {
            // Solo mostrar archivos que parezcan correos
            if (strpos(basename($archivo), 'restaurante') !== false || 
                strpos(basename($archivo), 'correo') !== false || 
                strpos(basename($archivo), 'mail') !== false ||
                strpos(basename($archivo), 'reserva') !== false) {
                
                $nombre = basename($archivo);
                $fecha = date("d/m/Y H:i:s", filemtime($archivo));
                echo "<li>";
                echo "<a href='ver_correos.php?file=" . urlencode($archivo) . "' target='_blank'>" . htmlspecialchars($nombre) . "</a>";
                echo "<span class='email-date'>" . $fecha . "</span>";
                echo "</li>";
            }
        }
        echo "</ul>";
    } else {
        echo "<p>No se encontraron correos en este directorio.</p>";
    }
    echo "</div>";
    echo "</div>";
}

if (!$correos_encontrados) {
    echo "<div class='alert alert-warning'>";
    echo "<h3>No se encontraron correos</h3>";
    echo "<p>No se encontraron correos en ninguno de los directorios buscados.</p>";
    echo "<p>Puede que aún no se haya generado ningún correo o que los correos se estén guardando en otra ubicación.</p>";
    echo "</div>";
}

// Enlace para generar un correo de prueba
echo "<div style='margin-top: 20px;'>";
echo "<a href='test_cancelacion.php' class='btn btn-primary'>Generar correo de prueba</a>";
echo "</div>";

echo "</div></body></html>";
?>
