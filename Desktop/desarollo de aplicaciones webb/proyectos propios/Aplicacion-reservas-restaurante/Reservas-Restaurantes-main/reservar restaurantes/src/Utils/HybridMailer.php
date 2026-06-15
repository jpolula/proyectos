<?php
require_once '../../enviar_correo_directo.php';
namespace App\Utils;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

/**
 * Clase HybridMailer - Sistema híbrido que intenta enviar correos reales
 * pero si falla, guarda los correos en archivos locales como respaldo
 */
class HybridMailer {
    private $from_email;
    private $from_name;
    private $debug;
    private $smtp_host;
    private $smtp_port;
    private $smtp_secure;
    private $smtp_user;
    private $smtp_pass;
    private $smtp_auth;
    private $mail_dir;

    public function __construct() {
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

        // Directorio donde se guardarán los correos como respaldo
        $this->mail_dir = dirname(__DIR__, 2) . '/emails_sent';
        
        // Crear el directorio si no existe
        if (!is_dir($this->mail_dir)) {
            mkdir($this->mail_dir, 0777, true);
        }

        try {
            // Conectar a la base de datos
            $pdo = new \PDO($dsn, $user, $pass, $options);
            
            // Obtener la configuración de correo de la base de datos
            $stmt = $pdo->prepare("SELECT * FROM administrador WHERE id = 1");
            $stmt->execute();
            $config = $stmt->fetch();
            
            // Configurar remitente
            $this->from_email = $config['email_remitente'] ?? 'mafupets@gmail.com';
            $this->from_name = $config['email_nombre_remitente'] ?? 'Reservas Restaurantes';
            $this->debug = true; // Habilitar depuración
            
            // Configuración SMTP
            $this->smtp_host = 'smtp.gmail.com';
            $this->smtp_port = 587;
            $this->smtp_secure = 'tls';
            $this->smtp_user = 'mafupets@gmail.com';
            $this->smtp_pass = 'kkna ioni kpmw ouat';
            $this->smtp_auth = true;
            
        } catch (\PDOException $e) {
            // Si hay un error en la base de datos, usar valores por defecto
            $this->from_email = 'mafupets@gmail.com';
            $this->from_name = 'Reservas Restaurantes';
            $this->smtp_host = 'smtp.gmail.com';
            $this->smtp_port = 587;
            $this->smtp_secure = 'tls';
            $this->smtp_user = 'mafupets@gmail.com';
            $this->smtp_pass = 'kkna ioni kpmw ouat';
            $this->smtp_auth = true;
            error_log("Error al obtener configuración de correo: " . $e->getMessage());
        } catch (\Exception $e) {
            error_log("Error al configurar el mailer: " . $e->getMessage());
        }
    }
    
    /**
     * Envía un correo electrónico usando PHPMailer o guarda en archivo como respaldo
     * 
     * @param string|array $to Dirección o direcciones de correo del destinatario
     * @param string $subject Asunto del correo
     * @param string $body Cuerpo del correo (HTML)
     * @param string $altBody Cuerpo alternativo (texto plano)
     * @return bool Éxito o fracaso del envío
     */
    public function send($to, $subject, $body, $altBody = '') {
        // Intentar enviar el correo real primero
        $sent = $this->sendRealEmail($to, $subject, $body, $altBody);
        
        // Si el envío real falló, guardar en archivo como respaldo
        if (!$sent) {
            $sent = $this->saveEmailToFile($to, $subject, $body, $altBody);
            if ($sent) {
                error_log("El correo no pudo enviarse por SMTP, pero se guardó una copia local");
            }
        }
        
        return $sent;
    }
    
    /**
     * Intenta enviar un correo real usando PHPMailer
     */
    private function sendRealEmail($to, $subject, $body, $altBody = '') {
        try {
            // Verificar que PHPMailer está disponible
            if (!class_exists('\PHPMailer\PHPMailer\PHPMailer')) {
                require_once dirname(__DIR__, 2) . '/vendor/autoload.php';
            }
            
            // Crear instancia de PHPMailer
            $mail = new PHPMailer(true);
            
            // Configuración de depuración
            if ($this->debug) {
                $mail->SMTPDebug = SMTP::DEBUG_SERVER;
                $mail->Debugoutput = function($str, $level) {
                    error_log("PHPMailer [$level]: $str");
                };
            }
            
            // Configuración del servidor SMTP
            $mail->isSMTP();
            $mail->Host = $this->smtp_host;
            $mail->Port = $this->smtp_port;
            
            if ($this->smtp_secure) {
                $mail->SMTPSecure = $this->smtp_secure;
            }
            
            if ($this->smtp_auth) {
                $mail->SMTPAuth = true;
                $mail->Username = $this->smtp_user;
                $mail->Password = $this->smtp_pass;
            }
            
            // Configuración del remitente
            $mail->setFrom($this->from_email, $this->from_name);
            $mail->addReplyTo($this->from_email, $this->from_name);
            
            // Configuración de destinatarios
            if (is_array($to)) {
                foreach ($to as $email) {
                    $mail->addAddress($email);
                }
            } else {
                $mail->addAddress($to);
            }
            
            // Configuración del contenido
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $body;
            
            // Si no hay cuerpo alternativo, crear uno a partir del HTML
            if (empty($altBody)) {
                $altBody = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $body));
            }
            $mail->AltBody = $altBody;
            
            // Configuración de caracteres
            $mail->CharSet = 'UTF-8';
            
            // Registrar información de depuración
            if ($this->debug) {
                $to_addresses = is_array($to) ? implode(', ', $to) : $to;
                error_log("Intentando enviar correo a: " . $to_addresses);
                error_log("Asunto: $subject");
            }
            
            // Enviar el correo
            $result = $mail->send();
            
            if ($result) {
                error_log("Correo enviado correctamente por SMTP");
            } else {
                error_log("Error al enviar correo por SMTP: " . $mail->ErrorInfo);
            }
            
            return $result;
        } catch (Exception $e) {
            error_log("Excepción al enviar correo por SMTP: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Guarda una copia del correo en un archivo como respaldo
     */
    private function saveEmailToFile($to, $subject, $body, $altBody) {
        try {
            // Asegurarse de que el directorio existe
            if (!is_dir($this->mail_dir)) {
                if (!@mkdir($this->mail_dir, 0777, true)) {
                    $this->mail_dir = sys_get_temp_dir() . '/emails_sent';
                    if (!is_dir($this->mail_dir)) {
                        @mkdir($this->mail_dir, 0777, true);
                    }
                }
            }
            
            // Si no se pudo crear el directorio, usar el directorio temporal
            if (!is_dir($this->mail_dir) || !is_writable($this->mail_dir)) {
                $this->mail_dir = sys_get_temp_dir();
            }
            
            // Crear un nombre de archivo único
            $to_str = is_array($to) ? implode(',', $to) : $to;
            $filename = $this->mail_dir . '/' . date('Y-m-d_H-i-s') . '_' . md5($to_str . $subject) . '.html';
            
            // Crear el contenido del archivo
            $content = "<!DOCTYPE html>\n";
            $content .= "<html>\n";
            $content .= "<head>\n";
            $content .= "<meta charset=\"UTF-8\">\n";
            $content .= "<title>Correo: " . htmlspecialchars($subject) . "</title>\n";
            $content .= "<style>\n";
            $content .= "body { font-family: Arial, sans-serif; margin: 20px; }\n";
            $content .= ".email-container { border: 1px solid #ddd; padding: 20px; max-width: 800px; margin: 0 auto; }\n";
            $content .= ".email-header { background-color: #f8f9fa; padding: 10px; margin-bottom: 20px; }\n";
            $content .= ".email-body { margin-top: 20px; }\n";
            $content .= ".email-plain { background-color: #f8f9fa; padding: 10px; margin-top: 20px; white-space: pre-wrap; }\n";
            $content .= "</style>\n";
            $content .= "</head>\n";
            $content .= "<body>\n";
            $content .= "<div class=\"email-container\">\n";
            $content .= "<div class=\"email-header\">\n";
            $content .= "<h2>Correo (Simulado)</h2>\n";
            $content .= "<p><strong>Fecha:</strong> " . date('d/m/Y H:i:s') . "</p>\n";
            $content .= "<p><strong>Para:</strong> " . htmlspecialchars($to_str) . "</p>\n";
            $content .= "<p><strong>De:</strong> " . htmlspecialchars($this->from_name) . " &lt;" . htmlspecialchars($this->from_email) . "&gt;</p>\n";
            $content .= "<p><strong>Asunto:</strong> " . htmlspecialchars($subject) . "</p>\n";
            $content .= "</div>\n";
            $content .= "<div class=\"email-body\">\n";
            $content .= "<h3>Versión HTML:</h3>\n";
            $content .= "<html><body>" . $body . "</body></html>\n";
            $content .= "</div>\n";
            $content .= "<div class=\"email-plain\">\n";
            $content .= "<h3>Versión texto plano:</h3>\n";
            $content .= "<pre>" . htmlspecialchars($altBody) . "</pre>\n";
            $content .= "</div>\n";
            $content .= "</div>\n";
            $content .= "</body>\n";
            $content .= "</html>";
            
            // Guardar el archivo
            if (file_put_contents($filename, $content)) {
                error_log("Correo guardado como archivo en: $filename");
                return true;
            } else {
                error_log("Error al guardar correo como archivo en: $filename");
                return false;
            }
        } catch (\Exception $e) {
            error_log("Error al guardar correo como archivo: " . $e->getMessage());
            return false;
        }
    }
}
