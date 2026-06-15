<?php
// Incluir archivo de autenticación
require_once 'auth.php';

// Configuración de la conexión a la base de datos
$host = 'localhost';
$db = 'restaurante_reservas';
$user = 'root';
$pass = '';

try {
    // Conectar a la base de datos
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Error de conexión: ' . $e->getMessage());
}

// Obtener la fecha seleccionada o usar la fecha actual
$fecha = isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d');

// Obtener los turnos
try {
    $stmt = $pdo->query("SELECT id, nombre FROM turnos ORDER BY id");
    $turnos = $stmt->fetchAll();
} catch (PDOException $e) {
    die('Error al obtener los turnos: ' . $e->getMessage());
}

// Obtener las zonas disponibles
$zonas = ['dentro' => 'Interior', 'fuera' => 'Terraza'];

// Mensaje para mostrar resultados de operaciones
$mensaje = '';
$tipo_mensaje = '';

// Procesar el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['accion'])) {
        $accion = $_POST['accion'];
        
        if ($accion === 'actualizar_disponibilidad') {
            try {
                $pdo->beginTransaction();
                
                $fecha_post = $_POST['fecha'] ?? date('Y-m-d');
                
                // Eliminar registros existentes para esta fecha
                $stmt = $pdo->prepare("DELETE FROM dias_disponibles WHERE fecha = ?");
                $stmt->execute([$fecha_post]);
                
                // Insertar nuevos registros
                $stmt = $pdo->prepare("
                    INSERT INTO dias_disponibles (fecha, zona, turno_id, disponible) 
                    VALUES (?, ?, ?, ?)
                ");
                
                foreach ($turnos as $turno) {
                    foreach (array_keys($zonas) as $zona) {
                        $disponible = isset($_POST["disponible_{$turno['id']}_{$zona}"]) ? 1 : 0;
                        $stmt->execute([$fecha_post, $zona, $turno['id'], $disponible]);
                    }
                }
                
                $pdo->commit();
                
                $mensaje = 'Disponibilidad actualizada correctamente.';
                $tipo_mensaje = 'success';
                
                // Actualizar la fecha seleccionada
                $fecha = $fecha_post;
            } catch (PDOException $e) {
                $pdo->rollBack();
                $mensaje = 'Error al actualizar la disponibilidad: ' . $e->getMessage();
                $tipo_mensaje = 'error';
            }
        } elseif ($accion === 'actualizar_capacidad') {
            try {
                $pdo->beginTransaction();
                
                $fecha_post = $_POST['fecha'] ?? date('Y-m-d');
                
                // Eliminar registros existentes para esta fecha
                $stmt = $pdo->prepare("DELETE FROM capacidad WHERE fecha = ?");
                $stmt->execute([$fecha_post]);
                
                // Insertar nuevos registros
                $stmt = $pdo->prepare("
                    INSERT INTO capacidad (fecha, zona, turno_id, aforo_maximo) 
                    VALUES (?, ?, ?, ?)
                ");
                
                foreach ($turnos as $turno) {
                    foreach (array_keys($zonas) as $zona) {
                        $aforo = isset($_POST["aforo_{$turno['id']}_{$zona}"]) ? (int)$_POST["aforo_{$turno['id']}_{$zona}"] : 0;
                        if ($aforo > 0) {
                            $stmt->execute([$fecha_post, $zona, $turno['id'], $aforo]);
                        }
                    }
                }
                
                $pdo->commit();
                
                $mensaje = 'Capacidad actualizada correctamente.';
                $tipo_mensaje = 'success';
                
                // Actualizar la fecha seleccionada
                $fecha = $fecha_post;
            } catch (PDOException $e) {
                $pdo->rollBack();
                $mensaje = 'Error al actualizar la capacidad: ' . $e->getMessage();
                $tipo_mensaje = 'error';
            }
        }
    }
}

// Obtener la disponibilidad actual para la fecha seleccionada
$disponibilidad = [];
try {
    $stmt = $pdo->prepare("
        SELECT zona, turno_id, disponible 
        FROM dias_disponibles 
        WHERE fecha = ?
    ");
    $stmt->execute([$fecha]);
    
    while ($row = $stmt->fetch()) {
        $disponibilidad[$row['turno_id']][$row['zona']] = $row['disponible'];
    }
} catch (PDOException $e) {
    // Si no hay registros, no mostrar error
}

// Obtener la capacidad actual para la fecha seleccionada
$capacidad = [];
try {
    $stmt = $pdo->prepare("
        SELECT zona, turno_id, aforo_maximo 
        FROM capacidad 
        WHERE fecha = ?
    ");
    $stmt->execute([$fecha]);
    
    while ($row = $stmt->fetch()) {
        $capacidad[$row['turno_id']][$row['zona']] = $row['aforo_maximo'];
    }
} catch (PDOException $e) {
    // Si no hay registros, no mostrar error
}

// Título de la página
$pageTitle = 'Gestión de Disponibilidad';
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
    <!-- Flatpickr para selección de fechas -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
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
            
            <!-- Selector de Fecha -->
            <div class="bg-white p-4 rounded-md shadow-md mb-6">
                <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="GET" class="flex items-end space-x-4">
                    <div class="flex-grow">
                        <label for="fecha" class="block text-sm font-medium text-gray-700 mb-1">Seleccionar Fecha</label>
                        <input type="text" id="fecha_selector" name="fecha" class="w-full p-2 border border-gray-300 rounded-md" value="<?php echo $fecha; ?>">
                    </div>
                    <div>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition duration-300">
                            Ver Disponibilidad
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Pestañas -->
            <div class="mb-4">
                <div class="border-b border-gray-200">
                    <nav class="-mb-px flex">
                        <a href="#disponibilidad" id="tab-disponibilidad" class="border-blue-500 text-blue-600 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm mr-8">
                            Disponibilidad
                        </a>
                        <a href="#capacidad" id="tab-capacidad" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            Capacidad
                        </a>
                    </nav>
                </div>
            </div>
            
            <!-- Formulario de Disponibilidad -->
            <div id="panel-disponibilidad" class="bg-white shadow overflow-hidden sm:rounded-lg">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        Configuración de Disponibilidad para <?php echo date('d/m/Y', strtotime($fecha)); ?>
                    </h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500">
                        Define qué turnos y zonas están disponibles para reservas en esta fecha.
                    </p>
                </div>
                <div class="border-t border-gray-200">
                    <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="p-6">
                        <input type="hidden" name="accion" value="actualizar_disponibilidad">
                        <input type="hidden" name="fecha" value="<?php echo $fecha; ?>">
                        
                        <div class="grid grid-cols-1 gap-6">
                            <?php foreach ($turnos as $turno): ?>
                                <div class="bg-gray-50 p-4 rounded-md">
                                    <h4 class="text-md font-medium text-gray-900 mb-4">
                                        Turno: <?php echo ucfirst($turno['nombre']); ?>
                                    </h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <?php foreach ($zonas as $zona_key => $zona_nombre): ?>
                                            <div class="flex items-start">
                                                <div class="flex items-center h-5">
                                                    <input id="disponible_<?php echo $turno['id']; ?>_<?php echo $zona_key; ?>" 
                                                           name="disponible_<?php echo $turno['id']; ?>_<?php echo $zona_key; ?>" 
                                                           type="checkbox" 
                                                           class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                                           <?php echo (isset($disponibilidad[$turno['id']][$zona_key]) && $disponibilidad[$turno['id']][$zona_key]) ? 'checked' : ''; ?>>
                                                </div>
                                                <div class="ml-3 text-sm">
                                                    <label for="disponible_<?php echo $turno['id']; ?>_<?php echo $zona_key; ?>" class="font-medium text-gray-700">
                                                        <?php echo $zona_nombre; ?>
                                                    </label>
                                                    <p class="text-gray-500">Marcar como disponible para reservas</p>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            
                            <div class="pt-5">
                                <div class="flex justify-end">
                                    <button type="submit" class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        Guardar Disponibilidad
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Formulario de Capacidad -->
            <div id="panel-capacidad" class="bg-white shadow overflow-hidden sm:rounded-lg hidden">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        Configuración de Capacidad para <?php echo date('d/m/Y', strtotime($fecha)); ?>
                    </h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500">
                        Define la capacidad máxima para cada turno y zona en esta fecha.
                    </p>
                </div>
                <div class="border-t border-gray-200">
                    <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="p-6">
                        <input type="hidden" name="accion" value="actualizar_capacidad">
                        <input type="hidden" name="fecha" value="<?php echo $fecha; ?>">
                        
                        <div class="grid grid-cols-1 gap-6">
                            <?php foreach ($turnos as $turno): ?>
                                <div class="bg-gray-50 p-4 rounded-md">
                                    <h4 class="text-md font-medium text-gray-900 mb-4">
                                        Turno: <?php echo ucfirst($turno['nombre']); ?>
                                    </h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <?php foreach ($zonas as $zona_key => $zona_nombre): ?>
                                            <div>
                                                <label for="aforo_<?php echo $turno['id']; ?>_<?php echo $zona_key; ?>" class="block text-sm font-medium text-gray-700">
                                                    Aforo máximo - <?php echo $zona_nombre; ?>
                                                </label>
                                                <div class="mt-1">
                                                    <input type="number" 
                                                           id="aforo_<?php echo $turno['id']; ?>_<?php echo $zona_key; ?>" 
                                                           name="aforo_<?php echo $turno['id']; ?>_<?php echo $zona_key; ?>" 
                                                           class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md p-2 border"
                                                           value="<?php echo isset($capacidad[$turno['id']][$zona_key]) ? $capacidad[$turno['id']][$zona_key] : 0; ?>" 
                                                           min="0">
                                                </div>
                                                <p class="mt-1 text-xs text-gray-500">
                                                    Número máximo de personas permitidas.
                                                </p>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            
                            <div class="pt-5">
                                <div class="flex justify-end">
                                    <button type="submit" class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        Guardar Capacidad
                                    </button>
                                </div>
                            </div>
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

    <script>
        // Inicializar Flatpickr para el selector de fecha
        flatpickr("#fecha_selector", {
            dateFormat: "Y-m-d",
            locale: "es",
            allowInput: true
        });
        
        // Gestión de pestañas
        document.getElementById('tab-disponibilidad').addEventListener('click', function(e) {
            e.preventDefault();
            document.getElementById('panel-disponibilidad').classList.remove('hidden');
            document.getElementById('panel-capacidad').classList.add('hidden');
            document.getElementById('tab-disponibilidad').classList.add('border-blue-500', 'text-blue-600');
            document.getElementById('tab-disponibilidad').classList.remove('border-transparent', 'text-gray-500');
            document.getElementById('tab-capacidad').classList.remove('border-blue-500', 'text-blue-600');
            document.getElementById('tab-capacidad').classList.add('border-transparent', 'text-gray-500');
        });
        
        document.getElementById('tab-capacidad').addEventListener('click', function(e) {
            e.preventDefault();
            document.getElementById('panel-capacidad').classList.remove('hidden');
            document.getElementById('panel-disponibilidad').classList.add('hidden');
            document.getElementById('tab-capacidad').classList.add('border-blue-500', 'text-blue-600');
            document.getElementById('tab-capacidad').classList.remove('border-transparent', 'text-gray-500');
            document.getElementById('tab-disponibilidad').classList.remove('border-blue-500', 'text-blue-600');
            document.getElementById('tab-disponibilidad').classList.add('border-transparent', 'text-gray-500');
        });
    </script>
</body>
</html>
