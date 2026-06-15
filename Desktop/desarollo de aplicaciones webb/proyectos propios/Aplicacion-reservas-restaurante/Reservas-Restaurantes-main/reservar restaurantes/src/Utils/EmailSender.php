<?php
namespace App\Utils;

// Usar rutas absolutas para los includes
$base_path = dirname(dirname(dirname(__FILE__)));
require_once $base_path . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

/**
 * Clase EmailSender - Versión simplificada y robusta para envío de correos
 */
class EmailSender {
    /**
     * Envía un correo electrónico usando PHPMailer con configuración directa
     * Si falla, intenta usar métodos alternativos
     * 
     * @param string|array $to Destinatario(s)
     * @param string $subject Asunto del correo
     * @param string $body Cuerpo HTML del correo
     * @param string $altBody Cuerpo alternativo (texto plano)
     * @param bool $debug Habilitar depuración
     * @return bool Éxito o fracaso del envío
     */
    public static function enviarCorreo($to, $subject, $body, $altBody = '', $debug = false) {
        try {
            // Obtener configuración de correo
            $config = self::obtenerConfiguracion();
            
            // Verificar que tenemos configuración válida
            if (empty($config['smtp_user']) || empty($config['smtp_pass'])) {
                error_log("ERROR: Faltan credenciales SMTP para enviar correo. SMTP User: {$config['smtp_user']}");
                return self::enviarCorreoNativo($to, $subject, $body); // Intentar método alternativo
            }
            
            error_log("Intentando enviar correo con las siguientes credenciales:\n" .
                     "SMTP Host: {$config['smtp_host']}\n" .
                     "SMTP Port: {$config['smtp_port']}\n" .
                     "SMTP User: {$config['smtp_user']}");
            
            // Crear instancia de PHPMailer
            $mail = new PHPMailer(true);
            
            // Configuración de depuración
            if ($debug) {
                $mail->SMTPDebug = SMTP::DEBUG_SERVER;
                $mail->Debugoutput = function($str, $level) {
                    error_log("PHPMailer [$level]: $str");
                };
            } else {
                $mail->SMTPDebug = 0;
            }
            
            // Configuración del servidor SMTP
            $mail->isSMTP();
            $mail->Host = $config['smtp_host'];
            $mail->Port = $config['smtp_port'];
            $mail->SMTPSecure = $config['smtp_secure'];
            $mail->SMTPAuth = true;
            $mail->Username = $config['smtp_user'];
            $mail->Password = $config['smtp_pass'];
            $mail->CharSet = 'UTF-8';
            
            // Configuración adicional para evitar problemas de conexión
            $mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                ]
            ];
            
            // Configurar remitente
            $mail->setFrom($config['from_email'], $config['from_name']);
            
            // Configurar destinatarios
            if (is_array($to)) {
                foreach ($to as $email) {
                    if (!empty($email)) {
                        $mail->addAddress($email);
                    }
                }
            } else {
                $mail->addAddress($to);
            }
            
            // Configurar contenido
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $body;
            
            // Si no hay cuerpo alternativo, crear uno a partir del HTML
            if (empty($altBody)) {
                $altBody = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $body));
            }
            $mail->AltBody = $altBody;
            
            // Habilitar modo debug para todos los correos temporalmente
            $mail->SMTPDebug = SMTP::DEBUG_SERVER;
            $mail->Debugoutput = function($str, $level) {
                error_log("PHPMailer Debug [$level]: $str");
            };
            
            // Enviar el correo
            $result = $mail->send();
            
            if ($result) {
                $to_str = is_array($to) ? implode(', ', $to) : $to;
                error_log("Correo enviado correctamente a: $to_str");
                return true;
            } else {
                error_log("Error al enviar correo con SMTP: " . $mail->ErrorInfo);
                error_log("Intentando método alternativo...");
                // Intentar método alternativo si falla SMTP
                return self::enviarCorreoNativo($to, $subject, $body);
            }
            
        } catch (Exception $e) {
            error_log("Excepción al enviar correo con SMTP: " . $e->getMessage());
            error_log("Intentando método alternativo...");
            // Intentar método alternativo si hay excepción
            return self::enviarCorreoNativo($to, $subject, $body);
        }
    }
    
    /**
     * Método alternativo para enviar correos usando la función mail() nativa de PHP
     * 
     * @param string|array $to Destinatario(s)
     * @param string $subject Asunto del correo
     * @param string $body Cuerpo HTML del correo
     * @return bool Éxito o fracaso del envío
     */
    private static function enviarCorreoNativo($to, $subject, $body) {
        try {
            // Preparar destinatarios si es un array
            if (is_array($to)) {
                $to = implode(', ', $to);
            }
            
            // Cabeceras
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            $headers .= "From: Reservas Restaurantes <reservas@restaurante.com>" . "\r\n";
            $headers .= "Reply-To: reservas@restaurante.com" . "\r\n";
            $headers .= "X-Mailer: PHP/" . phpversion();
            
            // Intentar enviar el correo con la función mail() nativa
            $result = mail($to, $subject, $body, $headers);
            
            if ($result) {
                error_log("Correo enviado correctamente a {$to} usando mail() nativo");
            } else {
                error_log("Error al enviar correo a {$to} usando mail() nativo");
            }
            
            return $result;
        } catch (\Exception $e) {
            error_log("Excepción al enviar correo con mail() nativo: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtiene la configuración de correo desde la base de datos o usa valores por defecto
     * 
     * @return array Configuración de correo
     */
    private static function obtenerConfiguracion() {
        // Configuración por defecto (usar solo si no hay configuración en la BD)
        $config = [
            'smtp_host' => 'smtp.gmail.com',
            'smtp_port' => 587,
            'smtp_secure' => 'tls',
            'smtp_user' => 'mafupets@gmail.com', // Usar una cuenta válida por defecto
            'smtp_pass' => 'asyh gqmm gnte rsxp', // Contraseña de aplicación
            'from_email' => 'mafupets@gmail.com',
            'from_name' => 'Reservas Restaurantes'
        ];
        
        try {
            // Conectar a la base de datos
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
                $config['from_email'] = $admin['email'];
                $config['smtp_host'] = !empty($admin['email_host']) ? $admin['email_host'] : 'smtp.gmail.com';
                $config['smtp_port'] = !empty($admin['email_puerto']) ? (int)$admin['email_puerto'] : 587;
                $config['smtp_secure'] = !empty($admin['email_seguridad']) ? $admin['email_seguridad'] : 'tls';
                $config['smtp_user'] = $admin['email'];
                $config['smtp_pass'] = $admin['email_password'];
                
                error_log("Configuración de correo obtenida de la tabla administrador: {$config['from_email']}");
            } else {
                // Si no hay configuración en administrador, intentar con la tabla configuracion
                $stmt = $pdo->prepare("SELECT * FROM configuracion WHERE id = 1");
                $stmt->execute();
                $conf = $stmt->fetch();
                
                if ($conf) {
                    error_log("Configuración encontrada en la tabla configuracion");
                    
                    // Verificar si el envío de correos está activado
                    if (isset($conf['email_activo']) && $conf['email_activo'] == 0) {
                        error_log("ADVERTENCIA: El envío de correos está desactivado en la configuración (email_activo = 0)");
                        // Forzar activación para esta prueba
                        $conf['email_activo'] = 1;
                        error_log("FORZANDO ACTIVACIÓN DE CORREOS PARA PRUEBAS");
                    }
                    
                    // Asegurar que siempre tengamos valores válidos
                    if (!empty($conf['email_remitente'])) {
                        $config['from_email'] = $conf['email_remitente'];
                        $config['smtp_user'] = $conf['email_remitente']; // Usar el email remitente como usuario
                    } else {
                        error_log("ADVERTENCIA: No hay email remitente configurado, usando valor por defecto");
                    }
                    
                    if (!empty($conf['email_password'])) {
                        $config['smtp_pass'] = $conf['email_password'];
                    } else {
                        error_log("ADVERTENCIA: No hay contraseña de email configurada, usando valor por defecto");
                    }
                    
                    $config['from_name'] = !empty($conf['email_nombre_remitente']) ? $conf['email_nombre_remitente'] : 'Reservas Restaurantes';
                    $config['smtp_host'] = !empty($conf['email_host']) ? $conf['email_host'] : 'smtp.gmail.com';
                    $config['smtp_port'] = !empty($conf['email_puerto']) ? (int)$conf['email_puerto'] : 587;
                    $config['smtp_secure'] = !empty($conf['email_seguridad']) ? $conf['email_seguridad'] : 'tls';
                    
                    // Registrar información detallada para depuración
                    error_log("Configuración detallada de correo:".
                              "\nFrom Email: {$config['from_email']}".  
                              "\nFrom Name: {$config['from_name']}".  
                              "\nSMTP Host: {$config['smtp_host']}".  
                              "\nSMTP Port: {$config['smtp_port']}".  
                              "\nSMTP Secure: {$config['smtp_secure']}".  
                              "\nSMTP User: {$config['smtp_user']}".  
                              "\nEmail Activo: {$conf['email_activo']}".  
                              "\nNotificaciones Admin: {$conf['notificaciones_admin']}");
                    
                    error_log("Configuración de correo obtenida de la tabla configuracion: {$config['from_email']}");
                } else {
                    error_log("No se encontró configuración de correo en la base de datos. Usando configuración por defecto.");
                }
            }
        } catch (\Exception $e) {
            error_log("Error al obtener configuración de correo: " . $e->getMessage());
            error_log("Usando configuración de correo por defecto.");
        }
        
        return $config;
    }
    
    /**
     * Envía un correo de confirmación de reserva al cliente
     * 
     * @param string $email Email del cliente
     * @param string $nombre Nombre del cliente
     * @param array $datos_reserva Datos de la reserva (fecha, hora, turno, zona, num_personas, observaciones)
     * @return bool Éxito o fracaso del envío
     */
    public static function enviarConfirmacionReserva($email, $nombre, $datos_reserva) {
        // Verificar si el envío de correos está activado
        try {
            // Conectar a la base de datos
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
            
            // Verificar si el envío de correos está activado
            $stmt = $pdo->query("SELECT email_activo FROM configuracion WHERE id = 1");
            $config = $stmt->fetch();
            $email_activo = $config['email_activo'] ?? 0;
        
            if (!$email_activo) {
                error_log("El envío de correos está desactivado en la configuración. No se enviará correo de notificación pendiente.");
                return true; // Devolvemos true para no interrumpir el flujo
            }
        } catch (\Exception $e) {
            error_log("Error al verificar configuración de correo: " . $e->getMessage());
            // Continuamos con el envío por si acaso
        }
        
        // Asunto del correo
        $subject = "Reserva Confirmada - Restaurante";
        
        // Construir el cuerpo HTML del correo
        $body = "<html><body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>";
        $body .= "<div style='max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px;'>";
        $body .= "<h1 style='color: #2c3e50; border-bottom: 2px solid #eee; padding-bottom: 10px;'>Reserva Confirmada</h1>";
        $body .= "<p>Estimado/a <strong>{$nombre}</strong>,</p>";
        $body .= "<p>Nos complace confirmar su reserva en nuestro restaurante con los siguientes detalles:</p>";
        
        $body .= "<div style='background-color: #f8f9fa; border-left: 4px solid #28a745; padding: 15px; margin: 20px 0;'>";
        $body .= "<p><strong>Fecha:</strong> {$datos_reserva['fecha']}</p>";
        $body .= "<p><strong>Hora de llegada:</strong> {$datos_reserva['hora']} h</p>";
        $body .= "<p><strong>Turno:</strong> {$datos_reserva['turno']}</p>";
        $body .= "<p><strong>Zona:</strong> {$datos_reserva['zona']}</p>";
        $body .= "<p><strong>Número de personas:</strong> {$datos_reserva['num_personas']}</p>";
        
        if (!empty($datos_reserva['observaciones'])) {
            $body .= "<p><strong>Alérgenos:</strong> {$datos_reserva['observaciones']}</p>";
        }
        
        if (!empty($datos_reserva['necesidades_especiales'])) {
            $body .= "<p><strong>Necesidades especiales:</strong> {$datos_reserva['necesidades_especiales']}</p>";
        }
        
        // Agregar checks personalizados si existen
        if (!empty($datos_reserva['checkboxes_seleccionados'])) {
            $body .= "<p><strong>Opciones seleccionadas:</strong></p>";
            $body .= "<ul style='margin-top: 5px; padding-left: 20px;'>";
            $checkboxes = explode(', ', $datos_reserva['checkboxes_seleccionados']);
            foreach ($checkboxes as $checkbox) {
                $body .= "<li>{$checkbox}</li>";
            }
            $body .= "</ul>";
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
        $altBody .= "Fecha: {$datos_reserva['fecha']}\n";
        $altBody .= "Hora de llegada: {$datos_reserva['hora']} h\n";
        $altBody .= "Turno: {$datos_reserva['turno']}\n";
        $altBody .= "Zona: {$datos_reserva['zona']}\n";
        $altBody .= "Número de personas: {$datos_reserva['num_personas']}\n";
        
        if (!empty($datos_reserva['observaciones'])) {
            $altBody .= "Alérgenos: {$datos_reserva['observaciones']}\n";
        }
        
        if (!empty($datos_reserva['necesidades_especiales'])) {
            $altBody .= "Necesidades especiales: {$datos_reserva['necesidades_especiales']}\n";
        }
        
        // Agregar checks personalizados si existen (versión texto plano)
        if (!empty($datos_reserva['checkboxes_seleccionados'])) {
            $altBody .= "\nOpciones seleccionadas:\n";
            $checkboxes = explode(', ', $datos_reserva['checkboxes_seleccionados']);
            foreach ($checkboxes as $checkbox) {
                $altBody .= "- {$checkbox}\n";
            }
        }
        
        $altBody .= "\nLe esperamos en nuestro restaurante. Si necesita modificar o cancelar su reserva, por favor contáctenos lo antes posible.\n\n";
        $altBody .= "Gracias por elegirnos.\n\n";
        $altBody .= "Este es un correo automático, por favor no responda a este mensaje.";
        
        // Enviar el correo
        return self::enviarCorreo($email, $subject, $body, $altBody);
    }
    
    /**
     * Envía un correo de notificación de reserva pendiente al cliente
     * 
     * @param string $email Email del cliente
     * @param string $nombre Nombre del cliente
     * @param array $datos_reserva Datos de la reserva (fecha, hora, turno, zona, num_personas, observaciones)
     * @return bool Éxito o fracaso del envío
     */
    public static function enviarNotificacionReservaPendiente($email, $nombre, $datos_reserva) {
        // Verificar si el envío de correos está activado
        try {
            // Conectar a la base de datos
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
            
            // Verificar si el envío de correos está activado
            $stmt = $pdo->query("SELECT email_activo FROM configuracion WHERE id = 1");
            $config = $stmt->fetch();
            $email_activo = $config['email_activo'] ?? 0;
            
            if (!$email_activo) {
                error_log("El envío de correos está desactivado en la configuración. No se enviará correo de notificación pendiente.");
                return true; // Devolvemos true para no interrumpir el flujo
            }
        } catch (\Exception $e) {
            error_log("Error al verificar configuración de correo: " . $e->getMessage());
            // Continuamos con el envío por si acaso
        }
        
        // Asunto del correo
        $subject = "Reserva Pendiente de Aprobación - Restaurante";
        
        // Construir el cuerpo HTML del correo
        $body = "<html><body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>";
        $body .= "<div style='max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px;'>";
        $body .= "<h1 style='color: #2c3e50; border-bottom: 2px solid #eee; padding-bottom: 10px;'>Reserva Pendiente de Aprobación</h1>";
        $body .= "<p>Estimado/a <strong>{$nombre}</strong>,</p>";
        $body .= "<p>Hemos recibido su solicitud de reserva en nuestro restaurante. Debido al número de personas, su reserva está pendiente de aprobación por parte de nuestro equipo.</p>";
        $body .= "<p>Nos pondremos en contacto con usted lo antes posible para confirmar la disponibilidad.</p>";
        
        $body .= "<div style='background-color: #f8f9fa; border-left: 4px solid #FFC107; padding: 15px; margin: 20px 0;'>";
        $body .= "<p><strong>Fecha:</strong> {$datos_reserva['fecha']}</p>";
        $body .= "<p><strong>Hora de llegada:</strong> {$datos_reserva['hora']} h</p>";
        $body .= "<p><strong>Turno:</strong> {$datos_reserva['turno']}</p>";
        $body .= "<p><strong>Zona:</strong> {$datos_reserva['zona']}</p>";
        $body .= "<p><strong>Número de personas:</strong> {$datos_reserva['num_personas']}</p>";
        
        if (!empty($datos_reserva['observaciones'])) {
            $body .= "<p><strong>Alérgenos:</strong> {$datos_reserva['observaciones']}</p>";
        }
        
        if (!empty($datos_reserva['necesidades_especiales'])) {
            $body .= "<p><strong>Necesidades especiales:</strong> {$datos_reserva['necesidades_especiales']}</p>";
        }
        
        // Agregar checks personalizados si existen
        if (!empty($datos_reserva['checkboxes_seleccionados'])) {
            $body .= "<p><strong>Opciones seleccionadas:</strong></p>";
            $body .= "<ul style='margin-top: 5px; padding-left: 20px;'>";
            $checkboxes = explode(', ', $datos_reserva['checkboxes_seleccionados']);
            foreach ($checkboxes as $checkbox) {
                $body .= "<li>{$checkbox}</li>";
            }
            $body .= "</ul>";
        }
        
        $body .= "</div>";
        
        $body .= "<p>Gracias por su comprensión y paciencia.</p>";
        $body .= "<p style='margin-top: 30px; padding-top: 10px; border-top: 1px solid #eee; font-size: 12px; color: #777;'>";
        $body .= "Este es un correo automático, por favor no responda a este mensaje.";
        $body .= "</p>";
        $body .= "</div>";
        $body .= "</body></html>";
        
        // Versión texto plano
        $altBody = "Reserva Pendiente de Aprobación\n\n";
        $altBody .= "Estimado/a {$nombre},\n\n";
        $altBody .= "Hemos recibido su solicitud de reserva en nuestro restaurante. Debido al número de personas, su reserva está pendiente de aprobación por parte de nuestro equipo.\n\n";
        $altBody .= "Nos pondremos en contacto con usted lo antes posible para confirmar la disponibilidad.\n\n";
        $altBody .= "Fecha: {$datos_reserva['fecha']}\n";
        $altBody .= "Hora de llegada: {$datos_reserva['hora']} h\n";
        $altBody .= "Turno: {$datos_reserva['turno']}\n";
        $altBody .= "Zona: {$datos_reserva['zona']}\n";
        $altBody .= "Número de personas: {$datos_reserva['num_personas']}\n";
        
        if (!empty($datos_reserva['observaciones'])) {
            $altBody .= "Alérgenos: {$datos_reserva['observaciones']}\n";
        }
        
        if (!empty($datos_reserva['necesidades_especiales'])) {
            $altBody .= "Necesidades especiales: {$datos_reserva['necesidades_especiales']}\n";
        }
        
        // Agregar checks personalizados si existen (versión texto plano)
        if (!empty($datos_reserva['checkboxes_seleccionados'])) {
            $altBody .= "\nOpciones seleccionadas:\n";
            $checkboxes = explode(', ', $datos_reserva['checkboxes_seleccionados']);
            foreach ($checkboxes as $checkbox) {
                $altBody .= "- {$checkbox}\n";
            }
        }
        
        $altBody .= "\nGracias por su comprensión y paciencia.\n\n";
        $altBody .= "Este es un correo automático, por favor no responda a este mensaje.";
        
        // Enviar el correo
        return self::enviarCorreo($email, $subject, $body, $altBody);
    }
    
    /**
     * Envía un correo de notificación a los administradores sobre una nueva reserva
     * 
     * @param array $datos_reserva Datos de la reserva (fecha, hora, turno, zona, num_personas, observaciones, cliente_nombre, cliente_email)
     * @param string $estado Estado de la reserva ('pendiente' o 'confirmada')
     * @return bool Éxito o fracaso del envío
     */
    public static function enviarNotificacionAdmin($datos_reserva, $estado = 'pendiente') {
        // Registrar el inicio de la función para depuración
        error_log("Iniciando envío de notificación a administradores. Estado: $estado");
        try {
            // Conectar a la base de datos
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
            
            // Obtener la configuración de notificaciones
            $stmt = $pdo->query("SELECT * FROM configuracion WHERE id = 1");
            $config = $stmt->fetch();
            
            // Registrar la configuración obtenida para depuración
            error_log("Configuración obtenida: email_activo=" . ($config['email_activo'] ?? 'no definido') . 
                      ", notificaciones_admin=" . ($config['notificaciones_admin'] ?? 'no definido'));
            
            $notificaciones_admin = $config['notificaciones_admin'] ?? 'pendientes';
            $email_activo = $config['email_activo'] ?? 0;
            
            // Verificar si el email está activo en la configuración
            if (!$email_activo) {
                error_log("El envío de correos está desactivado en la configuración (email_activo = 0)");
                return true; // No es un error, es una configuración
            }
            
            // Verificar si se debe enviar la notificación según la configuración
            error_log("Configuración de notificaciones: notificaciones_admin = {$notificaciones_admin}, estado = {$estado}");
            
            if ($notificaciones_admin === 'ninguna') {
                error_log("Notificaciones a administradores desactivadas en la configuración (notificaciones_admin = ninguna)");
                return true; // Devolvemos true porque no es un error, es una configuración
            }
            
            if ($notificaciones_admin === 'pendientes' && $estado !== 'pendiente') {
                error_log("Notificación no enviada: configuración actual solo permite notificaciones de reservas pendientes. Estado actual: {$estado}");
                return true; // Devolvemos true porque no es un error, es una configuración
            }
            
            // Si llegamos aquí, significa que:
            // 1. notificaciones_admin = 'todas' (enviar todas las notificaciones)
            // 2. O notificaciones_admin = 'pendientes' y estado = 'pendiente'
            error_log("Se enviarán notificaciones a los administradores. Configuración: {$notificaciones_admin}, Estado: {$estado}");
            
            // Obtener los correos de todos los administradores activos
            $stmt = $pdo->prepare("SELECT id, usuario, email FROM administrador WHERE activo = 1 AND email IS NOT NULL AND email != ''");
            $stmt->execute();
            $admins = $stmt->fetchAll();
            
            if (empty($admins)) {
                error_log("No se encontraron administradores activos con correo electrónico para enviar notificación");
                return false;
            }
            
            // Registrar los administradores encontrados
            foreach ($admins as $admin) {
                error_log("Administrador encontrado: ID={$admin['id']}, Usuario={$admin['usuario']}, Email={$admin['email']}");
            }
            
            // Extraer los correos
            $admin_emails = array_column($admins, 'email');
            
            // Filtrar correos vacíos (por si acaso la consulta SQL no los filtra correctamente)
            $admin_emails = array_filter($admin_emails, function($email) {
                return !empty($email);
            });
            
            if (empty($admin_emails)) {
                error_log("Después de filtrar, no quedan correos válidos de administradores");
                // Añadir un correo de prueba para asegurar que se envía al menos a un destinatario
                $admin_emails[] = 'mafupets@gmail.com';
                error_log("Añadido correo de prueba para asegurar el envío");
            }
            
            error_log("Correos de administradores a los que se enviará la notificación: " . implode(", ", $admin_emails));
            
            // Asunto del correo según el estado
            $subject = $estado === 'pendiente' 
                ? "Nueva reserva pendiente de aprobación - Restaurante"
                : "Nueva reserva confirmada - Restaurante";
            
            // Cuerpo HTML
            $body = "<html><body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>";
            $body .= "<div style='max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px;'>";
            
            if ($estado === 'pendiente') {
                $body .= "<h1 style='color: #2c3e50; border-bottom: 2px solid #eee; padding-bottom: 10px;'>Nueva Reserva Pendiente</h1>";
                $body .= "<p>Se ha recibido una nueva reserva que requiere aprobación:</p>";
            } else {
                $body .= "<h1 style='color: #2c3e50; border-bottom: 2px solid #eee; padding-bottom: 10px;'>Nueva Reserva Confirmada</h1>";
                $body .= "<p>Se ha registrado una nueva reserva confirmada:</p>";
            }
            
            $body .= "<div style='background-color: #f8f9fa; border-left: 4px solid #FFC107; padding: 15px; margin: 20px 0;'>";
            $body .= "<p><strong>Cliente:</strong> {$datos_reserva['cliente_nombre']}</p>";
            $body .= "<p><strong>Email:</strong> {$datos_reserva['cliente_email']}</p>";
            $body .= "<p><strong>Fecha:</strong> {$datos_reserva['fecha']}</p>";
            $body .= "<p><strong>Hora de llegada:</strong> {$datos_reserva['hora']} h</p>";
            $body .= "<p><strong>Turno:</strong> {$datos_reserva['turno']}</p>";
            $body .= "<p><strong>Zona:</strong> {$datos_reserva['zona']}</p>";
            $body .= "<p><strong>Número de personas:</strong> {$datos_reserva['num_personas']}</p>";
            
            if (!empty($datos_reserva['observaciones'])) {
                $body .= "<p><strong>Alérgenos:</strong> {$datos_reserva['observaciones']}</p>";
            }
            
            if (!empty($datos_reserva['necesidades_especiales'])) {
                $body .= "<p><strong>Necesidades especiales:</strong> {$datos_reserva['necesidades_especiales']}</p>";
            }
            
            // Agregar checks personalizados si existen
            if (!empty($datos_reserva['checkboxes_seleccionados'])) {
                $body .= "<p><strong>Opciones seleccionadas:</strong></p>";
                $body .= "<ul style='margin-top: 5px; padding-left: 20px;'>";
                $checkboxes = explode(', ', $datos_reserva['checkboxes_seleccionados']);
                
                foreach ($checkboxes as $checkbox) {
                    // Extraer el texto principal y la respuesta (si existe)
                    $checkbox_text = trim($checkbox);
                    $main_text = $checkbox_text;
                    $respuesta = '';
                    
                    // Buscar texto entre paréntesis al final
                    if (preg_match('/^(.*?)\s*\((.*?)\)$/', $checkbox_text, $matches)) {
                        $main_text = trim($matches[1]);
                        $respuesta = trim($matches[2]);
                    }
                    
                    $body .= "<li style='margin-bottom: 8px;'>";
                    $body .= "<div style='font-weight: 500;'>" . htmlspecialchars($main_text) . "</div>";
                    
                    // Mostrar la respuesta si existe
                    if (!empty($respuesta)) {
                        $body .= "<div style='font-size: 0.9em; color: #4a5568; margin-left: 10px; margin-top: 2px; font-style: italic; border-left: 2px solid #e2e8f0; padding-left: 8px;'>";
                        $body .= htmlspecialchars($respuesta);
                        $body .= "</div>";
                    }
                    
                    $body .= "</li>";
                }
                $body .= "</ul>";
            }
            
            $body .= "</div>";
            
            if ($estado === 'pendiente') {
                $body .= "<p>Por favor, acceda al <a href='http://localhost/reservar%20restaurantes/admin/reservas.php' style='color: #4CAF50; text-decoration: none; font-weight: bold;'>panel de administración</a> para revisar y gestionar esta reserva.</p>";
            } else {
                $body .= "<p>Puede acceder al <a href='http://localhost/reservar%20restaurantes/admin/reservas.php' style='color: #4CAF50; text-decoration: none; font-weight: bold;'>panel de administración</a> para ver los detalles completos de esta reserva.</p>";
            }
            $body .= "<p style='margin-top: 30px; padding-top: 10px; border-top: 1px solid #eee; font-size: 12px; color: #777;'>";
            $body .= "Este es un correo automático, por favor no responda a este mensaje.";
            $body .= "</p>";
            $body .= "</div>";
            $body .= "</body></html>";
            
            // Versión texto plano
            if ($estado === 'pendiente') {
                $altBody = "Nueva Reserva Pendiente\n\n";
                $altBody .= "Se ha recibido una nueva reserva que requiere aprobación:\n\n";
            } else {
                $altBody = "Nueva Reserva Confirmada\n\n";
                $altBody .= "Se ha registrado una nueva reserva confirmada:\n\n";
            }
            $altBody .= "Cliente: {$datos_reserva['cliente_nombre']}\n";
            $altBody .= "Email: {$datos_reserva['cliente_email']}\n";
            $altBody .= "Fecha: {$datos_reserva['fecha']}\n";
            $altBody .= "Hora de llegada: {$datos_reserva['hora']} h\n";
            $altBody .= "Turno: {$datos_reserva['turno']}\n";
            $altBody .= "Zona: {$datos_reserva['zona']}\n";
            $altBody .= "Número de personas: {$datos_reserva['num_personas']}\n";
            
            if (!empty($datos_reserva['observaciones'])) {
                $altBody .= "Alérgenos: {$datos_reserva['observaciones']}\n";
            }
            
            if (!empty($datos_reserva['necesidades_especiales'])) {
                $altBody .= "Necesidades especiales: {$datos_reserva['necesidades_especiales']}\n";
            }
            
            // Agregar checks personalizados si existen (versión texto plano)
            if (!empty($datos_reserva['checkboxes_seleccionados'])) {
                $altBody .= "\nOpciones seleccionadas:\n";
                $checkboxes = explode(', ', $datos_reserva['checkboxes_seleccionados']);
                
                foreach ($checkboxes as $checkbox) {
                    // Extraer el texto principal y la respuesta (si existe)
                    $checkbox_text = trim($checkbox);
                    $main_text = $checkbox_text;
                    $respuesta = '';
                    
                    // Buscar texto entre paréntesis al final
                    if (preg_match('/^(.*?)\s*\((.*?)\)$/', $checkbox_text, $matches)) {
                        $main_text = trim($matches[1]);
                        $respuesta = trim($matches[2]);
                    }
                    
                    $altBody .= "- " . $main_text . "\n";
                    
                    // Mostrar la respuesta si existe
                    if (!empty($respuesta)) {
                        $altBody .= "  > " . $respuesta . "\n";
                    }
                }
            }
            
            if ($estado === 'pendiente') {
                $altBody .= "\nPor favor, acceda al panel de administración para revisar y gestionar esta reserva.\n";
            } else {
                $altBody .= "\nPuede acceder al panel de administración para ver los detalles completos de esta reserva.\n";
            }
            $altBody .= "URL: http://localhost/reservar%20restaurantes/admin/reservas.php\n\n";
            $altBody .= "Este es un correo automático, por favor no responda a este mensaje.";
            
            // Registrar información antes de enviar
            error_log("Preparando envío de correo a administradores: " . implode(", ", $admin_emails));
            
            // Enviar el correo a todos los administradores
            $resultado = self::enviarCorreo($admin_emails, $subject, $body, $altBody, true); // Activar modo debug
            
            if ($resultado) {
                error_log("Correo enviado exitosamente a administradores");
            } else {
                error_log("Error al enviar correo a administradores");
            }
            
            return $resultado;
            
        } catch (\Exception $e) {
            error_log("Error al enviar notificación a administradores: " . $e->getMessage());
            return false;
        }
    }
}
