<?php
/**
 * Página de gestión de reservas simplificada
 * Esta versión mantiene solo la funcionalidad esencial para listar y gestionar reservas
 */

// Incluir archivos necesarios
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

// Conexión a la base de datos
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die('Error de conexión: ' . $e->getMessage());
}

// Inicializar variables
$mensaje = '';
$tipo_mensaje = '';
$pageTitle = 'Gestión de Reservas';

// Procesar acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['accion'])) {
        $accion = $_POST['accion'];
        $reserva_id = isset($_POST['reserva_id']) ? (int)$_POST['reserva_id'] : 0;
        
        if ($accion === 'modificar') {
            // Obtener datos del formulario
            $fecha = isset($_POST['fecha']) ? $_POST['fecha'] : '';
            $hora = isset($_POST['hora']) ? $_POST['hora'] : '';
            $turno_id = isset($_POST['turno_id']) ? (int)$_POST['turno_id'] : 0;
            $zona = isset($_POST['zona']) ? $_POST['zona'] : '';
            $cantidad_personas = isset($_POST['cantidad_personas']) ? (int)$_POST['cantidad_personas'] : 0;
            $observaciones = isset($_POST['observaciones']) ? $_POST['observaciones'] : '';
            $necesidades_especiales = isset($_POST['necesidades_especiales']) ? $_POST['necesidades_especiales'] : '';
            $tiene_alergenos = isset($_POST['tiene_alergenos']) ? 1 : 0;
            
            // Validar fecha
            $fecha_obj = DateTime::createFromFormat('Y-m-d', $fecha);
            $fecha_bd = $fecha_obj ? $fecha_obj->format('Y-m-d') : $fecha;
            
            // Actualizar reserva
            $stmt = $pdo->prepare("
                UPDATE reservas 
                SET fecha = ?, hora = ?, turno_id = ?, zona = ?, 
                    cantidad_personas = ?, observaciones = ?, 
                    necesidades_especiales = ?, tiene_alergenos = ?
                WHERE id = ?
            ");
            
            if ($stmt->execute([
                $fecha_bd, $hora, $turno_id, $zona, $cantidad_personas, 
                $observaciones, $necesidades_especiales, $tiene_alergenos, $reserva_id
            ])) {
                $mensaje = 'Reserva modificada correctamente.';
                $tipo_mensaje = 'success';
            } else {
                $mensaje = 'Error al modificar la reserva.';
                $tipo_mensaje = 'error';
            }
        } elseif ($accion === 'confirmar') {
            // Primero obtener los datos actuales de la reserva
            $stmt_get = $pdo->prepare("SELECT * FROM reservas WHERE id = ?");
            $stmt_get->execute([$reserva_id]);
            $reserva_actual = $stmt_get->fetch();
            
            // Verificar si es una reserva pendiente que necesita actualizar el número de personas
            $cantidad_personas = $reserva_actual['cantidad_personas'];
            $personas_solicitadas = isset($reserva_actual['personas_solicitadas']) ? $reserva_actual['personas_solicitadas'] : 0;
            $observaciones = $reserva_actual['observaciones'];
            
            // Primero intentar usar el nuevo campo personas_solicitadas
            if ($cantidad_personas == 0 && $personas_solicitadas > 0) {
                // Usar el nuevo campo personas_solicitadas
                $cantidad_personas_real = $personas_solicitadas;
                
                // Actualizar el estado de la reserva a confirmada y actualizar la cantidad de personas
                $stmt = $pdo->prepare("UPDATE reservas SET estado = 'confirmada', cantidad_personas = ? WHERE id = ?");
                $stmt->execute([$cantidad_personas_real, $reserva_id]);
                
                error_log("Reserva #$reserva_id confirmada: Actualizada cantidad de personas de 0 a $cantidad_personas_real usando campo personas_solicitadas");
                
                // Verificar que la actualización se haya realizado correctamente
                $verify_stmt = $pdo->prepare("SELECT cantidad_personas FROM reservas WHERE id = ?");
                $verify_stmt->execute([$reserva_id]);
                $updated_count = $verify_stmt->fetchColumn();
                error_log("Verificación: Reserva #$reserva_id ahora tiene $updated_count personas");
            }
            // Compatibilidad con versiones anteriores: buscar en observaciones si no hay personas_solicitadas
            else if ($cantidad_personas == 0 && $observaciones && preg_match('/Personas solicitadas: (\d+)/', $observaciones, $matches)) {
                $cantidad_personas_real = (int)$matches[1];
                
                // Actualizar la observación para quitar la nota temporal
                $observaciones = preg_replace('/\| Personas solicitadas: \d+/', '', $observaciones);
                $observaciones = preg_replace('/Personas solicitadas: \d+\s*\|?/', '', $observaciones);
                $observaciones = trim($observaciones);
                
                // Actualizar el estado de la reserva a confirmada y actualizar la cantidad de personas
                $stmt = $pdo->prepare("UPDATE reservas SET estado = 'confirmada', cantidad_personas = ?, observaciones = ? WHERE id = ?");
                $stmt->execute([$cantidad_personas_real, $observaciones, $reserva_id]);
                
                error_log("Reserva #$reserva_id confirmada: Actualizada cantidad de personas de 0 a $cantidad_personas_real usando patrón en observaciones (compatibilidad)");
            } else {
                // Actualizar solo el estado de la reserva a confirmada
                $stmt = $pdo->prepare("UPDATE reservas SET estado = 'confirmada' WHERE id = ?");
                $stmt->execute([$reserva_id]);
                
                error_log("Reserva #$reserva_id confirmada: No se requirió actualizar la cantidad de personas (ya tiene $cantidad_personas)");
            }
            
            if (true) { // Siempre continuamos con el envío del correo
                // Obtener los datos de la reserva para enviar el correo
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
                $reserva = $stmt->fetch();
                
                if ($reserva) {
                    // Preparar el contenido del correo
                    $asunto = "Confirmación de reserva - Restaurante";
                    
                    // Crear el cuerpo del correo en HTML
                    $cuerpo = "
                        <html>
                        <head>
                            <style>
                                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                                h1 { color: #4CAF50; }
                                .info { background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0; }
                                .footer { margin-top: 30px; font-size: 0.9em; color: #666; }
                            </style>
                        </head>
                        <body>
                            <div class='container'>
                                <h1>Confirmación de Reserva</h1>
                                <p>Estimado/a <strong>{$reserva['nombre']}</strong>,</p>
                                <p>Nos complace confirmar su reserva en nuestro restaurante. A continuación, encontrará los detalles:</p>
                                
                                <div class='info'>
                                    <p><strong>Fecha:</strong> {$reserva['fecha_formateada']}</p>
                                    <p><strong>Hora de llegada:</strong> {$reserva['hora_formateada']} h</p>
                                    <p><strong>Turno:</strong> " . ucfirst($reserva['turno_nombre']) . "</p>
                                    <p><strong>Zona:</strong> " . ($reserva['zona'] == 'dentro' ? 'Interior' : 'Terraza') . "</p>
                                    <p><strong>Número de personas:</strong> {$reserva['cantidad_personas']}</p>
                                </div>
                                
                                <p>Si necesita modificar o cancelar su reserva, por favor contáctenos lo antes posible.</p>
                                
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
                        Confirmación de Reserva
                        
                        Estimado/a {$reserva['nombre']},
                        
                        Nos complace confirmar su reserva en nuestro restaurante. A continuación, encontrará los detalles:
                        
                        Fecha: {$reserva['fecha_formateada']}
                        Hora: {$reserva['hora_formateada']}
                        Turno: " . ucfirst($reserva['turno_nombre']) . "
                        Zona: " . ($reserva['zona'] == 'dentro' ? 'Interior' : 'Terraza') . "
                        Número de personas: {$reserva['cantidad_personas']}
                        
                        Si necesita modificar o cancelar su reserva, por favor contáctenos lo antes posible.
                        
                        ¡Esperamos recibirle pronto en nuestro restaurante!
                        
                        Este es un correo automático, por favor no responda a este mensaje.
                    ";
                    
                    // Intentar enviar el correo
                    $enviado = false;
                    
                    try {
                        // Usar la función de envío directo
                        $enviado = enviar_correo_directo($reserva['email'], $asunto, $cuerpo, $texto_alternativo);
                        
                        if ($enviado) {
                            $mensaje = 'Reserva confirmada correctamente y se ha enviado un correo de confirmación al cliente.';
                            error_log("Correo de confirmación enviado correctamente a: {$reserva['email']}");
                        } else {
                            $mensaje = 'Reserva confirmada correctamente, pero no se pudo enviar el correo de confirmación.';
                            error_log("Error al enviar correo de confirmación a: {$reserva['email']}");
                        }
                    } catch (Exception $e) {
                        $mensaje = 'Reserva confirmada correctamente, pero hubo un error al enviar el correo: ' . $e->getMessage();
                        error_log("Excepción al enviar correo de confirmación: " . $e->getMessage());
                    }
                } else {
                    $mensaje = 'Reserva confirmada correctamente, pero no se pudo obtener la información para enviar el correo.';
                }
                
                $tipo_mensaje = 'success';
            } else {
                $mensaje = 'Error al confirmar la reserva.';
                $tipo_mensaje = 'error';
            }
        } elseif ($accion === 'rechazar') {
            // Redirigir a la página de denegación independiente
            header("Location: denegar.php?id=$reserva_id");
            exit;
        } elseif ($accion === 'eliminar') {
            // Eliminar la reserva
            $stmt = $pdo->prepare("DELETE FROM reservas WHERE id = ?");
            if ($stmt->execute([$reserva_id])) {
                $mensaje = 'Reserva eliminada correctamente.';
                $tipo_mensaje = 'success';
            } else {
                $mensaje = 'Error al eliminar la reserva.';
                $tipo_mensaje = 'error';
            }
        }
    }
}

// Procesar filtros
$filtro_fecha = isset($_GET['filtro_fecha']) ? $_GET['filtro_fecha'] : '';
$filtro_estado = isset($_GET['filtro_estado']) ? $_GET['filtro_estado'] : '';
$filtro_turno = isset($_GET['filtro_turno']) ? $_GET['filtro_turno'] : '';
$filtro_zona = isset($_GET['filtro_zona']) ? $_GET['filtro_zona'] : '';

// Construir la consulta SQL base
$sql = "
    SELECT r.id, r.cliente_id, r.fecha, r.zona, r.turno_id, r.hora, r.cantidad_personas, r.personas_solicitadas, 
           r.observaciones, r.necesidades_especiales, r.tiene_alergenos, r.alergenos, r.estado, r.fecha_creacion, r.fecha_modificacion,
           c.nombre, c.email, c.telefono, t.nombre AS turno_nombre,
           DATE_FORMAT(r.fecha, '%d/%m/%Y') AS fecha_formateada,
           TIME_FORMAT(r.hora, '%H:%i') AS hora_formateada,
           DATE_FORMAT(r.fecha_creacion, '%d/%m/%Y %H:%i') AS fecha_creacion_formateada,
           (SELECT GROUP_CONCAT(cp.texto SEPARATOR ', ') 
            FROM reservas_checkboxes rc 
            JOIN checkboxes_personalizados cp ON rc.checkbox_id = cp.id 
            WHERE rc.reserva_id = r.id AND rc.valor = 1) AS checkboxes_seleccionados
    FROM reservas r
    JOIN clientes c ON r.cliente_id = c.id
    JOIN turnos t ON r.turno_id = t.id
    WHERE 1=1
";

$params = [];

// Agregar condiciones de filtro si están presentes
if (!empty($filtro_fecha)) {
    $sql .= " AND r.fecha = ?";
    $params[] = $filtro_fecha;
}

if (!empty($filtro_estado)) {
    $sql .= " AND r.estado = ?";
    $params[] = $filtro_estado;
}

if (!empty($filtro_turno)) {
    $sql .= " AND r.turno_id = ?";
    $params[] = $filtro_turno;
}

if (!empty($filtro_zona)) {
    $sql .= " AND r.zona = ?";
    $params[] = $filtro_zona;
}

// Agregar orden
$sql .= " ORDER BY r.fecha DESC, r.hora ASC";

// Preparar y ejecutar la consulta
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$reservas = $stmt->fetchAll();

// Obtener todos los turnos para el formulario de edición
$stmt = $pdo->query("SELECT id, nombre FROM turnos ORDER BY id");
$turnos = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - Panel de Administración</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
            </div>
        </div>
    </nav>

    <!-- Contenido principal -->
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="px-4 py-6 sm:px-0">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-semibold text-gray-900"><?php echo $pageTitle; ?></h1>
                <a href="index.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md text-sm font-medium transition duration-300">
                    Volver al Panel
                </a>
            </div>
            
            <?php if (!empty($mensaje)): ?>
                <div class="mb-4 p-4 rounded-md <?php 
                    if ($tipo_mensaje === 'success') echo 'bg-green-100 text-green-700 border-green-300';
                    elseif ($tipo_mensaje === 'error') echo 'bg-red-100 text-red-700 border-red-300';
                    else echo 'bg-yellow-100 text-yellow-700 border-yellow-300';
                ?>">
                    <?php echo $mensaje; ?>
                </div>
            <?php endif; ?>
            
            <!-- Filtros de búsqueda -->
            <div class="bg-white p-4 rounded-md shadow mb-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Filtrar Reservas</h2>
                <form method="GET" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label for="filtro_fecha" class="block text-sm font-medium text-gray-700 mb-1">Fecha</label>
                        <input type="date" id="filtro_fecha" name="filtro_fecha" value="<?php echo htmlspecialchars($filtro_fecha); ?>" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                    </div>
                    <div>
                        <label for="filtro_estado" class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                        <select id="filtro_estado" name="filtro_estado" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                            <option value="">Todos</option>
                            <option value="pendiente" <?php echo $filtro_estado === 'pendiente' ? 'selected' : ''; ?>>Pendiente</option>
                            <option value="confirmada" <?php echo $filtro_estado === 'confirmada' ? 'selected' : ''; ?>>Confirmada</option>
                            <option value="rechazada" <?php echo $filtro_estado === 'rechazada' ? 'selected' : ''; ?>>Rechazada</option>
                        </select>
                    </div>
                    <div>
                        <label for="filtro_turno" class="block text-sm font-medium text-gray-700 mb-1">Turno</label>
                        <select id="filtro_turno" name="filtro_turno" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                            <option value="">Todos</option>
                            <?php foreach ($turnos as $turno): ?>
                                <option value="<?php echo $turno['id']; ?>" <?php echo $filtro_turno == $turno['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars(ucfirst($turno['nombre'])); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label for="filtro_zona" class="block text-sm font-medium text-gray-700 mb-1">Zona</label>
                        <select id="filtro_zona" name="filtro_zona" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                            <option value="">Todas</option>
                            <option value="dentro" <?php echo $filtro_zona === 'dentro' ? 'selected' : ''; ?>>Interior</option>
                            <option value="fuera" <?php echo $filtro_zona === 'fuera' ? 'selected' : ''; ?>>Terraza</option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <i class="fas fa-search mr-2"></i> Filtrar
                        </button>
                        
                        <!-- Botones para generar PDF -->
                        <a href="generar_pdf.php?formato=a4<?php 
                            echo (!empty($filtro_fecha) ? '&filtro_fecha=' . urlencode($filtro_fecha) : '');
                            echo (!empty($filtro_estado) ? '&filtro_estado=' . urlencode($filtro_estado) : '');
                            echo (!empty($filtro_turno) ? '&filtro_turno=' . urlencode($filtro_turno) : '');
                            echo (!empty($filtro_zona) ? '&filtro_zona=' . urlencode($filtro_zona) : '');
                        ?>" target="_blank" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 ml-2">
                            <i class="fas fa-file-pdf mr-2"></i> PDF A4
                        </a>
                    </div>
                </form>
            </div>
            
            <!-- Tabla de reservas responsive -->
            <!-- Botón para imprimir múltiples tickets -->
            <div class="mb-4">
                <button id="btnImprimirSeleccionados" class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                    <i class="fas fa-print mr-2"></i> Imprimir Seleccionados
                </button>
            </div>
            
            <div class="bg-white shadow overflow-hidden sm:rounded-md">
                <?php if (count($reservas) > 0): ?>
                    <!-- Estilos específicos para la tabla responsive -->
                    <style>
                        /* Estilos para pantallas grandes (desktop) */
                        @media (min-width: 1024px) {
                            .reservas-table-container {
                                width: 100%;
                            }
                            .reservas-table {
                                width: 100%;
                                table-layout: fixed;
                            }
                            .reservas-table th, .reservas-table td {
                                white-space: normal;
                                overflow: hidden;
                                text-overflow: ellipsis;
                            }
                            .reservas-table .col-id {
                                width: 40px;
                            }
                            .reservas-table .col-cliente {
                                width: 12%;
                            }
                            .reservas-table .col-fecha {
                                width: 10%;
                            }
                            .reservas-table .col-turno, .reservas-table .col-zona {
                                width: 8%;
                            }
                            .reservas-table .col-personas {
                                width: 7%;
                            }
                            /* Columna de observaciones eliminada */
                            .reservas-table .col-alergenos {
                                width: 15%;
                            }
                            .reservas-table .col-necesidades {
                                width: 12%;
                            }
                            .reservas-table .col-checkboxes {
                                width: 12%;
                            }
                            .reservas-table .col-estado {
                                width: 8%;
                            }
                            .reservas-table .col-acciones {
                                width: 14%;
                            }
                            .reservas-card {
                                display: none;
                            }
                        }
                        
                        /* Estilos para pantallas medianas y pequeñas (tablets y móviles) */
                        @media (max-width: 1023px) {
                            .reservas-table {
                                display: none;
                            }
                            .reservas-card {
                                display: block;
                            }
                        }
                    </style>
                    
                    <!-- Vista de tabla para pantallas grandes -->
                    <div class="reservas-table-container">
                        <table class="min-w-full divide-y divide-gray-200 table-fixed reservas-table">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider" style="width: 40px;">
                                        <input type="checkbox" id="selectAll" class="form-checkbox h-4 w-4 text-blue-600 transition duration-150 ease-in-out">
                                    </th>
                                    <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider col-id">ID</th>
                                    <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider col-cliente">Cliente</th>
                                    <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider col-fecha">Fecha/Hora</th>
                                    <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider col-turno">Turno</th>
                                    <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider col-zona">Zona</th>
                                    <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider col-personas">Personas</th>
                                    <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-yellow-600 col-alergenos"><i class="fas fa-exclamation-triangle mr-1"></i>Alérgenos</th>
                                    <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-blue-600 col-necesidades"><i class="fas fa-info-circle mr-1"></i>Necesidades</th>
                                    <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-purple-600 col-checkboxes"><i class="fas fa-check-square mr-1"></i>Checkboxes</th>
                                    <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider col-estado">Estado</th>
                                    <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider col-acciones">Acciones</th>
                                </tr>
                            </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($reservas as $reserva): ?>
                                <tr>
                                    <td class="px-3 py-4 text-center">
                                        <input type="checkbox" class="reserva-checkbox form-checkbox h-4 w-4 text-blue-600 transition duration-150 ease-in-out" value="<?php echo $reserva['id']; ?>" <?php echo ($reserva['estado'] === 'pendiente' || $reserva['estado'] === 'rechazada') ? 'disabled' : ''; ?> data-estado="<?php echo $reserva['estado']; ?>">
                                    </td>
                                    <td class="px-3 py-4 text-sm text-gray-500">
                                        <?php echo $reserva['id']; ?>
                                    </td>
                                    <td class="px-3 py-4">
                                        <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($reserva['nombre']); ?></div>
                                        <div class="text-sm text-gray-500"><?php echo htmlspecialchars($reserva['email']); ?></div>
                                        <div class="text-sm text-gray-500"><?php echo htmlspecialchars($reserva['telefono']); ?></div>
                                    </td>
                                    <td class="px-3 py-4">
                                        <div class="text-sm text-gray-900"><?php echo $reserva['fecha_formateada']; ?></div>
                                        <div class="text-sm font-medium text-blue-600"><?php echo $reserva['hora_formateada']; ?> h</div>
                                        <div class="text-sm text-gray-500 mt-1"><small>Creada: <?php echo $reserva['fecha_creacion_formateada']; ?></small></div>
                                    </td>
                                    <td class="px-3 py-4 text-sm text-gray-500">
                                        <?php echo ucfirst(htmlspecialchars($reserva['turno_nombre'])); ?>
                                    </td>
                                    <td class="px-3 py-4 text-sm text-gray-500">
                                        <?php echo $reserva['zona'] === 'dentro' ? 'Interior' : 'Terraza'; ?>
                                    </td>
                                    <td class="px-3 py-4 text-sm text-gray-500">
                                        <?php 
                                        // Si la cantidad de personas es 0 y el estado es pendiente
                                        if ($reserva['cantidad_personas'] == 0 && $reserva['estado'] === 'pendiente') {
                                            $numero_real = 0;
                                            
                                            // Primero intentar usar el campo personas_solicitadas
                                            if (isset($reserva['personas_solicitadas']) && $reserva['personas_solicitadas'] > 0) {
                                                $numero_real = $reserva['personas_solicitadas'];
                                            }
                                            // Compatibilidad con versiones anteriores: buscar en las observaciones
                                            else if (!empty($reserva['observaciones']) && preg_match('/Personas solicitadas: (\d+)/', $reserva['observaciones'], $matches)) {
                                                $numero_real = (int)$matches[1];
                                                
                                                // Actualizar el campo personas_solicitadas para futuras consultas
                                                if ($numero_real > 0) {
                                                    $update_stmt = $pdo->prepare("UPDATE reservas SET personas_solicitadas = ? WHERE id = ?");
                                                    $update_stmt->execute([$numero_real, $reserva['id']]);
                                                    error_log("Actualizado personas_solicitadas para reserva #{$reserva['id']} a $numero_real");
                                                }
                                            }
                                            
                                            if ($numero_real > 0) {
                                                echo '<span class="font-medium text-orange-600">' . $numero_real . '</span>';
                                            } else {
                                                echo '<span class="text-gray-400">Pendiente</span>';
                                            }
                                        } else {
                                            echo $reserva['cantidad_personas'];
                                        }
                                        ?>
                                    </td>
                                    <!-- Columna de Alérgenos (incluye observaciones) -->
                                    <td class="px-3 py-4 text-sm">
                                        <?php
                                        // Mostrar observaciones limpias (sin la parte de personas solicitadas)
                                        $observaciones_limpias = $reserva['observaciones'];
                                        if ($reserva['cantidad_personas'] == 0 && $reserva['estado'] === 'pendiente') {
                                            $observaciones_limpias = preg_replace('/Personas solicitadas: \d+\s*\|?\s*/', '', $observaciones_limpias);
                                            $observaciones_limpias = preg_replace('/\|\s*$/', '', $observaciones_limpias);
                                            $observaciones_limpias = trim($observaciones_limpias);
                                        }
                                        
                                        // Mostrar icono de alérgenos si corresponde
                                        if ($reserva['tiene_alergenos']) {
                                            echo '<div class="flex items-center mb-1">';
                                            echo '<i class="fas fa-exclamation-triangle text-orange-500 text-lg mr-2"></i>';
                                            echo '<span class="font-medium text-orange-600">Alérgenos</span>';
                                            echo '</div>';
                                        }
                                        
                                        // Mostrar observaciones (que pueden incluir detalles sobre alérgenos)
                                        if (!empty($observaciones_limpias)) {
                                            // Limitar el texto a 50 caracteres y añadir puntos suspensivos si es más largo
                                            $texto_limitado = strlen($observaciones_limpias) > 50 ? 
                                                substr($observaciones_limpias, 0, 50) . '...' : 
                                                $observaciones_limpias;
                                                
                                            echo '<div class="text-xs text-gray-600" title="' . htmlspecialchars($observaciones_limpias) . '">';
                                            echo htmlspecialchars($texto_limitado);
                                            echo '</div>';
                                        }
                                        ?>
                                    </td>
                                    
                                    <!-- Columna de Necesidades Especiales -->
                                    <td class="px-3 py-4 text-sm text-gray-500">
                                        <?php
                                        if (!empty($reserva['necesidades_especiales'])) {
                                            // Limitar el texto a 50 caracteres y añadir puntos suspensivos si es más largo
                                            $texto_necesidades = strlen($reserva['necesidades_especiales']) > 50 ? 
                                                substr($reserva['necesidades_especiales'], 0, 50) . '...' : 
                                                $reserva['necesidades_especiales'];
                                                
                                            echo '<div class="text-xs bg-blue-50 p-2 rounded border border-blue-200" title="' . htmlspecialchars($reserva['necesidades_especiales']) . '">';
                                            echo '<i class="fas fa-info-circle text-blue-500 mr-1"></i> ';
                                            echo htmlspecialchars($texto_necesidades);
                                            echo '</div>';
                                        }
                                        ?>
                                    </td>
                                    <td class="px-3 py-4 text-sm text-gray-500">
                                        <?php if (!empty($reserva['checkboxes_seleccionados'])): ?>
                                            <div class="text-sm text-gray-700">
                                                <?php echo htmlspecialchars($reserva['checkboxes_seleccionados']); ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-gray-400 italic">Ninguno</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-3 py-4">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php 
                                            if ($reserva['estado'] === 'confirmada') {
                                                echo 'bg-green-100 text-green-800';
                                            } elseif ($reserva['estado'] === 'rechazada') {
                                                echo 'bg-red-100 text-red-800';
                                            } else {
                                                echo 'bg-yellow-100 text-yellow-800';
                                            }
                                        ?>">
                                            <?php echo ucfirst($reserva['estado']); ?>
                                        </span>
                                    </td>
                                    <td class="px-3 py-4 text-sm font-medium">
                                        <div class="grid grid-cols-1 gap-1">
                                            <!-- Botón Modificar -->
                                            <button type="button" 
                                                onclick="abrirModalEditar(
                                                    <?php echo $reserva['id']; ?>, 
                                                    '<?php echo $reserva['fecha']; ?>', 
                                                    '<?php echo $reserva['hora']; ?>', 
                                                    <?php echo $reserva['turno_id']; ?>, 
                                                    '<?php echo $reserva['zona']; ?>', 
                                                    <?php echo $reserva['cantidad_personas']; ?>, 
                                                    '<?php echo addslashes($reserva['observaciones']); ?>', 
                                                    '<?php echo addslashes($reserva['necesidades_especiales']); ?>', 
                                                    <?php echo $reserva['tiene_alergenos']; ?>
                                                )"
                                                class="inline-flex items-center justify-center px-2 py-1 border border-transparent text-xs font-medium rounded text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 w-full">
                                                <i class="fas fa-edit mr-1"></i> Modificar
                                            </button>
                                            
                                            <?php if ($reserva['estado'] === 'pendiente'): ?>
                                                <!-- Botón Confirmar -->
                                                <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="m-0">
                                                    <input type="hidden" name="accion" value="confirmar">
                                                    <input type="hidden" name="reserva_id" value="<?php echo $reserva['id']; ?>">
                                                    <button type="submit" class="inline-flex items-center justify-center px-2 py-1 border border-transparent text-xs font-medium rounded text-green-700 bg-green-100 hover:bg-green-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 w-full">
                                                        <i class="fas fa-check mr-1"></i> Confirmar
                                                    </button>
                                                </form>
                                                
                                                <!-- Botón Rechazar -->
                                                <a href="denegar.php?id=<?php echo $reserva['id']; ?>" class="inline-flex items-center justify-center px-2 py-1 border border-transparent text-xs font-medium rounded text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 w-full">
                                                    <i class="fas fa-times mr-1"></i> Rechazar
                                                </a>
                                            <?php endif; ?>
                                            
                                            <!-- Botón Eliminar -->
                                            <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="m-0" onsubmit="return confirm('¿Está seguro de que desea eliminar esta reserva? Esta acción no se puede deshacer.');">
                                                <input type="hidden" name="accion" value="eliminar">
                                                <input type="hidden" name="reserva_id" value="<?php echo $reserva['id']; ?>">
                                                <button type="submit" class="inline-flex items-center justify-center px-2 py-1 border border-transparent text-xs font-medium rounded text-gray-700 bg-gray-100 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 w-full">
                                                    <i class="fas fa-trash-alt mr-1"></i> Eliminar
                                                </button>
                                            </form>
                                            
                                            <!-- Botón Imprimir Ticket -->
                                            <a href="imprimir_ticket.php?id=<?php echo $reserva['id']; ?>" target="_blank" class="inline-flex items-center justify-center px-2 py-1 border border-transparent text-xs font-medium rounded text-green-700 bg-green-100 hover:bg-green-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 w-full">
                                                <i class="fas fa-print mr-1"></i> Imprimir
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        </table>
                    </div>
                    
                    <!-- Vista de tarjetas para dispositivos móviles -->
                    <div class="reservas-card space-y-4 p-4">
                        <?php foreach ($reservas as $reserva): ?>
                            <div class="bg-white border rounded-lg shadow-sm overflow-hidden">
                                <div class="p-4 border-b">
                                    <div class="flex justify-between items-center">
                                        <div class="flex items-center">
                                            <input type="checkbox" class="reserva-checkbox form-checkbox h-4 w-4 text-blue-600 mr-2" value="<?php echo $reserva['id']; ?>" <?php echo ($reserva['estado'] === 'pendiente' || $reserva['estado'] === 'rechazada') ? 'disabled' : ''; ?> data-estado="<?php echo $reserva['estado']; ?>">
                                            <span class="text-sm font-medium text-gray-500">ID: <?php echo $reserva['id']; ?></span>
                                        </div>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php 
                                            if ($reserva['estado'] === 'confirmada') {
                                                echo 'bg-green-100 text-green-800';
                                            } elseif ($reserva['estado'] === 'rechazada') {
                                                echo 'bg-red-100 text-red-800';
                                            } else {
                                                echo 'bg-yellow-100 text-yellow-800';
                                            }
                                        ?>">
                                            <?php echo ucfirst($reserva['estado']); ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="p-4 space-y-3">
                                    <!-- Datos del cliente -->
                                    <div class="border-b pb-3">
                                        <h3 class="text-sm font-semibold text-gray-700 mb-1">Cliente</h3>
                                        <p class="text-sm"><?php echo htmlspecialchars($reserva['nombre']); ?></p>
                                        <p class="text-xs text-gray-500"><?php echo htmlspecialchars($reserva['email']); ?></p>
                                        <p class="text-xs text-gray-500"><?php echo htmlspecialchars($reserva['telefono']); ?></p>
                                    </div>
                                    
                                    <!-- Datos de la reserva -->
                                    <div class="grid grid-cols-2 gap-3 border-b pb-3">
                                        <div>
                                            <h3 class="text-sm font-semibold text-gray-700 mb-1">Fecha/Hora</h3>
                                            <p class="text-sm"><?php echo $reserva['fecha_formateada']; ?></p>
                                            <p class="text-sm font-medium text-blue-600"><?php echo $reserva['hora_formateada']; ?> h</p>
                                            <p class="text-xs text-gray-500 mt-1">Creada: <?php echo $reserva['fecha_creacion_formateada']; ?></p>
                                        </div>
                                        <div>
                                            <h3 class="text-sm font-semibold text-gray-700 mb-1">Detalles</h3>
                                            <p class="text-sm">Turno: <?php echo ucfirst(htmlspecialchars($reserva['turno_nombre'])); ?></p>
                                            <p class="text-sm">Zona: <?php echo $reserva['zona'] === 'dentro' ? 'Interior' : 'Terraza'; ?></p>
                                            <p class="text-sm">Personas: <?php 
                                                if ($reserva['cantidad_personas'] == 0 && $reserva['estado'] === 'pendiente' && !empty($reserva['observaciones'])) {
                                                    if (preg_match('/Personas solicitadas: (\d+)/', $reserva['observaciones'], $matches)) {
                                                        echo '<span class="font-medium text-orange-600">' . $matches[1] . '</span>';
                                                    } else {
                                                        echo '<span class="text-gray-400">Pendiente</span>';
                                                    }
                                                } else {
                                                    echo $reserva['cantidad_personas'];
                                                }
                                            ?></p>
                                        </div>
                                    </div>
                                    
                                    <!-- Observaciones y necesidades especiales -->
                                    <?php 
                                    // Mostrar observaciones limpias (sin la parte de personas solicitadas)
                                    $observaciones_limpias = $reserva['observaciones'];
                                    if ($reserva['cantidad_personas'] == 0 && $reserva['estado'] === 'pendiente') {
                                        $observaciones_limpias = preg_replace('/Personas solicitadas: \d+\s*\|?\s*/', '', $observaciones_limpias);
                                        $observaciones_limpias = preg_replace('/\|\s*$/', '', $observaciones_limpias);
                                        $observaciones_limpias = trim($observaciones_limpias);
                                    }
                                    
                                    if (!empty($observaciones_limpias) || !empty($reserva['necesidades_especiales']) || $reserva['tiene_alergenos']):
                                    ?>
                                    <div class="border-b pb-3">
                                        <?php if ($reserva['tiene_alergenos'] || !empty($observaciones_limpias)): ?>
                                            <div class="mb-2">
                                                <?php if ($reserva['tiene_alergenos']): ?>
                                                <div class="flex items-center mb-2">
                                                    <i class="fas fa-exclamation-triangle text-orange-500 mr-2"></i>
                                                    <span class="text-sm font-medium text-orange-600">Alérgenos</span>
                                                </div>
                                                <?php endif; ?>
                                                
                                                <?php if (!empty($observaciones_limpias)): ?>
                                                <p class="text-xs text-gray-600"><?php echo htmlspecialchars($observaciones_limpias); ?></p>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($reserva['necesidades_especiales'])): ?>
                                            <div>
                                                <h3 class="text-sm font-semibold text-blue-600 mb-1">
                                                    <i class="fas fa-info-circle mr-1"></i> Necesidades Especiales
                                                </h3>
                                                <p class="text-xs bg-blue-50 p-2 rounded border border-blue-200">
                                                    <?php echo htmlspecialchars($reserva['necesidades_especiales']); ?>
                                                </p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- Checkboxes personalizados -->
                                    <div class="border-b pb-3">
                                        <h3 class="text-sm font-semibold text-gray-700 mb-1">Checkboxes personalizados</h3>
                                        <?php if (!empty($reserva['checkboxes_seleccionados'])): ?>
                                            <div class="flex items-center mb-2">
                                                <span class="inline-flex items-center justify-center h-6 w-6 rounded-full bg-purple-100 text-purple-800 mr-2">
                                                    <i class="fas fa-check-square text-xs"></i>
                                                </span>
                                                <span class="text-sm"><?php echo htmlspecialchars($reserva['checkboxes_seleccionados']); ?></span>
                                            </div>
                                        <?php else: ?>
                                            <p class="text-sm text-gray-500 italic">Ninguno seleccionado</p>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- Acciones -->
                                    <div class="mt-4">
                                        <!-- Botón Modificar -->
                                        <button type="button" 
                                            onclick="abrirModalEditar(
                                                <?php echo $reserva['id']; ?>, 
                                                '<?php echo $reserva['fecha']; ?>', 
                                                '<?php echo $reserva['hora']; ?>', 
                                                <?php echo $reserva['turno_id']; ?>, 
                                                '<?php echo $reserva['zona']; ?>', 
                                                <?php echo $reserva['cantidad_personas']; ?>, 
                                                '<?php echo addslashes($reserva['observaciones']); ?>', 
                                                '<?php echo addslashes($reserva['necesidades_especiales']); ?>', 
                                                <?php echo $reserva['tiene_alergenos']; ?>
                                            )"
                                            class="inline-flex items-center justify-center px-3 py-2 border border-transparent text-sm font-medium rounded text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                            <i class="fas fa-edit mr-1"></i> Modificar
                                        </button>
                                        
                                        <?php if ($reserva['estado'] === 'pendiente'): ?>
                                            <!-- Botón Confirmar -->
                                            <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="m-0">
                                                <input type="hidden" name="accion" value="confirmar">
                                                <input type="hidden" name="reserva_id" value="<?php echo $reserva['id']; ?>">
                                                <button type="submit" class="inline-flex items-center justify-center px-3 py-2 border border-transparent text-sm font-medium rounded text-green-700 bg-green-100 hover:bg-green-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 w-full">
                                                    <i class="fas fa-check mr-1"></i> Confirmar
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <!-- Botón Eliminar -->
                                            <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="m-0" onsubmit="return confirm('¿Está seguro de que desea eliminar esta reserva? Esta acción no se puede deshacer.');">
                                                <input type="hidden" name="accion" value="eliminar">
                                                <input type="hidden" name="reserva_id" value="<?php echo $reserva['id']; ?>">
                                                <button type="submit" class="inline-flex items-center justify-center px-3 py-2 border border-transparent text-sm font-medium rounded text-gray-700 bg-gray-100 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 w-full">
                                                    <i class="fas fa-trash-alt mr-1"></i> Eliminar
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <?php if ($reserva['estado'] === 'pendiente'): ?>
                                    <div>
                                        <!-- Botón Rechazar -->
                                        <a href="denegar.php?id=<?php echo $reserva['id']; ?>" class="inline-flex items-center justify-center px-3 py-2 border border-transparent text-sm font-medium rounded text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 w-full">
                                            <i class="fas fa-times mr-1"></i> Rechazar
                                        </a>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; // Fin del bucle de reservas en vista móvil ?>
                    </div>
                <?php else: ?>
                    <div class="p-6 text-center text-gray-500">
                        <p class="mb-4"><i class="fas fa-calendar-times text-4xl"></i></p>
                        <p>No hay reservas disponibles.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal para editar reserva -->
    <div id="modalEditarReserva" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-lg shadow-xl max-w-3xl w-full max-h-screen overflow-y-auto">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900">Modificar Reserva</h3>
                    <button type="button" onclick="cerrarModal()" class="text-gray-400 hover:text-gray-500">
                        <span class="sr-only">Cerrar</span>
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            
            <form id="formEditarReserva" method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                <input type="hidden" name="accion" value="modificar">
                <input type="hidden" id="reserva_id" name="reserva_id" value="">
                
                <div class="px-6 py-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="fecha" class="block text-sm font-medium text-gray-700 mb-1">Fecha</label>
                            <input type="date" id="fecha" name="fecha" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                        </div>
                        <div>
                            <label for="hora" class="block text-sm font-medium text-gray-700 mb-1">Hora</label>
                            <input type="time" id="hora" name="hora" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                        </div>
                        <div>
                            <label for="turno_id" class="block text-sm font-medium text-gray-700 mb-1">Turno</label>
                            <select id="turno_id" name="turno_id" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                <?php foreach ($turnos as $turno): ?>
                                    <option value="<?php echo $turno['id']; ?>"><?php echo htmlspecialchars($turno['nombre']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label for="zona" class="block text-sm font-medium text-gray-700 mb-1">Zona</label>
                            <select id="zona" name="zona" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                <option value="dentro">Interior</option>
                                <option value="fuera">Terraza</option>
                            </select>
                        </div>
                        <div>
                            <label for="cantidad_personas" class="block text-sm font-medium text-gray-700 mb-1">Número de personas</label>
                            <input type="number" id="cantidad_personas" name="cantidad_personas" min="1" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                        </div>
                        <div>
                            <label for="tiene_alergenos" class="flex items-center text-sm font-medium text-gray-700 mb-1">
                                <input type="checkbox" id="tiene_alergenos" name="tiene_alergenos" value="1" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded mr-2">
                                Tiene alérgenos
                            </label>
                        </div>
                        <div class="md:col-span-2">
                            <label for="observaciones" class="block text-sm font-medium text-gray-700 mb-1">Observaciones</label>
                            <textarea id="observaciones" name="observaciones" rows="3" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"></textarea>
                        </div>
                        <div class="md:col-span-2">
                            <label for="necesidades_especiales" class="block text-sm font-medium text-gray-700 mb-1">Necesidades especiales</label>
                            <textarea id="necesidades_especiales" name="necesidades_especiales" rows="3" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"></textarea>
                        </div>
                    </div>
                </div>
                
                <div class="px-6 py-4 bg-gray-50 text-right">
                    <button type="button" onclick="cerrarModal()" class="py-2 px-4 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 mr-2">
                        Cancelar
                    </button>
                    <button type="submit" class="py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Guardar cambios
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Funcionalidad para seleccionar e imprimir múltiples tickets
        document.addEventListener('DOMContentLoaded', function() {
            const selectAllCheckbox = document.getElementById('selectAll');
            const reservaCheckboxes = document.querySelectorAll('.reserva-checkbox');
            const btnImprimirSeleccionados = document.getElementById('btnImprimirSeleccionados');
            
            // Función para actualizar el estado del botón de imprimir
            function updatePrintButtonState() {
                const checkedBoxes = document.querySelectorAll('.reserva-checkbox:checked');
                btnImprimirSeleccionados.disabled = checkedBoxes.length === 0;
            }
            
            // Función para deshabilitar checkboxes según el estado
            function disableCheckboxesByState() {
                reservaCheckboxes.forEach(checkbox => {
                    const estado = checkbox.getAttribute('data-estado');
                    if (estado === 'pendiente' || estado === 'rechazada') {
                        checkbox.disabled = true;
                        checkbox.title = 'No se pueden imprimir reservas ' + estado + 's';
                    }
                });
            }
            
            // Evento para el checkbox de seleccionar todos
            selectAllCheckbox.addEventListener('change', function() {
                reservaCheckboxes.forEach(checkbox => {
                    // Solo marcar los checkboxes que no estén deshabilitados
                    if (!checkbox.disabled) {
                        checkbox.checked = selectAllCheckbox.checked;
                    }
                });
                updatePrintButtonState();
            });
            
            // Evento para cada checkbox individual
            reservaCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    // Verificar si todos están seleccionados
                    const allChecked = document.querySelectorAll('.reserva-checkbox:not(:checked)').length === 0;
                    selectAllCheckbox.checked = allChecked;
                    
                    updatePrintButtonState();
                });
            });
            
            // Evento para el botón de imprimir seleccionados
            btnImprimirSeleccionados.addEventListener('click', function() {
                const selectedIds = [];
                reservaCheckboxes.forEach(checkbox => {
                    if (checkbox.checked) {
                        selectedIds.push(checkbox.value);
                    }
                });
                
                if (selectedIds.length > 0) {
                    // Abrir la página de impresión múltiple con los IDs seleccionados
                    window.open('imprimir_multiple.php?ids=' + selectedIds.join(','), '_blank');
                }
            });
            
            // Inicializar el estado del botón
            updatePrintButtonState();
            
            // Deshabilitar checkboxes según el estado
            disableCheckboxesByState();
        });
        
        // Obtener la fecha actual en formato YYYY-MM-DD
        const hoy = new Date().toISOString().split('T')[0];
        
        // Función para abrir el modal de edición
        function abrirModalEditar(id, fecha, hora, turno_id, zona, cantidad_personas, observaciones, necesidades_especiales, tiene_alergenos) {
            // Establecer los valores en el formulario
            document.getElementById('reserva_id').value = id;
            document.getElementById('fecha').value = fecha;
            document.getElementById('hora').value = hora;
            document.getElementById('turno_id').value = turno_id;
            document.getElementById('zona').value = zona;
            document.getElementById('cantidad_personas').value = cantidad_personas;
            document.getElementById('observaciones').value = observaciones;
            document.getElementById('necesidades_especiales').value = necesidades_especiales;
            document.getElementById('tiene_alergenos').checked = tiene_alergenos === 1;
            
            // Mostrar el modal
            document.getElementById('modalEditarReserva').classList.remove('hidden');
        }
        
        function cerrarModal() {
            document.getElementById('modalEditarReserva').classList.add('hidden');
        }
    </script>

    <!-- Pie de página -->
    <footer class="bg-white border-t border-gray-200 py-4 mt-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <p class="text-center text-sm text-gray-500">
                &copy; <?php echo date('Y'); ?> Panel de Administración de Reservas
            </p>
        </div>
    </footer>
</body>
</html>
