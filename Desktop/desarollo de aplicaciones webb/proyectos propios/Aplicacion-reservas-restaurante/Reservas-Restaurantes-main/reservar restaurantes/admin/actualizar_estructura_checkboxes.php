<?php
// Incluir archivo de autenticación
require_once 'auth.php';

// Título de la página
$pageTitle = 'Actualizar Estructura de Checkboxes';

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
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // Verificar si las columnas ya existen
    $columnas = [
        'checkboxes_personalizados' => ['es_obligatorio', 'tiene_textarea', 'placeholder_textarea'],
        'reservas_checkboxes' => ['texto_respuesta']
    ];
    
    $mensajes = [];
    
    // Verificar y agregar columnas si no existen
    foreach ($columnas as $tabla => $campos) {
        foreach ($campos as $campo) {
            $stmt = $pdo->prepare("SHOW COLUMNS FROM `$tabla` LIKE ?");
            $stmt->execute([$campo]);
            
            if ($stmt->rowCount() === 0) {
                // La columna no existe, vamos a crearla
                if ($tabla === 'checkboxes_personalizados') {
                    if ($campo === 'es_obligatorio') {
                        $pdo->exec("ALTER TABLE `$tabla` ADD COLUMN `$campo` TINYINT(1) NOT NULL DEFAULT 0 AFTER `activo`");
                        $mensajes[] = "✅ Columna '$campo' añadida a la tabla '$tabla'.";
                    } elseif ($campo === 'tiene_textarea') {
                        $pdo->exec("ALTER TABLE `$tabla` ADD COLUMN `$campo` TINYINT(1) NOT NULL DEFAULT 0 AFTER `es_obligatorio`");
                        $mensajes[] = "✅ Columna '$campo' añadida a la tabla '$tabla'.";
                    } elseif ($campo === 'placeholder_textarea') {
                        $pdo->exec("ALTER TABLE `$tabla` ADD COLUMN `$campo` VARCHAR(255) DEFAULT NULL AFTER `tiene_textarea`");
                        $mensajes[] = "✅ Columna '$campo' añadida a la tabla '$tabla'.";
                    }
                } elseif ($tabla === 'reservas_checkboxes' && $campo === 'texto_respuesta') {
                    $pdo->exec("ALTER TABLE `$tabla` ADD COLUMN `$campo` TEXT DEFAULT NULL AFTER `valor`");
                    $mensajes[] = "✅ Columna '$campo' añadida a la tabla '$tabla'.";
                }
            } else {
                $mensajes[] = "ℹ️ La columna '$campo' ya existe en la tabla '$tabla'.";
            }
        }
    }
    
    // Si no hubo que hacer cambios
    if (empty($mensajes)) {
        $mensajes[] = "✅ La estructura de la base de datos ya está actualizada. No se requieren cambios.";
    }
    
} catch (PDOException $e) {
    $error = "❌ Error al actualizar la estructura de la base de datos: " . $e->getMessage();
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
                    <a href="checkboxes_personalizados.php" class="bg-blue-500 hover:bg-blue-700 text-white px-3 py-1 rounded-md text-sm font-medium transition duration-300 mr-2">
                        Volver a Checkboxes
                    </a>
                    <a href="index.php" class="bg-gray-500 hover:bg-gray-600 text-white px-3 py-1 rounded-md text-sm font-medium transition duration-300">
                        Panel Principal
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Contenido principal -->
    <div class="max-w-4xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="px-4 py-6 sm:px-0">
            <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                <div class="px-4 py-5 sm:px-6">
                    <h1 class="text-lg leading-6 font-medium text-gray-900">
                        <?php echo $pageTitle; ?>
                    </h1>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500">
                        Actualización de la estructura de la base de datos para soportar checkboxes con áreas de texto.
                    </p>
                </div>
                <div class="border-t border-gray-200 px-4 py-5 sm:p-0">
                    <div class="px-4 py-4 sm:px-6">
                        <?php if (isset($error)): ?>
                            <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm text-red-700">
                                            <?php echo $error; ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($mensajes)): ?>
                            <div class="space-y-4">
                                <?php foreach ($mensajes as $mensaje): ?>
                                    <?php if (strpos($mensaje, '✅') !== false): ?>
                                        <div class="bg-green-50 border-l-4 border-green-400 p-4">
                                            <div class="flex">
                                                <div class="flex-shrink-0">
                                                    <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                                    </svg>
                                                </div>
                                                <div class="ml-3">
                                                    <p class="text-sm text-green-700">
                                                        <?php echo $mensaje; ?>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <div class="bg-blue-50 border-l-4 border-blue-400 p-4">
                                            <div class="flex">
                                                <div class="flex-shrink-0">
                                                    <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h2a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                                    </svg>
                                                </div>
                                                <div class="ml-3">
                                                    <p class="text-sm text-blue-700">
                                                        <?php echo $mensaje; ?>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                            
                            <div class="mt-6 bg-blue-50 border-l-4 border-blue-400 p-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h2a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm text-blue-700">
                                            La estructura de la base de datos ha sido actualizada correctamente. Ahora puedes configurar los checkboxes con áreas de texto opcionales.
                                        </p>
                                        <div class="mt-4">
                                            <a href="checkboxes_personalizados.php" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded inline-flex items-center">
                                                <i class="fas fa-check-square mr-2"></i>
                                                Ir a la gestión de checkboxes
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pie de página -->
    <footer class="bg-white border-t border-gray-200 py-4 mt-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <p class="text-center text-sm text-gray-500">
                &copy; <?php echo date('Y'); ?> Sistema de Reservas. Todos los derechos reservados.
            </p>
        </div>
    </footer>
</body>
</html>
