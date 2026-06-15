<?php
// Incluir archivo de autenticación
require_once 'auth.php';

// Incluir sistema de notificaciones
require_once 'notificaciones.php';

// Título de la página
$pageTitle = 'Panel de Administración';
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
    <?php include_once '../includes/header.php'; ?>
    
    <style>
        /* Aplicar estilos de interacción con color secundario */
        .hover\:bg-gray-50:hover {
            background-color: var(--color-secondary-light) !important;
            border-left: 3px solid var(--color-secondary) !important;
        }
        
        /* Estilos para elementos seleccionables en administración */
        input:focus, select:focus, textarea:focus {
            border-color: var(--color-secondary) !important;
            box-shadow: 0 0 0 3px var(--color-secondary-light) !important;
        }
        
        /* Botones de acción secundarios */
        .btn-secondary {
            background-color: var(--color-secondary) !important;
            color: white !important;
        }
        
        .btn-secondary:hover {
            background-color: var(--color-secondary-dark) !important;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <!-- Barra de navegación superior -->
    <nav class="bg-blue-600 text-white shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col items-center justify-between py-2">
                <div class="flex items-center mb-2" style="max-width: 250px; margin: 0 auto;">
                    <?php echo obtener_logo('w-full h-auto'); ?>
                </div>
                <div class="flex items-center">
                    <span class="mr-4">Bienvenido, <?php echo htmlspecialchars($_SESSION['admin_usuario']); ?></span>
                    <a href="logout.php" class="bg-blue-500 hover:bg-blue-700 text-white px-3 py-1 rounded-md text-sm font-medium transition duration-300">
                        Cerrar Sesión
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Contenido principal -->
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="px-4 py-6 sm:px-0">
            <h1 class="text-2xl font-semibold text-gray-900 mb-6">Panel de Administración</h1>
            
            <!-- Mostrar notificaciones -->
            <?php echo mostrar_notificaciones(); ?>
            
            <!-- Tarjetas de acceso rápido -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Tarjeta de Reservas -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-blue-600 rounded-md p-3">
                                <i class="fas fa-calendar-check text-white text-2xl"></i>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">
                                        Reservas Pendientes Totales
                                    </dt>
                                    <dd>
                                        <div class="text-lg font-medium text-gray-900">
                                            <?php
                                            // Obtener el número de reservas pendientes
                                            try {
                                                $pdo = new PDO('mysql:host=localhost;dbname=restaurante_reservas', 'root', '', [
                                                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                                                ]);
                                                $stmt = $pdo->query("SELECT COUNT(*) FROM reservas WHERE estado = 'pendiente'");
                                                echo $stmt->fetchColumn();
                                            } catch (PDOException $e) {
                                                echo "Error";
                                            }
                                            ?>
                                        </div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-5 py-3">
                        <div class="text-sm">
                            <?php 
                            $hoy = date('Y-m-d');
                            ?>
                            <a href="reservas.php?estado=pendiente&fecha=<?php echo $hoy; ?>" class="font-medium text-blue-600 hover:text-blue-700">
                                Ver reservas pendientes de hoy
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Tarjeta de Configuración -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-blue-600 rounded-md p-3">
                                <i class="fas fa-cogs text-white text-2xl"></i>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">
                                        Configuración del Sistema
                                    </dt>
                                    <dd>
                                        <div class="text-lg font-medium text-gray-900">
                                            Ajustes Generales
                                        </div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-5 py-3">
                        <div class="text-sm">
                            <a href="configuracion.php" class="font-medium text-green-600 hover:text-green-500">
                                Modificar configuración
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Tarjeta de Disponibilidad -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-blue-600 rounded-md p-3">
                                <i class="fas fa-calendar-alt text-white text-2xl"></i>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">
                                        Gestión de Disponibilidad
                                    </dt>
                                    <dd>
                                        <div class="text-lg font-medium text-gray-900">
                                            Días y Capacidad
                                        </div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-5 py-3">
                        <div class="text-sm">
                            <a href="disponibilidad.php" class="font-medium text-purple-600 hover:text-purple-500">
                                Gestionar disponibilidad
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Tarjeta de Historial de Reservas -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-blue-600 rounded-md p-3">
                                <i class="fas fa-history text-white text-2xl"></i>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">
                                        Historial de Reservas
                                    </dt>
                                    <dd>
                                        <div class="text-lg font-medium text-gray-900">
                                            Reservas Pasadas
                                        </div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-5 py-3">
                        <div class="text-sm">
                            <a href="historial_reservas.php" class="font-medium text-indigo-600 hover:text-indigo-500">
                                Ver historial completo
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Enlaces a todas las secciones -->
            <div class="bg-white shadow overflow-hidden sm:rounded-md">
                <ul class="divide-y divide-gray-200">
                    <li>
                        <a href="reservas.php" class="block hover:bg-gray-50">
                            <div class="px-4 py-4 sm:px-6">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 text-blue-600">
                                        <i class="fas fa-list-alt"></i>
                                    </div>
                                    <div class="ml-3">
                                        <div class="text-sm font-medium text-blue-600">Gestión de Reservas</div>
                                        <div class="text-sm text-gray-500">Ver, confirmar y gestionar todas las reservas</div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </li>
                    
                    <li>
                        <a href="historial_reservas.php" class="block hover:bg-gray-50">
                            <div class="px-4 py-4 sm:px-6">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 text-indigo-600">
                                        <i class="fas fa-history"></i>
                                    </div>
                                    <div class="ml-3">
                                        <div class="text-sm font-medium text-indigo-600">Historial de Reservas</div>
                                        <div class="text-sm text-gray-500">Ver todas las reservas pasadas con filtros por fecha</div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </li>
                    
                    <li>
                        <a href="avisos.php" class="block hover:bg-gray-50">
                            <div class="px-4 py-4 sm:px-6">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 text-blue-600">
                                        <i class="fas fa-bullhorn"></i>
                                    </div>
                                    <div class="ml-3">
                                        <div class="text-sm font-medium text-blue-600">Gestión de Avisos</div>
                                        <div class="text-sm text-gray-500">Configurar avisos personalizados para los clientes</div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </li>
                    
                    <li>
                        <a href="ocupacion.php" class="block hover:bg-gray-50">
                            <div class="px-4 py-4 sm:px-6">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 text-green-600">
                                        <i class="fas fa-chart-bar"></i>
                                    </div>
                                    <div class="ml-3">
                                        <div class="text-sm font-medium text-green-600">Ocupación por Día</div>
                                        <div class="text-sm text-gray-500">Ver la cantidad de personas por día y las reservas existentes</div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </li>
                    <li>
                        <a href="turnos.php" class="block hover:bg-gray-50">
                            <div class="px-4 py-4 sm:px-6">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 text-green-600">
                                        <i class="fas fa-clock fa-lg"></i>
                                    </div>
                                    <div class="ml-4">
                                        <p class="text-sm font-medium text-gray-900">Gestión de Turnos</p>
                                        <p class="text-sm text-gray-500">Configurar horarios de los turnos (mediodía/noche)</p>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </li>
                    <li>
                        <a href="capacidad.php" class="block hover:bg-gray-50">
                            <div class="px-4 py-4 sm:px-6">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 text-yellow-600">
                                        <i class="fas fa-users fa-lg"></i>
                                    </div>
                                    <div class="ml-4">
                                        <p class="text-sm font-medium text-gray-900">Capacidad del Restaurante</p>
                                        <p class="text-sm text-gray-500">Configurar aforo máximo por zona y turno</p>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </li>
                    <li>
                        <a href="dias_disponibles.php" class="block hover:bg-gray-50">
                            <div class="px-4 py-4 sm:px-6">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 text-red-600">
                                        <i class="fas fa-calendar fa-lg"></i>
                                    </div>
                                    <div class="ml-4">
                                        <p class="text-sm font-medium text-gray-900">Días Disponibles</p>
                                        <p class="text-sm text-gray-500">Configurar qué días está abierto el restaurante</p>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </li>
                    <li>
                        <a href="configuracion_email.php" class="block hover:bg-gray-50">
                            <div class="px-4 py-4 sm:px-6">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 text-blue-600">
                                        <i class="fas fa-envelope fa-lg"></i>
                                    </div>
                                    <div class="ml-4">
                                        <p class="text-sm font-medium text-gray-900">Configuración de Correo</p>
                                        <p class="text-sm text-gray-500">Configurar el correo electrónico para enviar notificaciones</p>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </li>
                    <li>
                        <a href="configuracion.php" class="block hover:bg-gray-50">
                            <div class="px-4 py-4 sm:px-6">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 text-purple-600">
                                        <i class="fas fa-cog fa-lg"></i>
                                    </div>
                                    <div class="ml-4">
                                        <p class="text-sm font-medium text-gray-900">Configuración General</p>
                                        <p class="text-sm text-gray-500">Ajustes generales del sistema de reservas</p>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </li>
                    <li>
                        <a href="gestionar_administradores.php" class="block hover:bg-gray-50">
                            <div class="px-4 py-4 sm:px-6">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 text-indigo-600">
                                        <i class="fas fa-user-shield fa-lg"></i>
                                    </div>
                                    <div class="ml-4">
                                        <p class="text-sm font-medium text-gray-900">Gestión de Administradores</p>
                                        <p class="text-sm text-gray-500">Crear y gestionar usuarios administradores</p>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </li>

                    <li>
                        <a href="personalizacion.php" class="block hover:bg-gray-50">
                            <div class="px-4 py-4 sm:px-6">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 text-purple-600">
                                        <i class="fas fa-paint-brush"></i>
                                    </div>
                                    <div class="ml-3">
                                        <div class="text-sm font-medium text-purple-600">Personalización</div>
                                        <div class="text-sm text-gray-500">Personalizar logo, colores y tipografía del sistema</div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Pie de página -->
    <footer class="bg-white border-t border-gray-200 py-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <p class="text-center text-gray-500 text-sm">
                &copy; <?php echo date('Y'); ?> Sistema de Reservas de Restaurantes. Todos los derechos reservados.
            </p>
            <p class="text-center text-gray-500 text-sm mt-2">
                Realizada con ❤️ por <a href="https://impulsatelecom.com/" target="_blank" class="hover:text-gray-700 transition-colors duration-300">Impulsa Telecom</a>
            </p>
        </div>
    </footer>
</body>
</html>
