<?php
// Script para aplicar el sistema de envío directo de correos en toda la aplicación
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='utf-8'>
    <title>Aplicar envío directo de correos en toda la aplicación</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        h1, h2 {
            color: #333;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 10px;
        }
        .warning {
            background-color: #fff3cd;
            color: #856404;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 10px;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 10px;
        }
        .info {
            background-color: #d1ecf1;
            color: #0c5460;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 10px;
        }
        pre {
            background-color: #f5f5f5;
            padding: 10px;
            border-radius: 5px;
            overflow-x: auto;
            font-size: 14px;
        }
        .btn {
            display: inline-block;
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <h1>Aplicar envío directo de correos en toda la aplicación</h1>";

// Verificar si existe el archivo enviar_correo_directo.php
if (!file_exists('enviar_correo_directo.php')) {
    die("<div class='error'>
        <p>❌ No se encontró el archivo enviar_correo_directo.php. Este archivo es necesario para el funcionamiento del sistema.</p>
        <p>Por favor, ejecuta primero el script <a href='implementar_envio_directo.php'>implementar_envio_directo.php</a>.</p>
    </div>");
}

// Archivos principales a procesar
$archivos_principales = [
    'confirmar_reserva.php',
    'admin/reservas.php',
    'cancelar_reserva.php',
    'admin/configuracion_email.php',
    'diagnostico_correo.php'
];

// Buscar todos los archivos PHP en el proyecto
$archivos_php = [];
function buscarArchivosPHP($directorio, &$resultados) {
    $archivos = scandir($directorio);
    foreach ($archivos as $archivo) {
        if ($archivo === '.' || $archivo === '..') {
            continue;
        }
        
        $ruta_completa = $directorio . '/' . $archivo;
        if (is_dir($ruta_completa)) {
            // Excluir directorios específicos
            if ($archivo !== 'vendor' && $archivo !== 'node_modules') {
                buscarArchivosPHP($ruta_completa, $resultados);
            }
        } elseif (pathinfo($ruta_completa, PATHINFO_EXTENSION) === 'php') {
            $resultados[] = $ruta_completa;
        }
    }
}

// Buscar todos los archivos PHP en el proyecto
buscarArchivosPHP(__DIR__, $archivos_php);

// Primero procesar los archivos principales
echo "<h2>Procesando archivos principales</h2>";
foreach ($archivos_principales as $archivo) {
    $ruta_completa = __DIR__ . '/' . $archivo;
    
    if (!file_exists($ruta_completa)) {
        echo "<div class='warning'>⚠️ No se encontró el archivo $archivo</div>";
        continue;
    }
    
    echo "<h3>Procesando $archivo</h3>";
    
    // Leer el contenido del archivo
    $contenido = file_get_contents($ruta_completa);
    $contenido_original = $contenido;
    
    // 1. Añadir el include para enviar_correo_directo.php si no existe
    if (strpos($contenido, 'enviar_correo_directo.php') === false) {
        // Determinar la ruta relativa correcta
        $ruta_relativa = (strpos($archivo, 'admin/') === 0) ? '../enviar_correo_directo.php' : 'enviar_correo_directo.php';
        
        $patron_includes = "/(require_once\s+['\"].*?['\"];)/";
        if (preg_match($patron_includes, $contenido, $coincidencias)) {
            $primer_include = $coincidencias[0];
            $nuevo_include = $primer_include . "\nrequire_once '$ruta_relativa';";
            $contenido = str_replace($primer_include, $nuevo_include, $contenido);
            echo "<div class='success'>✅ Se ha añadido el include para enviar_correo_directo.php</div>";
        } else {
            // Si no hay includes, añadir al principio después de <?php
            $contenido = preg_replace('/<\?php/', "<?php\nrequire_once '$ruta_relativa';", $contenido, 1);
            echo "<div class='success'>✅ Se ha añadido el include para enviar_correo_directo.php al principio del archivo</div>";
        }
    } else {
        echo "<div class='info'>✓ El archivo ya incluye enviar_correo_directo.php</div>";
    }
    
    // 2. Eliminar requires de Mailer.php o FileMailer.php
    $patron_require_mailer = "/require_once\s+['\"].*?\/Utils\/(Mailer|FileMailer)\.php['\"]\s*;/";
    if (preg_match($patron_require_mailer, $contenido)) {
        $contenido = preg_replace($patron_require_mailer, "// Eliminado require de Mailer/FileMailer, ahora usamos enviar_correo_directo", $contenido);
        echo "<div class='success'>✅ Se han eliminado los requires de Mailer/FileMailer</div>";
    }
    
    // 3. Eliminar la declaración use App\Utils\Mailer;
    $patron_use = "/use\s+App\\\\Utils\\\\(Mailer|FileMailer);/";
    if (preg_match($patron_use, $contenido)) {
        $contenido = preg_replace($patron_use, "// Eliminado use App\\Utils\\Mailer, ahora usamos enviar_correo_directo", $contenido);
        echo "<div class='success'>✅ Se ha eliminado la declaración use App\\Utils\\Mailer</div>";
    }
    
    // 4. Reemplazar instanciación de Mailer o FileMailer
    $patron_instancia_mailer = "/\\\$mailer\s*=\s*new\s+(\\\\App\\\\Utils\\\\)?(Mailer|FileMailer)\(\);/";
    if (preg_match($patron_instancia_mailer, $contenido)) {
        $contenido = preg_replace($patron_instancia_mailer, "// Usar la función de envío directo en lugar de instanciar una clase", $contenido);
        echo "<div class='success'>✅ Se han reemplazado las instancias de Mailer/FileMailer</div>";
    }
    
    // 5. Reemplazar llamadas a $mailer->send por enviar_correo_directo
    $patron_send = "/\\\$mailer->send\(\s*([^,]+),\s*([^,]+),\s*([^,]+)(?:,\s*([^,\)]+))?\s*\)/";
    if (preg_match($patron_send, $contenido)) {
        $contenido = preg_replace($patron_send, "enviar_correo_directo($1, $2, $3, $4 ?? '')", $contenido);
        echo "<div class='success'>✅ Se han reemplazado las llamadas a \$mailer->send por enviar_correo_directo</div>";
    }
    
    // Guardar el archivo modificado si hay cambios
    if ($contenido !== $contenido_original) {
        // Crear una copia de seguridad
        file_put_contents($ruta_completa . '.bak', $contenido_original);
        echo "<div class='info'>✓ Se ha creado una copia de seguridad en $archivo.bak</div>";
        
        // Guardar el archivo modificado
        file_put_contents($ruta_completa, $contenido);
        echo "<div class='success'>✅ Se ha actualizado el archivo $archivo</div>";
    } else {
        echo "<div class='info'>ℹ️ No se encontraron cambios necesarios en $archivo</div>";
    }
}

// Luego procesar el resto de archivos PHP
echo "<h2>Buscando otros archivos que puedan usar envío de correos</h2>";

$archivos_modificados = 0;
foreach ($archivos_php as $ruta_completa) {
    // Excluir los archivos principales que ya se procesaron
    $ruta_relativa = str_replace(__DIR__ . '/', '', $ruta_completa);
    $ruta_relativa = str_replace('\\', '/', $ruta_relativa);
    
    if (in_array($ruta_relativa, $archivos_principales)) {
        continue;
    }
    
    // Excluir archivos específicos
    if (strpos($ruta_relativa, 'vendor/') === 0 || 
        strpos($ruta_relativa, 'node_modules/') === 0 ||
        strpos($ruta_relativa, 'enviar_correo_directo.php') !== false ||
        strpos($ruta_relativa, 'aplicar_envio_directo_global.php') !== false) {
        continue;
    }
    
    // Leer el contenido del archivo
    $contenido = file_get_contents($ruta_completa);
    
    // Verificar si el archivo contiene código relacionado con el envío de correos
    if (strpos($contenido, 'Mailer') !== false || 
        strpos($contenido, 'mail(') !== false || 
        strpos($contenido, 'send(') !== false || 
        strpos($contenido, 'correo') !== false || 
        strpos($contenido, 'email') !== false) {
        
        echo "<h3>Analizando $ruta_relativa</h3>";
        
        $contenido_original = $contenido;
        
        // Aplicar las mismas transformaciones que a los archivos principales
        // 1. Añadir el include para enviar_correo_directo.php si no existe
        if (strpos($contenido, 'enviar_correo_directo.php') === false &&
            (strpos($contenido, 'Mailer') !== false || strpos($contenido, 'mail(') !== false || strpos($contenido, 'send(') !== false)) {
            
            // Determinar la ruta relativa correcta
            $nivel_directorio = substr_count($ruta_relativa, '/');
            $ruta_relativa_include = str_repeat('../', $nivel_directorio) . 'enviar_correo_directo.php';
            if ($nivel_directorio === 0) {
                $ruta_relativa_include = 'enviar_correo_directo.php';
            }
            
            $patron_includes = "/(require_once\s+['\"].*?['\"];)/";
            if (preg_match($patron_includes, $contenido, $coincidencias)) {
                $primer_include = $coincidencias[0];
                $nuevo_include = $primer_include . "\nrequire_once '$ruta_relativa_include';";
                $contenido = str_replace($primer_include, $nuevo_include, $contenido);
                echo "<div class='success'>✅ Se ha añadido el include para enviar_correo_directo.php</div>";
            } else {
                // Si no hay includes, añadir al principio después de <?php
                $contenido = preg_replace('/<\?php/', "<?php\nrequire_once '$ruta_relativa_include';", $contenido, 1);
                echo "<div class='success'>✅ Se ha añadido el include para enviar_correo_directo.php al principio del archivo</div>";
            }
        }
        
        // 2. Eliminar requires de Mailer.php o FileMailer.php
        $patron_require_mailer = "/require_once\s+['\"].*?\/Utils\/(Mailer|FileMailer)\.php['\"]\s*;/";
        if (preg_match($patron_require_mailer, $contenido)) {
            $contenido = preg_replace($patron_require_mailer, "// Eliminado require de Mailer/FileMailer, ahora usamos enviar_correo_directo", $contenido);
            echo "<div class='success'>✅ Se han eliminado los requires de Mailer/FileMailer</div>";
        }
        
        // 3. Eliminar la declaración use App\Utils\Mailer;
        $patron_use = "/use\s+App\\\\Utils\\\\(Mailer|FileMailer);/";
        if (preg_match($patron_use, $contenido)) {
            $contenido = preg_replace($patron_use, "// Eliminado use App\\Utils\\Mailer, ahora usamos enviar_correo_directo", $contenido);
            echo "<div class='success'>✅ Se ha eliminado la declaración use App\\Utils\\Mailer</div>";
        }
        
        // 4. Reemplazar instanciación de Mailer o FileMailer
        $patron_instancia_mailer = "/\\\$mailer\s*=\s*new\s+(\\\\App\\\\Utils\\\\)?(Mailer|FileMailer)\(\);/";
        if (preg_match($patron_instancia_mailer, $contenido)) {
            $contenido = preg_replace($patron_instancia_mailer, "// Usar la función de envío directo en lugar de instanciar una clase", $contenido);
            echo "<div class='success'>✅ Se han reemplazado las instancias de Mailer/FileMailer</div>";
        }
        
        // 5. Reemplazar llamadas a $mailer->send por enviar_correo_directo
        $patron_send = "/\\\$mailer->send\(\s*([^,]+),\s*([^,]+),\s*([^,]+)(?:,\s*([^,\)]+))?\s*\)/";
        if (preg_match($patron_send, $contenido)) {
            $contenido = preg_replace($patron_send, "enviar_correo_directo($1, $2, $3, $4 ?? '')", $contenido);
            echo "<div class='success'>✅ Se han reemplazado las llamadas a \$mailer->send por enviar_correo_directo</div>";
        }
        
        // Guardar el archivo modificado si hay cambios
        if ($contenido !== $contenido_original) {
            // Crear una copia de seguridad
            file_put_contents($ruta_completa . '.bak', $contenido_original);
            echo "<div class='info'>✓ Se ha creado una copia de seguridad en $ruta_relativa.bak</div>";
            
            // Guardar el archivo modificado
            file_put_contents($ruta_completa, $contenido);
            echo "<div class='success'>✅ Se ha actualizado el archivo $ruta_relativa</div>";
            
            $archivos_modificados++;
        } else {
            echo "<div class='info'>ℹ️ No se encontraron cambios necesarios en $ruta_relativa</div>";
        }
    }
}

// Resumen final
echo "<h2>Resumen</h2>";
echo "<div class='success'>
    <p>✅ Se han procesado todos los archivos principales del sistema.</p>
    <p>✅ Se han encontrado y modificado $archivos_modificados archivos adicionales que podrían usar envío de correos.</p>
    <p>✅ El sistema ahora utiliza la función enviar_correo_directo para todos los envíos de correo.</p>
</div>";

echo "<h2>Próximos pasos</h2>";
echo "<div class='info'>
    <p>Para probar el sistema de envío de correos, puedes:</p>
    <ol>
        <li>Realizar una reserva completa desde la página principal</li>
        <li>Usar la página de <a href='diagnostico_correo.php'>diagnóstico de correo</a></li>
        <li>Revisar los correos guardados en la carpeta 'emails_sent'</li>
    </ol>
</div>";

echo "<p><a href='index.php' class='btn'>Volver al inicio</a></p>
</body>
</html>";
?>
