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

// Ya no guardamos automáticamente la reserva, solo verificamos disponibilidad
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
        }
        
        // Guardar información de disponibilidad en la sesión para usarla después
        $_SESSION['disponibilidad_verificada'] = [
            'disponible' => $disponible,
            'aforo_maximo' => $aforo_maximo,
            'total_reservado' => $total_reservado,
            'aforo_disponible' => $aforo_disponible,
            'hay_disponibilidad' => $aforo_disponible >= $num_personas
        ];
    }
    
    // Formatear zona y turno para mostrar
    $zona_texto = $zona === 'dentro' ? 'Interior' : 'Terraza';
    $turno_texto = $nombre_turno === 'mediodia' ? 'Mediodía' : 'Noche';
    
    // Obtener la hora seleccionada por el usuario si está disponible
    $hora_seleccionada = isset($_SESSION['hora']) && !empty($_SESSION['hora']) ? $_SESSION['hora'] : $hora_inicio;
    
    // Formatear el horario del turno completo
    $horario_turno = substr($hora_inicio, 0, 5) . ' - ' . substr($hora_fin, 0, 5);
    
    // Formatear la hora específica seleccionada
    $horario_especifico = substr($hora_seleccionada, 0, 5);
    
    // Mantener la variable $horario para compatibilidad con código existente
    $horario = $horario_especifico . ' (' . $horario_turno . ')';
    
} catch (PDOException $e) {
    // Registrar el error pero continuar mostrando la página
    error_log("Error al verificar disponibilidad: " . $e->getMessage());
}

// Registrar datos POST para depuración
error_log("Datos POST en confirmar_reserva.php: " . print_r($_POST, true));

// Procesar el formulario de confirmación
if (isset($_POST['confirmar']) && $_POST['confirmar'] === 'true' && isset($_POST['confirmar_datos']) && $_POST['confirmar_datos'] === 'on') {
    error_log("Procesando confirmación de reserva");
    
    // Verificar que los datos POST estén presentes
    if (isset($_POST['fecha']) && isset($_POST['zona']) && isset($_POST['turno_id'])) {
        // Obtener los datos del formulario
        $fecha = $_POST['fecha'];
        $zona = $_POST['zona'];
        $turno_id = $_POST['turno_id'];
        $tiene_alergenos = isset($_POST['tiene_alergenos']) ? (int)$_POST['tiene_alergenos'] : 0;
        $alergenos = isset($_POST['alergenos']) ? $_POST['alergenos'] : '';
        $tiene_necesidades = isset($_POST['tiene_necesidades']) ? (int)$_POST['tiene_necesidades'] : 0;
        $necesidades_especiales = isset($_POST['necesidades_especiales']) ? $_POST['necesidades_especiales'] : '';
        $hora = isset($_POST['hora']) ? $_POST['hora'] : '';
        
        // Validar la fecha
        $fechaObj = DateTime::createFromFormat('Y-m-d', $fecha);
        $fechaValida = $fechaObj && $fechaObj->format('Y-m-d') === $fecha;
        
        if (!$fechaValida) {
            // Intentar con otro formato
            $fechaObj = DateTime::createFromFormat('d/m/Y', $fecha);
            if ($fechaObj) {
                $fecha = $fechaObj->format('Y-m-d');
            }
        }
        
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
                $stmt = $pdo->prepare("SELECT id, nombre, hora_inicio, hora_fin FROM turnos WHERE id = ?");
                $stmt->execute([$turno_id]);
                $turno = $stmt->fetch();
                
                if (!$turno) {
                    throw new Exception("Turno no válido");
                }
                
                $nombre_turno = $turno['nombre'];
                $hora_inicio = $turno['hora_inicio'];
                $hora_fin = $turno['hora_fin'];
                
                // Verificar disponibilidad del día
                $stmt = $pdo->prepare("
                    SELECT COUNT(*) as disponible 
                    FROM dias_disponibles 
                    WHERE fecha = ? AND turno_id = ? AND zona = ? AND disponible = 1
                ");
                $stmt->execute([$fecha_bd, $turno_id, $zona]);
                $disponible = $stmt->fetchColumn() > 0;
                
                // Inicializar variable para verificar disponibilidad
                $hay_disponibilidad = true;
                
                // Inicializar array de diagnóstico si no existe
                if (!isset($_SESSION['diagnostico'])) {
                    $_SESSION['diagnostico'] = [];
                }
                
                // Verificar si el día está disponible
                if (!$disponible) {
                    $_SESSION['diagnostico'][] = "ERROR: El día no está marcado como disponible";
                    $hay_disponibilidad = false;
                }
                
                // Obtener capacidad general de la configuración
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
                
                // Obtener el número de personas ya reservadas (solo reservas confirmadas)
                $stmt = $pdo->prepare("
                    SELECT SUM(cantidad_personas) as total_reservado
                    FROM reservas
                    WHERE fecha = ? AND zona = ? AND turno_id = ? AND estado = 'confirmada'
                ");
                $stmt->execute([$fecha_bd, $zona, $turno_id]);
                $resultado = $stmt->fetch();
                $total_reservado = $resultado['total_reservado'] ?: 0;
                
                // Calcular aforo disponible
                $aforo_disponible = $aforo_maximo - $total_reservado;
                
                // Registrar información para diagnóstico
                $_SESSION['diagnostico'][] = "Fecha: $fecha_bd, Zona: $zona, Turno: $nombre_turno";
                $_SESSION['diagnostico'][] = "Aforo máximo: $aforo_maximo, Total reservado: $total_reservado, Aforo disponible: $aforo_disponible, Personas solicitadas: $num_personas";
                
                // Verificar si hay suficiente aforo disponible
                if ($aforo_disponible < $num_personas) {
                    $_SESSION['diagnostico'][] = "ERROR: Aforo insuficiente";
                    $hay_disponibilidad = false;
                }
                
                if ($hay_disponibilidad) {
                    // Insertamos un cliente nuevo para cada reserva
                    $stmt = $pdo->prepare("
                        INSERT INTO clientes (nombre, email, telefono) 
                        VALUES (?, ?, ?)
                    ");
                    
                    $stmt->execute([$nombre, $email, $telefono]);
                    
                    // Obtener el ID del cliente insertado
                    $clienteId = $pdo->lastInsertId();
                    
                    // Usar la hora seleccionada por el usuario si está disponible, de lo contrario usar la hora de inicio del turno
                    $hora = isset($_SESSION['hora']) && !empty($_SESSION['hora']) ? $_SESSION['hora'] : $hora_inicio;
                    error_log("Hora seleccionada para la reserva: $hora");
                    
                    // Determinar el estado de la reserva según el número de personas
                    $estado = ($num_personas > $max_personas_sin_aprobacion) ? 'pendiente' : 'confirmada';
                    
                    // Si la reserva va a ser confirmada, verificar que haya suficiente aforo disponible
                    if ($estado === 'confirmada') {
                        // Verificar si hay suficiente aforo disponible para esta reserva
                        $stmt = $pdo->prepare("
                            SELECT SUM(cantidad_personas) as total_reservado
                            FROM reservas
                            WHERE fecha = ? AND zona = ? AND turno_id = ? AND estado = 'confirmada'
                        ");
                        $stmt->execute([$fecha_bd, $zona, $turno_id]);
                        $resultado = $stmt->fetch();
                        $total_reservado = $resultado['total_reservado'] ?: 0;
                        
                        $aforo_disponible = $aforo_maximo - $total_reservado;
                        $_SESSION['diagnostico'][] = "Verificación final - Aforo disponible: $aforo_disponible, Personas solicitadas: $num_personas";
                        
                        // Si no hay suficiente aforo disponible, cambiar el estado a pendiente
                        if ($aforo_disponible < $num_personas) {
                            $estado = 'pendiente';
                            $_SESSION['diagnostico'][] = "AVISO: Cambiando estado a pendiente por falta de aforo disponible";
                            error_log("Cambiando estado a pendiente por falta de aforo disponible. Aforo disponible: $aforo_disponible, Personas: $num_personas");
                        } else {
                            $_SESSION['diagnostico'][] = "ÉXITO: Hay suficiente aforo disponible para confirmar la reserva";
                        }
                        
                        // Registrar información detallada para depuración
                        error_log("Confirmar_reserva (verificación final): Fecha: $fecha_bd, Zona: $zona, Turno: $nombre_turno, Aforo máximo: $aforo_maximo, Total reservado: $total_reservado, Aforo disponible: $aforo_disponible, Personas solicitadas: $num_personas, Estado final: $estado");
                    }
                    
                    // Insertar la reserva con el nuevo cliente_id
                    $stmt = $pdo->prepare("
                        INSERT INTO reservas (
                            cliente_id, fecha, zona, turno_id, 
                            hora, cantidad_personas, observaciones, 
                            necesidades_especiales, tiene_alergenos, estado
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ");
                    
                    // Preparar observaciones combinando alérgenos y necesidades especiales
                    $observaciones = '';
                    
                    // Añadir alérgenos a las observaciones si existen
                    if ($tiene_alergenos && !empty($alergenos)) {
                        $observaciones .= "Alérgenos: {$alergenos}\n";
                        error_log("Agregando alérgenos a observaciones: {$alergenos}");
                    }
                    
                    // Añadir necesidades especiales a las observaciones si existen
                    if ($tiene_necesidades && !empty($necesidades_especiales)) {
                        $observaciones .= "Necesidades especiales: {$necesidades_especiales}";
                        error_log("Agregando necesidades especiales a observaciones: {$necesidades_especiales}");
                    }
                    
                    error_log("Observaciones finales: {$observaciones}");
                    
                    try {
                        // Registrar información detallada para depuración
                        error_log("Intentando insertar reserva con los siguientes datos:");
                        error_log("Cliente ID: $clienteId");
                        error_log("Fecha: $fecha_bd");
                        error_log("Zona: $zona");
                        error_log("Turno ID: $turno_id");
                        error_log("Hora: $hora");
                        error_log("Num Personas: $num_personas");
                        error_log("Observaciones: $observaciones");
                        error_log("Necesidades Especiales: $necesidades_especiales");
                        error_log("Tiene Alérgenos: " . ($tiene_alergenos ? 1 : 0));
                        error_log("Alérgenos: $alergenos");
                        error_log("Estado: $estado");
                        
                        // Verificar que los valores no sean nulos antes de la inserción
                        if (empty($clienteId)) {
                            throw new Exception("Error: ID de cliente no válido");
                        }
                        
                        if (empty($fecha_bd)) {
                            throw new Exception("Error: Fecha no válida");
                        }
                        
                        if (empty($zona)) {
                            throw new Exception("Error: Zona no válida");
                        }
                        
                        if (empty($turno_id)) {
                            throw new Exception("Error: Turno no válido");
                        }
                        
                        // Verificar si la columna fecha_creacion existe en la tabla reservas
                        $columna_existe = false;
                        try {
                            // Usar INFORMATION_SCHEMA para una verificación más robusta
                            $check = $pdo->prepare("
                                SELECT COLUMN_NAME 
                                FROM INFORMATION_SCHEMA.COLUMNS 
                                WHERE TABLE_SCHEMA = 'restaurante_reservas' 
                                AND TABLE_NAME = 'reservas' 
                                AND COLUMN_NAME = 'fecha_creacion'
                            ");
                            $check->execute();
                            $columna_existe = ($check->rowCount() > 0);
                            error_log("Verificación de columna fecha_creacion: " . ($columna_existe ? 'Existe' : 'No existe'));
                        } catch (PDOException $e) {
                            // Si hay un error, asumimos que la columna no existe
                            $columna_existe = false;
                            error_log("Error al verificar columna fecha_creacion: " . $e->getMessage());
                        }
                        
                        // Insertar la reserva con el nuevo cliente_id
                        $sql = "INSERT INTO reservas (
                            cliente_id, fecha, zona, turno_id, 
                            hora, cantidad_personas, observaciones, 
                            necesidades_especiales, tiene_alergenos, estado
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"; 
                        
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([
                            $clienteId, $fecha_bd, $zona, $turno_id, 
                            $hora, $num_personas, $observaciones, 
                            $necesidades_especiales, $tiene_alergenos ? 1 : 0, $estado
                        ]);
                        
                        error_log("Reserva insertada correctamente");
                    } catch (PDOException $e) {
                        error_log("Error PDO al insertar la reserva: " . $e->getMessage());
                        error_log("Código de error: " . $e->getCode());
                        throw $e; // Re-lanzar la excepción para que sea capturada por el bloque try/catch exterior
                    } catch (Exception $e) {
                        error_log("Error general al insertar la reserva: " . $e->getMessage());
                        throw $e; // Re-lanzar la excepción para que sea capturada por el bloque try/catch exterior
                    }
                    
                    // Obtener el ID de la reserva
                    $reservaId = $pdo->lastInsertId();
                    
                    // Guardar ID de reserva en la sesión
                    $_SESSION['reserva_id'] = $reservaId;
                    
                    // Formatear la zona para mostrar
                    $zona_texto = $zona === 'dentro' ? 'Interior' : 'Terraza';
                    
                    // Formatear el turno para mostrar
                    $turno_texto = $nombre_turno === 'mediodia' ? 'Mediodía' : 'Noche';
                    
                    // Obtener la hora seleccionada por el usuario si está disponible
                    $hora_seleccionada = isset($_SESSION['hora']) && !empty($_SESSION['hora']) ? $_SESSION['hora'] : $hora;
                    
                    // Formatear el horario del turno completo
                    $horario_turno = substr($hora_inicio, 0, 5) . ' - ' . substr($hora_fin, 0, 5);
                    
                    // Formatear la hora específica seleccionada
                    $horario_especifico = substr($hora_seleccionada, 0, 5);
                    
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
                            'reserva_id' => $reservaId
                        ];
                        
                        // Generar el código de confirmación
                        $codigo_confirmacion = substr(md5($reservaId . $fecha . time()), 0, 8);
                        
                        // Guardar el código de confirmación en la base de datos
                        $stmt = $pdo->prepare("UPDATE reservas SET codigo_confirmacion = ? WHERE id = ?");
                        $stmt->execute([$codigo_confirmacion, $reservaId]);
                        
                        // Generar el cuerpo del correo y el texto alternativo usando las nuevas funciones
                        if ($estado === 'confirmada') {
                            // Correo para reservas confirmadas automáticamente
                            $asunto = "Reserva Confirmada - Restaurante";
                            
                            // Generar el cuerpo del correo y el texto alternativo
                            list($cuerpo_html, $cuerpo_texto) = generarCorreoReservaConfirmada(
                                $nombre,
                                $fecha_bd,
                                $zona_texto,
                                $turno_texto,
                                $horario_turno,
                                $horario_especifico,
                                $num_personas,
                                $codigo_confirmacion,
                                $reservaId
                            );
                        } else {
                            // Correo para reservas pendientes de aprobación
                            $asunto = "Su reserva está en revisión - Restaurante";
                            
                            // Generar el cuerpo del correo y el texto alternativo
                            list($cuerpo_html, $cuerpo_texto) = generarCorreoReservaPendiente(
                                $nombre,
                                $fecha_bd,
                                $zona_texto,
                                $turno_texto,
                                $horario_turno,
                                $horario_especifico,
                                $num_personas,
                                $reservaId
                            );
                        }
                        
                        // Enviar el correo
                        $enviado = enviar_correo_directo($email, $nombre, $asunto, $cuerpo_html, $cuerpo_texto);
                        
                        if ($enviado) {
                            error_log("Correo enviado correctamente a: $email");
                        } else {
                            error_log("Error al enviar correo a: $email");
                        }
                    } catch (Exception $e) {
                        // Silenciar errores de envío de correo para no interrumpir el proceso
                        error_log("Error al enviar correo: " . $e->getMessage());
                    }
                    
                    // Redirigir a la página de confirmación final
                    header("Location: reserva_confirmada.php");
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
    } else {
        // Redirigir a la página de reserva con un mensaje de error
        $_SESSION['error_reserva'] = "Faltan datos necesarios para procesar la reserva.";
        header("Location: reserva.php");
        exit;
    }
}

// Formatear la zona para mostrar
$zona_texto = $zona === 'dentro' ? 'Interior' : 'Terraza';

// Formatear la fecha para mostrar
$fecha_obj = new DateTime($fecha);
$fecha_formateada = $fecha_obj->format('d/m/Y');

// Título de la página
$pageTitle = 'Confirmar Reserva';
?>
