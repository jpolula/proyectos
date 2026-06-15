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

// Obtener los turnos actuales
try {
    $stmt = $pdo->query("SELECT * FROM turnos ORDER BY id");
    $turnos = $stmt->fetchAll();
} catch (PDOException $e) {
    die('Error al obtener los turnos: ' . $e->getMessage());
}

// Procesar el formulario
$mensaje = '';
$tipo_mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['accion']) && $_POST['accion'] === 'actualizar') {
        $errores = false;
        
        foreach ($turnos as $turno) {
            $id = $turno['id'];
            $hora_inicio = $_POST["hora_inicio_$id"] ?? '';
            $hora_fin = $_POST["hora_fin_$id"] ?? '';
            
            if (empty($hora_inicio) || empty($hora_fin)) {
                $mensaje = 'Todos los campos son obligatorios.';
                $tipo_mensaje = 'error';
                $errores = true;
                break;
            }
            
            // Validar formato de hora
            if (!preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $hora_inicio) || 
                !preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $hora_fin)) {
                $mensaje = 'El formato de hora debe ser HH:MM (24h).';
                $tipo_mensaje = 'error';
                $errores = true;
                break;
            }
            
            // Validar que hora_fin sea posterior a hora_inicio
            if (strtotime($hora_fin) <= strtotime($hora_inicio)) {
                $mensaje = 'La hora de fin debe ser posterior a la hora de inicio.';
                $tipo_mensaje = 'error';
                $errores = true;
                break;
            }
        }
        
        if (!$errores) {
            try {
                $pdo->beginTransaction();
                
                foreach ($turnos as $turno) {
                    $id = $turno['id'];
                    $hora_inicio = $_POST["hora_inicio_$id"];
                    $hora_fin = $_POST["hora_fin_$id"];
                    
                    $stmt = $pdo->prepare("
                        UPDATE turnos 
                        SET hora_inicio = ?, hora_fin = ? 
                        WHERE id = ?
                    ");
                    $stmt->execute([$hora_inicio, $hora_fin, $id]);
                }
                
                $pdo->commit();
                
                $mensaje = 'Horarios de turnos actualizados correctamente.';
                $tipo_mensaje = 'success';
                
                // Actualizar la lista de turnos
                $stmt = $pdo->query("SELECT * FROM turnos ORDER BY id");
                $turnos = $stmt->fetchAll();
            } catch (PDOException $e) {
                $pdo->rollBack();
                $mensaje = 'Error al actualizar los turnos: ' . $e->getMessage();
                $tipo_mensaje = 'error';
            }
        }
    }
}

// Título de la página
$pageTitle = 'Gestión de Turnos';
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
            
            <!-- Formulario de Turnos -->
            <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        Configuración de Horarios de Turnos
                    </h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500">
                        Define los horarios de inicio y fin para cada turno del restaurante.
                    </p>
                </div>
                <div class="border-t border-gray-200">
                    <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="p-6">
                        <input type="hidden" name="accion" value="actualizar">
                        
                        <div class="grid grid-cols-1 gap-6">
                            <?php foreach ($turnos as $turno): ?>
                                <div class="bg-gray-50 p-4 rounded-md">
                                    <h4 class="text-md font-medium text-gray-900 mb-4">
                                        Turno: <?php echo ucfirst($turno['nombre']); ?>
                                    </h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label for="hora_inicio_<?php echo $turno['id']; ?>" class="block text-sm font-medium text-gray-700">
                                                Hora de inicio
                                            </label>
                                            <div class="mt-1">
                                                <input type="text" name="hora_inicio_<?php echo $turno['id']; ?>" id="hora_inicio_<?php echo $turno['id']; ?>" 
                                                       class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md p-2 border"
                                                       value="<?php echo $turno['hora_inicio']; ?>" placeholder="HH:MM" required>
                                            </div>
                                            <p class="mt-1 text-xs text-gray-500">
                                                Formato: HH:MM (24h)
                                            </p>
                                        </div>
                                        <div>
                                            <label for="hora_fin_<?php echo $turno['id']; ?>" class="block text-sm font-medium text-gray-700">
                                                Hora de fin
                                            </label>
                                            <div class="mt-1">
                                                <input type="text" name="hora_fin_<?php echo $turno['id']; ?>" id="hora_fin_<?php echo $turno['id']; ?>" 
                                                       class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md p-2 border"
                                                       value="<?php echo $turno['hora_fin']; ?>" placeholder="HH:MM" required>
                                            </div>
                                            <p class="mt-1 text-xs text-gray-500">
                                                Formato: HH:MM (24h)
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            
                            <div class="pt-5">
                                <div class="flex justify-end">
                                    <button type="submit" class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        Guardar Cambios
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
</body>
</html>
