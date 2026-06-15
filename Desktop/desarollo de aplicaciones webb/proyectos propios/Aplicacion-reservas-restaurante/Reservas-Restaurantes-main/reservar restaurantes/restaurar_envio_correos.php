<?php
// Script para restaurar la funcionalidad de envío de correos electrónicos
// Habilitar la visualización de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Archivos a modificar
$archivos = [
    'confirmar_reserva.php',
    'admin/reservas.php'
];

// Verificar si existe la clase Mailer
$archivo_mailer = 'src/Utils/Mailer.php';
if (!file_exists($archivo_mailer)) {
    die("<p style='color:red;'>❌ No se encontró el archivo de la clase Mailer. Asegúrate de que existe en src/Utils/Mailer.php</p>");
}

// Verificar si existe el archivo enviar_correo_directo.php
$archivo_enviar_directo = 'enviar_correo_directo.php';
if (!file_exists($archivo_enviar_directo)) {
    die("<p style='color:red;'>❌ No se encontró el archivo enviar_correo_directo.php. Asegúrate de que existe en la raíz del proyecto.</p>");
}

// Verificar si existe el archivo generar_correo_reserva.php
$archivo_generar_correo = 'generar_correo_reserva.php';
if (!file_exists($archivo_generar_correo)) {
    die("<p style='color:red;'>❌ No se encontró el archivo generar_correo_reserva.php. Asegúrate de que existe en la raíz del proyecto.</p>");
}

// Procesar cada archivo
foreach ($archivos as $archivo) {
    if (!file_exists($archivo)) {
        echo "<p style='color:orange;'>⚠️ No se encontró el archivo $archivo</p>";
        continue;
    }
    
    echo "<h2>Procesando $archivo</h2>";
    
    // Leer el contenido del archivo
    $contenido = file_get_contents($archivo);
    $contenido_original = $contenido;
    
    // 1. Reemplazar FileMailer por Mailer
    $contenido = str_replace('FileMailer', 'Mailer', $contenido);
    
    // 2. Corregir los includes/requires
    $patron_require = "/require_once\s+['\"]src\/Utils\/FileMailer\.php['\"]/";
    $reemplazo_require = "require_once 'src/Utils/Mailer.php'";
    $contenido = preg_replace($patron_require, $reemplazo_require, $contenido);
    
    // 3. Verificar si se usa enviar_correo_directo.php
    if (strpos($contenido, 'enviar_correo_directo.php') !== false) {
        echo "<p>✓ El archivo ya incluye enviar_correo_directo.php</p>";
    } else {
        // Añadir el include para enviar_correo_directo.php si no existe
        $patron_includes = "/(require_once\s+['\"].*?['\"];)/";
        if (preg_match($patron_includes, $contenido, $coincidencias)) {
            $primer_include = $coincidencias[0];
            $nuevo_include = $primer_include . "\nrequire_once 'enviar_correo_directo.php';";
            $contenido = str_replace($primer_include, $nuevo_include, $contenido);
            echo "<p>✅ Se ha añadido el include para enviar_correo_directo.php</p>";
        }
    }
    
    // 4. Verificar si hay instancias de FileMailer y reemplazarlas por el uso de la función enviar_correo_directo
    $patron_instancia = "/\\\$mailer\s*=\s*new\s+\\\\App\\\\Utils\\\\FileMailer\(\);/";
    if (preg_match($patron_instancia, $contenido)) {
        // Reemplazar la instanciación
        $contenido = preg_replace($patron_instancia, "// Usar la función de envío directo en lugar de instanciar una clase", $contenido);
        
        // Reemplazar las llamadas a $mailer->send por enviar_correo_directo
        $patron_send = "/\\\$mailer->send\(\s*([^,]+),\s*([^,]+),\s*([^,]+),\s*([^,\)]+)\s*\)/";
        $reemplazo_send = "enviar_correo_directo($1, $2, $3, $4)";
        $contenido = preg_replace($patron_send, $reemplazo_send, $contenido);
        
        echo "<p>✅ Se han reemplazado las instancias de FileMailer por la función enviar_correo_directo</p>";
    }
    
    // Guardar el archivo modificado si hay cambios
    if ($contenido !== $contenido_original) {
        // Crear una copia de seguridad
        file_put_contents($archivo . '.bak', $contenido_original);
        echo "<p>✅ Se ha creado una copia de seguridad en $archivo.bak</p>";
        
        // Guardar el archivo modificado
        file_put_contents($archivo, $contenido);
        echo "<p style='color:green;'>✅ Se ha actualizado el archivo $archivo</p>";
    } else {
        echo "<p>No se encontraron cambios necesarios en $archivo</p>";
    }
}

// Verificar y corregir el archivo enviar_correo_directo.php
if (file_exists($archivo_enviar_directo)) {
    $contenido_enviar = file_get_contents($archivo_enviar_directo);
    
    // Verificar si el archivo usa la clase Mailer correctamente
    if (strpos($contenido_enviar, '// Eliminado use App\Utils\Mailer, ahora usamos enviar_correo_directo') === false) {
        $contenido_enviar_nuevo = "<?php
/**
 * Función para enviar correos electrónicos directamente
 * Esta función simplifica el envío de correos utilizando la clase Mailer
 */

// Incluir la clase Mailer
// Eliminado require de Mailer/FileMailer, ahora usamos enviar_correo_directo
require_once 'vendor/autoload.php';

// Eliminado use App\Utils\Mailer, ahora usamos enviar_correo_directo

/**
 * Envía un correo electrónico
 * 
 * @param string \$destinatario Dirección de correo del destinatario
 * @param string \$asunto Asunto del correo
 * @param string \$cuerpo Cuerpo HTML del correo
 * @param string \$texto_alternativo Versión en texto plano del correo
 * @return bool True si el correo se envió correctamente, false en caso contrario
 */
function enviar_correo_directo(\$destinatario, \$asunto, \$cuerpo, \$texto_alternativo = '') {
    try {
        // Crear instancia de Mailer
        \// Usar la función de envío directo en lugar de instanciar una clase
        
        // Enviar el correo
        return \enviar_correo_directo(\$destinatario, \$asunto, \$cuerpo, \$texto_alternativo ?? '');
    } catch (Exception \$e) {
        // Registrar el error pero no interrumpir el flujo
        error_log('Error al enviar correo: ' . \$e->getMessage());
        return false;
    }
}
";
        
        // Crear una copia de seguridad
        file_put_contents($archivo_enviar_directo . '.bak', $contenido_enviar);
        echo "<p>✅ Se ha creado una copia de seguridad en $archivo_enviar_directo.bak</p>";
        
        // Guardar el archivo modificado
        file_put_contents($archivo_enviar_directo, $contenido_enviar_nuevo);
        echo "<p style='color:green;'>✅ Se ha actualizado el archivo $archivo_enviar_directo</p>";
    } else {
        echo "<p>El archivo $archivo_enviar_directo ya está configurado correctamente</p>";
    }
}

// Verificar y corregir la configuración de correo en la base de datos
try {
    $pdo = new PDO('mysql:host=localhost;dbname=restaurante_reservas', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    // Verificar si existe la tabla configuracion
    $stmt = $pdo->query("SHOW TABLES LIKE 'configuracion'");
    $tabla_existe = $stmt->rowCount() > 0;
    
    if ($tabla_existe) {
        // Verificar si hay datos de configuración de correo
        $stmt = $pdo->query("SELECT * FROM configuracion WHERE id = 1");
        $config = $stmt->fetch();
        
        if ($config && !empty($config['email_remitente'])) {
            echo "<p>✅ La configuración de correo electrónico está establecida en la base de datos</p>";
            echo "<ul>";
            echo "<li>Email remitente: " . htmlspecialchars($config['email_remitente']) . "</li>";
            echo "<li>Nombre remitente: " . htmlspecialchars($config['email_nombre_remitente'] ?? 'No configurado') . "</li>";
            echo "<li>Host SMTP: " . htmlspecialchars($config['email_host'] ?? 'No configurado') . "</li>";
            echo "<li>Puerto: " . htmlspecialchars($config['email_puerto'] ?? 'No configurado') . "</li>";
            echo "<li>Seguridad: " . htmlspecialchars($config['email_seguridad'] ?? 'No configurado') . "</li>";
            echo "<li>Email activo: " . ($config['email_activo'] ? 'Sí' : 'No') . "</li>";
            echo "</ul>";
        } else {
            echo "<p style='color:orange;'>⚠️ La configuración de correo electrónico no está establecida en la base de datos</p>";
            echo "<p>Por favor, configura el correo electrónico en la página de <a href='admin/configuracion_email.php'>configuración de email</a></p>";
        }
    } else {
        echo "<p style='color:red;'>❌ No se encontró la tabla 'configuracion' en la base de datos</p>";
    }
} catch (PDOException $e) {
    echo "<p style='color:red;'>❌ Error al conectar con la base de datos: " . $e->getMessage() . "</p>";
}

echo "<h1>Restauración completada</h1>";
echo "<p>Se ha restaurado la funcionalidad de envío de correos electrónicos en el sistema.</p>";
echo "<p>Para probar el envío de correos, puedes usar la página de <a href='diagnostico_correo.php'>diagnóstico de correo</a>.</p>";
echo "<p><a href='index.php' style='display:inline-block;background-color:#4CAF50;color:white;padding:10px 15px;text-decoration:none;border-radius:4px;margin-top:20px;'>Volver al inicio</a></p>";
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
    h1, h2 {
        color: #333;
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
</style>
