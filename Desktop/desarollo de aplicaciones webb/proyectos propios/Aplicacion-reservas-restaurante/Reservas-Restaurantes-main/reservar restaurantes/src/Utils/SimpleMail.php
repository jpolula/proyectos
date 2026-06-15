<?php
require_once '../../enviar_correo_directo.php';
namespace App\Utils;

/**
 * Clase SimpleMail - Una alternativa simple a PHPMailer que usa la función mail() nativa de PHP
 */
class SimpleMail {
    private $from_email;
    private $from_name;
    private $debug;

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

        try {
            // Conectar a la base de datos
            $pdo = new \PDO($dsn, $user, $pass, $options);
            
            // Obtener la configuración de correo de la base de datos
            $stmt = $pdo->prepare("SELECT * FROM administrador WHERE id = 1");
            $stmt->execute();
            $config = $stmt->fetch();
            
            // Configurar remitente
            $this->from_email = $config['email_remitente'] ?? 'no-reply@example.com';
            $this->from_name = $config['email_nombre_remitente'] ?? 'Reservas Restaurantes';
            $this->debug = true;
            
            if ($this->debug) {
                error_log("SimpleMail inicializado con remitente: {$this->from_name} <{$this->from_email}>");
            }
        } catch (\PDOException $e) {
            // Si hay un error en la base de datos, usar configuración por defecto
            error_log("Error de base de datos al configurar SimpleMail: " . $e->getMessage());
            $this->from_email = 'no-reply@example.com';
            $this->from_name = 'Reservas Restaurantes';
            $this->debug = true;
        }
    }
    
    /**
     * Envía un correo electrónico usando la función mail() nativa de PHP
     * 
     * @param string|array $to Dirección o direcciones de correo del destinatario
     * @param string $subject Asunto del correo
     * @param string $body Cuerpo del correo (HTML)
     * @param string $altBody Cuerpo alternativo (texto plano)
     * @return bool Éxito o fracaso del envío
     */
    public function send($to, $subject, $body, $altBody = '', $attachments = []) {
        try {
            // Preparar destinatarios
            if (is_array($to)) {
                $to_addresses = implode(', ', $to);
            } else {
                $to_addresses = $to;
            }
            
            // Generar un límite único para el contenido multiparte
            $boundary = md5(time());
            
            // Cabeceras del correo
            $headers = [
                'From' => "{$this->from_name} <{$this->from_email}>",
                'Reply-To' => $this->from_email,
                'MIME-Version' => '1.0',
                'Content-Type' => "multipart/alternative; boundary=\"$boundary\"",
                'X-Mailer' => 'PHP/' . phpversion()
            ];
            
            // Convertir array de cabeceras a string
            $headers_str = '';
            foreach ($headers as $name => $value) {
                $headers_str .= "$name: $value\r\n";
            }
            
            // Preparar el cuerpo del mensaje
            $message = "";
            
            // Añadir versión texto plano
            $plain_text = $altBody ?: strip_tags($body);
            $message .= "--$boundary\r\n";
            $message .= "Content-Type: text/plain; charset=UTF-8\r\n";
            $message .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
            $message .= $plain_text . "\r\n\r\n";
            
            // Añadir versión HTML
            $message .= "--$boundary\r\n";
            $message .= "Content-Type: text/html; charset=UTF-8\r\n";
            $message .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
            $message .= $body . "\r\n\r\n";
            
            // Cerrar el mensaje
            $message .= "--$boundary--";
            
            // Registrar información de depuración
            if ($this->debug) {
                error_log("Intentando enviar correo a: $to_addresses");
                error_log("Asunto: $subject");
                error_log("Cabeceras: " . print_r($headers, true));
            }
            
            // Enviar el correo
            $result = mail($to_addresses, $subject, $message, $headers_str);
            
            if ($result) {
                if ($this->debug) {
                    error_log("Correo enviado correctamente");
                }
                return true;
            } else {
                if ($this->debug) {
                    error_log("Error al enviar correo con mail()");
                }
                return false;
            }
        } catch (\Exception $e) {
            error_log("Excepción al enviar correo: " . $e->getMessage());
            return false;
        }
    }
}
?>
