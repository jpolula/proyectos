<?php
// Incluir archivo de autenticación
require_once 'auth.php';
require_once '../vendor/autoload.php';
require_once '../enviar_correo_directo.php';
require_once '../src/Utils/EmailSender.php';

use App\Utils\EmailSender;

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

// Función para verificar el aforo disponible
function verificarAforoDisponible($pdo, $reserva_id) {
    try {
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
            return [
                'disponible' => false,
                'mensaje' => 'No se encontró la reserva.',
                'aforo_disponible' => 0,
                'num_personas' => 0
            ];
        }
        
        $fecha = $reserva['fecha'];
        $zona = $reserva['zona'];
        $turno_id = $reserva['turno_id'];
        $num_personas = $reserva['cantidad_personas'];
        
        // Obtener la capacidad máxima para esa fecha, zona y turno
        $stmt = $pdo->prepare("
            SELECT aforo_maximo
            FROM capacidad
            WHERE fecha = ? AND zona = ? AND turno_id = ?
        ");
        $stmt->execute([$fecha, $zona, $turno_id]);
        $aforo_maximo = $stmt->fetchColumn();
        
        // Si no hay configuración específica, obtener la capacidad por defecto
        if ($aforo_maximo === false) {
            $turno_nombre = strtolower($reserva['turno_nombre']) === 'mediodía' || 
                           strtolower($reserva['turno_nombre']) === 'mediodia' ? 'mediodia' : 'noche';
            $campo_capacidad = 'capacidad_' . $zona . '_' . $turno_nombre;
            
            $stmt = $pdo->prepare("SELECT $campo_capacidad FROM configuracion WHERE id = 1");
            $stmt->execute();
            $aforo_maximo = $stmt->fetchColumn();
            
            // Si aún no hay valor, usar un valor por defecto
            if ($aforo_maximo === false) {
                $aforo_maximo = ($zona == 'dentro') ? 30 : 20;
            }
        }
        
        // Obtener el número de personas ya reservadas (excluyendo la reserva actual)
        $stmt = $pdo->prepare("
            SELECT SUM(cantidad_personas) as total_reservado
            FROM reservas
            WHERE fecha = ? AND zona = ? AND turno_id = ? AND estado = 'confirmada' AND id != ?
        ");
        $stmt->execute([$fecha, $zona, $turno_id, $reserva_id]);
        $resultado = $stmt->fetch();
        $total_reservado = $resultado['total_reservado'] ?: 0;
        
        // Calcular si hay suficiente aforo disponible
        $aforo_disponible = $aforo_maximo - $total_reservado;
        $hay_disponibilidad = $aforo_disponible >= $num_personas;
        
        return [
            'disponible' => $hay_disponibilidad,
            'mensaje' => $hay_disponibilidad 
                ? "Hay suficiente aforo disponible para confirmar la reserva."
                : "No hay suficiente aforo disponible para confirmar esta reserva.",
            'aforo_disponible' => $aforo_disponible,
            'num_personas' => $num_personas,
            'aforo_maximo' => $aforo_maximo,
            'total_reservado' => $total_reservado
        ];
        
    } catch (PDOException $e) {
        return [
            'disponible' => false,
            'mensaje' => 'Error al verificar el aforo disponible: ' . $e->getMessage(),
            'aforo_disponible' => 0,
            'num_personas' => 0
        ];
    }
}

// Función para enviar correo de confirmación
function enviarCorreoConfirmacion($pdo, $reserva_id) {
    try {
        // Obtener los datos de la reserva
        // Registrar que estamos obteniendo los datos para el correo
        error_log("Obteniendo datos para el correo de confirmación, reserva ID: " . $reserva_id);
        
        $stmt = $pdo->prepare("
            SELECT r.*, c.nombre, c.email, c.telefono, t.nombre AS turno_nombre, 
                   DATE_FORMAT(r.fecha, '%d/%m/%Y') AS fecha_formateada,
                   TIME_FORMAT(r.hora, '%H:%i') AS hora_formateada,
                   r.hora AS hora_original,
                   COALESCE(r.cantidad_personas, r.personas_solicitadas) AS cantidad_personas
            FROM reservas r
            JOIN clientes c ON r.cliente_id = c.id
            JOIN turnos t ON r.turno_id = t.id
            WHERE r.id = ?
        ");
        $stmt->execute([$reserva_id]);
        $reserva = $stmt->fetch();
        
        if (!$reserva) {
            return [
                'exito' => false,
                'mensaje' => 'No se pudo obtener la información para enviar el correo.'
            ];
        }
        
        // Registrar la hora original y la hora formateada para depuración
        error_log("Hora original en la base de datos: " . $reserva['hora_original']);
        error_log("Hora formateada para el correo: " . $reserva['hora_formateada']);
        
        // Asegurarse de que la hora formateada sea correcta
        if (!empty($reserva['hora_original'])) {
            // Formatear la hora manualmente para asegurar que sea correcta
            $hora_partes = explode(':', $reserva['hora_original']);
            if (count($hora_partes) >= 2) {
                $reserva['hora_formateada'] = $hora_partes[0] . ':' . $hora_partes[1];
                error_log("Hora reformateada manualmente: " . $reserva['hora_formateada']);
            }
        }
        
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
                    <p>Estimado/a <strong>{$reserva['nombre']}</strong>,</p>
                    <p>Nos complace confirmar su reserva en nuestro restaurante con los siguientes detalles:</p>
                    
                    <div class='info'>
                        <p><strong>Fecha:</strong> {$reserva['fecha_formateada']}</p>
                        <p><strong>Hora de llegada:</strong> {$reserva['hora_formateada']} h</p>
                        <p><strong>Turno:</strong> " . ucfirst($reserva['turno_nombre']) . "</p>
                        <p><strong>Zona:</strong> " . ($reserva['zona'] == 'dentro' ? 'Interior' : 'Terraza') . "</p>
                        <p><strong>Número de personas:</strong> {$reserva['cantidad_personas']}</p>
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
            
            Estimado/a {$reserva['nombre']},
            
            Nos complace confirmar su reserva en nuestro restaurante con los siguientes detalles:
            
            Fecha: {$reserva['fecha_formateada']}
            Hora de llegada: {$reserva['hora_formateada']} h
            Turno: " . ucfirst($reserva['turno_nombre']) . "
            Zona: " . ($reserva['zona'] == 'dentro' ? 'Interior' : 'Terraza') . "
            Número de personas: {$reserva['cantidad_personas']}
            
            Si necesita realizar algún cambio en su reserva, por favor contáctenos lo antes posible.
            
            ¡Esperamos recibirle pronto en nuestro restaurante!
            
            Este es un correo automático, por favor no responda a este mensaje.
        ";
        
        // Enviar el correo
        $enviado = enviar_correo_directo($reserva['email'], $asunto, $cuerpo, $texto_alternativo);
        
        return [
            'exito' => $enviado,
            'mensaje' => $enviado 
                ? 'Se ha enviado un correo de confirmación al cliente.'
                : 'No se pudo enviar el correo de confirmación.'
        ];
        
    } catch (Exception $e) {
        return [
            'exito' => false,
            'mensaje' => 'Error al enviar el correo: ' . $e->getMessage()
        ];
    }
}

// Inicializar variables
$resultado = [
    'exito' => false,
    'mensaje' => '',
    'redireccionar' => false
];

// Verificar si se ha enviado el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reserva_id'])) {
    try {
        // Conectar a la base de datos
        $pdo = new PDO($dsn, $user, $pass, $options);
        
        // Obtener el ID de la reserva
        $reserva_id = (int)$_POST['reserva_id'];
        
        // Verificar si hay suficiente aforo disponible
        $verificacion = verificarAforoDisponible($pdo, $reserva_id);
        
        if ($verificacion['disponible']) {
            // Hay suficiente aforo, confirmar la reserva
            // Primero, obtener el número de personas solicitadas si existe
            $stmt = $pdo->prepare("SELECT COALESCE(personas_solicitadas, cantidad_personas) as num_personas FROM reservas WHERE id = ?");
            $stmt->execute([$reserva_id]);
            $reserva = $stmt->fetch();
            $num_personas = $reserva ? $reserva['num_personas'] : 1;
            
            // Actualizar la reserva con el estado confirmado y el número de personas
            $stmt = $pdo->prepare("UPDATE reservas SET estado = 'confirmada', cantidad_personas = ? WHERE id = ?");
            if ($stmt->execute([$num_personas, $reserva_id])) {
                // Enviar correo de confirmación
                $correo = enviarCorreoConfirmacion($pdo, $reserva_id);
                
                // Obtener los datos de la reserva para enviar notificación a los administradores
                $stmt = $pdo->prepare("
                    SELECT r.*, c.nombre AS cliente_nombre, c.email AS cliente_email, t.nombre AS turno_nombre, 
                           DATE_FORMAT(r.fecha, '%d/%m/%Y') AS fecha_formateada,
                           TIME_FORMAT(r.hora, '%H:%i') AS hora_formateada
                    FROM reservas r
                    JOIN clientes c ON r.cliente_id = c.id
                    JOIN turnos t ON r.turno_id = t.id
                    WHERE r.id = ?
                ");
                $stmt->execute([$reserva_id]);
                $reserva_data = $stmt->fetch();
                
                if ($reserva_data) {
                    // Obtener los checkboxes seleccionados para incluirlos en los correos
                    $checkboxes_seleccionados = '';
                    $stmt_checkboxes = $pdo->prepare(
                        "SELECT cp.texto 
                         FROM reservas_checkboxes rc 
                         JOIN checkboxes_personalizados cp ON rc.checkbox_id = cp.id 
                         WHERE rc.reserva_id = ? AND rc.valor = 1"
                    );
                    $stmt_checkboxes->execute([$reserva_id]);
                    $checkboxes = $stmt_checkboxes->fetchAll(PDO::FETCH_COLUMN);
                    if (!empty($checkboxes)) {
                        $checkboxes_seleccionados = implode(', ', $checkboxes);
                    }
                    
                    // Preparar datos para la notificación
                    $datos_reserva = [
                        'cliente_nombre' => $reserva_data['cliente_nombre'],
                        'cliente_email' => $reserva_data['cliente_email'],
                        'fecha' => $reserva_data['fecha_formateada'],
                        'hora' => $reserva_data['hora_formateada'],
                        'turno' => $reserva_data['turno_nombre'],
                        'zona' => $reserva_data['zona'] == 'dentro' ? 'Interior' : 'Terraza',
                        'num_personas' => $reserva_data['cantidad_personas'],
                        'observaciones' => $reserva_data['observaciones'],
                        'necesidades_especiales' => $reserva_data['necesidades_especiales'],
                        'checkboxes_seleccionados' => $checkboxes_seleccionados
                    ];
                    
                    // Verificar la configuración de notificaciones antes de enviar
                    $stmt = $pdo->query("SELECT notificaciones_admin, email_activo FROM configuracion WHERE id = 1");
                    $config = $stmt->fetch();
                    $notificaciones_admin = $config['notificaciones_admin'] ?? 'pendientes';
                    $email_activo = $config['email_activo'] ?? 0;
                    
                    error_log("Configuración de notificaciones: email_activo=$email_activo, notificaciones_admin=$notificaciones_admin");
                    
                    // Solo enviar si el email está activo y las notificaciones están configuradas adecuadamente
                    if ($email_activo && ($notificaciones_admin === 'todas')) {
                        // Enviar notificación a los administradores
                        $notificacion_admin = EmailSender::enviarNotificacionAdmin($datos_reserva, 'confirmada');
                        
                        if ($notificacion_admin) {
                            error_log("Notificación de reserva confirmada enviada a los administradores para la reserva #$reserva_id");
                        } else {
                            error_log("Error al enviar notificación a los administradores para la reserva confirmada #$reserva_id");
                        }
                    } else {
                        error_log("No se envió notificación a administradores para reserva confirmada debido a la configuración: " . 
                                 "email_activo=$email_activo, notificaciones_admin=$notificaciones_admin");
                    }
                }
                
                $resultado = [
                    'exito' => true,
                    'mensaje' => 'Reserva confirmada correctamente. ' . $correo['mensaje'],
                    'redireccionar' => true
                ];
            } else {
                $resultado = [
                    'exito' => false,
                    'mensaje' => 'Error al confirmar la reserva.',
                    'redireccionar' => false
                ];
            }
        } else {
            // No hay suficiente aforo disponible
            $resultado = [
                'exito' => false,
                'mensaje' => "No hay suficiente aforo disponible para confirmar esta reserva. Aforo disponible: {$verificacion['aforo_disponible']} personas. La reserva requiere: {$verificacion['num_personas']} personas.",
                'redireccionar' => false
            ];
        }
    } catch (PDOException $e) {
        $resultado = [
            'exito' => false,
            'mensaje' => 'Error de base de datos: ' . $e->getMessage(),
            'redireccionar' => false
        ];
    } catch (Exception $e) {
        $resultado = [
            'exito' => false,
            'mensaje' => 'Error: ' . $e->getMessage(),
            'redireccionar' => false
        ];
    }
}

// Comprobar si la reserva tiene personas_solicitadas y actualizar cantidad_personas si es necesario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reserva_id']) && $resultado['exito']) {
    try {
        $reserva_id = (int)$_POST['reserva_id'];
        
        // Verificar si la reserva tiene personas_solicitadas
        $stmt = $pdo->prepare("SELECT personas_solicitadas FROM reservas WHERE id = ? AND estado = 'confirmada'");
        $stmt->execute([$reserva_id]);
        $reserva_data = $stmt->fetch();
        
        if ($reserva_data && isset($reserva_data['personas_solicitadas']) && $reserva_data['personas_solicitadas'] > 0) {
            // Actualizar cantidad_personas con el valor de personas_solicitadas
            $stmt = $pdo->prepare("UPDATE reservas SET cantidad_personas = personas_solicitadas WHERE id = ?");
            $stmt->execute([$reserva_id]);
            error_log("Actualizada cantidad_personas con el valor de personas_solicitadas para la reserva #$reserva_id");
        }
    } catch (Exception $e) {
        error_log("Error al actualizar cantidad_personas: " . $e->getMessage());
    }
}

// Configurar mensaje de sesión y redireccionar
session_start();
$_SESSION['mensaje'] = $resultado['mensaje'];
$_SESSION['tipo_mensaje'] = $resultado['exito'] ? 'success' : 'error';

// Redireccionar a la página de reservas
header('Location: reservas.php');
exit;
?>
