<?php
/**
 * Script para actualizar la tabla de reservas y añadir el campo fecha_creacion
 */

// Incluir archivo de autenticación
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

// Título de la página
$pageTitle = 'Actualizar Estructura de Base de Datos';

// Variable para almacenar mensajes
$mensaje = '';
$tipo_mensaje = '';

try {
    // Conectar a la base de datos
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // Verificar si la columna ya existe
    $stmt = $pdo->prepare("
        SELECT COLUMN_NAME 
        FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_SCHEMA = ? 
        AND TABLE_NAME = 'reservas' 
        AND COLUMN_NAME = 'fecha_creacion'
    ");
    $stmt->execute([$db]);
    
    if ($stmt->rowCount() == 0) {
        // La columna no existe, añadirla
        $pdo->exec("
            ALTER TABLE reservas 
            ADD COLUMN fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP
        ");
        
        // Verificar que la columna se haya creado correctamente
        $stmt = $pdo->prepare("
            SELECT COLUMN_NAME 
            FROM INFORMATION_SCHEMA.COLUMNS 
            WHERE TABLE_SCHEMA = ? 
            AND TABLE_NAME = 'reservas' 
            AND COLUMN_NAME = 'fecha_creacion'
        ");
        $stmt->execute([$db]);
        
        if ($stmt->rowCount() == 0) {
            throw new Exception("No se pudo crear la columna fecha_creacion");
        }
        
        // Actualizar las reservas existentes con la fecha actual
        $update_result = $pdo->exec("
            UPDATE reservas 
            SET fecha_creacion = NOW() 
            WHERE fecha_creacion IS NULL
        ");
        
        // Verificar que la actualización haya sido exitosa
        $count_stmt = $pdo->query("SELECT COUNT(*) FROM reservas WHERE fecha_creacion IS NULL");
        $null_count = $count_stmt->fetchColumn();
        
        if ($null_count > 0) {
            $mensaje = "Se ha añadido el campo 'fecha_creacion', pero no se pudieron actualizar todas las reservas existentes. Hay $null_count reservas sin fecha de creación.";
            $tipo_mensaje = "warning";
        } else {
            $mensaje = "Se ha añadido correctamente el campo 'fecha_creacion' a la tabla de reservas y se han actualizado $update_result reservas.";
            $tipo_mensaje = "success";
        }
    } else {
        // La columna ya existe, verificar si hay reservas sin fecha de creación
        $count_stmt = $pdo->query("SELECT COUNT(*) FROM reservas WHERE fecha_creacion IS NULL");
        $null_count = $count_stmt->fetchColumn();
        
        if ($null_count > 0) {
            // Hay reservas sin fecha de creación, actualizarlas
            $update_result = $pdo->exec("
                UPDATE reservas 
                SET fecha_creacion = NOW() 
                WHERE fecha_creacion IS NULL
            ");
            
            $mensaje = "El campo 'fecha_creacion' ya existe en la tabla de reservas. Se han actualizado $update_result reservas que no tenían fecha de creación.";
            $tipo_mensaje = "success";
        } else {
            $mensaje = "El campo 'fecha_creacion' ya existe en la tabla de reservas y todas las reservas tienen una fecha de creación.";
            $tipo_mensaje = "info";
        }
    }
} catch (PDOException $e) {
    $mensaje = "Error al actualizar la estructura de la base de datos: " . $e->getMessage();
    $tipo_mensaje = "error";
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
                    <span class="mr-4">Bienvenido, <?php echo htmlspecialchars($_SESSION['admin_usuario']); ?></span>
                    <a href="logout.php" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded-md text-sm font-medium transition duration-300">
                        Cerrar Sesión
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
                <div class="mb-6 p-4 rounded-md <?php 
                    echo $tipo_mensaje === 'success' ? 'bg-green-100 text-green-700 border-green-300' : 
                        ($tipo_mensaje === 'error' ? 'bg-red-100 text-red-700 border-red-300' : 
                        'bg-blue-100 text-blue-700 border-blue-300'); 
                ?>">
                    <p><?php echo $mensaje; ?></p>
                </div>
            <?php endif; ?>
            
            <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        Información de la actualización
                    </h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500">
                        Detalles sobre los cambios realizados en la estructura de la base de datos.
                    </p>
                </div>
                <div class="border-t border-gray-200 px-4 py-5 sm:p-0">
                    <dl class="sm:divide-y sm:divide-gray-200">
                        <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">
                                Tabla modificada
                            </dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                reservas
                            </dd>
                        </div>
                        <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">
                                Campo añadido
                            </dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                fecha_creacion (DATETIME)
                            </dd>
                        </div>
                        <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">
                                Valor por defecto
                            </dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                CURRENT_TIMESTAMP (fecha y hora actual al crear la reserva)
                            </dd>
                        </div>
                        <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">
                                Próximos pasos
                            </dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                <p>Ya puedes ver la fecha y hora de creación de cada reserva en el panel de administración.</p>
                                <a href="reservas.php" class="inline-block mt-3 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition duration-300">
                                    Ir a Gestión de Reservas
                                </a>
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <!-- Pie de página -->
    <footer class="bg-white border-t border-gray-200 py-4 mt-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <p class="text-center text-gray-500 text-sm">
                &copy; <?php echo date('Y'); ?> Sistema de Reservas de Restaurantes. Todos los derechos reservados.
            </p>
        </div>
    </footer>
</body>
</html>
