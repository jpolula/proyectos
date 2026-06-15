<?php
namespace App\Utils;

// Usar rutas absolutas para los includes
$base_path = dirname(dirname(dirname(__FILE__)));
require_once $base_path . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

class Mailer {
    public $from_email;
    public $from_name;
    public $debug;
    public $save_copy;
    public $smtp_host;
    public $smtp_port;
    public $smtp_secure;
    public $smtp_user;
    public $smtp_pass;
    public $smtp_auth;

    public function __construct() {
        // Valores predeterminados (se sobrescribirán con la configuración de la BD)
        $this->debug = false;
        $this->save_copy = true;
        $this->smtp_auth = true;
        
        // Configuración predeterminada para Gmail
        $this->smtp_host = 'smtp.gmail.com';
        $this->smtp_port = 587;
        $this->smtp_secure = 'tls';
        $this->from_name = 'Reservas Restaurantes';
        
        try {
            // Conectar a la base de datos para obtener configuración
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
            
            $pdo = new \PDO($dsn, $user, $pass, $options);
            
            // Primero intentar obtener la configuración de la tabla administrador
            $stmt = $pdo->prepare("SELECT email, email_password, email_host, email_puerto, email_seguridad FROM administrador WHERE activo = 1 LIMIT 1");
            $stmt->execute();
            $admin = $stmt->fetch();
            
            if ($admin && !empty($admin['email']) && !empty($admin['email_password'])) {
                // Usar configuración del administrador
                $this->from_email = $admin['email'];
                $this->smtp_host = !empty($admin['email_host']) ? $admin['email_host'] : 'smtp.gmail.com';
                $this->smtp_port = !empty($admin['email_puerto']) ? (int)$admin['email_puerto'] : 587;
                $this->smtp_secure = !empty($admin['email_seguridad']) ? $admin['email_seguridad'] : 'tls';
                $this->smtp_user = $admin['email'];
                $this->smtp_pass = $admin['email_password'];
                
                error_log("Configuración de correo obtenida de la tabla administrador: {$this->from_email}");
                error_log("SMTP: {$this->smtp_host}:{$this->smtp_port}, Seguridad: {$this->smtp_secure}");
            } else {
                // Si no hay configuración en administrador, intentar con la tabla configuracion
                $stmt = $pdo->prepare("SELECT email_remitente, email_nombre_remitente, email_host, email_puerto, email_usuario, email_password, email_seguridad FROM configuracion WHERE id = 1");
                $stmt->execute();
                $config = $stmt->fetch();
                
                if ($config && !empty($config['email_remitente']) && !empty($config['email_password'])) {
                    $this->from_email = $config['email_remitente'];
                    $this->from_name = !empty($config['email_nombre_remitente']) ? $config['email_nombre_remitente'] : 'Reservas Restaurantes';
                    $this->smtp_host = !empty($config['email_host']) ? $config['email_host'] : 'smtp.gmail.com';
                    $this->smtp_port = !empty($config['email_puerto']) ? (int)$config['email_puerto'] : 587;
                    $this->smtp_secure = !empty($config['email_seguridad']) ? $config['email_seguridad'] : 'tls';
                    $this->smtp_user = !empty($config['email_usuario']) ? $config['email_usuario'] : $config['email_remitente'];
                    $this->smtp_pass = $config['email_password'];
                    
                    error_log("Configuración de correo obtenida de la tabla configuracion: {$this->from_email}");
                    error_log("SMTP: {$this->smtp_host}:{$this->smtp_port}, Seguridad: {$this->smtp_secure}");
                } else {
                    // Configuración de respaldo para pruebas (solo para desarrollo)
                    $this->from_email = 'correo@ejemplo.com';
                    $this->smtp_user = 'correo@ejemplo.com';
                    $this->smtp_pass = 'contraseña_ejemplo';
                    
                    error_log("ADVERTENCIA: No se encontró configuración de correo válida en la base de datos. Usando configuración de respaldo para pruebas.");
                }
            }
            
            // Verificar que tenemos la configuración mínima necesaria
            if (empty($this->from_email) || empty($this->smtp_user) || empty($this->smtp_pass)) {
                error_log("ERROR: Faltan datos esenciales para la configuración de correo.");
                error_log("Remitente: {$this->from_email}, Usuario SMTP: {$this->smtp_user}");
            }
            
        } catch (\Exception $e) {
            error_log("Error al configurar el mailer: " . $e->getMessage());
            // No lanzamos excepción para permitir que la aplicación continúe funcionando
            // aunque el envío de correos falle
        }
    }
    
    /**
     * Envía un correo electrónico usando PHPMailer
     * 
     * @param string|array $to Dirección o direcciones de correo del destinatario
     * @param string $subject Asunto del correo
     * @param string $body Cuerpo del correo (HTML)
     * @param string $altBody Cuerpo alternativo (texto plano)
     * @return bool Éxito o fracaso del envío
     */
    public function send($to, $subject, $body, $altBody = '') {
        try {
            // Verificar que PHPMailer está disponible
            if (!class_exists('\PHPMailer\PHPMailer\PHPMailer')) {
                $autoload_path = dirname(__DIR__, 2) . '/vendor/autoload.php';
                if (file_exists($autoload_path)) {
                    require_once $autoload_path;
                } else {
                    error_log("ERROR: No se encontró el archivo autoload.php en $autoload_path");
                    return false;
                }
            }
            
            // Verificar que tenemos credenciales válidas
            if (empty($this->smtp_user) || empty($this->smtp_pass)) {
                error_log("ERROR: Faltan credenciales SMTP. Usuario: {$this->smtp_user}");
                return false;
            }
            
            // Crear instancia de PHPMailer
            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
            
            // Configuración de depuración
            $mail->SMTPDebug = \PHPMailer\PHPMailer\SMTP::DEBUG_SERVER;
            $mail->Debugoutput = function($str, $level) {
                error_log("PHPMailer [$level]: $str");
            };
            
            // Configuración del servidor SMTP
            $mail->isSMTP();
            $mail->Host = $this->smtp_host;
            $mail->Port = $this->smtp_port;
            $mail->SMTPSecure = $this->smtp_secure;
            $mail->SMTPAuth = true;
            $mail->Username = $this->smtp_user;
            $mail->Password = $this->smtp_pass;
            
            // Configuración adicional para evitar problemas de conexión
            $mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                ]
            ];
            
            // Registrar la configuración SMTP
            error_log("Configuración SMTP: Host={$this->smtp_host}, Puerto={$this->smtp_port}, Seguridad={$this->smtp_secure}, Usuario={$this->smtp_user}");
            
            // Verificar y configurar el remitente
            if (empty($this->from_email)) {
                error_log("ERROR: Falta dirección de correo del remitente");
                return false;
            }
            
            $mail->setFrom($this->from_email, $this->from_name);
            $mail->addReplyTo($this->from_email, $this->from_name);
            
            // Verificar y configurar destinatarios
            if (empty($to)) {
                error_log("ERROR: No se especificó ningún destinatario");
                return false;
            }
            
            if (is_array($to)) {
                foreach ($to as $email) {
                    if (!empty($email)) {
                        $mail->addAddress($email);
                        error_log("Añadido destinatario: $email");
                    }
                }
            } else {
                $mail->addAddress($to);
                error_log("Añadido destinatario: $to");
            }
            
            // Verificar y configurar el contenido
            if (empty($subject)) {
                error_log("ADVERTENCIA: El asunto del correo está vacío");
                $subject = "Notificación del sistema de reservas";
            }
            
            if (empty($body)) {
                error_log("ERROR: El cuerpo del correo está vacío");
                return false;
            }
            
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
            $to_addresses = is_array($to) ? implode(', ', $to) : $to;
            error_log("Intentando enviar correo a: " . $to_addresses);
            error_log("Asunto: $subject");
            
            // Guardar una copia del correo si está habilitado
            if ($this->save_copy) {
                $this->guardarCopiaCorreo($to_addresses, $subject, $body, $altBody);
            }
            
            try {
                // Enviar el correo
                $result = $mail->send();
                
                if ($result) {
                    error_log("Correo enviado correctamente a: $to_addresses");
                } else {
                    error_log("Error al enviar correo: " . $mail->ErrorInfo);
                }
                
                return $result;
            } catch (\Exception $e) {
                error_log("Excepción al enviar correo: " . $e->getMessage());
                return false;
            }
        } catch (Exception $e) {
            error_log("Excepción al enviar correo: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Guarda una copia del correo enviado en un archivo
     * 
     * @param string $to Destinatario
     * @param string $subject Asunto
     * @param string $body Cuerpo HTML
     * @param string $altBody Cuerpo texto plano
     * @return bool Éxito o fracaso
     */
    private function guardarCopiaCorreo($to, $subject, $body, $altBody) {
        try {
            // Crear directorio para copias de correos
            $email_dir = dirname(__DIR__, 2) . '/emails_sent';
            
            if (!is_dir($email_dir)) {
                if (!@mkdir($email_dir, 0777, true)) {
                    $email_dir = sys_get_temp_dir() . '/emails_sent';
                    if (!is_dir($email_dir)) {
                        @mkdir($email_dir, 0777, true);
                    }
                }
            }
            
            // Si no se pudo crear el directorio, usar el directorio temporal
            if (!is_dir($email_dir) || !is_writable($email_dir)) {
                $email_dir = sys_get_temp_dir();
            }
            
            // Crear un nombre de archivo único
            $filename = $email_dir . '/' . date('Y-m-d_H-i-s') . '_' . md5($to . $subject) . '.html';
            
            // Crear el contenido del archivo
            $content = "<!DOCTYPE html>\n";
            $content .= "<html>\n";
            $content .= "<head>\n";
            $content .= "<meta charset=\"UTF-8\">\n";
            $content .= "<title>Copia de correo: " . htmlspecialchars($subject) . "</title>\n";
            $content .= "<style>body { font-family: Arial, sans-serif; margin: 20px; }</style>\n";
            $content .= "</head>\n";
            $content .= "<body>\n";
            $content .= "<h2>Copia de correo enviado</h2>\n";
            $content .= "<p><strong>Fecha:</strong> " . date('d/m/Y H:i:s') . "</p>\n";
            $content .= "<p><strong>Para:</strong> " . htmlspecialchars($to) . "</p>\n";
            $content .= "<p><strong>Asunto:</strong> " . htmlspecialchars($subject) . "</p>\n";
            $content .= "<hr>\n";
            $content .= "<h3>Cuerpo HTML:</h3>\n";
            $content .= "<div style=\"border: 1px solid #ddd; padding: 10px; margin: 10px 0;\">\n";
            $content .= $body . "\n";
            $content .= "</div>\n";
            $content .= "<h3>Cuerpo texto plano:</h3>\n";
            $content .= "<pre style=\"border: 1px solid #ddd; padding: 10px; margin: 10px 0; white-space: pre-wrap;\">\n";
            $content .= htmlspecialchars($altBody) . "\n";
            $content .= "</pre>\n";
            $content .= "</body>\n";
            $content .= "</html>";
            
            // Guardar el archivo
            if (file_put_contents($filename, $content)) {
                error_log("Copia del correo guardada en: $filename");
                return true;
            } else {
                error_log("Error al guardar copia del correo en: $filename");
                return false;
            }
        } catch (\Exception $e) {
            error_log("Error al guardar copia del correo: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Envía un correo de confirmación de reserva al cliente
     * 
     * @param string $email Email del cliente
     * @param string $nombre Nombre del cliente
     * @param string $fecha Fecha de la reserva (formateada)
     * @param string $hora Hora de la reserva
     * @param string $turno Nombre del turno (mediodía/noche)
     * @param string $zona Zona de la reserva (interior/terraza)
     * @param int $num_personas Número de personas
     * @param string $observaciones Observaciones adicionales
     * @return bool Éxito o fracaso
     */
    public function enviarCorreoConfirmacionReserva($email, $nombre, $fecha, $hora, $turno, $zona, $num_personas, $observaciones = '') {
        // Asunto del correo
        $subject = "Confirmación de reserva - Restaurante";
        
        // Construir el cuerpo HTML del correo
        $body = "<html><body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>";
        $body .= "<div style='max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px;'>";
        $body .= "<h1 style='color: #2c3e50; border-bottom: 2px solid #eee; padding-bottom: 10px;'>Reserva Confirmada</h1>";
        $body .= "<p>Estimado/a <strong>{$nombre}</strong>,</p>";
        $body .= "<p>Nos complace confirmar su reserva en nuestro restaurante con los siguientes detalles:</p>";
        
        $body .= "<div style='background-color: #f8f9fa; border-left: 4px solid #4CAF50; padding: 15px; margin: 20px 0;'>";
        $body .= "<p><strong>Fecha:</strong> {$fecha}</p>";
        $body .= "<p><strong>Hora de llegada:</strong> {$hora} h</p>";
        $body .= "<p><strong>Turno:</strong> {$turno}</p>";
        $body .= "<p><strong>Zona:</strong> {$zona}</p>";
        $body .= "<p><strong>Número de personas:</strong> {$num_personas}</p>";
        
        if (!empty($observaciones)) {
            $body .= "<p><strong>Observaciones:</strong> {$observaciones}</p>";
        }
        
        $body .= "</div>";
        
        $body .= "<p>Le esperamos en nuestro restaurante. Si necesita modificar o cancelar su reserva, por favor contáctenos lo antes posible.</p>";
        $body .= "<p>Gracias por elegirnos.</p>";
        $body .= "<p style='margin-top: 30px; padding-top: 10px; border-top: 1px solid #eee; font-size: 12px; color: #777;'>";
        $body .= "Este es un correo automático, por favor no responda a este mensaje.";
        $body .= "</p>";
        $body .= "</div>";
        $body .= "</body></html>";
        
        // Versión texto plano
        $altBody = "Reserva Confirmada\n\n";
        $altBody .= "Estimado/a {$nombre},\n\n";
        $altBody .= "Nos complace confirmar su reserva en nuestro restaurante con los siguientes detalles:\n\n";
        $altBody .= "Fecha: {$fecha}\n";
        $altBody .= "Hora de llegada: {$hora} h\n";
        $altBody .= "Turno: {$turno}\n";
        $altBody .= "Zona: {$zona}\n";
        $altBody .= "Número de personas: {$num_personas}\n";
        
        if (!empty($observaciones)) {
            $altBody .= "Observaciones: {$observaciones}\n";
        }
        
        $altBody .= "\nLe esperamos en nuestro restaurante. Si necesita modificar o cancelar su reserva, por favor contáctenos lo antes posible.\n\n";
        $altBody .= "Gracias por elegirnos.\n\n";
        $altBody .= "Este es un correo automático, por favor no responda a este mensaje.";
        
        // Enviar el correo
        return $this->send($email, $subject, $body, $altBody);
    }
    
    /**
     * Envía un correo de notificación al cliente cuando su reserva está pendiente de aprobación
     * 
     * @param string $email Email del cliente
     * @param string $nombre Nombre del cliente
     * @param string $fecha Fecha de la reserva (formateada)
     * @param string $hora Hora de la reserva
     * @param string $turno Nombre del turno (mediodía/noche)
     * @param string $zona Zona de la reserva (interior/terraza)
     * @param int $num_personas Número de personas
     * @param string $observaciones Observaciones adicionales
     * @return bool Éxito o fracaso
     */
    public function enviarCorreoReservaPendiente($email, $nombre, $fecha, $hora, $turno, $zona, $num_personas, $observaciones = '') {
        // Asunto del correo
        $subject = "Reserva pendiente de aprobación - Restaurante";
        
        // Construir el cuerpo HTML del correo
        $body = "<html><body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>";
        $body .= "<div style='max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px;'>";
        $body .= "<h1 style='color: #2c3e50; border-bottom: 2px solid #eee; padding-bottom: 10px;'>Reserva Pendiente de Aprobación</h1>";
        $body .= "<p>Estimado/a <strong>{$nombre}</strong>,</p>";
        $body .= "<p>Hemos recibido su solicitud de reserva en nuestro restaurante. Debido al número de personas, su reserva está pendiente de aprobación por parte de nuestro equipo.</p>";
        $body .= "<p>Nos pondremos en contacto con usted lo antes posible para confirmar la disponibilidad.</p>";
        
        $body .= "<div style='background-color: #f8f9fa; border-left: 4px solid #FFC107; padding: 15px; margin: 20px 0;'>";
        $body .= "<p><strong>Fecha:</strong> {$fecha}</p>";
        $body .= "<p><strong>Hora de llegada:</strong> {$hora} h</p>";
        $body .= "<p><strong>Turno:</strong> {$turno}</p>";
        $body .= "<p><strong>Zona:</strong> {$zona}</p>";
        $body .= "<p><strong>Número de personas:</strong> {$num_personas}</p>";
        
        if (!empty($observaciones)) {
            $body .= "<p><strong>Observaciones:</strong> {$observaciones}</p>";
        }
        
        $body .= "</div>";
        
        $body .= "<p>Si tiene alguna pregunta o necesita realizar cambios, por favor contáctenos.</p>";
        $body .= "<p>Gracias por su paciencia y por elegirnos.</p>";
        $body .= "<p style='margin-top: 30px; padding-top: 10px; border-top: 1px solid #eee; font-size: 12px; color: #777;'>";
        $body .= "Este es un correo automático, por favor no responda a este mensaje.";
        $body .= "</p>";
        $body .= "</div>";
        $body .= "</body></html>";
        
        // Versión texto plano
        $altBody = "Reserva Pendiente de Aprobación\n\n";
        $altBody .= "Estimado/a {$nombre},\n\n";
        $altBody .= "Hemos recibido su solicitud de reserva en nuestro restaurante. Debido al número de personas, su reserva está pendiente de aprobación por parte de nuestro equipo.\n";
        $altBody .= "Nos pondremos en contacto con usted lo antes posible para confirmar la disponibilidad.\n\n";
        $altBody .= "Fecha: {$fecha}\n";
        $altBody .= "Hora de llegada: {$hora} h\n";
        $altBody .= "Turno: {$turno}\n";
        $altBody .= "Zona: {$zona}\n";
        $altBody .= "Número de personas: {$num_personas}\n";
        
        if (!empty($observaciones)) {
            $altBody .= "Observaciones: {$observaciones}\n";
        }
        
        $altBody .= "\nSi tiene alguna pregunta o necesita realizar cambios, por favor contáctenos.\n\n";
        $altBody .= "Gracias por su paciencia y por elegirnos.\n\n";
        $altBody .= "Este es un correo automático, por favor no responda a este mensaje.";
        
        // Enviar el correo
        return $this->send($email, $subject, $body, $altBody);
    }
    
    /**
     * Envía un correo de notificación a los administradores cuando hay una reserva pendiente
     * 
     * @param array $admin_emails Lista de emails de administradores
     * @param int $reserva_id ID de la reserva
     * @param string $nombre Nombre del cliente
     * @param string $email Email del cliente
     * @param string $fecha Fecha de la reserva (formateada)
     * @param string $hora Hora de la reserva
     * @param string $turno Nombre del turno (mediodía/noche)
     * @param string $zona Zona de la reserva (interior/terraza)
     * @param int $num_personas Número de personas
     * @param string $observaciones Observaciones adicionales
     * @return bool Éxito o fracaso
     */
    public function enviarNotificacionAdministradores($admin_emails, $reserva_id, $nombre, $email, $fecha, $hora, $turno, $zona, $num_personas, $observaciones = '') {
        // Asunto del correo
        $subject = "Nueva reserva pendiente de aprobación - ID: {$reserva_id}";
        
        // URL del panel de administración (ajustar según corresponda)
        $admin_url = "http://localhost/reservar%20restaurantes/admin/reservas.php?id={$reserva_id}";
        
        // Construir el cuerpo HTML del correo
        $body = "<html><body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>";
        $body .= "<div style='max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px;'>";
        $body .= "<h1 style='color: #2c3e50; border-bottom: 2px solid #eee; padding-bottom: 10px;'>Nueva Reserva Pendiente</h1>";
        $body .= "<p>Se ha recibido una nueva reserva que requiere aprobación:</p>";
        
        $body .= "<div style='background-color: #f8f9fa; border-left: 4px solid #007BFF; padding: 15px; margin: 20px 0;'>";
        $body .= "<p><strong>ID de Reserva:</strong> {$reserva_id}</p>";
        $body .= "<p><strong>Cliente:</strong> {$nombre}</p>";
        $body .= "<p><strong>Email:</strong> {$email}</p>";
        $body .= "<p><strong>Fecha:</strong> {$fecha}</p>";
        $body .= "<p><strong>Hora de llegada:</strong> {$hora} h</p>";
        $body .= "<p><strong>Turno:</strong> {$turno}</p>";
        $body .= "<p><strong>Zona:</strong> {$zona}</p>";
        $body .= "<p><strong>Número de personas:</strong> {$num_personas}</p>";
        
        if (!empty($observaciones)) {
            $body .= "<p><strong>Observaciones:</strong> {$observaciones}</p>";
        }
        
        $body .= "</div>";
        
        $body .= "<p style='text-align: center;'><a href='{$admin_url}' style='display: inline-block; background-color: #007BFF; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold;'>Revisar reserva en el panel</a></p>";
        
        $body .= "<p>Por favor, revise esta reserva lo antes posible.</p>";
        $body .= "<p style='margin-top: 30px; padding-top: 10px; border-top: 1px solid #eee; font-size: 12px; color: #777;'>";
        $body .= "Este es un correo automático del sistema de reservas.";
        $body .= "</p>";
        $body .= "</div>";
        $body .= "</body></html>";
        
        // Versión texto plano
        $altBody = "Nueva Reserva Pendiente\n\n";
        $altBody .= "Se ha recibido una nueva reserva que requiere aprobación:\n\n";
        $altBody .= "ID de Reserva: {$reserva_id}\n";
        $altBody .= "Cliente: {$nombre}\n";
        $altBody .= "Email: {$email}\n";
        $altBody .= "Fecha: {$fecha}\n";
        $altBody .= "Hora de llegada: {$hora} h\n";
        $altBody .= "Turno: {$turno}\n";
        $altBody .= "Zona: {$zona}\n";
        $altBody .= "Número de personas: {$num_personas}\n";
        
        if (!empty($observaciones)) {
            $altBody .= "Observaciones: {$observaciones}\n";
        }
        
        $altBody .= "\nPor favor, revise esta reserva lo antes posible.\n";
        $altBody .= "URL del panel: {$admin_url}\n\n";
        $altBody .= "Este es un correo automático del sistema de reservas.";
        
        // Enviar el correo a todos los administradores
        return $this->send($admin_emails, $subject, $body, $altBody);
    }
}
