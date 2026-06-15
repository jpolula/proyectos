<?php
// Archivo confirmar_reserva.php - Página de confirmación intermedia
session_start();

// Incluir el archivo para mostrar la hora seleccionada
require_once 'mostrar_hora_seleccionada.php';
require_once 'enviar_correo_directo.php';

// Incluir el archivo para generar correos con la hora seleccionada
require_once 'generar_correo_reserva.php';

// Habilitar la visualización de errores (solo para depuración)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Registrar variables de sesión para depuración
error_log("Variables de sesión en confirmar_reserva.php: " . print_r($_SESSION, true));

// Incluir la clase Mailer
require_once 'vendor/autoload.php';
// Eliminado require de Mailer/FileMailer, ahora usamos enviar_correo_directo

// Eliminado use App\Utils\Mailer, ahora usamos enviar_correo_directo

// Verificar si el usuario ha completado el formulario de datos personales
if (!isset($_SESSION['nombre']) || empty($_SESSION['nombre']) || !isset($_SESSION['email']) || empty($_SESSION['email'])) {
    // Si no hay datos de usuario, redirigir al formulario de datos personales
    header('Location: index.php');
    exit;
}

// Verificar si se han enviado los datos de la reserva
if (!isset($_SESSION['fecha']) || !isset($_SESSION['zona']) || !isset($_SESSION['turno_id'])) {
    // Si no hay datos de reserva, redirigir a la página de reserva
    header('Location: reserva.php');
    exit;
}

// Obtener los datos del usuario de la sesión
$nombre = $_SESSION['nombre'] ?? '';
$email = $_SESSION['email'] ?? '';
$telefono = $_SESSION['telefono'] ?? '';
$num_personas = $_SESSION['num_personas'] ?? 1;
$tiene_alergenos = $_SESSION['tiene_alergenos'] ?? false;
$alergenos = $_SESSION['alergenos'] ?? '';
$tiene_necesidades = $_SESSION['tiene_necesidades'] ?? false;
$necesidades_especiales = $_SESSION['necesidades_especiales'] ?? '';

// Obtener los datos de la reserva de la sesión
$fecha = $_SESSION['fecha'] ?? '';
$zona = $_SESSION['zona'] ?? '';
$turno_id = $_SESSION['turno_id'] ?? '';
$hora = $_SESSION['hora'] ?? '';
$nombre_turno = $_SESSION['nombre_turno'] ?? '';

// Guardar los datos de la reserva en la sesión para poder recuperarlos si el usuario decide volver atrás
$_SESSION['datos_reserva'] = [
    'fecha' => $fecha,
    'zona' => $zona,
    'turno_id' => $turno_id
];

// Obtener el número máximo de personas sin aprobación desde la base de datos
$max_personas_sin_aprobacion = 8; // Valor por defecto
try {
    $pdo = new PDO('mysql:host=localhost;dbname=restaurante_reservas', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    $stmt = $pdo->query("SELECT max_personas_sin_aprobacion FROM configuracion WHERE id = 1");
    $config = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($config) {
        $max_personas_sin_aprobacion = $config['max_personas_sin_aprobacion'];
    }
} catch (PDOException $e) {
    // Silenciar error y usar valor por defecto
    error_log("Error al obtener max_personas_sin_aprobacion: " . $e->getMessage());
}

// Verificar si la reserva debe guardarse automáticamente (número de personas menor o igual al máximo)
$autoguardar = $num_personas <= $max_personas_sin_aprobacion;

// Ya no guardamos automáticamente la reserva, solo guardamos la información para mostrarla
// y verificar disponibilidad
try {
    $pdo = new PDO('mysql:host=localhost;dbname=restaurante_reservas', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    // Obtener información del turno
    $stmt = $pdo->prepare("SELECT hora_inicio, hora_fin FROM turnos WHERE id = ?");
    $stmt->execute([$turno_id]);
    $turno_info = $stmt->fetch();
    $hora_inicio = $turno_info['hora_inicio'] ?? '';
    
    // Verificar disponibilidad del día
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as disponible 
        FROM dias_disponibles 
        WHERE fecha = ? AND turno_id = ? AND zona = ? AND disponible = 1
    ");
    $stmt->execute([$fecha, $turno_id, $zona]);
    $disponible = $stmt->fetchColumn() > 0;
    
    // Si el día no está disponible, mostramos un mensaje
    if (!$disponible) {
        $_SESSION['error_reserva'] = "Lo sentimos, el día seleccionado no está disponible para reservas.";
        $_SESSION['diagnostico'][] = "ERROR: El día no está marcado como disponible";
        // No redirigimos, permitimos que el usuario vea la página de confirmación
    }
    
    // Verificar aforo disponible
    if ($disponible) {
        // Obtener capacidad general de la configuración
        $stmt = $pdo->prepare("SELECT * FROM configuracion WHERE id = 1");
        $stmt->execute();
        $config = $stmt->fetch();
        
        // Determinar el campo de capacidad según turno y zona
        $stmt = $pdo->prepare("SELECT nombre FROM turnos WHERE id = ?");
        $stmt->execute([$turno_id]);
        $nombre_turno = $stmt->fetchColumn();
        
        $campo_capacidad = '';
        if ($nombre_turno == 'mediodia') {
            $campo_capacidad = ($zona == 'dentro') ? 'capacidad_dentro_mediodia' : 'capacidad_fuera_mediodia';
        } else {
            $campo_capacidad = ($zona == 'dentro') ? 'capacidad_dentro_noche' : 'capacidad_fuera_noche';
        }
        
        // Obtener el aforo máximo de la configuración
        $aforo_maximo = $config[$campo_capacidad] ?? (($zona == 'dentro') ? 30 : 20);
        
        // Obtener el número de personas ya reservadas (solo reservas confirmadas)
        $stmt = $pdo->prepare("
            SELECT SUM(cantidad_personas) as total_reservado
            FROM reservas
            WHERE fecha = ? AND zona = ? AND turno_id = ? AND estado = 'confirmada'
        ");
        $stmt->execute([$fecha, $zona, $turno_id]);
        $resultado = $stmt->fetch();
        $total_reservado = $resultado['total_reservado'] ?: 0;
        
        // Calcular aforo disponible
        $aforo_disponible = $aforo_maximo - $total_reservado;
        
        // Verificar si hay suficiente aforo disponible
        if ($aforo_disponible < $num_personas) {
            $_SESSION['error_reserva'] = "Lo sentimos, no hay suficiente aforo disponible para el número de personas indicado.";
            $_SESSION['diagnostico'][] = "ERROR: Aforo insuficiente. Disponible: $aforo_disponible, Solicitado: $num_personas";
            // No redirigimos, permitimos que el usuario vea la página de confirmación
                    $horario_especifico = substr($hora_seleccionada, 0, 5);
                    $horario_turno = substr($hora_inicio, 0, 5) . ' - ' . substr($turno_info['hora_fin'], 0, 5);
                
                    // Correo de confirmación
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
                                <p>Estimado/a <strong>{$nombre}</strong>,</p>
                                <p>Nos complace confirmar su reserva en nuestro restaurante con los siguientes detalles:</p>
                                
                                <div class='info'>
                                    <p><strong>Fecha:</strong> {$fecha}</p>
                                    <p><strong>Hora reservada:</strong> {$horario_especifico}</p>
                                    <p><strong>Horario del turno:</strong> {$horario_turno}</p>
                                    <p><strong>Turno:</strong> {$turno_texto}</p>
                                    <p><strong>Zona:</strong> {$zona_texto}</p>
                                    <p><strong>Número de personas:</strong> {$num_personas}</p>
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
                        
                        Estimado/a {$nombre},
                        
                        Nos complace confirmar su reserva en nuestro restaurante con los siguientes detalles:
                        
                        Fecha: {$fecha}
                        Horario: {$horario}
                        Turno: {$turno_texto}
                        Zona: {$zona_texto}
                        Número de personas: {$num_personas}
                        
                        Si necesita realizar algún cambio en su reserva, por favor contáctenos lo antes posible.
                        
                        ¡Esperamos recibirle pronto en nuestro restaurante!
                        
                        Este es un correo automático, por favor no responda a este mensaje.
                    ";
                    
                    // Registrar información antes de enviar
                    error_log("Enviando correo de confirmación automática a: $email");
                    
                    try {
                        // Enviar el correo al cliente
                        $enviado = enviar_correo_directo($email, $asunto, $cuerpo, $texto_alternativo ?? '');
                        
                        if ($enviado) {
                            error_log("Correo de confirmación automática enviado correctamente a: $email");
                        } else {
                            error_log("Error al enviar correo de confirmación automática a: $email");
                        }
                        
                        // Redirigir directamente a la página principal con mensaje de éxito
                        $_SESSION['reserva_exitosa'] = true; // Variable para mostrar mensaje de éxito
                        header("Location: index.php");
                        exit;
                    } catch (Exception $e) {
                        // Silenciar errores de envío de correo para no interrumpir el proceso
                        error_log("Error al enviar correo automático: " . $e->getMessage());
                        
                        // Redirigir a la página de confirmación final aún si hay error en el correo
                        header("Location: confirmacion.php");
                        exit;
                    }
            }
        }
    } catch (PDOException $e) {
        // Registrar el error pero continuar mostrando la página
        error_log("Error al guardar automáticamente la reserva: " . $e->getMessage());
    }
}

// Registrar datos POST para depuración
error_log("Datos POST en confirmar_reserva.php: " . print_r($_POST, true));

// Procesar la confirmación final
if (isset($_POST['confirmar']) && $_POST['confirmar'] === 'true' && isset($_POST['confirmar_datos']) && $_POST['confirmar_datos'] === 'on') {
    error_log("Procesando confirmación de reserva");
    
    // Verificar que los datos POST estén presentes
    if (isset($_POST['fecha']) && isset($_POST['zona']) && isset($_POST['turno_id'])) {
        // Recoger todos los datos del formulario
        $fecha = $_POST['fecha'];
        $zona = $_POST['zona'];
        $turno_id = $_POST['turno_id'];
        $tiene_alergenos = isset($_POST['tiene_alergenos']) ? $_POST['tiene_alergenos'] === '1' : false;
        $alergenos = isset($_POST['alergenos']) ? $_POST['alergenos'] : '';
        $tiene_necesidades = isset($_POST['tiene_necesidades']) ? $_POST['tiene_necesidades'] === '1' : false;
        $necesidades_especiales = isset($_POST['necesidades_especiales']) ? $_POST['necesidades_especiales'] : '';
        
        // Registrar los datos recibidos para depuración
        error_log("Datos recibidos del formulario:");
        error_log("Fecha: $fecha");
        error_log("Zona: $zona");
        error_log("Turno ID: $turno_id");
        error_log("Tiene Alérgenos: " . ($tiene_alergenos ? 'Sí' : 'No'));
        error_log("Alérgenos: $alergenos");
        error_log("Tiene Necesidades: " . ($tiene_necesidades ? 'Sí' : 'No'));
        error_log("Necesidades Especiales: $necesidades_especiales");
    } else {
        error_log("ERROR: Faltan datos requeridos en el formulario");
        $_SESSION['error_reserva'] = "Faltan datos requeridos para procesar la reserva.";
        header("Location: reserva.php");
        exit;
    }
    // Convertir fecha del formato d/m/Y a Y-m-d para la base de datos
    $fechaObj = DateTime::createFromFormat('Y-m-d', $fecha);
    if ($fechaObj) {
        $fecha_bd = $fecha; // Ya está en formato Y-m-d
        
        // Configuración de la base de datos
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
        
        try {
            // Conectar a la base de datos
            $pdo = new PDO($dsn, $user, $pass, $options);
            
            // Obtener el ID del turno
            $stmt = $pdo->prepare("SELECT id, hora_inicio, hora_fin FROM turnos WHERE id = ?");
            $stmt->execute([$turno_id]);
            $turno_info = $stmt->fetch();
            $hora_inicio = $turno_info['hora_inicio'] ?? '';
            $hora_fin = $turno_info['hora_fin'] ?? '';
            
            if (!$turno_id) {
                throw new Exception("Turno no válido.");
            }
            
            // Verificar disponibilidad
            $stmt = $pdo->prepare("
                SELECT COUNT(*) 
                FROM dias_disponibles 
                WHERE fecha = ? AND turno_id = ? AND zona = ?
            ");
            $stmt->execute([$fecha_bd, $turno_id, $zona]);
            $existe_configuracion = $stmt->fetchColumn() > 0;
            
            // Si no existe configuración para ese día, considerarlo disponible por defecto
            if (!$existe_configuracion) {
                // Insertar automáticamente el día como disponible
                $stmt = $pdo->prepare("INSERT INTO dias_disponibles (fecha, turno_id, zona, disponible) VALUES (?, ?, ?, 1)");
                $stmt->execute([$fecha_bd, $turno_id, $zona]);
                $disponible = true;
                error_log("Se ha añadido automáticamente el día $fecha_bd como disponible para turno $turno_id y zona $zona");
                $_SESSION['diagnostico'][] = "AVISO: Se ha añadido automáticamente el día $fecha_bd como disponible";
            } else {
                // Verificar si está marcado como disponible
                $stmt = $pdo->prepare("
                    SELECT disponible 
                    FROM dias_disponibles 
                    WHERE fecha = ? AND turno_id = ? AND zona = ?
                ");
                $stmt->execute([$fecha_bd, $turno_id, $zona]);
                $disponible = $stmt->fetchColumn();
            }
            
            // Obtener la capacidad desde la configuración general
            $stmt = $pdo->prepare("SELECT * FROM configuracion WHERE id = 1");
            $stmt->execute();
            $config = $stmt->fetch();
            
            // Determinar el campo de capacidad según turno y zona
            $campo_capacidad = '';
            if ($nombre_turno == 'mediodia') {
                $campo_capacidad = ($zona == 'dentro') ? 'capacidad_dentro_mediodia' : 'capacidad_fuera_mediodia';
            } else {
                $campo_capacidad = ($zona == 'dentro') ? 'capacidad_dentro_noche' : 'capacidad_fuera_noche';
            }
            
            // Obtener el aforo máximo de la configuración
            $aforo_maximo = $config[$campo_capacidad] ?? (($zona == 'dentro') ? 30 : 20);
            
            // Obtener el número de personas ya reservadas
            $stmt = $pdo->prepare("
                SELECT SUM(cantidad_personas) as total_reservado
                FROM reservas
                WHERE fecha = ? AND zona = ? AND turno_id = ? AND estado = 'confirmada'
            ");
            $stmt->execute([$fecha_bd, $zona, $turno_id]);
            $resultado = $stmt->fetch();
            $total_reservado = $resultado['total_reservado'] ?: 0;
            
            // Obtener el número máximo de personas sin aprobación
            $stmt = $pdo->prepare("SELECT max_personas_sin_aprobacion FROM configuracion WHERE id = 1");
            $stmt->execute();
            $max_personas_sin_aprobacion = $stmt->fetchColumn() ?: 4;
            
            // Determinar si la reserva será confirmada o pendiente
            $sera_confirmada = ($num_personas <= $max_personas_sin_aprobacion);
            
            // Calcular si hay suficiente aforo disponible
            $aforo_disponible = $aforo_maximo - $total_reservado;
            
            // Crear un array para almacenar mensajes de diagnóstico
            $_SESSION['diagnostico'] = [
                "Fecha: $fecha_bd, Zona: " . ($zona == 'dentro' ? 'Interior' : 'Terraza') . ", Turno: $nombre_turno",
                "Aforo máximo: $aforo_maximo, Total reservado: $total_reservado, Aforo disponible: $aforo_disponible",
                "Día marcado como disponible: " . ($disponible ? 'Sí' : 'No'),
                "Número de personas solicitadas: $num_personas",
                "Será confirmada automáticamente: " . ($sera_confirmada ? 'Sí' : 'No')
            ];
            
            // Si la reserva será confirmada automáticamente, verificar que haya suficiente aforo
            // Si la reserva será pendiente, no verificar aforo (el administrador decidirá después)
            $hay_disponibilidad = true; // Por defecto consideramos que hay disponibilidad
            
            // Verificar si el día está marcado como disponible
            if (!$disponible) {
                $_SESSION['diagnostico'][] = "ERROR: El día no está marcado como disponible";
                $hay_disponibilidad = false;
            }
            
            // Si la reserva será confirmada automáticamente, verificar también el aforo
            if ($sera_confirmada && $aforo_disponible < $num_personas) {
                $_SESSION['diagnostico'][] = "ERROR: No hay suficiente aforo disponible para confirmar automáticamente";
                $hay_disponibilidad = false;
            }
            
            // Registrar información detallada para depuración
            error_log("Confirmar_reserva (verificación inicial): Fecha: $fecha_bd, Zona: $zona, Turno: $nombre_turno, Aforo máximo: $aforo_maximo, Total reservado: $total_reservado, Aforo disponible: $aforo_disponible, Personas solicitadas: $num_personas, Hay disponibilidad: " . ($hay_disponibilidad ? 'Sí' : 'No'));
            
            $_SESSION['diagnostico'][] = "Resultado final: " . ($hay_disponibilidad ? 'DISPONIBLE' : 'NO DISPONIBLE');
            
            if ($hay_disponibilidad) {
                // Insertamos un cliente nuevo para cada reserva
                $stmt = $pdo->prepare("
                    INSERT INTO clientes (nombre, email, telefono) 
                    VALUES (?, ?, ?)
                ");
                
                // Obtener información del turno para mostrar en la página de confirmación
try {
    $pdo = new PDO('mysql:host=localhost;dbname=restaurante_reservas', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    // Obtener información del turno
    $stmt = $pdo->prepare("SELECT hora_inicio, hora_fin FROM turnos WHERE id = ?");
    $stmt->execute([$turno_id]);
    $turno_info = $stmt->fetch();
    $hora_inicio = $turno_info['hora_inicio'] ?? '';
    $hora_fin = $turno_info['hora_fin'] ?? '';
    
    // Formatear la zona para mostrar
    $zona_texto = $zona === 'dentro' ? 'Interior' : 'Terraza';
    
    // Formatear el turno para mostrar
    $stmt = $pdo->prepare("SELECT nombre FROM turnos WHERE id = ?");
    $stmt->execute([$turno_id]);
    $nombre_turno = $stmt->fetchColumn();
    $turno_texto = $nombre_turno === 'mediodia' ? 'Mediodía' : 'Noche';
    
    // Obtener la hora seleccionada por el usuario si está disponible
    $hora_seleccionada = isset($_SESSION['hora']) && !empty($_SESSION['hora']) ? $_SESSION['hora'] : $hora_inicio;
    
    // Formatear el horario del turno completo
    $horario_turno = substr($hora_inicio, 0, 5) . ' - ' . substr($hora_fin, 0, 5);
    
    // Formatear la hora específica seleccionada
    $horario_especifico = substr($hora_seleccionada, 0, 5);
    
    // Mantener la variable $horario para compatibilidad con código existente
    $horario = $horario_especifico . ' (' . $horario_turno . ')';
                
                try {
                    // Incluir archivos necesarios
                    // Eliminado require de Mailer/FileMailer, ahora usamos enviar_correo_directo
                    require_once 'generar_correo_reserva.php';
                    
                    // Crear instancia de Mailer con el namespace completo
                    // Usar la función de envío directo en lugar de instanciar una clase
                    
                    // Enviar correo según el estado de la reserva
                    // Preparar los datos para los correos
                    $datos_correo = [
                        'nombre' => $nombre,
                        'email' => $email,
                        'telefono' => $telefono,
                        'fecha' => $fecha,
                        'hora_seleccionada' => $horario_especifico,
                        'horario_turno' => $horario_turno,
                        'turno_texto' => $turno_texto,
                        'zona_texto' => $zona_texto,
                        'num_personas' => $num_personas,
                        'reserva_id' => $reservaId,
                        'estado' => $estado
                    ];
                    
                    if ($estado === 'confirmada') {
                        // Correo de confirmación para reservas automáticamente confirmadas
                        $asunto = "Confirmación de reserva - Restaurante";
                        
                        // Generar el cuerpo del correo y el texto alternativo usando las nuevas funciones
                        $cuerpo = generarCuerpoCorreoHTML($datos_correo);
                        $texto_alternativo = generarTextoAlternativoCorreo($datos_correo);
                        
                        // Registrar información antes de enviar
                        error_log("Enviando correo de confirmación a: $email");
                        
                        // Enviar el correo al cliente
                        $enviado = enviar_correo_directo($email, $asunto, $cuerpo, $texto_alternativo ?? '');
                        
                        if ($enviado) {
                            error_log("Correo de confirmación enviado correctamente a: $email");
                        } else {
                            error_log("Error al enviar correo de confirmación a: $email");
                        }
                    } else {
                        // Correo para reservas pendientes de aprobación
                        $asunto = "Su reserva está en revisión - Restaurante";
                        
                        // Generar el cuerpo del correo y el texto alternativo usando las nuevas funciones
                        $cuerpo = generarCuerpoCorreoHTML($datos_correo);
                        $texto_alternativo = generarTextoAlternativoCorreo($datos_correo);
                        
                        // Enviar el correo al cliente
                        enviar_correo_directo($email, $asunto, $cuerpo, $texto_alternativo ?? '');
                        
                        // Enviar notificación a todos los administradores
                        // Obtener correos de administradores
                        $stmt = $pdo->prepare("
                            SELECT email 
                            FROM administrador 
                            WHERE recibir_notificaciones = 1
                        ");
                        $stmt->execute();
                        $admin_emails = $stmt->fetchAll(PDO::FETCH_COLUMN);
                        
                        if (!empty($admin_emails)) {
                            $admin_asunto = "Nueva reserva pendiente de aprobación - Restaurante";
                            
                            // URL del panel de administración
                            $admin_url = 'http://' . $_SERVER['HTTP_HOST'] . '/reservar%20restaurantes/admin/reservas.php';
                            
                            // Generar el correo para administradores usando las nuevas funciones
                            $admin_cuerpo = generarCuerpoCorreoAdminHTML($datos_correo, $admin_url);
                            $admin_texto_alternativo = generarTextoAlternativoCorreoAdmin($datos_correo, $admin_url);
                            
                            // Registrar información antes de enviar
                            error_log("Enviando correo de notificación a administradores: " . implode(', ', $admin_emails));
                            
                            // Enviar el correo a todos los administradores
                            $enviado_admin = enviar_correo_directo($admin_emails, $admin_asunto, $admin_cuerpo, $admin_texto_alternativo ?? '');
                            
                            if ($enviado_admin) {
                                error_log("Correo de notificación enviado correctamente a los administradores");
                            } else {
                                error_log("Error al enviar correo de notificación a los administradores");
                            }
                        } else {
                            error_log("No se encontraron administradores para enviar notificaciones");
                        }
                    }
                } catch (Exception $e) {
                    // Silenciar errores de envío de correo para no interrumpir el proceso
                    error_log("Error al enviar correo: " . $e->getMessage());
                }
                
                // Redirigir directamente a la página principal con mensaje de éxito
                $_SESSION['reserva_exitosa'] = true; // Variable para mostrar mensaje de éxito
                header("Location: index.php");
                exit;
            } else {
                // Redirigir a la página de reserva con un mensaje de error
                $_SESSION['error_reserva'] = "Lo sentimos, no hay disponibilidad para la fecha, zona y turno seleccionados.";
                header("Location: reserva.php");
                exit;
            }
        } catch (Exception $e) {
            // Redirigir a la página de reserva con un mensaje de error
            $_SESSION['error_reserva'] = "Error al procesar la reserva: " . $e->getMessage();
            header("Location: reserva.php");
            exit;
        }
    } else {
        // Redirigir a la página de reserva con un mensaje de error
        $_SESSION['error_reserva'] = "Formato de fecha incorrecto.";
        header("Location: reserva.php");
        exit;
    }
}

// Formatear la zona para mostrar
$zona_texto = $zona === 'dentro' ? 'Interior' : 'Terraza';

// Formatear el turno para mostrar
$turno_texto = $nombre_turno === 'mediodia' ? 'Mediodía' : 'Noche';

// Obtener horario del turno
$horario = '';
try {
    $pdo = new PDO('mysql:host=localhost;dbname=restaurante_reservas', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    $stmt = $pdo->prepare("SELECT hora_inicio, hora_fin FROM turnos WHERE id = ?");
    $stmt->execute([$turno_id]);
    $turno_info = $stmt->fetch();
    if ($turno_info) {
        $horario = substr($turno_info['hora_inicio'], 0, 5) . ' - ' . substr($turno_info['hora_fin'], 0, 5);
    }
} catch (PDOException $e) {
    // Silenciar error
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmar Reserva - Restaurante</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <!-- Encabezado -->
        <header class="mb-8 text-center">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Confirmar Reserva</h1>
            <p class="text-gray-600">Por favor, revise los detalles de su reserva antes de confirmar</p>
        </header>
        
        <main class="max-w-4xl mx-auto">
            <div class="bg-white rounded-lg shadow-md p-8 mb-8">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">Resumen de su reserva</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Datos del cliente -->
                    <div>
                        <h3 class="text-lg font-medium text-blue-700 mb-4 border-b border-gray-200 pb-2">Datos del cliente</h3>
                        <div class="space-y-3">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Nombre:</p>
                                <p class="text-base font-medium text-gray-800"><?php echo htmlspecialchars($nombre); ?></p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Email:</p>
                                <p class="text-base font-medium text-gray-800"><?php echo htmlspecialchars($email); ?></p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Teléfono:</p>
                                <p class="text-base font-medium text-gray-800"><?php echo htmlspecialchars($telefono); ?></p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Número de personas:</p>
                                <p class="text-base font-medium text-gray-800"><?php echo $num_personas; ?></p>
                            </div>
                            <?php if ($tiene_alergenos): ?>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Alérgenos:</p>
                                <p class="text-base font-medium text-gray-800"><?php echo !empty($alergenos) ? htmlspecialchars($alergenos) : 'Sí'; ?></p>
                            </div>
                            <?php endif; ?>
                            <?php if ($tiene_necesidades): ?>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Necesidades especiales:</p>
                                <p class="text-base font-medium text-gray-800"><?php echo !empty($necesidades_especiales) ? htmlspecialchars($necesidades_especiales) : 'Sí'; ?></p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Datos de la reserva -->
                    <div>
                        <h3 class="text-lg font-medium text-blue-700 mb-4 border-b border-gray-200 pb-2">Detalles de la reserva</h3>
                        <div class="space-y-3">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Fecha:</p>
                                <p class="text-base font-medium text-gray-800"><?php echo htmlspecialchars($fecha); ?></p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Turno:</p>
                                <p class="text-base font-medium text-gray-800"><?php echo htmlspecialchars($turno_texto); ?></p>
                            </div>
                            <?php 
                            // Obtener la información de horario
                            $info_horario = obtener_info_horario($_SESSION['hora'] ?? null, $hora_inicio ?? null, $hora_fin ?? null);
                            
                            // Mostrar la hora específica seleccionada por el usuario
                            if (!empty($info_horario['horario_especifico'])): 
                            ?>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Hora reservada:</p>
                                <p class="text-base font-medium text-gray-800"><?php echo htmlspecialchars($info_horario['horario_especifico']); ?></p>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($info_horario['horario_turno'])): ?>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Horario del turno:</p>
                                <p class="text-base font-medium text-gray-800"><?php echo htmlspecialchars($info_horario['horario_turno']); ?></p>
                            </div>
                            <?php endif; ?>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Zona:</p>
                                <p class="text-base font-medium text-gray-800"><?php echo htmlspecialchars($zona_texto); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-8 border-t border-gray-200 pt-6">
                    <div class="flex flex-col md:flex-row justify-center space-y-4 md:space-y-0 md:space-x-4">
                        <!-- Botón para volver atrás y modificar los datos -->
                        <form action="reserva.php" method="get" class="flex-1 max-w-xs mx-auto md:mx-0">
                            <button type="submit" class="w-full py-3 px-6 border border-gray-300 rounded-md shadow-sm text-base font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <i class="fas fa-arrow-left mr-2"></i> Modificar datos
                            </button>
                        </form>
                        
                        <!-- Botón para confirmar la reserva -->
                        <form action="confirmar_reserva.php" method="post" id="form_confirmar" class="flex-1 max-w-xs mx-auto md:mx-0">
                            <input type="hidden" name="fecha" value="<?php echo htmlspecialchars($fecha); ?>">
                            <input type="hidden" name="zona" value="<?php echo htmlspecialchars($zona); ?>">
                            <input type="hidden" name="turno_id" value="<?php echo htmlspecialchars($turno_id); ?>">
                            <input type="hidden" name="tiene_alergenos" value="<?php echo $tiene_alergenos ? '1' : '0'; ?>">
                            <input type="hidden" name="alergenos" value="<?php echo htmlspecialchars($alergenos); ?>">
                            <input type="hidden" name="tiene_necesidades" value="<?php echo $tiene_necesidades ? '1' : '0'; ?>">
                            <input type="hidden" name="necesidades_especiales" value="<?php echo htmlspecialchars($necesidades_especiales); ?>">
                            <input type="hidden" name="confirmar" value="true">
                            
                            <!-- Checkbox de confirmación de datos -->
                            <div class="mb-4 text-left">
                                <label class="inline-flex items-center">
                                    <input type="checkbox" id="confirmar_datos" name="confirmar_datos" class="form-checkbox h-5 w-5 text-blue-600" required>
                                    <span class="ml-2 text-gray-700">Confirmo que todos los datos son correctos</span>
                                </label>
                                
                                <!-- Aviso sobre correos en spam -->
                                <div class="mt-3 p-3 bg-blue-50 border-l-4 border-blue-500 text-blue-700 text-sm">
                                        <p class="font-medium">⚠️ Importante:</p>
                                        <p>Al confirmar la reserva, recibirás un correo electrónico con los detalles. Por favor, revisa también tu carpeta de spam o correo no deseado, ya que en ocasiones nuestros correos pueden ser filtrados por tu proveedor de correo electrónico.</p>
                                    </div>
                                    
                                    <!-- Avisos personalizados desde la base de datos -->
                                    <?php
                                    try {
                                        // Obtener avisos activos ordenados
                                        $stmt = $pdo->prepare("SELECT texto FROM avisos_reserva WHERE activo = 1 ORDER BY orden ASC");
                                        $stmt->execute();
                                        $avisos = $stmt->fetchAll();
                                        
                                        if (count($avisos) > 0) {
                                            echo '<div class="mt-4 space-y-3">';
                                            foreach ($avisos as $aviso) {
                                                echo '<div class="p-3 bg-yellow-50 border-l-4 border-yellow-500 text-yellow-800 text-sm">';
                                                echo '<p>' . htmlspecialchars($aviso['texto']) . '</p>';
                                                echo '</div>';
                                            }
                                            echo '</div>';
                                        }
                                    } catch (\Exception $e) {
                                        // Silenciar errores para no interrumpir el proceso
                                        error_log("Error al obtener avisos personalizados: " . $e->getMessage());
                                    }
                                    ?>
                            </div>
                            
                            <div id="mensaje_procesando" class="hidden mb-3 p-2 bg-yellow-100 text-yellow-800 text-center rounded">
                                <div class="flex items-center justify-center">
                                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-yellow-800" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Procesando su reserva, por favor espere...
                                </div>
                            </div>
                            
                            <button type="submit" id="btn_confirmar" class="w-full py-3 px-6 border border-transparent rounded-md shadow-sm text-base font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                                Confirmar reserva <i class="fas fa-check ml-2"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </main>
        
        <!-- Pie de página -->
        <footer class="mt-12 text-center text-gray-500 text-sm">
            <p>&copy; <?php echo date('Y'); ?> Sistema de Reservas de Restaurantes</p>
        </footer>
    </div>
</body>
<script>
    // Script para habilitar/deshabilitar el botón de confirmar según el estado del checkbox
    // y prevenir múltiples envíos del formulario
    document.addEventListener('DOMContentLoaded', function() {
        const checkbox = document.getElementById('confirmar_datos');
        const btnConfirmar = document.getElementById('btn_confirmar');
        const formConfirmar = document.getElementById('form_confirmar');
        const mensajeProcesando = document.getElementById('mensaje_procesando');
        let formularioEnviado = false;
        
        // Habilitar/deshabilitar el botón según el estado del checkbox
        checkbox.addEventListener('change', function() {
            btnConfirmar.disabled = !this.checked || formularioEnviado;
        });
        
        // Prevenir múltiples envíos del formulario
        formConfirmar.addEventListener('submit', function(e) {
            if (formularioEnviado) {
                // Si el formulario ya ha sido enviado, prevenir un nuevo envío
                e.preventDefault();
                return false;
            }
            
            // Marcar el formulario como enviado
            formularioEnviado = true;
            
            // Deshabilitar el botón y mostrar mensaje de procesamiento
            btnConfirmar.disabled = true;
            btnConfirmar.innerHTML = 'Procesando...';
            mensajeProcesando.classList.remove('hidden');
            
            // Permitir que el formulario se envíe
            return true;
        });
    });
</script>
</html>
