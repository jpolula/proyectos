<?php
/**
 * Página de gestión de reservas
 * Sistema de administración de reservas para restaurantes
 */

// Incluir archivo de autenticación
require_once 'auth.php';

// Título de la página
$pageTitle = 'Gestión de Reservas';

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

// Inicializar variables
$mensaje = '';
$tipo_mensaje = '';

// Obtener parámetros de filtro de la URL
$filtro_fecha = isset($_GET['fecha']) ? $_GET['fecha'] : (isset($_GET['filtro_fecha']) ? $_GET['filtro_fecha'] : '');
$filtro_estado = isset($_GET['estado']) ? $_GET['estado'] : (isset($_GET['filtro_estado']) ? $_GET['filtro_estado'] : '');
$filtro_turno = isset($_GET['filtro_turno']) ? $_GET['filtro_turno'] : '';
$filtro_zona = isset($_GET['filtro_zona']) ? $_GET['filtro_zona'] : '';

// Verificar si hay mensajes en la sesión
// No es necesario iniciar la sesión aquí porque ya se inicia en auth.php
if (isset($_SESSION['mensaje'])) {
    $mensaje = $_SESSION['mensaje'];
    $tipo_mensaje = $_SESSION['tipo_mensaje'] ?? 'success';
    
    // Limpiar los mensajes de la sesión
    unset($_SESSION['mensaje']);
    unset($_SESSION['tipo_mensaje']);
}

// Procesar acciones POST (eliminar, modificar, etc.)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['accion'])) {
        try {
            $pdo = new PDO($dsn, $user, $pass, $options);
            
            // Acción de eliminar reserva
            if ($_POST['accion'] === 'eliminar' && isset($_POST['reserva_id'])) {
                $reserva_id = $_POST['reserva_id'];
                
                // Primero eliminar registros relacionados en reservas_checkboxes
                $stmt = $pdo->prepare("DELETE FROM reservas_checkboxes WHERE reserva_id = ?");
                $stmt->execute([$reserva_id]);
                
                // Luego eliminar la reserva
                $stmt = $pdo->prepare("DELETE FROM reservas WHERE id = ?");
                $stmt->execute([$reserva_id]);
                
                if ($stmt->rowCount() > 0) {
                    $mensaje = "La reserva ha sido eliminada correctamente.";
                    $tipo_mensaje = "success";
                } else {
                    $mensaje = "No se pudo eliminar la reserva. Puede que ya no exista.";
                    $tipo_mensaje = "error";
                }
                
                // Redirigir para evitar reenvío del formulario
                header("Location: " . $_SERVER['PHP_SELF'] . "?filtro_fecha=" . urlencode($filtro_fecha) . 
                       (isset($_GET['filtro_estado']) ? "&filtro_estado=" . urlencode($_GET['filtro_estado']) : "") . 
                       (isset($_GET['filtro_turno']) ? "&filtro_turno=" . urlencode($_GET['filtro_turno']) : "") . 
                       (isset($_GET['filtro_zona']) ? "&filtro_zona=" . urlencode($_GET['filtro_zona']) : ""));
                exit;
            }
            
            // Aquí se pueden agregar más acciones como modificar, etc.
        } catch (PDOException $e) {
            $mensaje = "Error en la base de datos: " . $e->getMessage();
            $tipo_mensaje = "error";
        }
    }
}

// Procesar filtros
// Si no hay filtro de fecha, usar la fecha actual por defecto
if (isset($_GET['filtro_fecha']) && !empty($_GET['filtro_fecha'])) {
    $filtro_fecha = $_GET['filtro_fecha'];
} else {
    // Si no hay filtro, usar la fecha actual
    $filtro_fecha = date('Y-m-d');
}

// Los filtros ya están inicializados al inicio del script

// Obtener las reservas con sus checkboxes seleccionados
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // Construir la consulta SQL base
    $sql = "SELECT r.id, r.fecha, r.hora, r.zona, r.cantidad_personas, r.personas_solicitadas, r.estado, 
           r.tiene_alergenos, r.observaciones, r.alergenos, r.necesidades_especiales,
           c.nombre, c.email, c.telefono, t.nombre AS turno_nombre,
           DATE_FORMAT(r.fecha, '%d/%m/%Y') AS fecha_formateada,
           TIME_FORMAT(r.hora, '%H:%i') AS hora_formateada,
           DATE_FORMAT(r.fecha_creacion, '%d/%m/%Y %H:%i') AS fecha_creacion_formateada,
           (
               SELECT GROUP_CONCAT(
                   CONCAT(
                       cp.texto,
                       IF(rc.texto_respuesta IS NOT NULL AND rc.texto_respuesta != '', 
                          CONCAT(' (', rc.texto_respuesta, ')'), 
                          '')
                   ) 
                   SEPARATOR ', ' 
               )
               FROM reservas_checkboxes rc 
               JOIN checkboxes_personalizados cp ON rc.checkbox_id = cp.id 
               WHERE rc.reserva_id = r.id AND rc.valor = 1
           ) AS checkboxes_seleccionados
           FROM reservas r
           JOIN clientes c ON r.cliente_id = c.id
           JOIN turnos t ON r.turno_id = t.id
           WHERE 1=1";
    
    $params = [];
    
    // Aplicar filtros
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
    
    // Ordenar y limitar resultados
    $sql .= " ORDER BY r.fecha DESC, r.id DESC LIMIT 50";
    
    // Preparar y ejecutar la consulta
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $reservas = $stmt->fetchAll();
    
    // Obtener turnos para el filtro
    $stmt_turnos = $pdo->query("SELECT id, nombre FROM turnos ORDER BY id");
    $turnos = $stmt_turnos->fetchAll();
    
} catch (PDOException $e) {
    die('Error de conexión: ' . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - Sistema de Reservas</title>
    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome para iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen">
    <!-- Barra de navegación superior -->
    <nav class="bg-blue-600 text-white shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <span class="text-xl font-bold">Sistema de Reservas</span>
                </div>
                <div class="flex items-center">
                    <a href="index.php" class="bg-gray-500 hover:bg-gray-600 text-white px-3 py-1 rounded-md text-sm font-medium transition duration-300">
                        Panel Principal
                    </a>
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
                <div class="mb-6 p-4 rounded-md <?php echo $tipo_mensaje === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <?php if ($tipo_mensaje === 'success'): ?>
                                <i class="fas fa-check-circle"></i>
                            <?php else: ?>
                                <i class="fas fa-exclamation-circle"></i>
                            <?php endif; ?>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium"><?php echo $mensaje; ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Filtros -->
            <div class="bg-white p-4 rounded-md shadow mb-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Filtrar Reservas</h2>
                <form id="filtrosForm" method="GET" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label for="filtro_fecha" class="block text-sm font-medium text-gray-700 mb-1">Fecha</label>
                        <input type="date" id="filtro_fecha" name="filtro_fecha" value="<?php echo $filtro_fecha; ?>" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
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
                                <option value="<?php echo $turno['id']; ?>" <?php echo $filtro_turno == $turno['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($turno['nombre']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label for="filtro_zona" class="block text-sm font-medium text-gray-700 mb-1">Zona</label>
                        <select id="filtro_zona" name="filtro_zona" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                            <option value="">Todas</option>
                            <option value="dentro" <?php echo $filtro_zona === 'dentro' ? 'selected' : ''; ?>>Interior</option>
                            <option value="terraza" <?php echo $filtro_zona === 'terraza' ? 'selected' : ''; ?>>Terraza</option>
                        </select>
                    </div>
                    <div class="md:col-span-4 flex justify-end">
                        <a href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <i class="fas fa-times mr-2"></i> Limpiar filtros
                        </a>
                    </div>
                </form>
            </div>
            
            <!-- Botones de acción -->
            <div class="mb-4 flex space-x-2">
                <button id="btnImprimirSeleccionados" class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                    <i class="fas fa-print mr-2"></i> Imprimir Seleccionados
                </button>
                <a href="generar_excel.php?filtro_fecha=<?php echo urlencode($filtro_fecha); ?>&filtro_estado=<?php echo urlencode($filtro_estado); ?>&filtro_turno=<?php echo urlencode($filtro_turno); ?>&filtro_zona=<?php echo urlencode($filtro_zona); ?>" class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                    <i class="fas fa-file-excel mr-2"></i> Exportar a Excel
                </a>
                <a href="generar_pdf.php?filtro_fecha=<?php echo urlencode($filtro_fecha); ?>&filtro_estado=<?php echo urlencode($filtro_estado); ?>&filtro_turno=<?php echo urlencode($filtro_turno); ?>&filtro_zona=<?php echo urlencode($filtro_zona); ?>" class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                    <i class="fas fa-file-pdf mr-2"></i> Exportar a PDF A4
                </a>
            </div>
            
            <!-- Tabla de reservas responsive -->
            <div class="bg-white shadow overflow-hidden sm:rounded-md">
                <?php if (count($reservas) > 0): ?>
                    <!-- Tabla para pantallas grandes -->
                    <div class="hidden lg:block">
                        <div class="overflow-hidden">
                            <table class="min-w-full divide-y divide-gray-200 table-fixed">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="w-10 px-2 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            <input type="checkbox" id="selectAll" class="form-checkbox h-4 w-4 text-blue-600 transition duration-150 ease-in-out">
                                        </th>
                                        <th scope="col" class="w-12 px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                        <th scope="col" class="w-48 px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                                        <th scope="col" class="w-32 px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha/Hora</th>
                                        <th scope="col" class="w-24 px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Turno</th>
                                        <th scope="col" class="w-24 px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Zona</th>
                                        <th scope="col" class="w-20 px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Personas</th>
                                        <th scope="col" class="w-32 px-2 py-3 text-left text-xs font-medium text-yellow-600 uppercase tracking-wider"><i class="fas fa-exclamation-triangle mr-1"></i>Alérgenos</th>
                                        <th scope="col" class="w-40 px-2 py-3 text-left text-xs font-medium text-blue-600 uppercase tracking-wider"><i class="fas fa-info-circle mr-1"></i>Necesidades</th>
                                        <th scope="col" class="w-40 px-2 py-3 text-left text-xs font-medium text-purple-600 uppercase tracking-wider"><i class="fas fa-check-square mr-1"></i>Checkboxes</th>
                                        <th scope="col" class="w-24 px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                        <th scope="col" class="w-32 px-2 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                    </tr>
                                </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($reservas as $reserva): ?>
                                    <tr>
                                        <td class="px-2 py-4 text-center">
                                            <input type="checkbox" class="reserva-checkbox form-checkbox h-4 w-4 text-blue-600 transition duration-150 ease-in-out" value="<?php echo $reserva['id']; ?>" <?php echo ($reserva['estado'] === 'pendiente' || $reserva['estado'] === 'rechazada') ? 'disabled' : ''; ?> data-estado="<?php echo $reserva['estado']; ?>">
                                        </td>
                                        <td class="px-2 py-4 text-sm text-gray-500">
                                            <?php echo $reserva['id']; ?>
                                        </td>
                                        <td class="px-2 py-4 text-sm">
                                            <div class="font-medium text-gray-900"><?php echo htmlspecialchars($reserva['nombre']); ?></div>
                                            <div class="text-gray-500"><?php echo htmlspecialchars($reserva['email']); ?></div>
                                            <div class="text-gray-500"><?php echo htmlspecialchars($reserva['telefono']); ?></div>
                                        </td>
                                        <td class="px-2 py-4 text-sm text-gray-500">
                                            <div><?php echo $reserva['fecha_formateada']; ?></div>
                                            <div><?php echo $reserva['hora_formateada']; ?> h</div>
                                            <div class="text-xs text-gray-400"><?php echo $reserva['fecha_creacion_formateada']; ?></div>
                                        </td>
                                        <td class="px-2 py-4 text-sm text-gray-500">
                                            <?php echo ucfirst($reserva['turno_nombre']); ?>
                                        </td>
                                        <td class="px-2 py-4 text-sm text-gray-500">
                                            <?php echo $reserva['zona'] === 'dentro' ? 'Interior' : 'Terraza'; ?>
                                        </td>
                                        <td class="px-2 py-4 text-sm text-gray-500">
                                            <?php 
                                            // Mostrar el número real de personas solicitadas para reservas pendientes
                                            if ($reserva['estado'] === 'pendiente' && isset($reserva['personas_solicitadas']) && $reserva['personas_solicitadas'] > 0) {
                                                echo $reserva['personas_solicitadas'] . ' <span class="text-xs text-yellow-600">(pendiente)</span>';
                                            } else {
                                                echo $reserva['cantidad_personas'];
                                            }
                                            ?>
                                        </td>
                                        <td class="px-2 py-4 text-sm">
                                            <?php if ($reserva['tiene_alergenos'] == 1 || (isset($reserva['alergenos']) && !empty($reserva['alergenos']))): ?>
                                                <span class="text-yellow-600"><i class="fas fa-exclamation-triangle mr-1"></i> Sí</span>
                                                <?php if (isset($reserva['alergenos']) && !empty($reserva['alergenos'])): ?>
                                                    <div class="text-xs mt-1 text-gray-600"><?php echo htmlspecialchars($reserva['alergenos']); ?></div>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="text-gray-400">No</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-2 py-4 text-sm">
                                            <?php if (!empty($reserva['necesidades_especiales'])): ?>
                                                <div class="text-blue-600">
                                                    <i class="fas fa-info-circle mr-1"></i>
                                                    <?php echo htmlspecialchars($reserva['necesidades_especiales']); ?>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-gray-400">No</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-2 py-4 text-sm">
                                            <?php if (!empty($reserva['checkboxes_seleccionados'])): ?>
                                                <div class="space-y-2">
                                                    <?php 
                                                    $checkboxes = explode(', ', $reserva['checkboxes_seleccionados']);
                                                    foreach ($checkboxes as $checkbox): 
                                                        // Extraer el texto entre paréntesis si existe
                                                        $checkbox_text = trim($checkbox);
                                                        $main_text = $checkbox_text;
                                                        $sub_text = '';
                                                        
                                                        if (preg_match('/^(.*?)\s*\((.*?)\)$/', $checkbox_text, $matches)) {
                                                            $main_text = trim($matches[1]);
                                                            $sub_text = trim($matches[2]);
                                                        }
                                                    ?>
                                                        <div class="flex flex-col">
                                                            <span class="font-medium text-purple-700"><?php echo htmlspecialchars($main_text); ?></span>
                                                            <?php if (!empty($sub_text)): ?>
                                                                <span class="text-xs text-gray-500 pl-1"><?php echo htmlspecialchars($sub_text); ?></span>
                                                            <?php endif; ?>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-gray-400">Ninguno</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-2 py-4 text-sm">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php 
                                                if ($reserva['estado'] === 'confirmada') echo 'bg-green-100 text-green-800';
                                                else if ($reserva['estado'] === 'pendiente') echo 'bg-yellow-100 text-yellow-800';
                                                else if ($reserva['estado'] === 'rechazada') echo 'bg-red-100 text-red-800';
                                                else echo 'bg-gray-100 text-gray-800';
                                            ?>">
                                                <?php echo ucfirst($reserva['estado']); ?>
                                            </span>
                                        </td>
                                        <td class="px-2 py-4 text-sm font-medium">
                                            <div class="grid grid-cols-1 gap-2">
                                                <!-- Botón Modificar -->
                                                <button type="button" 
                                                    onclick="abrirModalEditar(<?php echo $reserva['id']; ?>)"
                                                    class="inline-flex items-center justify-center px-2 py-1 border border-transparent text-xs font-medium rounded text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 w-full">
                                                    <i class="fas fa-edit mr-1"></i> Modificar
                                                </button>
                                                
                                                <?php if ($reserva['estado'] === 'pendiente'): ?>
                                                    <!-- Botón Confirmar -->
                                                    <form method="POST" action="procesar_confirmacion.php" class="m-0">
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
                                                
                                                <!-- Botón Imprimir -->
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
                    </div>
                    
                    <!-- Vista para móviles y tablets -->
                    <div class="lg:hidden">
                        <?php foreach ($reservas as $reserva): ?>
                            <div class="bg-white rounded-lg shadow-sm mb-4 overflow-hidden border border-gray-200">
                                <!-- Cabecera con ID y Estado -->
                                <div class="bg-gray-50 px-4 py-2 flex justify-between items-center">
                                    <div class="flex items-center">
                                        <input type="checkbox" class="reserva-checkbox form-checkbox h-4 w-4 text-blue-600 mr-2" value="<?php echo $reserva['id']; ?>" <?php echo ($reserva['estado'] === 'pendiente' || $reserva['estado'] === 'rechazada') ? 'disabled' : ''; ?> data-estado="<?php echo $reserva['estado']; ?>">
                                        <span class="font-medium text-gray-700">ID: <?php echo $reserva['id']; ?></span>
                                    </div>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php 
                                        if ($reserva['estado'] === 'confirmada') echo 'bg-green-100 text-green-800';
                                        else if ($reserva['estado'] === 'pendiente') echo 'bg-yellow-100 text-yellow-800';
                                        else if ($reserva['estado'] === 'rechazada') echo 'bg-red-100 text-red-800';
                                        else echo 'bg-gray-100 text-gray-800';
                                    ?>">
                                        <?php echo ucfirst($reserva['estado']); ?>
                                    </span>
                                </div>
                                
                                <!-- Contenido principal -->
                                <div class="p-4 space-y-3">
                                    <!-- Cliente -->
                                    <div class="border-b border-gray-100 pb-3">
                                        <h3 class="text-sm font-semibold text-gray-500 mb-1">Cliente</h3>
                                        <div class="font-medium text-gray-900"><?php echo htmlspecialchars($reserva['nombre']); ?></div>
                                        <div class="text-sm text-gray-500"><?php echo htmlspecialchars($reserva['email']); ?></div>
                                        <div class="text-sm text-gray-500"><?php echo htmlspecialchars($reserva['telefono']); ?></div>
                                    </div>
                                    
                                    <!-- Detalles de la reserva -->
                                    <div class="grid grid-cols-2 gap-3 border-b border-gray-100 pb-3">
                                        <div>
                                            <h3 class="text-sm font-semibold text-gray-500 mb-1">Fecha</h3>
                                            <div class="text-gray-900"><?php echo $reserva['fecha_formateada']; ?></div>
                                        </div>
                                        <div>
                                            <h3 class="text-sm font-semibold text-gray-500 mb-1">Hora</h3>
                                            <div class="text-gray-900"><?php echo $reserva['hora_formateada']; ?> h</div>
                                        </div>
                                        <div>
                                            <h3 class="text-sm font-semibold text-gray-500 mb-1">Turno</h3>
                                            <div class="text-gray-900"><?php echo ucfirst($reserva['turno_nombre']); ?></div>
                                        </div>
                                        <div>
                                            <h3 class="text-sm font-semibold text-gray-500 mb-1">Zona</h3>
                                            <div class="text-gray-900"><?php echo $reserva['zona'] === 'dentro' ? 'Interior' : 'Terraza'; ?></div>
                                        </div>
                                    </div>
                                    
                                    <!-- Personas -->
                                    <div class="border-b border-gray-100 pb-3">
                                        <h3 class="text-sm font-semibold text-gray-500 mb-1">Personas</h3>
                                        <div class="text-gray-900">
                                            <?php 
                                            // Mostrar el número real de personas solicitadas para reservas pendientes
                                            if ($reserva['estado'] === 'pendiente' && isset($reserva['personas_solicitadas']) && $reserva['personas_solicitadas'] > 0) {
                                                echo $reserva['personas_solicitadas'] . ' <span class="text-xs text-yellow-600 font-medium">(pendiente)</span>';
                                            } else {
                                                echo $reserva['cantidad_personas'];
                                            }
                                            ?>
                                        </div>
                                    </div>
                                    
                                    <!-- Información adicional -->
                                    <div class="space-y-2 border-b border-gray-100 pb-3">
                                        <!-- Alérgenos -->
                                        <div class="flex items-start">
                                            <div class="w-1/3 text-sm font-semibold text-gray-500">Alérgenos:</div>
                                            <div class="w-2/3">
                                                <?php if ($reserva['tiene_alergenos'] == 1 || (isset($reserva['alergenos']) && !empty($reserva['alergenos']))): ?>
                                                    <span class="text-yellow-600"><i class="fas fa-exclamation-triangle mr-1"></i> Sí</span>
                                                    <?php if (isset($reserva['alergenos']) && !empty($reserva['alergenos'])): ?>
                                                        <div class="text-xs mt-1 text-gray-600"><?php echo htmlspecialchars($reserva['alergenos']); ?></div>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="text-gray-400">No</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        
                                        <!-- Necesidades especiales -->
                                        <div class="flex items-start">
                                            <div class="w-1/3 text-sm font-semibold text-gray-500">Necesidades:</div>
                                            <div class="w-2/3">
                                                <?php if (!empty($reserva['necesidades_especiales'])): ?>
                                                    <div class="text-blue-600">
                                                        <i class="fas fa-info-circle mr-1"></i>
                                                        <?php echo htmlspecialchars($reserva['necesidades_especiales']); ?>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="text-gray-400">No</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        
                                        <!-- Checkboxes personalizados -->
                                        <div class="flex items-start">
                                            <div class="w-1/3 text-sm font-semibold text-gray-500">Preferencias:</div>
                                            <div class="w-2/3">
                                                <?php if (!empty($reserva['checkboxes_seleccionados'])): ?>
                                                    <div class="space-y-2">
                                                        <?php 
                                                        $checkboxes = explode(', ', $reserva['checkboxes_seleccionados']);
                                                        foreach ($checkboxes as $checkbox): 
                                                            // Extraer el texto entre paréntesis si existe
                                                            $checkbox_text = trim($checkbox);
                                                            $main_text = $checkbox_text;
                                                            $sub_text = '';
                                                            
                                                            if (preg_match('/^(.*?)\s*\((.*?)\)$/', $checkbox_text, $matches)) {
                                                                $main_text = trim($matches[1]);
                                                                $sub_text = trim($matches[2]);
                                                            }
                                                        ?>
                                                            <div class="flex flex-col">
                                                                <span class="text-sm font-medium text-purple-700"><?php echo htmlspecialchars($main_text); ?></span>
                                                                <?php if (!empty($sub_text)): ?>
                                                                    <span class="text-xs text-gray-500 pl-1"><?php echo htmlspecialchars($sub_text); ?></span>
                                                                <?php endif; ?>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="text-gray-400">Ninguno</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Botones de acción -->
                                    <div class="flex flex-col space-y-2 mt-3">
                                        <?php if ($reserva['estado'] === 'pendiente'): ?>
                                            <!-- Botón Confirmar -->
                                            <form method="POST" action="procesar_confirmacion.php" class="m-0">
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
                                        
                                        <!-- Botón Modificar -->
                                        <button type="button" onclick="abrirModalEditar(<?php echo $reserva['id']; ?>)" class="inline-flex items-center justify-center px-2 py-1 border border-transparent text-xs font-medium rounded text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 w-full">
                                            <i class="fas fa-edit mr-1"></i> Modificar
                                        </button>
                                        
                                        <!-- Botón Eliminar -->
                                        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="m-0" onsubmit="return confirm('¿Está seguro de que desea eliminar esta reserva? Esta acción no se puede deshacer.');">
                                            <input type="hidden" name="accion" value="eliminar">
                                            <input type="hidden" name="reserva_id" value="<?php echo $reserva['id']; ?>">
                                            <button type="submit" class="inline-flex items-center justify-center px-2 py-1 border border-transparent text-xs font-medium rounded text-gray-700 bg-gray-100 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 w-full">
                                                <i class="fas fa-trash-alt mr-1"></i> Eliminar
                                            </button>
                                        </form>
                                        
                                        <!-- Botón Imprimir -->
                                        <a href="imprimir_ticket.php?id=<?php echo $reserva['id']; ?>" target="_blank" class="inline-flex items-center justify-center px-2 py-1 border border-transparent text-xs font-medium rounded text-green-700 bg-green-100 hover:bg-green-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 w-full">
                                            <i class="fas fa-print mr-1"></i> Imprimir
                                        </a>
                            <?php endforeach; ?>
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

    <!-- Pie de página -->
    <footer class="bg-white border-t border-gray-200 py-4 mt-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <p class="text-center text-sm text-gray-500">
                &copy; <?php echo date('Y'); ?> Panel de Administración de Reservas
            </p>
        </div>
    </footer>

    <!-- JavaScript para manejar la selección de reservas y el modal de edición -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Función para actualizar los valores de los campos del formulario desde la URL
            function actualizarCamposDesdeURL() {
                const urlParams = new URLSearchParams(window.location.search);
                const form = document.getElementById('filtrosForm');
                if (!form) return;
                
                // Actualizar valores de los campos del formulario desde la URL
                const inputs = form.querySelectorAll('input, select');
                
                inputs.forEach(input => {
                    const paramName = input.name;
                    // Mapeo de parámetros alternativos (fecha -> filtro_fecha, estado -> filtro_estado)
                    if (urlParams.has('fecha') && paramName === 'filtro_fecha') {
                        input.value = urlParams.get('fecha');
                    } else if (urlParams.has('estado') && paramName === 'filtro_estado') {
                        input.value = urlParams.get('estado');
                    } else if (urlParams.has(paramName)) {
                        input.value = urlParams.get(paramName);
                    }
                });
            }
            
            // Configurar el manejador del formulario
            const form = document.getElementById('filtrosForm');
            if (form) {
                // Actualizar campos al cargar la página
                actualizarCamposDesdeURL();
                
                // Función para enviar el formulario
                function enviarFormulario() {
                    const formData = new FormData(form);
                    const params = new URLSearchParams();
                    
                    // Agregar solo los parámetros con valor
                    for (const [key, value] of formData.entries()) {
                        if (value) {
                            params.append(key, value);
                        }
                    }
                    
                    // Construir la URL con los parámetros
                    const newUrl = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
                    
                    // Redirigir a la nueva URL para aplicar los filtros
                    window.location.href = newUrl;
                }
                
                // Manejar el envío del formulario
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    enviarFormulario();
                });
                
                // También actualizar la URL al cambiar los campos del formulario
                const filtros = form.querySelectorAll('input, select');
                filtros.forEach(filtro => {
                    filtro.addEventListener('change', function() {
                        enviarFormulario();
                    });
                });
                
                // Manejar el botón de limpiar filtros
                const btnLimpiar = form.querySelector('a[href*="' + window.location.pathname + '"]');
                if (btnLimpiar) {
                    btnLimpiar.addEventListener('click', function(e) {
                        e.preventDefault();
                        window.location.href = window.location.pathname;
                    });
                }
            }
            
            // Selector para el checkbox "Seleccionar todos"
            const selectAllCheckbox = document.getElementById('selectAll');
            // Selectores para los checkboxes individuales de reservas
            const reservaCheckboxes = document.querySelectorAll('.reserva-checkbox:not([disabled])');
            // Botón para imprimir los seleccionados
            const btnImprimirSeleccionados = document.getElementById('btnImprimirSeleccionados');
            
            // Función para actualizar el estado del botón de imprimir
            function actualizarBotonImprimir() {
                const haySeleccionados = Array.from(reservaCheckboxes).some(checkbox => checkbox.checked);
                btnImprimirSeleccionados.disabled = !haySeleccionados;
            }
            
            // Evento para el checkbox "Seleccionar todos"
            if (selectAllCheckbox) {
                selectAllCheckbox.addEventListener('change', function() {
                    reservaCheckboxes.forEach(checkbox => {
                        checkbox.checked = selectAllCheckbox.checked;
                    });
                    actualizarBotonImprimir();
                });
            }
            
            // Eventos para los checkboxes individuales
            reservaCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    actualizarBotonImprimir();
                    
                    // Actualizar el checkbox "Seleccionar todos" si es necesario
                    if (selectAllCheckbox) {
                        const todosSeleccionados = Array.from(reservaCheckboxes).every(cb => cb.checked);
                        selectAllCheckbox.checked = todosSeleccionados;
                    }
                });
            });
            
            // Evento para el botón de imprimir seleccionados
            if (btnImprimirSeleccionados) {
                btnImprimirSeleccionados.addEventListener('click', function() {
                    const idsSeleccionados = Array.from(reservaCheckboxes)
                        .filter(checkbox => checkbox.checked)
                        .map(checkbox => checkbox.value);
                    
                    if (idsSeleccionados.length > 0) {
                        const url = 'imprimir_tickets.php?ids=' + idsSeleccionados.join(',');
                        window.open(url, '_blank');
                    }
                });
            }
            
            // Inicializar el estado del botón
            actualizarBotonImprimir();
            
            // Función para deshabilitar checkboxes según el estado
            function disableCheckboxesByState() {
                document.querySelectorAll('.reserva-checkbox').forEach(checkbox => {
                    const estado = checkbox.getAttribute('data-estado');
                    if (estado === 'pendiente' || estado === 'rechazada') {
                        checkbox.disabled = true;
                        checkbox.title = 'No se pueden imprimir reservas ' + estado + 's';
                    }
                });
            }
            
            // Ejecutar al cargar la página
            disableCheckboxesByState();
        });
        
        // Funciones para el modal de edición de reservas
        function abrirModalEditar(id) {
            // Establecer el ID de la reserva en el formulario
            document.getElementById('reserva_id').value = id;
            
            // Realizar una petición AJAX para obtener los datos de la reserva
            fetch('obtener_reserva.php?id=' + id)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert('Error al cargar los datos de la reserva: ' + data.error);
                        return;
                    }
                    
                    // Llenar el formulario con los datos recibidos
                    document.getElementById('fecha').value = data.fecha;
                    document.getElementById('hora').value = data.hora;
                    document.getElementById('turno_id').value = data.turno_id;
                    document.getElementById('zona').value = data.zona;
                    document.getElementById('cantidad_personas').value = data.cantidad_personas;
                    document.getElementById('observaciones').value = data.observaciones || '';
                    document.getElementById('necesidades_especiales').value = data.necesidades_especiales || '';
                    document.getElementById('tiene_alergenos').checked = data.tiene_alergenos == 1;
                    
                    // Mostrar el modal
                    document.getElementById('modalEditarReserva').classList.remove('hidden');
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al cargar los datos de la reserva. Por favor, inténtelo de nuevo.');
                });
        }
        
        function cerrarModal() {
            document.getElementById('modalEditarReserva').classList.add('hidden');
        }
    </script>
</body>
</html>
