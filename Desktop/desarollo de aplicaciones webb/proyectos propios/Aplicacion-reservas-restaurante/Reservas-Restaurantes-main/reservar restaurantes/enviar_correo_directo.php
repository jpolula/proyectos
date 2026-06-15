<?php
/**
 * Función para enviar correos electrónicos directamente
 * Esta función envía los correos directamente utilizando PHPMailer
 */

// Incluir PHPMailer
require_once 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Envía un correo electrónico utilizando PHPMailer
 * 
 * @param string $destinatario Dirección de correo del destinatario
 * @param string $asunto Asunto del correo
 * @param string $cuerpo Cuerpo HTML del correo
 * @param string $texto_alternativo Versión en texto plano del correo
 * @return bool True si el correo se envió correctamente, false en caso contrario
 */
function enviar_correo_directo($destinatario, $asunto, $cuerpo, $texto_alternativo = '') {
    try {
        // Configuración de la base de datos
        $host = 'localhost';
        $db = 'restaurante_reservas';
        $user = 'root';
        $pass = '';
        $charset = 'utf8mb4';
        
        $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
        $options = [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES => false,
        ];
        
        // Conectar a la base de datos
        $pdo = new \PDO($dsn, $user, $pass, $options);
        
        // Obtener la configuración de correo de la base de datos
        $stmt = $pdo->prepare("SELECT * FROM configuracion WHERE id = 1");
        $stmt->execute();
        $config = $stmt->fetch();
        
        // Verificar si el envío de correos está activo
        if (!$config || !$config['email_activo']) {
            error_log("El envío de correos está desactivado en la configuración");
            return false;
        }
        
        // Configurar remitente
        $from_email = $config['email_remitente'] ?? 'no-reply@example.com';
        $from_name = $config['email_nombre_remitente'] ?? 'Reservas Restaurantes';
        $email_host = $config['email_host'] ?? 'smtp.gmail.com';
        $email_puerto = $config['email_puerto'] ?? 587;
        $email_seguridad = $config['email_seguridad'] ?? 'tls';
        $email_usuario = $config['email_remitente'] ?? '';
        $email_password = $config['email_password'] ?? '';
        
        // Crear instancia de PHPMailer
        $mail = new PHPMailer(true);
        
        // Configuración del servidor
        $mail->isSMTP();                                      // Usar SMTP
        $mail->Host       = $email_host;                      // Servidor SMTP
        $mail->SMTPAuth   = true;                             // Habilitar autenticación SMTP
        $mail->Username   = $email_usuario;                   // Usuario SMTP
        $mail->Password   = $email_password;                  // Contraseña SMTP
        $mail->SMTPSecure = $email_seguridad;                 // Habilitar encriptación TLS
        $mail->Port       = $email_puerto;                    // Puerto TCP
        $mail->CharSet    = 'UTF-8';                          // Codificación de caracteres
        
        // Habilitar debug si es necesario
        // $mail->SMTPDebug = 2;                              // Habilitar debug
        
        // Remitente
        $mail->setFrom($from_email, $from_name);
        
        // Destinatario
        $mail->addAddress($destinatario);                     // Añadir destinatario
        
        // Contenido
        $mail->isHTML(true);                                  // Formato HTML
        $mail->Subject = $asunto;                             // Asunto
        $mail->Body    = $cuerpo;                             // Cuerpo HTML
        $mail->AltBody = $texto_alternativo;                  // Cuerpo texto plano
        
        // Enviar el correo
        $mail->send();
        error_log("Correo enviado correctamente a: {$destinatario}");
        return true;
    } catch (Exception $e) {
        error_log('Error al enviar correo: ' . $e->getMessage());
        return false;
    }
}
