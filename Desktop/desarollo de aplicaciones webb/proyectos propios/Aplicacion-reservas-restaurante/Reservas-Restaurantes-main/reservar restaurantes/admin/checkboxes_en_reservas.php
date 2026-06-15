<?php
/**
 * Script para mostrar los checkboxes personalizados seleccionados en una reserva
 */

// Incluir archivo de autenticación
require_once 'auth.php';

// Título de la página
$pageTitle = 'Checkboxes en Reservas';

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

// Obtener las reservas con sus checkboxes seleccionados
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // Consulta para obtener las reservas con sus checkboxes
    $sql = "SELECT r.id, r.fecha, c.nombre, r.estado,
           DATE_FORMAT(r.fecha, '%d/%m/%Y') AS fecha_formateada,
           (SELECT GROUP_CONCAT(cp.texto SEPARATOR ', ') 
            FROM reservas_checkboxes rc 
            JOIN checkboxes_personalizados cp ON rc.checkbox_id = cp.id 
            WHERE rc.reserva_id = r.id AND rc.valor = 1) AS checkboxes_seleccionados
           FROM reservas r
           LEFT JOIN clientes c ON r.cliente_id = c.id
           ORDER BY r.fecha DESC, r.id DESC
           LIMIT 20";
    
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
                    <a href="reservas.php" class="bg-blue-500 hover:bg-blue-700 text-white px-3 py-1 rounded-md text-sm font-medium transition duration-300 mr-2">
                        Volver a Reservas
                    </a>
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
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-purple-600 uppercase tracking-wider">
                                    <i class="fas fa-check-square mr-1"></i>Checkboxes Seleccionados
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($reservas as $reserva): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $reserva['id']; ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $reserva['fecha_formateada']; ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($reserva['nombre']); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
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
                                    <td class="px-6 py-4">
                                        <?php if (!empty($reserva['checkboxes_seleccionados'])): ?>
                                            <div class="text-sm text-gray-700">
                                                <?php echo htmlspecialchars($reserva['checkboxes_seleccionados']); ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-gray-400 italic">Ninguno</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="p-6 text-center text-gray-500">
                        <p class="mb-4"><i class="fas fa-calendar-times text-4xl"></i></p>
                        <p>No hay reservas disponibles.</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Instrucciones -->
            <div class="mt-8 bg-white p-6 rounded-lg shadow">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Instrucciones para ver los checkboxes en la página principal de reservas</h2>
                <p class="mb-4">Para ver los checkboxes personalizados seleccionados en la página principal de reservas, debes modificar el archivo <code>reservas.php</code> para incluir una nueva columna en la tabla.</p>
                <p class="mb-4">Debido a un error de sintaxis en el archivo original, se ha creado esta página alternativa que muestra la información de manera correcta.</p>
                <p>Para acceder a la página principal de reservas, haz clic en el botón "Volver a Reservas" en la parte superior de la página.</p>
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
