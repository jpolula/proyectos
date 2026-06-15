<?php
// Incluir archivo de autenticación
require_once 'auth.php';
require_once '../vendor/autoload.php';
require_once '../enviar_correo_directo.php';

// Configuración de la conexión a la base de datos
$host = 'localhost';
$db = 'restaurante_reservas';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

// Inicializar respuesta
$respuesta = [
    'exito' => false,
    'mensaje' => '',
    'redireccionar' => false
];

try {
    // Conexión a la base de datos
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // Verificar si se ha enviado el ID de la reserva
    if (!isset($_POST['reserva_id']) || empty($_POST['reserva_id'])) {
        throw new Exception('No se ha proporcionado el ID de la reserva.');
    }
    
    $reserva_id = (int)$_POST['reserva_id'];
    
    // Obtener los datos de la reserva
    $stmt = $pdo->prepare("
        SELECT r.*, t.nombre AS turno_nombre
        FROM reservas r
        JOIN turnos t ON r.turno_id = t.id
        WHERE r.id = ?
    ");
    $stmt->execute([$reserva_id]);
    $reserva = $stmt->fetch();
    
    if (!$reserva) {
        throw new Exception('No se encontró la reserva.');
    }
    
    // Verificar que la reserva esté en estado pendiente
    if ($reserva['estado'] !== 'pendiente') {
        throw new Exception('La reserva no está en estado pendiente.');
    }
    
    // Obtener datos necesarios para verificar el aforo
    $fecha = $reserva['fecha'];
    $zona = $reserva['zona'];
    $turno_id = $reserva['turno_id'];
    $num_personas = $reserva['cantidad_personas'];
    
    // Obtener la capacidad directamente de la configuración general
    $stmt = $pdo->prepare("SELECT * FROM configuracion WHERE id = 1");
    $stmt->execute();
    $config = $stmt->fetch();
    
    // Determinar el campo de capacidad según turno y zona
    $turno_nombre = strtolower($reserva['turno_nombre']) === 'mediodía' || 
                   strtolower($reserva['turno_nombre']) === 'mediodia' ? 'mediodia' : 'noche';
    
    $campo_capacidad = '';
    if ($turno_nombre == 'mediodia') {
        $campo_capacidad = ($zona == 'dentro') ? 'capacidad_dentro_mediodia' : 'capacidad_fuera_mediodia';
    } else {
        $campo_capacidad = ($zona == 'dentro') ? 'capacidad_dentro_noche' : 'capacidad_fuera_noche';
    }
    
    // Obtener el aforo máximo de la configuración
    $aforo_maximo = $config[$campo_capacidad] ?? (($zona == 'dentro') ? 30 : 20);
    
    // Obtener el número de personas ya reservadas (excluyendo la reserva actual) - SOLO RESERVAS CONFIRMADAS
    $stmt = $pdo->prepare("
        SELECT SUM(cantidad_personas) as total_reservado
        FROM reservas
        WHERE fecha = ? AND zona = ? AND turno_id = ? AND id != ? AND estado = 'confirmada'
    ");
    $stmt->execute([$fecha, $zona, $turno_id, $reserva_id]);
    $resultado = $stmt->fetch();
    $total_reservado = $resultado['total_reservado'] ?: 0;
    
    // Calcular si hay suficiente aforo disponible
    $aforo_disponible = $aforo_maximo - $total_reservado;
    
    if ($aforo_disponible >= $num_personas) {
        // Hay suficiente aforo, confirmar la reserva
        $stmt = $pdo->prepare("UPDATE reservas SET estado = 'confirmada' WHERE id = ?");
        if ($stmt->execute([$reserva_id])) {
            // Obtener los datos completos de la reserva para enviar el correo
            $stmt = $pdo->prepare("
                SELECT r.*, c.nombre, c.email, c.telefono, t.nombre AS turno_nombre, 
                       DATE_FORMAT(r.fecha, '%d/%m/%Y') AS fecha_formateada,
                       TIME_FORMAT(r.hora, '%H:%i') AS hora_formateada
                FROM reservas r
                JOIN clientes c ON r.cliente_id = c.id
                JOIN turnos t ON r.turno_id = t.id
                WHERE r.id = ?
            ");
            $stmt->execute([$reserva_id]);
            $reserva_completa = $stmt->fetch();
            
            if ($reserva_completa) {
                // Enviar correo de confirmación
                try {
                    // Preparar el contenido del correo
                    $asunto = "Confirmación de reserva - Restaurante";
                    
                    // Crear el cuerpo del correo en HTML
                    $cuerpo = "
                        <html>
                        <head>
                            <style>
                                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                                h1 { color: #2c5282; }
                                .info { background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0; }
                                .footer { margin-top: 30px; font-size: 0.9em; color: #666; }
                            </style>
                        </head>
                        <body>
                            <div class='container'>
                                <h1>¡Su reserva ha sido confirmada!</h1>
                                <p>Estimado/a <strong>{$reserva_completa['nombre']}</strong>,</p>
                                <p>Nos complace confirmar su reserva en nuestro restaurante con los siguientes detalles:</p>
                                
                                <div class='info'>
                                    <p><strong>Fecha:</strong> {$reserva_completa['fecha_formateada']}</p>
                                    <p><strong>Hora de llegada:</strong> {$reserva_completa['hora_formateada']} h</p>
                                    <p><strong>Turno:</strong> " . ucfirst($reserva_completa['turno_nombre']) . "</p>
                                    <p><strong>Zona:</strong> " . ($reserva_completa['zona'] == 'dentro' ? 'Interior' : 'Terraza') . "</p>
                                    <p><strong>Número de personas:</strong> {$reserva_completa['cantidad_personas']}</p>
                                </div>
                                
                                <p>Si necesita realizar algún cambio en su reserva, por favor contáctenos lo antes posible.</p>
                                
                                <p>¡Esperamos recibirle pronto en nuestro restaurante!</p>
                                
                                <div class='footer'>
                                    <p>Este es un correo automático, por favor no responda a este mensaje.</p>
                                </div>
                            </div>
                        </body>
                        </html>
                    ";
                    
                    // Texto alternativo para clientes de correo que no soportan HTML
                    $texto_alternativo = "
                        ¡Su reserva ha sido confirmada!
                        
                        Estimado/a {$reserva_completa['nombre']},
                        
                        Nos complace confirmar su reserva en nuestro restaurante con los siguientes detalles:
                        
                        Fecha: {$reserva_completa['fecha_formateada']}
                        Hora: {$reserva_completa['hora_formateada']}
                        Turno: " . ucfirst($reserva_completa['turno_nombre']) . "
                        Zona: " . ($reserva_completa['zona'] == 'dentro' ? 'Interior' : 'Terraza') . "
                        Número de personas: {$reserva_completa['cantidad_personas']}
                        
                        Si necesita realizar algún cambio en su reserva, por favor contáctenos lo antes posible.
                        
                        ¡Esperamos recibirle pronto en nuestro restaurante!
                        
                        Este es un correo automático, por favor no responda a este mensaje.
                    ";
                    
                    // Enviar el correo
                    $enviado = enviar_correo_directo($reserva_completa['email'], $asunto, $cuerpo, $texto_alternativo);
                    
                    if ($enviado) {
                        $respuesta['mensaje'] = 'Reserva confirmada correctamente y se ha enviado un correo de confirmación al cliente.';
                        error_log("Correo de confirmación enviado correctamente a: {$reserva_completa['email']}");
                    } else {
                        $respuesta['mensaje'] = 'Reserva confirmada correctamente, pero no se pudo enviar el correo de confirmación.';
                        error_log("Error al enviar correo de confirmación a: {$reserva_completa['email']}");
                    }
                } catch (Exception $e) {
                    $respuesta['mensaje'] = 'Reserva confirmada correctamente, pero hubo un error al enviar el correo: ' . $e->getMessage();
                    error_log("Excepción al enviar correo de confirmación: " . $e->getMessage());
                }
            } else {
                $respuesta['mensaje'] = 'Reserva confirmada correctamente, pero no se pudo obtener la información para enviar el correo.';
                error_log("No se pudo obtener información de la reserva para enviar correo de confirmación. ID: {$reserva_id}");
            }
            
            $respuesta['exito'] = true;
            $respuesta['redireccionar'] = true;
        } else {
            throw new Exception('Error al confirmar la reserva en la base de datos.');
        }
    } else {
        // No hay suficiente aforo disponible
        $respuesta['mensaje'] = "No hay suficiente aforo disponible para confirmar esta reserva. Aforo disponible: {$aforo_disponible} personas. La reserva requiere: {$num_personas} personas.";
        $respuesta['exito'] = false;
    }
} catch (PDOException $e) {
    $respuesta['mensaje'] = 'Error de base de datos: ' . $e->getMessage();
    error_log("Error de PDO al confirmar reserva: " . $e->getMessage());
} catch (Exception $e) {
    $respuesta['mensaje'] = $e->getMessage();
    error_log("Error al confirmar reserva: " . $e->getMessage());
}

// Devolver respuesta en formato JSON
header('Content-Type: application/json');
echo json_encode($respuesta);
?>
