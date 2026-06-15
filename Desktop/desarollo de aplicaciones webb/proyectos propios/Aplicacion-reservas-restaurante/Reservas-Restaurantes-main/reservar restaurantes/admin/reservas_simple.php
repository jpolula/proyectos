<?php
/**
 * Página de gestión de reservas simplificada
 * Esta versión mantiene solo la funcionalidad esencial para listar y gestionar reservas
 */

// Incluir archivos necesarios
require_once 'auth.php';

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

// Obtener las reservas con sus checkboxes seleccionados
try {
    // Consulta para obtener las reservas con sus checkboxes
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
           ORDER BY r.fecha DESC, r.id DESC
           LIMIT 50";
    
    $stmt = $pdo->query($sql);
    $reservas = $stmt->fetchAll();
    
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
            </div>
            
            <!-- Tabla de reservas con checkboxes -->
            <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                <?php if (count($reservas) > 0): ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                    <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                                    <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha/Hora</th>
                                    <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Turno</th>
                                    <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Zona</th>
                                    <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Personas</th>
                                    <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-purple-600 uppercase tracking-wider">
                                        <i class="fas fa-check-square mr-1"></i>Checkboxes
                                    </th>
                                    <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($reservas as $reserva): ?>
                                    <tr>
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
                <p>Para acceder a todas las funcionalidades de gestión de reservas (confirmar, rechazar, eliminar, etc.), utiliza la página original de reservas.</p>
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
</body>
</html>
