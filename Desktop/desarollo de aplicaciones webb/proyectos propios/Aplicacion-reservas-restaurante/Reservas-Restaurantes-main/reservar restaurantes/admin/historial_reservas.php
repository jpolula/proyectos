<?php
/**
 * Página de historial de reservas pasadas
 * Permite visualizar todas las reservas de días anteriores con filtros por fecha
 */

// Incluir archivos necesarios
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
$pageTitle = 'Historial de Reservas';

// Obtener la fecha actual para comparar
$fecha_actual = date('Y-m-d');

// Procesar filtros
$fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : '';
$fecha_fin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : '';
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
           DATE_FORMAT(r.fecha_creacion, '%d/%m/%Y %H:%i') AS fecha_creacion_formateada
    FROM reservas r
    JOIN clientes c ON r.cliente_id = c.id
    JOIN turnos t ON r.turno_id = t.id
    WHERE r.fecha < ? 
";

$params = [$fecha_actual]; // Por defecto, mostrar solo reservas pasadas

// Agregar condiciones de filtro si están presentes
if (!empty($fecha_inicio)) {
    $sql .= " AND r.fecha >= ?";
    $params[] = $fecha_inicio;
}

if (!empty($fecha_fin)) {
    $sql .= " AND r.fecha <= ?";
    $params[] = $fecha_fin;
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

// Obtener todos los turnos para el formulario de filtro
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
                <h2 class="text-lg font-medium text-gray-900 mb-4">Filtrar Historial de Reservas</h2>
                <form method="GET" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="grid grid-cols-1 md:grid-cols-6 gap-4">
                    <div class="md:col-span-2">
                        <label for="fecha_inicio" class="block text-sm font-medium text-gray-700 mb-1">Desde</label>
                        <input type="date" id="fecha_inicio" name="fecha_inicio" value="<?php echo htmlspecialchars($fecha_inicio); ?>" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                    </div>
                    <div class="md:col-span-2">
                        <label for="fecha_fin" class="block text-sm font-medium text-gray-700 mb-1">Hasta</label>
                        <input type="date" id="fecha_fin" name="fecha_fin" value="<?php echo htmlspecialchars($fecha_fin); ?>" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
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
                    <div class="flex items-end md:col-span-6">
                        <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <i class="fas fa-search mr-2"></i> Filtrar
                        </button>
                        
                        <!-- Botones para exportación -->
                        <a href="generar_pdf.php?formato=a4&historial=1<?php 
                            echo (!empty($fecha_inicio) ? '&fecha_inicio=' . urlencode($fecha_inicio) : '');
                            echo (!empty($fecha_fin) ? '&fecha_fin=' . urlencode($fecha_fin) : '');
                            echo (!empty($filtro_estado) ? '&filtro_estado=' . urlencode($filtro_estado) : '');
                            echo (!empty($filtro_turno) ? '&filtro_turno=' . urlencode($filtro_turno) : '');
                            echo (!empty($filtro_zona) ? '&filtro_zona=' . urlencode($filtro_zona) : '');
                        ?>" target="_blank" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 ml-2">
                            <i class="fas fa-file-pdf mr-2"></i> Exportar a PDF
                        </a>
                        
                        <a href="generar_excel.php?<?php 
                            echo (!empty($fecha_inicio) ? 'fecha_inicio=' . urlencode($fecha_inicio) . '&' : '');
                            echo (!empty($fecha_fin) ? 'fecha_fin=' . urlencode($fecha_fin) . '&' : '');
                            echo (!empty($filtro_estado) ? 'filtro_estado=' . urlencode($filtro_estado) . '&' : '');
                            echo (!empty($filtro_turno) ? 'filtro_turno=' . urlencode($filtro_turno) . '&' : '');
                            echo (!empty($filtro_zona) ? 'filtro_zona=' . urlencode($filtro_zona) : '');
                        ?>" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 ml-2">
                            <i class="fas fa-file-excel mr-2"></i> Exportar a Excel
                        </a>
                    </div>
                </form>
            </div>
            
            <!-- Tabla de reservas históricas -->
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
                            .reservas-table .col-obs {
                                width: 15%;
                            }
                            .reservas-table .col-acciones {
                                width: 10%;
                            }
                        }
                        
                        /* Estilos para dispositivos móviles */
                        @media (max-width: 1023px) {
                            .reservas-table-container {
                                display: none;
                            }
                            .reservas-card {
                                display: block;
                            }
                        }
                        
                        /* Estilos para desktop */
                        @media (min-width: 1024px) {
                            .reservas-table-container {
                                display: block;
                            }
                            .reservas-card {
                                display: none;
                            }
                        }
                    </style>
                    
                    <div class="reservas-table-container">
                        <table class="min-w-full divide-y divide-gray-200 table-fixed reservas-table">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider col-id">
                                        ID
                                    </th>
                                    <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider col-cliente">
                                        Cliente
                                    </th>
                                    <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider col-fecha">
                                        Fecha
                                    </th>
                                    <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider col-turno">
                                        Turno
                                    </th>
                                    <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider col-zona">
                                        Zona
                                    </th>
                                    <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider col-personas">
                                        Personas
                                    </th>
                                    <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider col-obs">
                                        Observaciones
                                    </th>
                                    <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider col-acciones">
                                        Estado
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($reservas as $reserva): ?>
                                    <tr>
                                        <td class="px-3 py-4 text-sm text-gray-500">
                                            <?php echo $reserva['id']; ?>
                                        </td>
                                        <td class="px-3 py-4 text-sm text-gray-500">
                                            <div class="font-medium"><?php echo htmlspecialchars($reserva['nombre']); ?></div>
                                            <div class="text-xs text-gray-400"><?php echo htmlspecialchars($reserva['email']); ?></div>
                                            <div class="text-xs text-gray-400"><?php echo htmlspecialchars($reserva['telefono']); ?></div>
                                        </td>
                                        <td class="px-3 py-4 text-sm text-gray-500">
                                            <div><?php echo $reserva['fecha_formateada']; ?></div>
                                            <div class="text-xs font-semibold"><?php echo $reserva['hora_formateada']; ?> h</div>
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
                                            <?php
                                            // Mostrar observaciones limpias (sin la parte de personas solicitadas)
                                            $observaciones_limpias = $reserva['observaciones'];
                                            if ($reserva['cantidad_personas'] == 0 && $reserva['estado'] === 'pendiente') {
                                                $observaciones_limpias = preg_replace('/\| Personas solicitadas: \d+/', '', $observaciones_limpias);
                                                $observaciones_limpias = preg_replace('/Personas solicitadas: \d+\s*\|?/', '', $observaciones_limpias);
                                                $observaciones_limpias = trim($observaciones_limpias);
                                            }
                                            
                                            // Mostrar necesidades especiales y alérgenos
                                            if (!empty($reserva['necesidades_especiales']) || $reserva['tiene_alergenos'] || !empty($observaciones_limpias)):
                                            ?>
                                                <div class="text-sm text-gray-500">
                                                    <?php if ($reserva['tiene_alergenos']): ?>
                                                        <div class="flex items-center mb-1">
                                                            <span class="inline-flex items-center justify-center px-2 py-1 mr-2 text-xs font-bold leading-none text-red-100 bg-red-600 rounded-full">Alérgenos</span>
                                                        </div>
                                                    <?php endif; ?>
                                                    
                                                    <?php if (!empty($reserva['necesidades_especiales'])): ?>
                                                        <div class="mb-1">
                                                            <span class="font-medium">Necesidades especiales:</span>
                                                            <p class="text-xs mt-1"><?php echo nl2br(htmlspecialchars($reserva['necesidades_especiales'])); ?></p>
                                                        </div>
                                                    <?php endif; ?>
                                                    
                                                    <?php if (!empty($observaciones_limpias)): ?>
                                                        <div>
                                                            <span class="font-medium">Observaciones:</span>
                                                            <p class="text-xs mt-1"><?php echo nl2br(htmlspecialchars($observaciones_limpias)); ?></p>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-3 py-4 text-sm font-medium">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php 
                                                if ($reserva['estado'] === 'confirmada') echo 'bg-green-100 text-green-800';
                                                elseif ($reserva['estado'] === 'pendiente') echo 'bg-yellow-100 text-yellow-800';
                                                else echo 'bg-red-100 text-red-800';
                                            ?>">
                                                <?php 
                                                    if ($reserva['estado'] === 'confirmada') echo 'Confirmada';
                                                    elseif ($reserva['estado'] === 'pendiente') echo 'Pendiente';
                                                    else echo 'Rechazada';
                                                ?>
                                            </span>
                                            <div class="text-xs text-gray-400 mt-1">
                                                Creada: <?php echo $reserva['fecha_creacion_formateada']; ?>
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
                                        <span class="text-sm font-medium text-gray-500">ID: <?php echo $reserva['id']; ?></span>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php 
                                            if ($reserva['estado'] === 'confirmada') echo 'bg-green-100 text-green-800';
                                            elseif ($reserva['estado'] === 'pendiente') echo 'bg-yellow-100 text-yellow-800';
                                            else echo 'bg-red-100 text-red-800';
                                        ?>">
                                            <?php 
                                                if ($reserva['estado'] === 'confirmada') echo 'Confirmada';
                                                elseif ($reserva['estado'] === 'pendiente') echo 'Pendiente';
                                                else echo 'Rechazada';
                                            ?>
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
                                    <div class="border-b pb-3">
                                        <h3 class="text-sm font-semibold text-gray-700 mb-1">Detalles de la Reserva</h3>
                                        <div class="grid grid-cols-2 gap-2 text-sm">
                                            <div>
                                                <p class="text-gray-500"><span class="font-medium">Fecha:</span> <?php echo $reserva['fecha_formateada']; ?></p>
                                                <p class="text-gray-500"><span class="font-medium">Hora:</span> <?php echo $reserva['hora_formateada']; ?> h</p>
                                            </div>
                                            <div>
                                                <p class="text-gray-500"><span class="font-medium">Turno:</span> <?php echo ucfirst(htmlspecialchars($reserva['turno_nombre'])); ?></p>
                                                <p class="text-gray-500"><span class="font-medium">Zona:</span> <?php echo $reserva['zona'] === 'dentro' ? 'Interior' : 'Terraza'; ?></p>
                                                <p class="text-gray-500"><span class="font-medium">Personas:</span> <?php echo $reserva['cantidad_personas']; ?></p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Observaciones y necesidades especiales -->
                                    <?php
                                    // Mostrar observaciones limpias (sin la parte de personas solicitadas)
                                    $observaciones_limpias = $reserva['observaciones'];
                                    if ($reserva['cantidad_personas'] == 0 && $reserva['estado'] === 'pendiente') {
                                        $observaciones_limpias = preg_replace('/\| Personas solicitadas: \d+/', '', $observaciones_limpias);
                                        $observaciones_limpias = preg_replace('/Personas solicitadas: \d+\s*\|?/', '', $observaciones_limpias);
                                        $observaciones_limpias = trim($observaciones_limpias);
                                    }
                                    ?>
                                    <div class="border-b pb-3">
                                        <?php if ($reserva['tiene_alergenos'] || !empty($observaciones_limpias)): ?>
                                            <div class="mb-2">
                                                <?php if ($reserva['tiene_alergenos']): ?>
                                                <div class="flex items-center mb-2">
                                                    <span class="inline-flex items-center justify-center px-2 py-1 mr-2 text-xs font-bold leading-none text-red-100 bg-red-600 rounded-full">Alérgenos</span>
                                                </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($reserva['necesidades_especiales'])): ?>
                                            <div class="mb-2">
                                                <span class="text-sm font-medium text-gray-700">Necesidades especiales:</span>
                                                <p class="text-xs mt-1 text-gray-600"><?php echo nl2br(htmlspecialchars($reserva['necesidades_especiales'])); ?></p>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($observaciones_limpias)): ?>
                                            <div>
                                                <span class="text-sm font-medium text-gray-700">Observaciones:</span>
                                                <p class="text-xs mt-1 text-gray-600"><?php echo nl2br(htmlspecialchars($observaciones_limpias)); ?></p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- Información adicional -->
                                    <div class="text-xs text-gray-500">
                                        <p>Creada: <?php echo $reserva['fecha_creacion_formateada']; ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="p-6 text-center text-gray-500">
                        <p class="mb-4"><i class="fas fa-calendar-times text-4xl"></i></p>
                        <p>No hay reservas históricas disponibles para los filtros seleccionados.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-white py-4 text-center text-gray-500 text-sm mt-8">
        <p>Realizada con ❤️ por <a href="https://impulsatelecom.com/" target="_blank" class="hover:text-gray-700 transition-colors duration-300">Impulsa Telecom</a></p>
    </footer>
</body>
</html>
