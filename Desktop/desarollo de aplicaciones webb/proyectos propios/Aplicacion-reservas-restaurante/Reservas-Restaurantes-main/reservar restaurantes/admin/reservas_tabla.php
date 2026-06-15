<?php
/**
 * Versión simplificada de la página de gestión de reservas
 * Esta versión elimina la vista móvil que causa problemas de sintaxis
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

// Procesar filtros
$filtro_fecha = isset($_GET['filtro_fecha']) ? $_GET['filtro_fecha'] : '';
$filtro_estado = isset($_GET['filtro_estado']) ? $_GET['filtro_estado'] : '';
$filtro_turno = isset($_GET['filtro_turno']) ? $_GET['filtro_turno'] : '';
$filtro_zona = isset($_GET['filtro_zona']) ? $_GET['filtro_zona'] : '';

// Obtener las reservas con sus checkboxes seleccionados
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // Construir la consulta SQL base
    $sql = "SELECT r.id, r.fecha, r.hora, r.zona, r.cantidad_personas, r.estado, 
           r.tiene_alergenos, r.observaciones, r.tiene_necesidades, r.necesidades_especiales,
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
                <div class="mb-4 p-4 rounded-md <?php echo $tipo_mensaje === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                    <?php echo $mensaje; ?>
                </div>
            <?php endif; ?>
            
            <!-- Filtros -->
            <div class="bg-white p-4 rounded-md shadow mb-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Filtrar Reservas</h2>
                <form method="GET" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="grid grid-cols-1 md:grid-cols-4 gap-4">
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
                        <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <i class="fas fa-filter mr-2"></i> Filtrar
                        </button>
                        <a href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="ml-3 inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <i class="fas fa-times mr-2"></i> Limpiar
                        </a>
                    </div>
                </form>
            </div>
            
            <!-- Botón para imprimir múltiples tickets -->
            <div class="mb-4">
                <button id="btnImprimirSeleccionados" class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                    <i class="fas fa-print mr-2"></i> Imprimir Seleccionados
                </button>
            </div>
            
            <!-- Tabla de reservas -->
            <div class="bg-white shadow overflow-hidden sm:rounded-md">
                <?php if (count($reservas) > 0): ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider" style="width: 40px;">
                                        <input type="checkbox" id="selectAll" class="form-checkbox h-4 w-4 text-blue-600 transition duration-150 ease-in-out">
                                    </th>
                                    <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                    <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                                    <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha/Hora</th>
                                    <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Turno</th>
                                    <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Zona</th>
                                    <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Personas</th>
                                    <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-yellow-600"><i class="fas fa-exclamation-triangle mr-1"></i>Alérgenos</th>
                                    <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-blue-600"><i class="fas fa-info-circle mr-1"></i>Necesidades</th>
                                    <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-purple-600 uppercase tracking-wider"><i class="fas fa-check-square mr-1"></i>Checkboxes</th>
                                    <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                    <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
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
                                            <?php echo $reserva['cantidad_personas']; ?>
                                        </td>
                                        <td class="px-3 py-4 text-sm">
                                            <?php if ($reserva['tiene_alergenos']): ?>
                                                <span class="text-yellow-600"><i class="fas fa-exclamation-triangle mr-1"></i> Sí</span>
                                            <?php else: ?>
                                                <span class="text-gray-400">No</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-3 py-4 text-sm">
                                            <?php if ($reserva['tiene_necesidades']): ?>
                                                <div class="text-blue-600">
                                                    <i class="fas fa-info-circle mr-1"></i>
                                                    <?php echo htmlspecialchars($reserva['necesidades_especiales']); ?>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-gray-400">No</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-3 py-4 text-sm">
                                            <?php if (!empty($reserva['checkboxes_seleccionados'])): ?>
                                                <div class="text-purple-600">
                                                    <?php echo htmlspecialchars($reserva['checkboxes_seleccionados']); ?>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-gray-400">Ninguno</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-3 py-4">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php 
                                                if ($reserva['estado'] === 'confirmada') {
                                                    echo 'bg-green-100 text-green-800';
                                                } elseif ($reserva['estado'] === 'pendiente') {
                                                    echo 'bg-yellow-100 text-yellow-800';
                                                } elseif ($reserva['estado'] === 'rechazada') {
                                                    echo 'bg-red-100 text-red-800';
                                                } else {
                                                    echo 'bg-gray-100 text-gray-800';
                                                }
                                            ?>">
                                                <?php echo ucfirst($reserva['estado']); ?>
                                            </span>
                                        </td>
                                        <td class="px-3 py-4 text-sm font-medium">
                                            <div class="grid grid-cols-1 gap-1">
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
                <?php else: ?>
                    <div class="p-6 text-center text-gray-500">
                        <p class="mb-4"><i class="fas fa-calendar-times text-4xl"></i></p>
                        <p>No hay reservas disponibles.</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Nota informativa -->
            <div class="mt-8 bg-white p-6 rounded-lg shadow">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Versión simplificada de gestión de reservas</h2>
                <p class="mb-4">Esta es una versión simplificada de la página de gestión de reservas que incluye la columna de checkboxes personalizados.</p>
                <p>La vista móvil ha sido temporalmente deshabilitada para corregir problemas de sintaxis.</p>
            </div>
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

    <!-- JavaScript para manejar la selección de reservas -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
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
        });
    </script>
</body>
</html>
