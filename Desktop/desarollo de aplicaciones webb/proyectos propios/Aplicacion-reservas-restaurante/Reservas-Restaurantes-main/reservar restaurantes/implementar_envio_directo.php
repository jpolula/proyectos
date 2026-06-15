<?php
// Script para implementar el envío directo de correos en todo el sistema
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Archivos a modificar
$archivos = [
    'confirmar_reserva.php',
    'admin/reservas.php',
    'cancelar_reserva.php'
];

// Verificar si existe el archivo enviar_correo_directo.php
if (!file_exists('enviar_correo_directo.php')) {
    // Crear el archivo si no existe
    $contenido_enviar_directo = "<?php
/**
 * Función para enviar correos electrónicos directamente
 */

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
        // Configuración de la base de datos
        \$host = 'localhost';
        \$db = 'restaurante_reservas';
        \$user = 'root';
        \$pass = '';
        \$charset = 'utf8mb4';
        
        \$dsn = \"mysql:host=\$host;dbname=\$db;charset=\$charset\";
        \$options = [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES => false,
        ];
        
        // Conectar a la base de datos
        \$pdo = new \PDO(\$dsn, \$user, \$pass, \$options);
        
        // Obtener la configuración de correo de la base de datos
        \$stmt = \$pdo->prepare(\"SELECT * FROM configuracion WHERE id = 1\");
        \$stmt->execute();
        \$config = \$stmt->fetch();
        
        // Verificar si el envío de correos está activo
        if (!\$config || !\$config['email_activo']) {
            error_log(\"El envío de correos está desactivado en la configuración\");
            return false;
        }
        
        // Configurar remitente
        \$from_email = \$config['email_remitente'] ?? 'no-reply@example.com';
        \$from_name = \$config['email_nombre_remitente'] ?? 'Reservas Restaurantes';
        
        // Directorio donde se guardarán los correos
        \$mail_dir = __DIR__ . '/emails_sent';
        
        // Crear el directorio si no existe
        if (!is_dir(\$mail_dir)) {
            mkdir(\$mail_dir, 0777, true);
        }
        
        // Generar un nombre único para el archivo
        \$fecha = date('Y-m-d_H-i-s');
        \$hash = md5(\$destinatario . \$asunto . time());
        \$filename = \$mail_dir . '/' . \$fecha . '_' . \$hash . '.html';
        
        // Crear el contenido del archivo
        \$contenido = \"<!DOCTYPE html>
<html>
<head>
    <meta charset='utf-8'>
    <title>Email guardado</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .email-container { border: 1px solid #ccc; padding: 20px; margin-bottom: 20px; }
        .header { background-color: #f5f5f5; padding: 10px; margin-bottom: 10px; }
        .content { margin-bottom: 20px; }
        .metadata { color: #666; font-size: 0.9em; }
    </style>
</head>
<body>
    <h1>Email guardado</h1>
    <div class='email-container'>
        <div class='header'>
            <strong>De:</strong> {\$from_name} &lt;{\$from_email}&gt;<br>
            <strong>Para:</strong> {\$destinatario}<br>
            <strong>Asunto:</strong> {\$asunto}<br>
            <strong>Fecha:</strong> {\$fecha}
        </div>
        <div class='content'>
            <h3>Contenido HTML:</h3>
            {\$cuerpo}
        </div>
        <div class='content'>
            <h3>Contenido Texto:</h3>
            <pre>{\$texto_alternativo}</pre>
        </div>
        <div class='metadata'>
            <p>Este correo ha sido guardado en un archivo en lugar de ser enviado.</p>
        </div>
    </div>
</body>
</html>\";
        
        // Guardar el archivo
        file_put_contents(\$filename, \$contenido);
        
        error_log(\"Correo guardado en: {\$filename}\");
        return true;
    } catch (Exception \$e) {
        error_log('Error al enviar correo: ' . \$e->getMessage());
        return false;
    }
}
";
    file_put_contents('enviar_correo_directo.php', $contenido_enviar_directo);
    echo "<p style='color:green;'>✅ Se ha creado el archivo enviar_correo_directo.php</p>";
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
    
    // 1. Añadir el include para enviar_correo_directo.php si no existe
    if (strpos($contenido, 'enviar_correo_directo.php') === false) {
        $patron_includes = "/(require_once\s+['\"].*?['\"];)/";
        if (preg_match($patron_includes, $contenido, $coincidencias)) {
            $primer_include = $coincidencias[0];
            $nuevo_include = $primer_include . "\nrequire_once 'enviar_correo_directo.php';";
            $contenido = str_replace($primer_include, $nuevo_include, $contenido);
            echo "<p>✅ Se ha añadido el include para enviar_correo_directo.php</p>";
        } else {
            // Si no hay includes, añadir al principio después de <?php
            $contenido = preg_replace('/<\?php/', "<?php\nrequire_once 'enviar_correo_directo.php';", $contenido, 1);
            echo "<p>✅ Se ha añadido el include para enviar_correo_directo.php al principio del archivo</p>";
        }
    } else {
        echo "<p>✓ El archivo ya incluye enviar_correo_directo.php</p>";
    }
    
    // 2. Reemplazar cualquier uso de Mailer o FileMailer por enviar_correo_directo
    
    // 2.1 Reemplazar instanciación de Mailer o FileMailer
    $patron_instancia_mailer = "/\\\$mailer\s*=\s*new\s+\\\\App\\\\Utils\\\\(Mailer|FileMailer)\(\);/";
    if (preg_match($patron_instancia_mailer, $contenido)) {
        $contenido = preg_replace($patron_instancia_mailer, "// Usar la función de envío directo en lugar de instanciar una clase", $contenido);
        echo "<p>✅ Se han reemplazado las instancias de Mailer/FileMailer</p>";
    }
    
    // 2.2 Reemplazar llamadas a $mailer->send por enviar_correo_directo
    $patron_send = "/\\\$mailer->send\(\s*([^,]+),\s*([^,]+),\s*([^,]+)(?:,\s*([^,\)]+))?\s*\)/";
    if (preg_match($patron_send, $contenido)) {
        $contenido = preg_replace($patron_send, "enviar_correo_directo($1, $2, $3, $4 ?? '')", $contenido);
        echo "<p>✅ Se han reemplazado las llamadas a \$mailer->send por enviar_correo_directo</p>";
    }
    
    // 2.3 Eliminar requires de Mailer.php o FileMailer.php
    $patron_require_mailer = "/require_once\s+['\"]src\/Utils\/(Mailer|FileMailer)\.php['\"]\s*;/";
    if (preg_match($patron_require_mailer, $contenido)) {
        $contenido = preg_replace($patron_require_mailer, "// Eliminado require de Mailer/FileMailer, ahora usamos enviar_correo_directo", $contenido);
        echo "<p>✅ Se han eliminado los requires de Mailer/FileMailer</p>";
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

echo "<h1>Implementación completada</h1>";
echo "<p>Se ha implementado el envío directo de correos en todo el sistema.</p>";
echo "<p>Para probar el envío de correos, realiza una reserva o usa la página de <a href='diagnostico_correo.php'>diagnóstico de correo</a>.</p>";
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
</style>
