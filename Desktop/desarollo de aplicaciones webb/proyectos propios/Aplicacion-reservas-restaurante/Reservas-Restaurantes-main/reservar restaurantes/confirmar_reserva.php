<?php
// Archivo confirmar_reserva.php - Página de confirmación intermedia
session_start();

// Incluir autoload y PHPMailer
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/src/Utils/EmailSender.php';

// Declaraciones use
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
use App\Utils\EmailSender;

// Incluir el archivo de avisos
require_once 'avisos.php';

// Verificar si hay datos de reserva en la sesión
if (!isset($_SESSION["fecha"]) || !isset($_SESSION["zona"]) || !isset($_SESSION["turno_id"])) {
    // Si no hay datos, redirigir al inicio
    header("Location: index.php");
    exit;
}

// Procesar la confirmación final
if (isset($_POST["confirmar"]) && $_POST["confirmar"] === "true" && isset($_POST["confirmar_datos"]) && $_POST["confirmar_datos"] === "on") {
    // Recoger todos los datos del formulario
    $fecha = $_POST["fecha"];
    $zona = $_POST["zona"];
    $turno_id = $_POST["turno_id"];
    $nombre = $_SESSION["nombre"];
    $email = $_SESSION["email"];
    $telefono = $_SESSION["telefono"];
    $num_personas = $_SESSION["num_personas"];
    $tiene_alergenos = isset($_POST["tiene_alergenos"]) ? $_POST["tiene_alergenos"] : 0;
    $alergenos = isset($_POST["alergenos"]) ? $_POST["alergenos"] : "";
    $tiene_necesidades = isset($_POST["tiene_necesidades"]) ? $_POST["tiene_necesidades"] : 0;
    $necesidades = isset($_POST["necesidades"]) ? $_POST["necesidades"] : "";
    
    // Bloque try principal para todo el proceso de reserva
    try {
        // Conectar a la base de datos
        $pdo = new PDO("mysql:host=localhost;dbname=restaurante_reservas", "root", "", [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        
        // VERIFICACIÓN ESTRICTA DE DISPONIBILIDAD
        error_log("INICIO DE VERIFICACIÓN ESTRICTA DE DISPONIBILIDAD EN CONFIRMAR_RESERVA.PHP");
        $hay_disponibilidad = true;
        
        // 1. Verificar si el día está disponible para reservas
        $stmt = $pdo->prepare("SELECT * FROM dias_disponibles WHERE fecha = ? AND turno_id = ? AND zona = ?");
        $stmt->execute([$fecha, $turno_id, $zona]);
        $disponible = $stmt->fetch();
        
        // Registrar para depuración
        error_log("1. Verificando disponibilidad para fecha: $fecha, turno: $turno_id, zona: $zona - Resultado: " . ($disponible ? 'Disponible' : 'No disponible'));
        
        if (!$disponible || (isset($disponible['disponible']) && $disponible['disponible'] != 1)) {
            $_SESSION["error_reserva"] = "Lo sentimos, no hay disponibilidad para la fecha, zona y turno seleccionados.";
            header("Location: reserva.php");
            exit;
        }
        
        // 2. Verificar si hay bloqueos para esta fecha/zona/turno
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM bloqueos WHERE fecha = ? AND zona = ? AND turno_id = ?");
        $stmt->execute([$fecha, $zona, $turno_id]);
        $bloqueado = ($stmt->fetchColumn() > 0);
        
        error_log("2. Verificando bloqueos para fecha: $fecha, turno: $turno_id, zona: $zona - Resultado: " . ($bloqueado ? 'Bloqueado' : 'No bloqueado'));
        
        if ($bloqueado) {
            $_SESSION["error_reserva"] = "Lo sentimos, la fecha seleccionada está bloqueada para reservas.";
            header("Location: reserva.php");
            exit;
        }
        
        // 3. Verificar aforo disponible - Obtener capacidad máxima
        $stmt = $pdo->prepare("SELECT * FROM capacidad WHERE fecha = ? AND turno_id = ? AND zona = ?");
        $stmt->execute([$fecha, $turno_id, $zona]);
        $capacidad = $stmt->fetch();
        
        if (!$capacidad) {
            // Si no hay configuración específica para esta fecha, intentar obtener la capacidad por campo específico
            $turno_nombre = ($turno_id == 1) ? 'mediodia' : 'noche';
            $campo_capacidad = 'capacidad_' . $zona . '_' . $turno_nombre;
            
            $stmt = $pdo->prepare("SELECT $campo_capacidad FROM configuracion WHERE id = 1");
            $stmt->execute();
            $aforo_especifico = $stmt->fetchColumn();
            
            if ($aforo_especifico !== false) {
                // Usar el aforo específico para esta zona y turno
                $capacidad = [
                    'aforo_maximo' => $aforo_especifico
                ];
                error_log("3. Usando aforo específico para fecha: $fecha, turno: $turno_id, zona: $zona, campo: $campo_capacidad, valor: $aforo_especifico");
            } else {
                // Intentar con aforo_default
                $stmt = $pdo->prepare("SELECT * FROM configuracion WHERE id = 1");
                $stmt->execute();
                $config = $stmt->fetch();
                
                if ($config && isset($config['aforo_default'])) {
                    // Usar el aforo por defecto de la configuración
                    $capacidad = [
                        'aforo_maximo' => $config['aforo_default']
                    ];
                    error_log("3. Usando aforo_default para fecha: $fecha, turno: $turno_id, zona: $zona, valor: {$config['aforo_default']}");
                } else {
                    // Si no hay configuración por defecto, establecer un valor razonable
                    $capacidad = [
                        'aforo_maximo' => 40 // Valor por defecto si no hay configuración
                    ];
                    error_log("3. Usando valor fijo por defecto para fecha: $fecha, turno: $turno_id, zona: $zona, valor: 40");
                }
            }
        } else {
            error_log("3. Usando capacidad específica de la tabla capacidad para fecha: $fecha, turno: $turno_id, zona: $zona, valor: {$capacidad['aforo_maximo']}");
        }
        
        // Verificar la estructura de la tabla reservas primero
        $stmt = $pdo->prepare("SHOW COLUMNS FROM reservas");
        $stmt->execute();
        $columnas_info = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Extraer solo los nombres de las columnas
        $columnas = array_map(function($col) {
            return $col['Field'];
        }, $columnas_info);
        
        // Registrar las columnas disponibles para depuración
        error_log("Columnas en la tabla reservas: " . implode(", ", $columnas));
        
        // Variable para almacenar el nombre de la columna de personas
        $columna_personas = null;
        
        // Buscar la columna para el número de personas
        if (in_array('num_personas', $columnas)) {
            $columna_personas = 'num_personas';
        } else if (in_array('personas', $columnas)) {
            $columna_personas = 'personas';
        } else if (in_array('n_personas', $columnas)) {
            $columna_personas = 'n_personas';
        } else if (in_array('numero_personas', $columnas)) {
            $columna_personas = 'numero_personas';
        } else if (in_array('cantidad_personas', $columnas)) {
            $columna_personas = 'cantidad_personas';
        }
        
        // Si no se encuentra la columna, lanzar un error
        if ($columna_personas === null) {
            throw new \Exception("La tabla reservas no tiene una columna para almacenar el número de personas. Columnas disponibles: " . implode(", ", $columnas));
        }
        
        // 4. Contar personas ya reservadas usando el nombre de columna correcto
        // IMPORTANTE: Solo contamos reservas CONFIRMADAS para el cálculo de aforo
        $sql_contar = "SELECT SUM($columna_personas) as total FROM reservas WHERE fecha = ? AND turno_id = ? AND zona = ? AND estado = 'confirmada'";
        $stmt = $pdo->prepare($sql_contar);
        $stmt->execute([$fecha, $turno_id, $zona]);
        $resultado = $stmt->fetch();
        $personas_reservadas = $resultado["total"] ? (int)$resultado["total"] : 0;
        
        // Calcular aforo disponible
        $aforo_disponible = $capacidad["aforo_maximo"] - $personas_reservadas;
        
        // Registrar información detallada para depuración
        error_log("4. VERIFICACIÓN FINAL DE AFORO EN CONFIRMAR_RESERVA.PHP:");
        error_log("- Fecha: $fecha, Zona: $zona, Turno: $turno_id");
        error_log("- Aforo máximo configurado: " . $capacidad["aforo_maximo"]);
        error_log("- Personas ya reservadas (confirmadas): $personas_reservadas");
        error_log("- Aforo disponible actual: $aforo_disponible");
        error_log("- Personas solicitadas en esta reserva: $num_personas");
        error_log("- ¿Hay suficiente aforo?: " . ($aforo_disponible >= $num_personas ? 'SÍ' : 'NO'));
        
        // VERIFICACIÓN ESTRICTA: Comprobar si hay suficiente aforo disponible
        if ($aforo_disponible < $num_personas) {
            error_log("RESERVA BLOQUEADA: Aforo insuficiente para fecha: $fecha, zona: $zona, turno: $turno_id");
            $_SESSION["error_reserva"] = "Lo sentimos, no hay suficiente aforo disponible para su reserva.";
            header("Location: reserva.php");
            exit;
        }
        
        // VERIFICACIÓN ADICIONAL: Si es el 15 de mayo (fecha mencionada por el cliente)
        if (substr($fecha, 0, 10) === '2025-05-15') {
            error_log("VERIFICACIÓN ESPECIAL PARA EL 15 DE MAYO: Fecha: $fecha, Zona: $zona, Turno: $turno_id");
            // Verificar nuevamente el aforo para esta fecha específica
            $stmt = $pdo->prepare("SELECT aforo_maximo FROM capacidad WHERE fecha = ? AND zona = ? AND turno_id = ?");
            $stmt->execute(['2025-05-15', $zona, $turno_id]);
            $aforo_15mayo = $stmt->fetchColumn();
            
            if ($aforo_15mayo !== false) {
                // Si hay una configuración específica para el 15 de mayo
                $stmt = $pdo->prepare("SELECT SUM($columna_personas) as total FROM reservas WHERE fecha = ? AND turno_id = ? AND zona = ? AND estado = 'confirmada'");
                $stmt->execute(['2025-05-15', $turno_id, $zona]);
                $resultado_15mayo = $stmt->fetch();
                $personas_reservadas_15mayo = $resultado_15mayo["total"] ? (int)$resultado_15mayo["total"] : 0;
                
                $aforo_disponible_15mayo = $aforo_15mayo - $personas_reservadas_15mayo;
                
                error_log("VERIFICACIÓN 15 MAYO: Aforo máximo: $aforo_15mayo, Reservadas: $personas_reservadas_15mayo, Disponible: $aforo_disponible_15mayo, Solicitadas: $num_personas");
                
                if ($aforo_disponible_15mayo < $num_personas) {
                    error_log("RESERVA BLOQUEADA PARA EL 15 DE MAYO: Aforo insuficiente");
                    $_SESSION["error_reserva"] = "Lo sentimos, no hay suficiente aforo disponible para el 15 de mayo.";
                    header("Location: reserva.php");
                    exit;
                }
            }
        }
        
        // Definir variables para evitar warnings
        $tiene_alergenos = isset($_POST['tiene_alergenos']) ? $_POST['tiene_alergenos'] : false;
        $alergenos = isset($_POST['alergenos']) ? $_POST['alergenos'] : "";
        $necesidades = isset($_POST['necesidades_especiales']) ? $_POST['necesidades_especiales'] : "";
        
        // Determinar estado de la reserva
        $config_stmt = $pdo->prepare("SELECT * FROM configuracion WHERE id = 1");
        $config_stmt->execute();
        $config = $config_stmt->fetch();
        $max_personas_sin_aprobacion = $config["max_personas_sin_aprobacion"];
        
        $estado = ($num_personas > $max_personas_sin_aprobacion) ? "pendiente" : "confirmada";
        $observaciones = $tiene_alergenos ? $alergenos : "";
        
        // Primero, verificar si el cliente ya existe o crear uno nuevo
        $cliente_id = null;
        
        // Buscar el cliente por email
        $stmt_cliente = $pdo->prepare("SELECT id FROM clientes WHERE email = ? LIMIT 1");
        $stmt_cliente->execute([$email]);
        $cliente_existente = $stmt_cliente->fetch(PDO::FETCH_ASSOC);
        
        if ($cliente_existente) {
            // Si el cliente ya existe, usar su ID
            $cliente_id = $cliente_existente['id'];
            error_log("Cliente existente encontrado con ID: $cliente_id");
            
            // Actualizar los datos del cliente si es necesario
            $stmt_update = $pdo->prepare("UPDATE clientes SET nombre = ?, telefono = ? WHERE id = ?");
            $stmt_update->execute([$nombre, $telefono, $cliente_id]);
        } else {
            // Si el cliente no existe, crear uno nuevo
            $stmt_insert = $pdo->prepare("INSERT INTO clientes (nombre, email, telefono) VALUES (?, ?, ?)");
            $stmt_insert->execute([$nombre, $email, $telefono]);
            $cliente_id = $pdo->lastInsertId();
            error_log("Nuevo cliente creado con ID: $cliente_id");
        }
        
        // Verificar que se obtuvo un ID de cliente válido
        if (!$cliente_id) {
            throw new \Exception("No se pudo obtener un ID de cliente válido. Verifique la tabla clientes.");
        }
        
        // Obtener la hora seleccionada de la sesión
        $hora_seleccionada = isset($_SESSION["hora"]) ? $_SESSION["hora"] : null;
        
        // Si no hay hora seleccionada, usar la hora de inicio del turno
        if (!$hora_seleccionada) {
            $stmt_turno = $pdo->prepare("SELECT hora_inicio FROM turnos WHERE id = ?");
            $stmt_turno->execute([$turno_id]);
            $turno_info = $stmt_turno->fetch();
            $hora_seleccionada = $turno_info ? $turno_info['hora_inicio'] : date('H:i:s');
        }
        
        // Registrar para depuración
        error_log("Hora seleccionada por el usuario: " . $hora_seleccionada);
        
        // Verificar si hay necesidades especiales
        $tiene_necesidades_especiales = isset($_SESSION["tiene_necesidades"]) && $_SESSION["tiene_necesidades"];
        $necesidades_especiales_texto = $tiene_necesidades_especiales && isset($_SESSION["necesidades_especiales"]) ? $_SESSION["necesidades_especiales"] : "";
        
        // Registrar para depuración
        error_log("Necesidades especiales: " . ($tiene_necesidades_especiales ? "Sí" : "No"));
        error_log("Texto de necesidades especiales: " . $necesidades_especiales_texto);
        
        // Mapear los campos de la tabla reservas
        $campos_mapeados = [
            'fecha' => 'fecha',
            'turno_id' => 'turno_id',
            'zona' => 'zona',
            'cliente_id' => 'cliente_id',  // Usar cliente_id en lugar de campos individuales del cliente
            'personas' => [$columna_personas],
            'personas_solicitadas' => 'personas_solicitadas', // Campo para guardar el número real de personas en reservas pendientes
            'observaciones' => ['observaciones', 'obs', 'comentarios'],
            'alergenos' => ['alergenos', 'tiene_alergenos', 'alergias'],
            'necesidades_especiales' => ['necesidades_especiales', 'necesidades'], // Cambiado el nombre de la clave
            'estado' => ['estado', 'status'],
            'hora' => ['hora', 'hora_reserva', 'hora_llegada']
        ];
        
        // Construir dinámicamente los campos y valores para la consulta SQL
        $campos_sql = [];
        $valores_sql = [];
        $valores_params = [];
        
        // Recorrer el mapeo de campos
        foreach ($campos_mapeados as $campo_base => $alternativas) {
            if (!is_array($alternativas)) {
                $alternativas = [$alternativas];
            }
            
            // Buscar la primera alternativa que exista en la tabla
            $campo_encontrado = null;
            foreach ($alternativas as $alt) {
                if (in_array($alt, $columnas)) {
                    $campo_encontrado = $alt;
                    break;
                }
            }
            
            // Si se encontró el campo, agregarlo a la consulta
            if ($campo_encontrado) {
                $campos_sql[] = $campo_encontrado;
                $valores_sql[] = '?';
                
                // Determinar el valor a insertar según el campo
                switch ($campo_base) {
                    case 'fecha':
                        $valores_params[] = $fecha;
                        break;
                    case 'turno_id':
                        $valores_params[] = $turno_id;
                        break;
                    case 'zona':
                        $valores_params[] = $zona;
                        break;
                    case 'cliente_id':
                        $valores_params[] = $cliente_id;
                        break;
                    case 'personas':
                        // Si la reserva está pendiente (excede el máximo sin aprobación), 
                        // guardamos 0 personas temporalmente hasta que sea confirmada
                        if ($estado === 'pendiente') {
                            $valores_params[] = 0; // Valor temporal hasta confirmación
                        } else {
                            $valores_params[] = $num_personas;
                        }
                        break;
                    case 'personas_solicitadas':
                        // Guardar siempre el número real de personas solicitadas
                        // Este campo se usará para actualizar la cantidad de personas cuando se confirme una reserva pendiente
                        $valores_params[] = $num_personas;
                        break;
                    case 'observaciones':
                        $valores_params[] = $observaciones;
                        break;
                    case 'alergenos':
                        $valores_params[] = $alergenos;
                        break;
                    case 'necesidades_especiales':
                        $valores_params[] = $necesidades_especiales_texto;
                        break;
                    case 'estado':
                        $valores_params[] = $estado;
                        break;
                    case 'hora':
                        // Asegurarse de que se guarde la hora exacta seleccionada por el usuario
                        $valores_params[] = $hora_seleccionada;
                        // Registrar la hora que se está guardando en la base de datos
                        error_log("Guardando hora en la base de datos: " . $hora_seleccionada);
                        break;
                }
            } else if ($campo_base === 'personas') {
                // Si no se encuentra el campo para personas, es un error crítico
                throw new \Exception("No se encontró ninguna columna para almacenar el número de personas. Columnas disponibles: " . implode(", ", $columnas));
            }
        }
        
        // Añadir fecha_creacion si existe
        if (in_array('fecha_creacion', $columnas)) {
            $campos_sql[] = 'fecha_creacion';
            $valores_sql[] = 'NOW()';
        }
        
        // Construir la consulta SQL final
        $sql = "INSERT INTO reservas (" . implode(", ", $campos_sql) . ") VALUES (" . implode(", ", $valores_sql) . ")";
        
        // Ya hemos definido estas variables al inicio del script
        
        // Actualizar los valores de estado y observaciones en los parámetros si es necesario
        // Buscar los índices de los campos estado y observaciones
        $indice_estado = array_search('estado', $campos_sql);
        $indice_observaciones = array_search('observaciones', $campos_sql);
        
        if ($indice_estado !== false && isset($valores_params[$indice_estado])) {
            $valores_params[$indice_estado] = $estado;
        }
        
        if ($indice_observaciones !== false && isset($valores_params[$indice_observaciones])) {
            $valores_params[$indice_observaciones] = $observaciones;
        }
        
        // Registrar la consulta SQL para depuración
        error_log("Consulta SQL: $sql");
        error_log("Parámetros: " . print_r($valores_params, true));
        
        // Guardar la reserva
        $stmt = $pdo->prepare($sql);
        $stmt->execute($valores_params);
        
        // Obtener el ID de la reserva recién creada
        $reserva_id = $pdo->lastInsertId();
        
        // Guardar los valores de los checkboxes personalizados y sus respuestas
        if (isset($_SESSION['checkboxes_personalizados']) && !empty($_SESSION['checkboxes_personalizados'])) {
            foreach ($_SESSION['checkboxes_personalizados'] as $checkbox_id => $valor) {
                try {
                    // Obtener el texto de respuesta si existe
                    $texto_respuesta = null;
                    if (isset($_SESSION['respuestas_checkboxes'][$checkbox_id]) && !empty(trim($_SESSION['respuestas_checkboxes'][$checkbox_id]))) {
                        $texto_respuesta = trim($_SESSION['respuestas_checkboxes'][$checkbox_id]);
                    }
                    
                    $stmt_checkbox = $pdo->prepare("INSERT INTO reservas_checkboxes (reserva_id, checkbox_id, valor, texto_respuesta) VALUES (?, ?, ?, ?)");
                    $stmt_checkbox->execute([
                        $reserva_id, 
                        $checkbox_id, 
                        $valor ? 1 : 0,
                        $texto_respuesta
                    ]);
                } catch (\Exception $e) {
                    // Registrar el error pero continuar con el proceso
                    error_log("Error al guardar checkbox personalizado #$checkbox_id para la reserva #$reserva_id: " . $e->getMessage());
                }
            }
        }
        
        // Obtener información del turno para el correo
        $stmt = $pdo->prepare("SELECT * FROM turnos WHERE id = ?");
        $stmt->execute([$turno_id]);
        $turno = $stmt->fetch();
        
        // Formatear la fecha
        $fecha_obj = DateTime::createFromFormat("Y-m-d", $fecha);
        $fecha_formateada = $fecha_obj ? $fecha_obj->format("d/m/Y") : $fecha;
        
        // Obtener la hora seleccionada de la sesión
        $hora_seleccionada = isset($_SESSION["hora"]) ? $_SESSION["hora"] : $turno["hora_inicio"];
        
        // Formatear el horario y asegurarse de que se use la hora exacta seleccionada por el usuario
        $horario_especifico = substr($hora_seleccionada, 0, 5);
        $horario_turno = substr($turno["hora_inicio"], 0, 5) . " - " . substr($turno["hora_fin"], 0, 5);
        
        // Registrar la hora seleccionada para depuración
        error_log("Hora seleccionada por el usuario (confirmar_reserva.php): " . $hora_seleccionada);
        
        // Intentar enviar los correos correspondientes
        try {
            // Formatear la zona para mostrarla de forma amigable
            $zona_formateada = ($zona === "dentro") ? "Interior" : "Terraza";
            
            // Obtener los checkboxes seleccionados para incluirlos en los correos
            $checkboxes_seleccionados = '';
            if ($reserva_id) {
                $stmt_checkboxes = $pdo->prepare(
                    "SELECT cp.texto, rc.texto_respuesta 
                     FROM reservas_checkboxes rc 
                     JOIN checkboxes_personalizados cp ON rc.checkbox_id = cp.id 
                     WHERE rc.reserva_id = ? AND rc.valor = 1"
                );
                $stmt_checkboxes->execute([$reserva_id]);
                $checkboxes = $stmt_checkboxes->fetchAll();
                
                if (!empty($checkboxes)) {
                    $checkboxes_seleccionados = [];
                    foreach ($checkboxes as $cb) {
                        $texto = strip_tags($cb['texto']);
                        
                        // Agregar el texto de respuesta si existe
                        if (!empty($cb['texto_respuesta'])) {
                            $texto .= ": " . strip_tags($cb['texto_respuesta']);
                        }
                        
                        $checkboxes_seleccionados[] = $texto;
                    }
                    $checkboxes_seleccionados = implode(", ", $checkboxes_seleccionados);
                }
            }
            
            // Preparar los datos de la reserva para los correos
            $datos_reserva = [
                'fecha' => $fecha_formateada,
                'hora' => $horario_especifico,
                'turno' => $turno['nombre'],
                'zona' => $zona_formateada,
                'num_personas' => $num_personas,
                'observaciones' => $observaciones,
                'necesidades_especiales' => $necesidades,
                'cliente_nombre' => $nombre,
                'cliente_email' => $email,
                'checkboxes_seleccionados' => $checkboxes_seleccionados
            ];
            
            // Registrar intento de envío de correo
            error_log("Intentando enviar correo para la reserva #$reserva_id con estado: $estado");
            
            // Obtener la configuración de correo electrónico
            $stmt = $pdo->query("SELECT email_activo, notificaciones_admin FROM configuracion WHERE id = 1");
            $config = $stmt->fetch();
            $email_activo = $config['email_activo'] ?? 0;
            $notificaciones_admin = $config['notificaciones_admin'] ?? 'pendientes';
            
            error_log("Configuración de correo: email_activo=$email_activo, notificaciones_admin=$notificaciones_admin");
            
            // Verificar si el envío de correos está activado
            if (!$email_activo) {
                error_log("El envío de correos está desactivado en la configuración (email_activo = 0). No se enviarán correos.");
            } else {
                // Enviar correo al cliente según el estado de la reserva
                if ($estado === "confirmada") {
                    // Enviar correo de confirmación al cliente
                    if (EmailSender::enviarConfirmacionReserva($email, $nombre, $datos_reserva)) {
                        error_log("Correo de confirmación enviado a $email para la reserva #$reserva_id");
                    } else {
                        error_log("Error al enviar correo de confirmación para la reserva #$reserva_id");
                    }
                    
                    // Enviar notificación a los administradores según la configuración
                    if (EmailSender::enviarNotificacionAdmin($datos_reserva, "confirmada")) {
                        error_log("Notificación a administradores para reserva confirmada enviada o no requerida según configuración");
                    } else {
                        error_log("Error al enviar notificación a administradores para reserva confirmada");
                    }
                } else {
                    // Es una reserva pendiente - Enviar correo al cliente
                    if (EmailSender::enviarNotificacionReservaPendiente($email, $nombre, $datos_reserva)) {
                        error_log("Correo de notificación de reserva pendiente enviado a $email para la reserva #$reserva_id");
                    } else {
                        error_log("Error al enviar correo de notificación de reserva pendiente para la reserva #$reserva_id");
                    }
                    
                    // Enviar notificación a los administradores según la configuración
                    if (EmailSender::enviarNotificacionAdmin($datos_reserva, "pendiente")) {
                        error_log("Notificación a administradores para reserva pendiente enviada o no requerida según configuración");
                    } else {
                        error_log("Error al enviar notificación a administradores para reserva pendiente");
                    }
                }
            }
        } catch (\Exception $e) {
            // Registrar el error pero continuar con el proceso
            error_log("ERROR al enviar correos para la reserva #$reserva_id: " . $e->getMessage());
        }
        
        // Limpiar variables de sesión relacionadas con la reserva
        unset($_SESSION["fecha"]);
        unset($_SESSION["zona"]);
        unset($_SESSION["turno_id"]);
        unset($_SESSION["nombre"]);
        unset($_SESSION["email"]);
        unset($_SESSION["telefono"]);
        unset($_SESSION["num_personas"]);
        unset($_SESSION["tiene_alergenos"]);
        unset($_SESSION["alergenos"]);
        unset($_SESSION["tiene_necesidades"]);
        unset($_SESSION["necesidades"]);
        unset($_SESSION["hora_seleccionada"]);
        
        // Obtener la URL de redirección configurada en la tabla de configuración
        $stmt_redireccion = $pdo->prepare("SELECT url_redireccion_reserva FROM configuracion WHERE id = 1");
        $stmt_redireccion->execute();
        $config_redireccion = $stmt_redireccion->fetch();
        
        // Guardar la URL de inicio configurada en la sesión (por defecto index.php si no hay configuración)
        $url_inicio = isset($config_redireccion['url_redireccion_reserva']) && !empty($config_redireccion['url_redireccion_reserva']) 
            ? $config_redireccion['url_redireccion_reserva'] 
            : 'index.php';
        
        // Guardar la URL de inicio en la sesión para usarla en el botón "Volver al inicio"
        $_SESSION["url_inicio"] = $url_inicio;
        
        // Registrar la URL para depuración
        error_log("URL de inicio configurada: " . $url_inicio);
        error_log("Redirigiendo a reserva_exitosa.php");
        
        // Redirigir siempre a la página de reserva exitosa
        $_SESSION["reserva_exitosa"] = true;
        // Guardar el estado de la reserva en la sesión para mostrar el mensaje adecuado
        $_SESSION["estado_reserva"] = $estado;
        header("Location: reserva_exitosa.php");
        exit;
        
    } catch (\Exception $e) {
        $_SESSION["error_reserva"] = "Error al procesar la reserva: " . $e->getMessage();
        header("Location: reserva.php");
        exit;
    }
}

// Obtener datos de la sesión
$fecha = $_SESSION["fecha"];
$zona = $_SESSION["zona"];
$turno_id = $_SESSION["turno_id"];
$nombre = $_SESSION["nombre"];
$email = $_SESSION["email"];
$telefono = $_SESSION["telefono"];
$num_personas = $_SESSION["num_personas"];
$tiene_alergenos = isset($_SESSION["tiene_alergenos"]) ? $_SESSION["tiene_alergenos"] : false;
$alergenos = isset($_SESSION["alergenos"]) ? $_SESSION["alergenos"] : "";
$tiene_necesidades = isset($_SESSION["tiene_necesidades"]) ? $_SESSION["tiene_necesidades"] : false;
$necesidades = isset($_SESSION["necesidades"]) ? $_SESSION["necesidades"] : "";

// Obtener el valor de max_personas_sin_aprobacion de la configuración
try {
    $pdo_config = new PDO("mysql:host=localhost;dbname=restaurante_reservas", "root", "", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    $stmt_config = $pdo_config->prepare("SELECT max_personas_sin_aprobacion FROM configuracion WHERE id = 1");
    $stmt_config->execute();
    $config_result = $stmt_config->fetch(PDO::FETCH_ASSOC);
    
    if ($config_result) {
        $_SESSION['max_personas_sin_aprobacion'] = $config_result['max_personas_sin_aprobacion'];
    } else {
        // Valor por defecto si no se encuentra en la base de datos
        $_SESSION['max_personas_sin_aprobacion'] = 10;
    }
} catch (\Exception $e) {
    // En caso de error, establecer un valor por defecto
    $_SESSION['max_personas_sin_aprobacion'] = 10;
    error_log("Error al obtener max_personas_sin_aprobacion: " . $e->getMessage());
}

// Formatear la zona para mostrar
$zona_texto = $zona === "dentro" ? "Interior" : "Terraza";

// Obtener información del turno
try {
    $pdo = new PDO("mysql:host=localhost;dbname=restaurante_reservas", "root", "", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    $stmt = $pdo->prepare("SELECT * FROM turnos WHERE id = ?");
    $stmt->execute([$turno_id]);
    $turno = $stmt->fetch();
    
    $turno_nombre = $turno["nombre"];
    $hora_inicio = $turno["hora_inicio"];
    $hora_fin = $turno["hora_fin"];
    
    // Formatear la fecha
    $fecha_obj = DateTime::createFromFormat("Y-m-d", $fecha);
    $fecha_formateada = $fecha_obj ? $fecha_obj->format("d/m/Y") : $fecha;
    
    // Obtener la hora seleccionada de la sesión
    $hora_seleccionada = isset($_SESSION["hora_seleccionada"]) ? $_SESSION["hora_seleccionada"] : $hora_inicio;
    
    // Formatear el horario
    $horario_especifico = substr($hora_seleccionada, 0, 5);
    $horario_turno = substr($hora_inicio, 0, 5) . " - " . substr($hora_fin, 0, 5);
    $horario = $horario_especifico . " (" . $horario_turno . ")";
    
} catch (\Exception $e) {
    $turno_nombre = "No disponible";
    $horario = "No disponible";
    $fecha_formateada = $fecha;
}
?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmar Reserva</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <?php include_once 'includes/header.php'; ?>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <div class="flex flex-col items-center text-center mb-4">
            <div class="mb-4" style="max-width: 250px; margin: 0 auto;">
                <?php echo obtener_logo('w-full h-auto'); ?>
            </div>
        </div>
        
        <!-- Encabezado -->
        <header class="text-white rounded-lg shadow-lg p-6 mb-8 text-center" style="background-color: var(--color-primary);">
            <p>Confirmar Reserva - Por favor, revise los detalles de su reserva antes de confirmar</p>
        </header>
        
        <main class="max-w-4xl mx-auto">
            <?php
            // Mostrar mensajes de sesión (errores, éxitos, etc.)
            mostrarMensajesSesion();
            
            // Mostrar mensajes de aviso para el administrador si es necesario
            if (isset($_SESSION['max_personas_sin_aprobacion']) && $num_personas > $_SESSION['max_personas_sin_aprobacion']) {
                mostrarAvisoAdmin(
                    "Reserva con muchas personas", 
                    "Esta reserva requiere aprobación manual porque excede el número máximo de personas sin aprobación (" . $_SESSION['max_personas_sin_aprobacion'] . ")."
                );
            }
            
            // Mostrar mensaje si hay necesidades especiales o alérgenos
            if ($tiene_alergenos && $tiene_necesidades) {
                mostrarAvisoAdmin(
                    "Alérgenos y necesidades especiales", 
                    "Esta reserva incluye información sobre alérgenos que debe ser revisada y necesidades especiales que deben ser atendidas."
                );
            } else if ($tiene_alergenos) {
                mostrarAvisoAdmin(
                    "Alérgenos", 
                    "Esta reserva incluye información sobre alérgenos que debe ser revisada."
                );
            } else if ($tiene_necesidades) {
                mostrarAvisoAdmin(
                    "Necesidades especiales", 
                    "Esta reserva incluye necesidades especiales que deben ser atendidas."
                );
            }
            ?>
            <div class="bg-white rounded-lg shadow-md p-8 mb-8">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">Resumen de su reserva</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Datos del cliente -->
                    <div>
                        <h3 class="text-lg font-medium text-blue-700 mb-4 border-b border-gray-200 pb-2">Datos del cliente</h3>
                        <div class="space-y-3">
                            <p><strong>Nombre:</strong> <?php echo htmlspecialchars($nombre); ?></p>
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></p>
                            <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($telefono); ?></p>
                            <p><strong>Número de personas:</strong> <?php echo htmlspecialchars($num_personas); ?></p>
                        </div>
                    </div>
                    
                    <!-- Detalles de la reserva -->
                    <div>
                        <h3 class="text-lg font-medium text-blue-700 mb-4 border-b border-gray-200 pb-2">Detalles de la reserva</h3>
                        <div class="space-y-3">
                            <?php
                            // Formatear la fecha
                            $fecha_obj = DateTime::createFromFormat("Y-m-d", $fecha);
                            $fecha_formateada = $fecha_obj ? $fecha_obj->format("d/m/Y") : $fecha;
                            
                            // Determinar el nombre del turno
                            try {
                                $pdo_reserva = new PDO("mysql:host=localhost;dbname=restaurante_reservas", "root", "", [
                                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                                ]);
                                $stmt_turno = $pdo_reserva->prepare("SELECT nombre FROM turnos WHERE id = ?");
                                $stmt_turno->execute([$turno_id]);
                                $turno_info = $stmt_turno->fetch();
                                $turno_nombre = $turno_info ? ucfirst($turno_info['nombre']) : ($turno_id == 1 ? 'Mediodía' : 'Noche');
                            } catch (\Exception $e) {
                                $turno_nombre = $turno_id == 1 ? 'Mediodía' : 'Noche';
                            }
                            
                            // Formatear la zona
                            $zona_texto = $zona === 'dentro' ? 'Interior' : 'Terraza';
                            
                            // Obtener la hora seleccionada
                            $hora_mostrar = isset($_SESSION['hora']) ? $_SESSION['hora'] : '00:00';
                            ?>
                            <p><strong>Fecha:</strong> <?php echo htmlspecialchars($fecha_formateada); ?></p>
                            <p><strong>Turno:</strong> <?php echo htmlspecialchars($turno_nombre); ?></p>
                            <p><strong>Hora de llegada:</strong> <?php echo htmlspecialchars($hora_mostrar); ?> h</p>
                            <p><strong>Zona:</strong> <?php echo htmlspecialchars($zona_texto); ?></p>
                        </div>
                        
                        <?php
                        // Mostrar checkboxes personalizados seleccionados
                        if (isset($_SESSION['checkboxes_personalizados']) && !empty($_SESSION['checkboxes_personalizados'])) {
                            try {
                                $pdo_checkboxes = new PDO("mysql:host=localhost;dbname=restaurante_reservas", "root", "", [
                                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                                ]);
                                
                                $ids_checkboxes = array_keys($_SESSION['checkboxes_personalizados']);
                                $placeholders = implode(',', array_fill(0, count($ids_checkboxes), '?'));
                                
                                $stmt_checkboxes = $pdo_checkboxes->prepare("SELECT id, texto, descripcion, activo, es_obligatorio, tiene_textarea, placeholder_textarea, orden FROM checkboxes_personalizados WHERE id IN ($placeholders) AND activo = 1");
                                $stmt_checkboxes->execute($ids_checkboxes);
                                $checkboxes_seleccionados = $stmt_checkboxes->fetchAll(PDO::FETCH_ASSOC);
                                
                                if (!empty($checkboxes_seleccionados)) {
                                    echo '<div class="mt-4">';
                                    echo '<h4 class="font-semibold mb-2">Opciones seleccionadas:</h4>';
                                    echo '<ul class="space-y-3">';
                                    foreach ($checkboxes_seleccionados as $checkbox) {
                                        $checkbox_id = $checkbox['id'];
                                        $texto_respuesta = isset($_SESSION['respuestas_checkboxes'][$checkbox_id]) ? $_SESSION['respuestas_checkboxes'][$checkbox_id] : '';
                                        
                                        echo '<li class="bg-gray-50 p-3 rounded-lg border border-gray-200">';
                                        echo '<div class="font-medium">' . htmlspecialchars($checkbox['texto']) . '</div>';
                                        
                                        // Mostrar la respuesta si existe
                                        if (!empty($texto_respuesta)) {
                                            echo '<div class="mt-1 text-sm text-gray-600 bg-white p-2 rounded border-l-2 border-blue-300 pl-3">';
                                            echo nl2br(htmlspecialchars($texto_respuesta));
                                            echo '</div>';
                                        }
                                        
                                        echo '</li>';
                                    }
                                    echo '</ul>';
                                    echo '</div>';
                                }
                            } catch (\Exception $e) {
                                // Silenciar errores para no interrumpir el flujo del usuario
                                error_log("Error al cargar checkboxes personalizados en confirmación: " . $e->getMessage());
                            }
                        }
                        ?>
                    </div>
                </div>
                
                <!-- Sección de alérgenos y necesidades especiales -->
                <?php if ($tiene_alergenos || $tiene_necesidades): ?>
                <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Alérgenos -->
                    <?php if ($tiene_alergenos): ?>
                    <div>
                        <h3 class="text-lg font-medium text-yellow-700 mb-4 border-b border-gray-200 pb-2 flex items-center">
                            <i class="fas fa-exclamation-triangle text-yellow-500 mr-2"></i> Alérgenos
                        </h3>
                        <div class="bg-yellow-50 p-4 rounded-lg border border-yellow-200 shadow-sm">
                            <?php echo nl2br(htmlspecialchars($alergenos)); ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Necesidades especiales -->
                    <?php if ($tiene_necesidades): ?>
                    <div>
                        <h3 class="text-lg font-medium text-blue-700 mb-4 border-b border-gray-200 pb-2 flex items-center">
                            <i class="fas fa-info-circle text-blue-500 mr-2"></i> Necesidades especiales
                        </h3>
                        <div class="bg-blue-50 p-4 rounded-lg border border-blue-200 shadow-sm">
                            <?php echo nl2br(htmlspecialchars($_SESSION["necesidades_especiales"])); ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <div class="mt-8 border-t border-gray-200 pt-6">
                    <div class="flex flex-col md:flex-row justify-center space-y-4 md:space-y-0 md:space-x-4">
                        <!-- Botón para volver al inicio y modificar los datos -->
                        <form action="index.php" method="get" class="flex-1 max-w-xs mx-auto md:mx-0">
                            <button type="submit" class="w-full py-3 px-6 border border-gray-300 rounded-md shadow-sm text-base font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 focus:border-blue-500">
                                <i class="fas fa-arrow-left mr-2"></i> Modificar datos
                            </button>
                        </form>
                        
                        <!-- Formulario para confirmar la reserva -->
                        <form action="confirmar_reserva.php" method="post" id="form_confirmar" class="flex-1 max-w-xs mx-auto md:mx-0">
                            <input type="hidden" name="fecha" value="<?php echo htmlspecialchars($fecha); ?>">
                            <input type="hidden" name="zona" value="<?php echo htmlspecialchars($zona); ?>">
                            <input type="hidden" name="turno_id" value="<?php echo htmlspecialchars($turno_id); ?>">
                            <input type="hidden" name="tiene_alergenos" value="<?php echo $tiene_alergenos ? '1' : '0'; ?>">
                            <input type="hidden" name="alergenos" value="<?php echo htmlspecialchars($alergenos); ?>">
                            <input type="hidden" name="tiene_necesidades" value="<?php echo $tiene_necesidades ? '1' : '0'; ?>">
                            <input type="hidden" name="necesidades_especiales" value="<?php echo htmlspecialchars($_SESSION["necesidades_especiales"]); ?>">
                            <input type="hidden" name="confirmar" value="true">
                            
                            <?php
                            // Obtener los avisos de la base de datos
                            try {
                                $pdo_avisos = new PDO("mysql:host=localhost;dbname=restaurante_reservas", "root", "", [
                                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                                ]);
                                
                                $stmt_avisos = $pdo_avisos->prepare("SELECT * FROM avisos_reserva WHERE activo = 1 ORDER BY orden ASC");
                                $stmt_avisos->execute();
                                $avisos = $stmt_avisos->fetchAll(PDO::FETCH_ASSOC);
                                
                                // Mostrar los avisos activos
                                foreach ($avisos as $aviso) {
                                    mostrarInfo($aviso['texto']);
                                }
                                
                            } catch (\Exception $e) {
                                // Si hay error al obtener los avisos, mostrar los avisos predeterminados
                                mostrarInfo("Debes llegar con al menos 10 minutos de antelación o podrías perder tu reserva.");
                                
                                if ($num_personas >= 8) {
                                    mostrarInfo("Para grupos de más de 8 personas, se requiere un depósito del 20% que será descontado de la cuenta final.");
                                }
                                
                                mostrarInfo("Si necesitas cancelar tu reserva, por favor hazlo con al menos 2 horas de antelación.");
                            }
                            
                            // Mostrar avisos específicos de la sesión si existen
                            if (isset($_SESSION['avisos_confirmacion']) && is_array($_SESSION['avisos_confirmacion'])) {
                                foreach ($_SESSION['avisos_confirmacion'] as $aviso) {
                                    if (isset($aviso['tipo']) && isset($aviso['mensaje'])) {
                                        switch ($aviso['tipo']) {
                                            case 'exito':
                                                mostrarExito($aviso['mensaje']);
                                                break;
                                            case 'error':
                                                mostrarError($aviso['mensaje']);
                                                break;
                                            case 'advertencia':
                                                mostrarAdvertencia($aviso['mensaje']);
                                                break;
                                            case 'info':
                                                mostrarInfo($aviso['mensaje']);
                                                break;
                                        }
                                    }
                                }
                                // Limpiar los avisos después de mostrarlos
                                unset($_SESSION['avisos_confirmacion']);
                            }
                            ?>
                            
                            <div class="mb-4">
                                <div class="flex items-center">
                                    <input type="checkbox" id="confirmar_datos" name="confirmar_datos" class="h-4 w-4 border-gray-300 rounded custom-checkbox" style="color: var(--color-secondary); --tw-ring-color: var(--color-secondary); --tw-ring-opacity: 0.5; background-color: transparent !important;">
                                    <label for="confirmar_datos" class="ml-2 block text-sm text-black" style="background-color: transparent !important;">
                                        Confirmo que todos los datos son correctos
                                    </label>
                                </div>
                            </div>
                            
                            <button type="submit" id="btn_confirmar" class="w-full py-3 px-6 border border-transparent rounded-md shadow-sm text-base font-medium text-white focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed" style="background-color: var(--color-primary); --tw-ring-color: var(--color-primary); --tw-ring-opacity: 0.5;" disabled>
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
            <p class="mt-2">Realizada con ❤️ por <a href="https://impulsatelecom.com/" target="_blank" class="hover:text-gray-700 transition-colors duration-300">Impulsa Telecom</a></p>
        </footer>
    </div>
</body>


<script>
    // Script para habilitar/deshabilitar el botón de confirmar según el estado del checkbox
    document.addEventListener('DOMContentLoaded', function() {
        const checkbox = document.getElementById('confirmar_datos');
        const btnConfirmar = document.getElementById('btn_confirmar');
        
        checkbox.addEventListener('change', function() {
            btnConfirmar.disabled = !this.checked;
        });
        
        // Prevenir múltiples envíos del formulario y mostrar indicador de carga
        const form = document.getElementById('form_confirmar');
        let formSubmitted = false;
        
        form.addEventListener('submit', function(e) {
            // Evitar múltiples envíos
            if (formSubmitted) {
                e.preventDefault();
                return false;
            }
            
            // Marcar como enviado y deshabilitar el botón
            formSubmitted = true;
            btnConfirmar.disabled = true;
            btnConfirmar.innerHTML = 'Procesando... <i class="fas fa-spinner fa-spin ml-2"></i>';
            
            // Establecer un timeout para habilitar el botón nuevamente si algo falla
            setTimeout(function() {
                if (document.getElementById('btn_confirmar')) {
                    formSubmitted = false;
                    document.getElementById('btn_confirmar').disabled = checkbox.checked ? false : true;
                    document.getElementById('btn_confirmar').innerHTML = 'Confirmar reserva <i class="fas fa-check ml-2"></i>';
                }
            }, 10000); // 10 segundos de timeout
        });
    });
</script>
</html>