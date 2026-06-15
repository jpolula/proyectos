<?php
// Script para implementar el envío de correo cuando se cancela una reserva
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

// Buscar la sección donde se maneja el rechazo de reservas
$patron_rechazar = "/case 'rechazar':(.*?)break;/s";
preg_match($patron_rechazar, $contenido, $coincidencias);

if (empty($coincidencias)) {
    die("No se encontró la sección de rechazo de reservas en el archivo.");
}

// Código original de rechazo
$codigo_original = $coincidencias[0];

// Nuevo código con envío de correo
$codigo_nuevo = "case 'rechazar':
                    \$stmt = \$pdo->prepare(\"UPDATE reservas SET estado = 'rechazada' WHERE id = ?\");
                    if (\$stmt->execute([\$reserva_id])) {
                        // Obtener los datos de la reserva
                        \$stmt = \$pdo->prepare(\"
                            SELECT r.*, c.nombre, c.email, c.telefono, t.nombre AS turno_nombre, 
                                   DATE_FORMAT(r.fecha, '%d/%m/%Y') AS fecha_formateada,
                                   TIME_FORMAT(r.hora, '%H:%i') AS hora_formateada
                            FROM reservas r
                            JOIN clientes c ON r.cliente_id = c.id
                            JOIN turnos t ON r.turno_id = t.id
                            WHERE r.id = ?
                        \");
                        \$stmt->execute([\$reserva_id]);
                        \$reserva = \$stmt->fetch();
                        
                        if (\$reserva) {
                            // Enviar correo de cancelación
                            try {
                                // Incluir la función de envío directo si no está incluida
                                if (!function_exists('enviar_correo_directo')) {
                                    require_once '../enviar_correo_directo.php';
                                }
                                
                                // Preparar el contenido del correo
                                \$asunto = \"Cancelación de reserva - Restaurante\";
                                
                                // Crear el cuerpo del correo en HTML
                                \$cuerpo = \"
                                    <html>
                                    <head>
                                        <style>
                                            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                                            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                                            h1 { color: #e53e3e; }
                                            .info { background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0; }
                                            .footer { margin-top: 30px; font-size: 0.9em; color: #666; }
                                        </style>
                                    </head>
                                    <body>
                                        <div class='container'>
                                            <h1>Cancelación de su reserva</h1>
                                            <p>Estimado/a <strong>{\$reserva['nombre']}</strong>,</p>
                                            <p>Lamentamos informarle que su reserva ha sido cancelada. Los detalles de la reserva eran los siguientes:</p>
                                            
                                            <div class='info'>
                                                <p><strong>Fecha:</strong> {\$reserva['fecha_formateada']}</p>
                                                <p><strong>Hora:</strong> {\$reserva['hora_formateada']}</p>
                                                <p><strong>Turno:</strong> \" . ucfirst(\$reserva['turno_nombre']) . \"</p>
                                                <p><strong>Zona:</strong> \" . (\$reserva['zona'] == 'dentro' ? 'Interior' : 'Terraza') . \"</p>
                                                <p><strong>Número de personas:</strong> {\$reserva['cantidad_personas']}</p>
                                            </div>
                                            
                                            <p>Si desea realizar una nueva reserva, puede hacerlo a través de nuestra página web o contactándonos directamente.</p>
                                            
                                            <p>Disculpe las molestias ocasionadas.</p>
                                            
                                            <div class='footer'>
                                                <p>Este es un correo automático, por favor no responda a este mensaje.</p>
                                            </div>
                                        </div>
                                    </body>
                                    </html>
                                \";
                                
                                // Texto alternativo para clientes de correo que no soportan HTML
                                \$texto_alternativo = \"
                                    Cancelación de su reserva
                                    
                                    Estimado/a {\$reserva['nombre']},
                                    
                                    Lamentamos informarle que su reserva ha sido cancelada. Los detalles de la reserva eran los siguientes:
                                    
                                    Fecha: {\$reserva['fecha_formateada']}
                                    Hora: {\$reserva['hora_formateada']}
                                    Turno: \" . ucfirst(\$reserva['turno_nombre']) . \"
                                    Zona: \" . (\$reserva['zona'] == 'dentro' ? 'Interior' : 'Terraza') . \"
                                    Número de personas: {\$reserva['cantidad_personas']}
                                    
                                    Si desea realizar una nueva reserva, puede hacerlo a través de nuestra página web o contactándonos directamente.
                                    
                                    Disculpe las molestias ocasionadas.
                                    
                                    Este es un correo automático, por favor no responda a este mensaje.
                                \";
                                
                                // Enviar el correo
                                \$enviado = enviar_correo_directo(\$reserva['email'], \$asunto, \$cuerpo, \$texto_alternativo);
                                
                                if (\$enviado) {
                                    \$mensaje = 'Reserva rechazada correctamente y se ha enviado un correo de notificación al cliente.';
                                    error_log(\"Correo de cancelación enviado correctamente a: {\$reserva['email']}\");
                                } else {
                                    \$mensaje = 'Reserva rechazada correctamente, pero no se pudo enviar el correo de notificación.';
                                    error_log(\"Error al enviar correo de cancelación a: {\$reserva['email']}\");
                                }
                                
                            } catch (\\Exception \$e) {
                                \$mensaje = 'Reserva rechazada correctamente, pero hubo un error al enviar el correo: ' . \$e->getMessage();
                                error_log(\"Excepción al enviar correo de cancelación: \" . \$e->getMessage());
                            }
                        } else {
                            \$mensaje = 'Reserva rechazada correctamente, pero no se pudo obtener la información para enviar el correo.';
                            error_log(\"No se pudo obtener información de la reserva para enviar correo de cancelación. ID: {\$reserva_id}\");
                        }
                        
                        \$tipo_mensaje = 'success';
                    }
                    break;";

// Reemplazar el código original por el nuevo
$contenido_modificado = str_replace($codigo_original, $codigo_nuevo, $contenido);

// Verificar si necesitamos incluir la función de envío directo
if (strpos($contenido_modificado, "require_once '../enviar_correo_directo.php';") === false && 
    strpos($contenido_modificado, "// Eliminado require de Mailer/FileMailer, ahora usamos enviar_correo_directo") !== false) {
    
    // Reemplazar la inclusión de Mailer por la función de envío directo
    $contenido_modificado = str_replace(
        "// Eliminado require de Mailer/FileMailer, ahora usamos enviar_correo_directo",
        "require_once '../enviar_correo_directo.php';",
        $contenido_modificado
    );
    
    // Eliminar el uso de la clase Mailer
    $contenido_modificado = str_replace(
        "use App\\Utils\\Mailer;",
        "",
        $contenido_modificado
    );
}

// Guardar el archivo modificado
file_put_contents($archivo, $contenido_modificado);

// Verificar si necesitamos crear la función de envío directo
if (!file_exists('enviar_correo_directo.php')) {
    // Crear la función de envío directo
    $codigo_funcion = '<?php
// Función para enviar correos directamente con PHPMailer
function enviar_correo_directo($destinatario, $asunto, $cuerpo_html, $cuerpo_texto = \'\') {
    try {
        // Verificar que PHPMailer está disponible
        if (!class_exists(\'\\PHPMailer\\PHPMailer\\PHPMailer\')) {
            require_once dirname(__DIR__) . \'/vendor/autoload.php\';
        }
        
        // Obtener configuración de correo de la base de datos
        $pdo = new PDO(\'mysql:host=localhost;dbname=restaurante_reservas\', \'root\', \'\', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        
        $stmt = $pdo->query("SELECT * FROM configuracion WHERE id = 1");
        $config = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Crear instancia de PHPMailer
        $mail = new \\PHPMailer\\PHPMailer\\PHPMailer(true);
        
        // Configuración del servidor SMTP
        $mail->isSMTP();
        $mail->Host = $config[\'email_host\'] ?? \'smtp.gmail.com\';
        $mail->Port = $config[\'email_puerto\'] ?? 587;
        
        if ($config[\'email_seguridad\'] ?? \'tls\') {
            $mail->SMTPSecure = $config[\'email_seguridad\'];
        }
        
        $mail->SMTPAuth = true;
        $mail->Username = $config[\'email_remitente\'] ?? \'mafupets@gmail.com\';
        $mail->Password = $config[\'email_password\'] ?? \'kkna ioni kpmw ouat\';
        
        // Configuración del remitente
        $mail->setFrom(
            $config[\'email_remitente\'] ?? \'mafupets@gmail.com\', 
            $config[\'email_nombre_remitente\'] ?? \'Reservas Restaurantes\'
        );
        $mail->addReplyTo(
            $config[\'email_remitente\'] ?? \'mafupets@gmail.com\', 
            $config[\'email_nombre_remitente\'] ?? \'Reservas Restaurantes\'
        );
        
        // Configuración de destinatarios
        if (is_array($destinatario)) {
            foreach ($destinatario as $email) {
                $mail->addAddress($email);
            }
        } else {
            $mail->addAddress($destinatario);
        }
        
        // Configuración del contenido
        $mail->isHTML(true);
        $mail->Subject = $asunto;
        $mail->Body = $cuerpo_html;
        
        // Si no hay cuerpo alternativo, crear uno a partir del HTML
        if (empty($cuerpo_texto)) {
            $cuerpo_texto = strip_tags(str_replace([\'<br>\', \'<br/>\', \'<br />\'], "\n", $cuerpo_html));
        }
        $mail->AltBody = $cuerpo_texto;
        
        // Configuración de caracteres
        $mail->CharSet = \'UTF-8\';
        
        // Registrar información de depuración
        $to_addresses = is_array($destinatario) ? implode(\', \', $destinatario) : $destinatario;
        error_log("Intentando enviar correo a: " . $to_addresses);
        error_log("Asunto: $asunto");
        
        // Guardar una copia del correo
        $email_dir = dirname(__DIR__) . \'/emails_sent\';
        if (!is_dir($email_dir)) {
            if (!@mkdir($email_dir, 0777, true)) {
                $email_dir = sys_get_temp_dir() . \'/emails_sent\';
                if (!is_dir($email_dir)) {
                    @mkdir($email_dir, 0777, true);
                }
            }
        }
        
        if (is_dir($email_dir) && is_writable($email_dir)) {
            $filename = $email_dir . \'/\' . date(\'Y-m-d_H-i-s\') . \'_\' . md5($to_addresses . $asunto) . \'.html\';
            file_put_contents($filename, $cuerpo_html);
        }
        
        // Enviar el correo
        $result = $mail->send();
        
        if ($result) {
            error_log("Correo enviado correctamente");
        } else {
            error_log("Error al enviar correo: " . $mail->ErrorInfo);
        }
        
        return $result;
    } catch (\\Exception $e) {
        error_log("Excepción al enviar correo: " . $e->getMessage());
        return false;
    }
}';
    
    file_put_contents('enviar_correo_directo.php', $codigo_funcion);
    echo "<p>✅ Creada la función enviar_correo_directo.php</p>";
} else {
    echo "<p>✓ La función enviar_correo_directo.php ya existe</p>";
}

echo "<h1>Implementación completada</h1>";
echo "<p>Se ha añadido la funcionalidad para enviar un correo al cliente cuando el administrador cancela una reserva.</p>";
echo "<p>Ahora, cuando se rechace una reserva desde el panel de administración, el cliente recibirá un correo electrónico informándole de la cancelación.</p>";
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
