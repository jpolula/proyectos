<?php
/**
 * Script independiente para denegar reservas
 * Este script maneja la denegación de reservas y el envío de correos de notificación
 * sin depender del archivo reservas.php
 */

// Incluir archivo de autenticación
require_once 'auth.php';
require_once '../vendor/autoload.php';

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

// Conexión a la base de datos
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die('Error de conexión: ' . $e->getMessage());
}

// Inicializar variables
$mensaje = '';
$tipo_mensaje = '';
$reserva_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Si no hay ID de reserva, mostrar mensaje de error
if ($reserva_id <= 0) {
    $mensaje = 'No se especificó una reserva para denegar';
    $tipo_mensaje = 'error';
} else {
    // Obtener información de la reserva
    // Registrar que estamos obteniendo los datos para el correo de denegación
    error_log("Obteniendo datos para el correo de denegación, reserva ID: " . $reserva_id);
    
    $stmt = $pdo->prepare("SELECT r.*, c.nombre, c.email, c.telefono, t.nombre AS turno_nombre, DATE_FORMAT(r.fecha, '%d/%m/%Y') AS fecha_formateada, TIME_FORMAT(r.hora, '%H:%i') AS hora_formateada, r.hora AS hora_original FROM reservas r JOIN clientes c ON r.cliente_id = c.id JOIN turnos t ON r.turno_id = t.id WHERE r.id = ?");
    $stmt->execute([$reserva_id]);
    $reserva = $stmt->fetch();
    
    // Si se encontró la reserva
    if ($reserva) {
        // Registrar la hora original y la hora formateada para depuración
        error_log("Hora original en la base de datos (denegar.php): " . $reserva['hora_original']);
        error_log("Hora formateada para el correo (denegar.php): " . $reserva['hora_formateada']);
        
        // Asegurarse de que la hora formateada sea correcta
        if (!empty($reserva['hora_original'])) {
            // Formatear la hora manualmente para asegurar que sea correcta
            $hora_partes = explode(':', $reserva['hora_original']);
            if (count($hora_partes) >= 2) {
                $reserva['hora_formateada'] = $hora_partes[0] . ':' . $hora_partes[1];
                error_log("Hora reformateada manualmente (denegar.php): " . $reserva['hora_formateada']);
            }
        }
        
        // Determinar el número correcto de personas
        $num_personas = (intval($reserva['cantidad_personas']) > 0) ? $reserva['cantidad_personas'] : $reserva['personas_solicitadas'];
        
        // Procesar la denegación si se envió el formulario
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmar_denegacion'])) {
            // Determinar el número correcto de personas para guardar
            $personas_a_guardar = $num_personas;
            
            // Actualizar el estado de la reserva a rechazada y asegurar que se mantenga la cantidad de personas
            $stmt = $pdo->prepare("UPDATE reservas SET estado = 'rechazada', cantidad_personas = ? WHERE id = ?");
            
            if ($stmt->execute([$personas_a_guardar, $reserva_id])) {
                // Preparar el contenido del correo
                $asunto = "Cancelación de reserva - Restaurante";
                
                // Crear el cuerpo del correo en HTML
                $cuerpo = "
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
                            <p>Estimado/a <strong>{$reserva['nombre']}</strong>,</p>
                            <p>Lamentamos informarle que su reserva ha sido cancelada. Los detalles de la reserva eran los siguientes:</p>
                            
                            <div class='info'>
                                <p><strong>Fecha:</strong> {$reserva['fecha_formateada']}</p>
                                <p><strong>Hora de llegada:</strong> {$reserva['hora_formateada']} h</p>
                                <p><strong>Turno:</strong> " . ucfirst($reserva['turno_nombre']) . "</p>
                                <p><strong>Zona:</strong> " . ($reserva['zona'] == 'dentro' ? 'Interior' : 'Terraza') . "</p>
                                <p><strong>Número de personas:</strong> {$num_personas}</p>
                            </div>
                            
                            <p>Si desea realizar una nueva reserva, puede hacerlo a través de nuestra página web o contactándonos directamente.</p>
                            
                            <p>Disculpe las molestias ocasionadas.</p>
                            
                            <div class='footer'>
                                <p>Este es un correo automático, por favor no responda a este mensaje.</p>
                            </div>
                        </div>
                    </body>
                    </html>
                ";
                
                // Texto alternativo para clientes de correo que no soportan HTML
                $texto_alternativo = "
                    Cancelación de su reserva
                    
                    Estimado/a {$reserva['nombre']},
                    
                    Lamentamos informarle que su reserva ha sido cancelada. Los detalles de la reserva eran los siguientes:
                    
                    Fecha: {$reserva['fecha_formateada']}
                    Hora de llegada: {$reserva['hora_formateada']} h
                    Turno: " . ucfirst($reserva['turno_nombre']) . "
                    Zona: " . ($reserva['zona'] == 'dentro' ? 'Interior' : 'Terraza') . "
                    Número de personas: {$num_personas}
                    
                    Si desea realizar una nueva reserva, puede hacerlo a través de nuestra página web o contactándonos directamente.
                    
                    Disculpe las molestias ocasionadas.
                    
                    Este es un correo automático, por favor no responda a este mensaje.
                ";
                
                // Intentar enviar el correo
                $enviado = false;
                
                // Método 1: Usar la función enviar_correo_directo si está disponible
                if (function_exists('enviar_correo_directo') || file_exists('../enviar_correo_directo.php')) {
                    if (!function_exists('enviar_correo_directo')) {
                        require_once '../enviar_correo_directo.php';
                    }
                    
                    try {
                        $enviado = enviar_correo_directo($reserva['email'], $asunto, $cuerpo, $texto_alternativo);
                        if ($enviado) {
                            error_log("Correo de cancelación enviado correctamente a: {$reserva['email']}");
                        }
                    } catch (Exception $e) {
                        error_log("Error al enviar correo directo: " . $e->getMessage());
                    }
                }
                
                // Método 2: Usar la clase EmailSender si está disponible y el método 1 falló
                if (!$enviado && (class_exists('\\App\\Utils\\EmailSender') || file_exists('../src/Utils/EmailSender.php'))) {
                    if (!class_exists('\\App\\Utils\\EmailSender')) {
                        require_once '../src/Utils/EmailSender.php';
                    }
                    
                    try {
                        $emailSender = new \App\Utils\EmailSender();
                        $enviado = $emailSender->enviarCorreo($reserva['email'], $asunto, $cuerpo, $texto_alternativo);
                        if ($enviado) {
                            error_log("Correo de cancelación enviado correctamente (método alternativo) a: {$reserva['email']}");
                        }
                    } catch (Exception $e) {
                        error_log("Error al enviar correo con EmailSender: " . $e->getMessage());
                    }
                }
                
                // Método 3: Usar mail() nativo como último recurso
                if (!$enviado) {
                    try {
                        $cabeceras = "MIME-Version: 1.0\r\n";
                        $cabeceras .= "Content-type: text/html; charset=UTF-8\r\n";
                        $cabeceras .= "From: Reservas Restaurante <no-reply@example.com>\r\n";
                        
                        $enviado = mail($reserva['email'], $asunto, $cuerpo, $cabeceras);
                        if ($enviado) {
                            error_log("Correo de cancelación enviado correctamente (método nativo) a: {$reserva['email']}");
                        }
                    } catch (Exception $e) {
                        error_log("Error al enviar correo nativo: " . $e->getMessage());
                    }
                }
                
                // Mensaje según el resultado
                if ($enviado) {
                    $mensaje = 'Reserva rechazada correctamente y se ha enviado un correo de notificación al cliente.';
                } else {
                    $mensaje = 'Reserva rechazada correctamente, pero no se pudo enviar el correo de notificación.';
                    error_log("No se pudo enviar el correo de cancelación a: {$reserva['email']} por ningún método");
                }
                
                $tipo_mensaje = 'success';
            } else {
                $mensaje = 'Error al rechazar la reserva.';
                $tipo_mensaje = 'error';
            }
        }
    } else {
        $mensaje = 'No se encontró la reserva especificada.';
        $tipo_mensaje = 'error';
    }
}

// Título de la página
$pageTitle = 'Denegar Reserva';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - Panel de Administración</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen">
    <!-- Barra de navegación superior -->
    <nav class="bg-blue-600 text-white shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center">
                        <span class="text-xl font-bold">Panel de Administración</span>
                    </div>
                </div>
                <div class="flex items-center">
                    <a href="logout.php" class="ml-4 px-3 py-2 rounded-md text-sm font-medium hover:bg-blue-700 transition duration-300">Cerrar Sesión</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Contenido principal -->
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="px-4 py-6 sm:px-0">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-semibold text-gray-900"><?php echo $pageTitle; ?></h1>
                <a href="reservas.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md text-sm font-medium transition duration-300">
                    Volver a Reservas
                </a>
            </div>

            <?php if (!empty($mensaje)): ?>
                <div class="mb-6 p-4 rounded-md <?php echo $tipo_mensaje === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                    <?php echo $mensaje; ?>
                </div>
            <?php endif; ?>

            <?php if ($reserva_id > 0 && isset($reserva) && !isset($_POST['confirmar_denegacion'])): ?>
                <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                    <div class="px-4 py-5 sm:px-6 bg-gray-50">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Información de la Reserva</h3>
                        <p class="mt-1 max-w-2xl text-sm text-gray-500">Detalles de la reserva que desea denegar.</p>
                    </div>
                    <div class="border-t border-gray-200">
                        <dl>
                            <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500">Cliente</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2"><?php echo htmlspecialchars($reserva['nombre']); ?></dd>
                            </div>
                            <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500">Email</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2"><?php echo htmlspecialchars($reserva['email']); ?></dd>
                            </div>
                            <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500">Teléfono</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2"><?php echo htmlspecialchars($reserva['telefono']); ?></dd>
                            </div>
                            <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500">Fecha</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2"><?php echo htmlspecialchars($reserva['fecha_formateada']); ?></dd>
                            </div>
                            <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500">Hora</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2"><?php echo htmlspecialchars($reserva['hora_formateada']); ?> h</dd>
                            </div>
                            <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500">Turno</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2"><?php echo ucfirst(htmlspecialchars($reserva['turno_nombre'])); ?></dd>
                            </div>
                            <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500">Zona</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2"><?php echo $reserva['zona'] == 'dentro' ? 'Interior' : 'Terraza'; ?></dd>
                            </div>
                            <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500">Número de personas</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2"><?php echo (intval($reserva['cantidad_personas']) > 0) ? $reserva['cantidad_personas'] : $reserva['personas_solicitadas']; ?></dd>
                            </div>
                            <?php if (!empty($reserva['observaciones'])): ?>
                            <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500">Observaciones</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2"><?php echo nl2br(htmlspecialchars($reserva['observaciones'])); ?></dd>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($reserva['necesidades_especiales'])): ?>
                            <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500">Necesidades especiales</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2"><?php echo nl2br(htmlspecialchars($reserva['necesidades_especiales'])); ?></dd>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($reserva['alergenos'])): ?>
                            <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500">Alérgenos</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2"><?php echo nl2br(htmlspecialchars($reserva['alergenos'])); ?></dd>
                            </div>
                            <?php endif; ?>
                            <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500">Estado</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    <?php 
                                    switch($reserva['estado']) {
                                        case 'confirmada':
                                            echo 'bg-green-100 text-green-800';
                                            break;
                                        case 'pendiente':
                                            echo 'bg-yellow-100 text-yellow-800';
                                            break;
                                        case 'rechazada':
                                            echo 'bg-red-100 text-red-800';
                                            break;
                                        case 'cancelada':
                                            echo 'bg-gray-100 text-gray-800';
                                            break;
                                    }
                                    ?>">
                                        <?php echo ucfirst($reserva['estado']); ?>
                                    </span>
                                </dd>
                            </div>
                        </dl>
                    </div>
                    <div class="px-4 py-5 sm:px-6 bg-gray-50 border-t border-gray-200">
                        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'] . '?id=' . $reserva_id); ?>">
                            <div class="flex justify-end">
                                <a href="reservas.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md text-sm font-medium transition duration-300 mr-2">
                                    Cancelar
                                </a>
                                <button type="submit" name="confirmar_denegacion" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md text-sm font-medium transition duration-300">
                                    Denegar Reserva
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php elseif (isset($_POST['confirmar_denegacion'])): ?>
                <div class="bg-white shadow overflow-hidden sm:rounded-lg p-6">
                    <div class="text-center">
                        <?php if ($tipo_mensaje === 'success'): ?>
                            <i class="fas fa-check-circle text-green-500 text-5xl mb-4"></i>
                            <h2 class="text-2xl font-semibold text-gray-900 mb-2">¡Reserva denegada correctamente!</h2>
                            <p class="text-gray-600 mb-6">La reserva ha sido marcada como rechazada y se ha notificado al cliente.</p>
                        <?php else: ?>
                            <i class="fas fa-times-circle text-red-500 text-5xl mb-4"></i>
                            <h2 class="text-2xl font-semibold text-gray-900 mb-2">Ha ocurrido un error</h2>
                            <p class="text-gray-600 mb-6">No se ha podido completar la operación. Por favor, inténtelo de nuevo.</p>
                        <?php endif; ?>
                        
                        <div class="mt-6">
                            <a href="reservas.php" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-md text-sm font-medium transition duration-300">
                                Volver a Reservas
                            </a>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="bg-white shadow overflow-hidden sm:rounded-lg p-6">
                    <div class="text-center">
                        <i class="fas fa-exclamation-triangle text-yellow-500 text-5xl mb-4"></i>
                        <h2 class="text-2xl font-semibold text-gray-900 mb-2">No se ha especificado una reserva</h2>
                        <p class="text-gray-600 mb-6">Por favor, seleccione una reserva para denegar desde la lista de reservas.</p>
                        
                        <div class="mt-6">
                            <a href="reservas.php" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-md text-sm font-medium transition duration-300">
                                Ir a Reservas
                            </a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Pie de página -->
    <footer class="bg-white border-t border-gray-200 py-4 mt-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <p class="text-center text-gray-500 text-sm">
                Realizada con ❤️ por <a href="https://impulsatelecom.com/" class="text-gray-600 hover:text-gray-800 transition duration-300">Impulsa Telecom</a>
            </p>
        </div>
    </footer>
</body>
</html>
