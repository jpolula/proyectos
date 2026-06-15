<?php
<?php
namespace App\Utils;

require_once '../../enviar_correo_directo.php';

/**
 * Clase FileMailer - Una alternativa para entornos de desarrollo
 * En lugar de enviar correos reales, guarda los correos en archivos para revisión
 */
class FileMailer {
    private $from_email;
    private $from_name;
    private $debug;
    private $mail_dir;

    public function __construct() {
        // Configuración de la base de datos
        $host = 'localhost';
        $db = 'restaurante_reservas';
        $user = 'root';
        $pass = '';
        $charset = 'utf8mb4';

        // Directorio donde se guardarán los correos
        $this->mail_dir = dirname(__DIR__, 2) . '/correos';
        
        // Crear el directorio si no existe
        if (!is_dir($this->mail_dir)) {
            mkdir($this->mail_dir, 0777, true);
        }

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
                error_log("FileMailer inicializado con remitente: {$this->from_name} <{$this->from_email}>");
            }
        } catch (\PDOException $e) {
            // Si hay un error en la base de datos, usar configuración por defecto
            error_log("Error de base de datos al configurar FileMailer: " . $e->getMessage());
            $this->from_email = 'no-reply@example.com';
            $this->from_name = 'Reservas Restaurantes';
            $this->debug = true;
        }
    }
    
    /**
     * Simula el envío de un correo electrónico guardándolo en un archivo
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
            
            // Crear un nombre de archivo único basado en la fecha y el destinatario
            $filename = $this->mail_dir . '/' . date('Y-m-d_H-i-s') . '_' . md5($to_addresses . $subject) . '.html';
            
            // Preparar el contenido del correo
            $email_content = "
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset='UTF-8'>
                <title>Correo Simulado</title>
                <style>
                    body { font-family: Arial, sans-serif; margin: 0; padding: 20px; color: #333; }
                    .email-container { max-width: 800px; margin: 0 auto; border: 1px solid #ddd; border-radius: 5px; overflow: hidden; }
                    .email-header { background-color: #f5f5f5; padding: 15px; border-bottom: 1px solid #ddd; }
                    .email-body { padding: 20px; }
                    .email-footer { background-color: #f5f5f5; padding: 15px; border-top: 1px solid #ddd; font-size: 12px; color: #777; }
                    .meta-info { margin-bottom: 20px; }
                    .meta-info table { width: 100%; border-collapse: collapse; }
                    .meta-info table th { text-align: left; padding: 5px; width: 120px; }
                    .meta-info table td { padding: 5px; }
                    .content { border: 1px solid #ddd; padding: 15px; margin-bottom: 20px; }
                    .content h3 { margin-top: 0; }
                </style>
            </head>
            <body>
                <div class='email-container'>
                    <div class='email-header'>
                        <h2>Correo Simulado - Sistema de Reservas</h2>
                    </div>
                    <div class='email-body'>
                        <div class='meta-info'>
                            <table>
                                <tr>
                                    <th>De:</th>
                                    <td>{$this->from_name} &lt;{$this->from_email}&gt;</td>
                                </tr>
                                <tr>
                                    <th>Para:</th>
                                    <td>$to_addresses</td>
                                </tr>
                                <tr>
                                    <th>Asunto:</th>
                                    <td>$subject</td>
                                </tr>
                                <tr>
                                    <th>Fecha:</th>
                                    <td>" . date('Y-m-d H:i:s') . "</td>
                                </tr>
                            </table>
                        </div>
                        
                        <div class='content'>
                            <h3>Versión HTML:</h3>
                            $body
                        </div>
                        
                        <div class='content'>
                            <h3>Versión Texto Plano:</h3>
                            <pre>" . htmlspecialchars($altBody ?: strip_tags($body)) . "</pre>
                        </div>
                    </div>
                    <div class='email-footer'>
                        Este correo ha sido simulado por FileMailer y guardado localmente en lugar de ser enviado.
                    </div>
                </div>
            </body>
            </html>
            ";
            
            // Guardar el correo en un archivo
            $result = file_put_contents($filename, $email_content);
            
            if ($result !== false) {
                if ($this->debug) {
                    error_log("Correo simulado guardado correctamente en: $filename");
                }
                
                // Crear un archivo de índice si no existe
                $index_file = $this->mail_dir . '/index.php';
                if (!file_exists($index_file)) {
                    $index_content = "<?php
                    // Listar todos los correos simulados
                    \$correos = glob(__DIR__ . '/*.html');
                    rsort(\$correos); // Ordenar por fecha (más reciente primero)
                    
                    echo '<h1>Correos Simulados</h1>';
                    echo '<p>Estos correos han sido guardados localmente en lugar de ser enviados.</p>';
                    
                    if (empty(\$correos)) {
                        echo '<p>No hay correos simulados.</p>';
                    } else {
                        echo '<ul>';
                        foreach (\$correos as \$correo) {
                            \$nombre = basename(\$correo);
                            \$fecha = substr(\$nombre, 0, 19);
                            \$fecha = str_replace('_', ' ', \$fecha);
                            echo '<li><a href=\"' . \$nombre . '\">' . \$fecha . '</a></li>';
                        }
                        echo '</ul>';
                    }
                    ";
                    file_put_contents($index_file, $index_content);
                }
                
                return true;
            } else {
                if ($this->debug) {
                    error_log("Error al guardar correo simulado en: $filename");
                }
                return false;
            }
        } catch (\Exception $e) {
            error_log("Excepción al simular correo: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Envía un correo de cancelación de reserva
     * 
     * @param string $email Email del cliente
     * @param string $nombre Nombre del cliente
     * @param array $datos_reserva Datos de la reserva cancelada
     * @return bool Éxito o fracaso del envío
     */
    public function enviarCorreoCancelacion($email, $nombre, $datos_reserva) {
        $subject = "Reserva Cancelada - No hay disponibilidad";
        
        // Crear el cuerpo del correo HTML
        $body = "<div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e0e0e0; border-radius: 5px;'>";
        $body .= "<h2 style='color: #d32f2f; text-align: center;'>Reserva Cancelada</h2>";
        $body .= "<p>Estimado/a <strong>$nombre</strong>,</p>";
        $body .= "<p>Lamentamos informarle que no podemos confirmar su reserva para la fecha y hora solicitadas debido a que no hay disponibilidad.</p>";
        
        $body .= "<div style='background-color: #f5f5f5; padding: 15px; margin: 15px 0; border-left: 4px solid #d32f2f;'>";
        $body .= "<h3 style='margin-top: 0; color: #333;'>Detalles de la reserva cancelada:</h3>";
        $body .= "<ul style='padding-left: 20px;'>";
        $body .= "<li><strong>Fecha:</strong> {$datos_reserva['fecha']}</li>";
        $body .= "<li><strong>Hora de llegada:</strong> {$datos_reserva['hora']} h</li>";
        $body .= "<li><strong>Zona:</strong> {$datos_reserva['zona']}</li>";
        $body .= "<li><strong>Personas:</strong> {$datos_reserva['personas']}</li>";
        $body .= "</ul>";
        $body .= "</div>";
        
        $body .= "<p>Le invitamos a realizar una nueva reserva en otra fecha u horario disponible.</p>";
        $body .= "<p>Si tiene alguna pregunta o necesita asistencia, no dude en contactarnos.</p>";
        $body .= "<p>Atentamente,<br><strong>Equipo de Reservas</strong></p>";
        $body .= "</div>";
        
        // Versión texto plano
        $altBody = "Reserva Cancelada - No hay disponibilidad\n\n";
        $altBody .= "Estimado/a $nombre,\n\n";
        $altBody .= "Lamentamos informarle que no podemos confirmar su reserva para la fecha y hora solicitadas debido a que no hay disponibilidad.\n\n";
        $altBody .= "Detalles de la reserva cancelada:\n";
        $altBody .= "- Fecha: {$datos_reserva['fecha']}\n";
        $altBody .= "- Hora de llegada: {$datos_reserva['hora']} h\n";
        $altBody .= "- Zona: {$datos_reserva['zona']}\n";
        $altBody .= "- Personas: {$datos_reserva['personas']}\n\n";
        $altBody .= "Le invitamos a realizar una nueva reserva en otra fecha u horario disponible.\n\n";
        $altBody .= "Si tiene alguna pregunta o necesita asistencia, no dude en contactarnos.\n\n";
        $altBody .= "Atentamente,\nEquipo de Reservas";
        
        // Enviar el correo
        return $this->send($email, $subject, $body, $altBody);
    }
}
?>
