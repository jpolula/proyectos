<?php
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

// Conexión a la base de datos
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die('Error de conexión: ' . $e->getMessage());
}

// Obtener la configuración actual
try {
    $stmt = $pdo->query("SELECT * FROM configuracion WHERE id = 1");
    $config = $stmt->fetch();
    
    if (!$config) {
        // Si no existe, crear una configuración por defecto
        $stmt = $pdo->prepare("
            INSERT INTO configuracion (id, max_personas_sin_aprobacion, capacidad_dentro_mediodia, capacidad_fuera_mediodia, capacidad_dentro_noche, capacidad_fuera_noche) 
            VALUES (1, 4, 30, 20, 30, 20)
        ");
        $stmt->execute();
        
        $config = [
            'id' => 1,
            'max_personas_sin_aprobacion' => 4,
            'capacidad_dentro_mediodia' => 30,
            'capacidad_fuera_mediodia' => 20,
            'capacidad_dentro_noche' => 30,
            'capacidad_fuera_noche' => 20
        ];
    }
} catch (PDOException $e) {
    die('Error al obtener la configuración: ' . $e->getMessage());
}

// Procesar el formulario
$mensaje = '';
$tipo_mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['accion']) && $_POST['accion'] === 'actualizar') {
        $max_personas = isset($_POST['max_personas_sin_aprobacion']) ? (int)$_POST['max_personas_sin_aprobacion'] : 4;
        $capacidad_dentro_mediodia = isset($_POST['capacidad_dentro_mediodia']) ? (int)$_POST['capacidad_dentro_mediodia'] : 30;
        $capacidad_fuera_mediodia = isset($_POST['capacidad_fuera_mediodia']) ? (int)$_POST['capacidad_fuera_mediodia'] : 20;
        $capacidad_dentro_noche = isset($_POST['capacidad_dentro_noche']) ? (int)$_POST['capacidad_dentro_noche'] : 30;
        $capacidad_fuera_noche = isset($_POST['capacidad_fuera_noche']) ? (int)$_POST['capacidad_fuera_noche'] : 20;
        $url_redireccion_reserva = isset($_POST['url_redireccion_reserva']) ? trim($_POST['url_redireccion_reserva']) : 'reserva_exitosa.php';
        $eliminar_capacidades = isset($_POST['eliminar_capacidades']) ? (int)$_POST['eliminar_capacidades'] : 0;
        $configurar_dias = isset($_POST['configurar_dias']) ? (int)$_POST['configurar_dias'] : 0;
        $eliminar_bloqueos = isset($_POST['eliminar_bloqueos']) ? (int)$_POST['eliminar_bloqueos'] : 0;
        
        // Validar datos
        if ($max_personas < 1) {
            $mensaje = 'El número máximo de personas sin aprobación debe ser al menos 1.';
            $tipo_mensaje = 'error';
        } elseif ($capacidad_dentro_mediodia < 0 || $capacidad_fuera_mediodia < 0 || 
                  $capacidad_dentro_noche < 0 || $capacidad_fuera_noche < 0) {
            $mensaje = 'La capacidad debe ser un número positivo.';
            $tipo_mensaje = 'error';
        } else {
            // Iniciar transacción para asegurar que todas las operaciones se completen o ninguna
            $pdo->beginTransaction();
            
            try {
                // 1. Actualizar la configuración global
                $stmt = $pdo->prepare("
                    UPDATE configuracion 
                    SET max_personas_sin_aprobacion = ?,
                        capacidad_dentro_mediodia = ?,
                        capacidad_fuera_mediodia = ?,
                        capacidad_dentro_noche = ?,
                        capacidad_fuera_noche = ?,
                        url_redireccion_reserva = ?
                    WHERE id = 1
                ");
                $stmt->execute([
                    $max_personas,
                    $capacidad_dentro_mediodia,
                    $capacidad_fuera_mediodia,
                    $capacidad_dentro_noche,
                    $capacidad_fuera_noche,
                    $url_redireccion_reserva
                ]);
                
                $mensaje = 'Configuración actualizada correctamente.';
                $tipo_mensaje = 'success';
                
                // 2. Eliminar configuraciones específicas de capacidad si se solicitó
                if ($eliminar_capacidades) {
                    $stmt = $pdo->query("DELETE FROM capacidad");
                    $mensaje .= ' Se han eliminado todas las configuraciones específicas de capacidad.';
                }
                
                // 3. Configurar días no disponibles si se solicitó
                if ($configurar_dias) {
                    // Obtener fechas de reservas futuras
                    $stmt = $pdo->query("
                        SELECT DISTINCT fecha 
                        FROM reservas 
                        WHERE fecha >= CURDATE()
                        ORDER BY fecha
                    ");
                    $fechas_reservas = $stmt->fetchAll(PDO::FETCH_COLUMN);
                    
                    // Obtener todos los turnos
                    $stmt = $pdo->query("SELECT id FROM turnos");
                    $turnos = $stmt->fetchAll(PDO::FETCH_COLUMN);
                    
                    // Zonas disponibles
                    $zonas = ['dentro', 'fuera'];
                    
                    // Configurar cada día
                    foreach ($fechas_reservas as $fecha) {
                        foreach ($turnos as $turno_id) {
                            foreach ($zonas as $zona) {
                                // Verificar si ya existe la configuración
                                $stmt = $pdo->prepare("
                                    SELECT COUNT(*) 
                                    FROM dias_disponibles 
                                    WHERE fecha = ? AND turno_id = ? AND zona = ?
                                ");
                                $stmt->execute([$fecha, $turno_id, $zona]);
                                $existe = $stmt->fetchColumn() > 0;
                                
                                if ($existe) {
                                    // Actualizar a disponible
                                    $stmt = $pdo->prepare("
                                        UPDATE dias_disponibles
                                        SET disponible = 1
                                        WHERE fecha = ? AND turno_id = ? AND zona = ?
                                    ");
                                    $stmt->execute([$fecha, $turno_id, $zona]);
                                } else {
                                    // Insertar como disponible
                                    $stmt = $pdo->prepare("
                                        INSERT INTO dias_disponibles (fecha, turno_id, zona, disponible)
                                        VALUES (?, ?, ?, 1)
                                    ");
                                    $stmt->execute([$fecha, $turno_id, $zona]);
                                }
                            }
                        }
                    }
                    
                    // Configurar específicamente el día 8 de mayo de 2025
                    $fecha_especial = '2025-05-08';
                    foreach ($turnos as $turno_id) {
                        foreach ($zonas as $zona) {
                            // Verificar si ya existe la configuración
                            $stmt = $pdo->prepare("
                                SELECT COUNT(*) 
                                FROM dias_disponibles 
                                WHERE fecha = ? AND turno_id = ? AND zona = ?
                            ");
                            $stmt->execute([$fecha_especial, $turno_id, $zona]);
                            $existe = $stmt->fetchColumn() > 0;
                            
                            if ($existe) {
                                // Actualizar a disponible
                                $stmt = $pdo->prepare("
                                    UPDATE dias_disponibles
                                    SET disponible = 1
                                    WHERE fecha = ? AND turno_id = ? AND zona = ?
                                ");
                                $stmt->execute([$fecha_especial, $turno_id, $zona]);
                            } else {
                                // Insertar como disponible
                                $stmt = $pdo->prepare("
                                    INSERT INTO dias_disponibles (fecha, turno_id, zona, disponible)
                                    VALUES (?, ?, ?, 1)
                                ");
                                $stmt->execute([$fecha_especial, $turno_id, $zona]);
                            }
                        }
                    }
                    
                    $mensaje .= ' Se han configurado automáticamente todos los días con reservas como disponibles.';
                }
                
                // 4. Eliminar bloqueos si se solicitó
                if ($eliminar_bloqueos) {
                    $stmt = $pdo->query("DELETE FROM bloqueos");
                    $mensaje .= ' Se han eliminado todos los bloqueos.';
                }
                
                // Confirmar todas las operaciones
                $pdo->commit();
                
                // Actualizar la configuración en la variable
                $config['max_personas_sin_aprobacion'] = $max_personas;
                $config['capacidad_dentro_mediodia'] = $capacidad_dentro_mediodia;
                $config['capacidad_fuera_mediodia'] = $capacidad_fuera_mediodia;
                $config['capacidad_dentro_noche'] = $capacidad_dentro_noche;
                $config['capacidad_fuera_noche'] = $capacidad_fuera_noche;
                $config['url_redireccion_reserva'] = $url_redireccion_reserva;
                
            } catch (PDOException $e) {
                // Si hay algún error, revertir todas las operaciones
                $pdo->rollBack();
                $mensaje = 'Error al actualizar la configuración: ' . $e->getMessage();
                $tipo_mensaje = 'error';
            }
        }
    }
}

// Título de la página
$pageTitle = 'Configuración General';
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
                    <a href="index.php" class="text-xl font-bold">Sistema de Reservas</a>
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
                <div class="mb-4 p-4 rounded-md <?php echo $tipo_mensaje === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                    <?php echo $mensaje; ?>
                </div>
            <?php endif; ?>
            
            <!-- Formulario de Configuración -->
            <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        Configuración del Sistema
                    </h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500">
                        Ajusta los parámetros generales del sistema de reservas.
                    </p>
                </div>
                <div class="border-t border-gray-200">
                    <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="p-6">
                        <input type="hidden" name="accion" value="actualizar">
                        
                        <div class="mb-6">
                            <label for="max_personas_sin_aprobacion" class="block text-sm font-medium text-gray-700">
                                Número máximo de personas sin aprobación
                            </label>
                            <div class="mt-1">
                                <input type="number" name="max_personas_sin_aprobacion" id="max_personas_sin_aprobacion" 
                                       class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md p-2 border"
                                       value="<?php echo $config['max_personas_sin_aprobacion']; ?>" min="1" required>
                            </div>
                            <p class="mt-1 text-sm text-gray-500">
                                Las reservas para más de este número de personas requerirán aprobación manual.
                            </p>
                        </div>
                        
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Capacidad del Restaurante</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Turno Mediodía -->
                                <div class="bg-gray-50 p-4 rounded-md">
                                    <h4 class="text-md font-medium text-gray-900 mb-4">Turno de Mediodía</h4>
                                    
                                    <div class="mb-4">
                                        <label for="capacidad_dentro_mediodia" class="block text-sm font-medium text-gray-700">
                                            Capacidad Interior
                                        </label>
                                        <div class="mt-1">
                                            <input type="number" name="capacidad_dentro_mediodia" id="capacidad_dentro_mediodia" 
                                                   class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md p-2 border"
                                                   value="<?php echo isset($config['capacidad_dentro_mediodia']) ? $config['capacidad_dentro_mediodia'] : 30; ?>" min="0" required>
                                        </div>
                                        <p class="mt-1 text-xs text-gray-500">
                                            Número máximo de personas en el interior durante el mediodía.
                                        </p>
                                    </div>
                                    
                                    <div>
                                        <label for="capacidad_fuera_mediodia" class="block text-sm font-medium text-gray-700">
                                            Capacidad Terraza
                                        </label>
                                        <div class="mt-1">
                                            <input type="number" name="capacidad_fuera_mediodia" id="capacidad_fuera_mediodia" 
                                                   class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md p-2 border"
                                                   value="<?php echo isset($config['capacidad_fuera_mediodia']) ? $config['capacidad_fuera_mediodia'] : 20; ?>" min="0" required>
                                        </div>
                                        <p class="mt-1 text-xs text-gray-500">
                                            Número máximo de personas en la terraza durante el mediodía.
                                        </p>
                                    </div>
                                </div>
                                
                                <!-- Turno Noche -->
                                <div class="bg-gray-50 p-4 rounded-md">
                                    <h4 class="text-md font-medium text-gray-900 mb-4">Turno de Noche</h4>
                                    
                                    <div class="mb-4">
                                        <label for="capacidad_dentro_noche" class="block text-sm font-medium text-gray-700">
                                            Capacidad Interior
                                        </label>
                                        <div class="mt-1">
                                            <input type="number" name="capacidad_dentro_noche" id="capacidad_dentro_noche" 
                                                   class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md p-2 border"
                                                   value="<?php echo isset($config['capacidad_dentro_noche']) ? $config['capacidad_dentro_noche'] : 30; ?>" min="0" required>
                                        </div>
                                        <p class="mt-1 text-xs text-gray-500">
                                            Número máximo de personas en el interior durante la noche.
                                        </p>
                                    </div>
                                    
                                    <div>
                                        <label for="capacidad_fuera_noche" class="block text-sm font-medium text-gray-700">
                                            Capacidad Terraza
                                        </label>
                                        <div class="mt-1">
                                            <input type="number" name="capacidad_fuera_noche" id="capacidad_fuera_noche" 
                                                   class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md p-2 border"
                                                   value="<?php echo isset($config['capacidad_fuera_noche']) ? $config['capacidad_fuera_noche'] : 20; ?>" min="0" required>
                                        </div>
                                        <p class="mt-1 text-xs text-gray-500">
                                            Número máximo de personas en la terraza durante la noche.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Redirección tras Reserva</h3>
                            <div class="mb-4">
                                <label for="url_redireccion_reserva" class="block text-sm font-medium text-gray-700">
                                    URL para el botón "Volver al inicio"
                                </label>
                                <div class="mt-1">
                                    <input type="text" name="url_redireccion_reserva" id="url_redireccion_reserva" 
                                           class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md" 
                                           value="<?php echo htmlspecialchars($config['url_redireccion_reserva'] ?? 'reserva_exitosa.php'); ?>">
                                </div>
                                <p class="mt-2 text-sm text-gray-500">URL que se usará para el botón "Volver al inicio" en la página de reserva exitosa. Por defecto es 'index.php'.</p>
                            </div>
                        </div>

                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Opciones Avanzadas</h3>
                            
                            <div class="bg-gray-50 p-4 rounded-md">
                                <div class="mb-4">
                                    <label for="eliminar_capacidades" class="block text-sm font-medium text-gray-700">
                                        Eliminar configuraciones específicas de días
                                    </label>
                                    <div class="mt-1">
                                        <select name="eliminar_capacidades" id="eliminar_capacidades" 
                                               class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md p-2 border">
                                            <option value="0">No, mantener configuraciones específicas</option>
                                            <option value="1">Sí, eliminar todas las configuraciones específicas</option>
                                        </select>
                                    </div>
                                    <p class="mt-1 text-xs text-gray-500">
                                        Si selecciona "Sí", se eliminarán todas las configuraciones específicas de capacidad para días individuales.
                                    </p>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="configurar_dias" class="block text-sm font-medium text-gray-700">
                                        Configurar automáticamente días no disponibles
                                    </label>
                                    <div class="mt-1">
                                        <select name="configurar_dias" id="configurar_dias" 
                                               class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md p-2 border">
                                            <option value="0">No, mantener configuración actual</option>
                                            <option value="1">Sí, configurar todos los días como disponibles</option>
                                        </select>
                                    </div>
                                    <p class="mt-1 text-xs text-gray-500">
                                        Si selecciona "Sí", se configurarán automáticamente todos los días con reservas como disponibles.
                                    </p>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="eliminar_bloqueos" class="block text-sm font-medium text-gray-700">
                                        Eliminar bloqueos existentes
                                    </label>
                                    <div class="mt-1">
                                        <select name="eliminar_bloqueos" id="eliminar_bloqueos" 
                                               class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md p-2 border">
                                            <option value="0">No, mantener bloqueos</option>
                                            <option value="1">Sí, eliminar todos los bloqueos</option>
                                        </select>
                                    </div>
                                    <p class="mt-1 text-xs text-gray-500">
                                        Si selecciona "Sí", se eliminarán todos los bloqueos existentes.
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-6 flex justify-end">
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition duration-300">
                                Guardar Configuración
                            </button>
                        </div>
                    </form>
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
