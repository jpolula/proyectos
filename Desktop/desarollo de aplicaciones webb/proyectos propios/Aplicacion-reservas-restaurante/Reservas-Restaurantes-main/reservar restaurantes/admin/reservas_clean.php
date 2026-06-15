<?php
/**
 * Página de gestión de reservas simplificada
 * Esta versión mantiene solo la funcionalidad esencial para listar y gestionar reservas
 */

// Incluir archivos necesarios
require_once 'auth.php';
require_once '../vendor/autoload.php';
require_once '../enviar_correo_directo.php';

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

// Procesar acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['accion'])) {
        $accion = $_POST['accion'];
        $reserva_id = isset($_POST['reserva_id']) ? (int)$_POST['reserva_id'] : 0;
        
        if ($accion === 'modificar') {
            // Obtener datos del formulario
            $fecha = isset($_POST['fecha']) ? $_POST['fecha'] : '';
            $hora = isset($_POST['hora']) ? $_POST['hora'] : '';
            $turno_id = isset($_POST['turno_id']) ? (int)$_POST['turno_id'] : 0;
            $zona = isset($_POST['zona']) ? $_POST['zona'] : '';
            $cantidad_personas = isset($_POST['cantidad_personas']) ? (int)$_POST['cantidad_personas'] : 0;
            $observaciones = isset($_POST['observaciones']) ? $_POST['observaciones'] : '';
            $necesidades_especiales = isset($_POST['necesidades_especiales']) ? $_POST['necesidades_especiales'] : '';
            $tiene_alergenos = isset($_POST['tiene_alergenos']) ? 1 : 0;
            
            // Validar fecha
            $fecha_obj = DateTime::createFromFormat('Y-m-d', $fecha);
            $fecha_bd = $fecha_obj ? $fecha_obj->format('Y-m-d') : $fecha;
            
            // Actualizar reserva
            $stmt = $pdo->prepare("
                UPDATE reservas 
                SET fecha = ?, hora = ?, turno_id = ?, zona = ?, 
                    cantidad_personas = ?, observaciones = ?, 
                    necesidades_especiales = ?, tiene_alergenos = ?
                WHERE id = ?
            ");
            
            if ($stmt->execute([
                $fecha_bd, $hora, $turno_id, $zona, $cantidad_personas, 
                $observaciones, $necesidades_especiales, $tiene_alergenos, $reserva_id
            ])) {
                $mensaje = 'Reserva modificada correctamente.';
                $tipo_mensaje = 'success';
            } else {
                $mensaje = 'Error al modificar la reserva.';
                $tipo_mensaje = 'error';
            }
        } elseif ($accion === 'confirmar') {
            // Actualizar el estado de la reserva a confirmada
            $stmt = $pdo->prepare("UPDATE reservas SET estado = 'confirmada' WHERE id = ?");
            
            if ($stmt->execute([$reserva_id])) {
                $mensaje = 'Reserva confirmada correctamente.';
                $tipo_mensaje = 'success';
            } else {
                $mensaje = 'Error al confirmar la reserva.';
                $tipo_mensaje = 'error';
            }
        } elseif ($accion === 'rechazar') {
            // Redirigir a la página de denegación independiente
            header("Location: denegar.php?id=$reserva_id");
            exit;
        } elseif ($accion === 'eliminar') {
            // Eliminar la reserva
            $stmt = $pdo->prepare("DELETE FROM reservas WHERE id = ?");
            if ($stmt->execute([$reserva_id])) {
                $mensaje = 'Reserva eliminada correctamente.';
                $tipo_mensaje = 'success';
            } else {
                $mensaje = 'Error al eliminar la reserva.';
                $tipo_mensaje = 'error';
            }
        }
    }
}

// Obtener todas las reservas
$stmt = $pdo->query("
    SELECT r.*, c.nombre, c.email, c.telefono, t.nombre AS turno_nombre,
           DATE_FORMAT(r.fecha, '%d/%m/%Y') AS fecha_formateada,
           TIME_FORMAT(r.hora, '%H:%i') AS hora_formateada
    FROM reservas r
    JOIN clientes c ON r.cliente_id = c.id
    JOIN turnos t ON r.turno_id = t.id
    ORDER BY r.fecha DESC, r.hora ASC
");
$reservas = $stmt->fetchAll();

// Obtener todos los turnos para el formulario de edición
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
                <h2 class="text-lg font-medium text-gray-900 mb-4">Filtrar Reservas</h2>
                <form method="GET" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label for="filtro_fecha" class="block text-sm font-medium text-gray-700 mb-1">Fecha</label>
                        <input type="date" id="filtro_fecha" name="filtro_fecha" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                    </div>
                    <div>
                        <label for="filtro_estado" class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                        <select id="filtro_estado" name="filtro_estado" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                            <option value="">Todos</option>
                            <option value="pendiente">Pendiente</option>
                            <option value="confirmada">Confirmada</option>
                            <option value="rechazada">Rechazada</option>
                        </select>
                    </div>
                    <div>
                        <label for="filtro_turno" class="block text-sm font-medium text-gray-700 mb-1">Turno</label>
                        <select id="filtro_turno" name="filtro_turno" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                            <option value="">Todos</option>
                            <?php foreach ($turnos as $turno): ?>
                                <option value="<?php echo $turno['id']; ?>"><?php echo htmlspecialchars($turno['nombre']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <i class="fas fa-search mr-2"></i> Filtrar
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Tabla de reservas -->
            <div class="bg-white shadow overflow-hidden sm:rounded-md overflow-x-auto">
                <?php if (count($reservas) > 0): ?>
                    <table class="min-w-full divide-y divide-gray-200 table-fixed">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-12">ID</th>
                                <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                                <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha/Hora</th>
                                <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Turno</th>
                                <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Zona</th>
                                <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Personas</th>
                                <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Detalles</th>
                                <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($reservas as $reserva): ?>
                                <tr>
                                    <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo $reserva['id']; ?>
                                    </td>
                                    <td class="px-3 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($reserva['nombre']); ?></div>
                                        <div class="text-sm text-gray-500"><?php echo htmlspecialchars($reserva['email']); ?></div>
                                        <div class="text-sm text-gray-500"><?php echo htmlspecialchars($reserva['telefono']); ?></div>
                                    </td>
                                    <td class="px-3 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900"><?php echo $reserva['fecha_formateada']; ?></div>
                                        <div class="text-sm text-gray-500"><?php echo $reserva['hora_formateada']; ?></div>
                                    </td>
                                    <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo ucfirst(htmlspecialchars($reserva['turno_nombre'])); ?>
                                    </td>
                                    <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo $reserva['zona'] === 'dentro' ? 'Interior' : 'Terraza'; ?>
                                    </td>
                                    <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo $reserva['cantidad_personas']; ?>
                                    </td>
                                    <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php
                                        // Mostrar observaciones si las hay
                                        if (!empty($reserva['observaciones'])) {
                                            echo '<div class="mb-1">';
                                            echo '<span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-sm font-medium bg-gray-100 text-gray-800 mr-1">';
                                            echo '<i class="fas fa-comment-alt mr-1"></i> Observaciones';
                                            echo '</span>';
                                            echo '</div>';
                                        }
                                        
                                        // Mostrar si tiene alérgenos
                                        if ($reserva['tiene_alergenos']) {
                                            echo '<div class="mb-1">';
                                            echo '<span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-sm font-medium bg-yellow-100 text-yellow-800 mr-1">';
                                            echo '<i class="fas fa-exclamation-triangle mr-1"></i> Alérgenos';
                                            echo '</span>';
                                            echo '</div>';
                                        }
                                        
                                        // Mostrar necesidades especiales si las hay
                                        if (!empty($reserva['necesidades_especiales'])) {
                                            echo '<div class="mb-1">';
                                            echo '<span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-sm font-medium bg-blue-100 text-blue-800 mr-1">';
                                            echo '<i class="fas fa-info-circle mr-1"></i> Necesidades especiales';
                                            echo '</span>';
                                            echo '</div>';
                                        }
                                        ?>
                                    </td>
                                    <td class="px-3 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php 
                                            if ($reserva['estado'] === 'confirmada') {
                                                echo 'bg-green-100 text-green-800';
                                            } elseif ($reserva['estado'] === 'rechazada') {
                                                echo 'bg-red-100 text-red-800';
                                            } else {
                                                echo 'bg-yellow-100 text-yellow-800';
                                            }
                                        ?>">
                                            <?php echo ucfirst($reserva['estado']); ?>
                                        </span>
                                    </td>
                                    <td class="px-3 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="grid grid-cols-1 gap-2 min-w-[150px]">
                                            <!-- Botón Modificar -->
                                            <button type="button" 
                                                onclick="abrirModalEditar(
                                                    <?php echo $reserva['id']; ?>, 
                                                    '<?php echo $reserva['fecha']; ?>', 
                                                    '<?php echo $reserva['hora']; ?>', 
                                                    <?php echo $reserva['turno_id']; ?>, 
                                                    '<?php echo $reserva['zona']; ?>', 
                                                    <?php echo $reserva['cantidad_personas']; ?>, 
                                                    '<?php echo addslashes($reserva['observaciones']); ?>', 
                                                    '<?php echo addslashes($reserva['necesidades_especiales']); ?>', 
                                                    <?php echo $reserva['tiene_alergenos']; ?>
                                                )"
                                                class="inline-flex items-center justify-center px-3 py-1.5 border border-transparent text-xs font-medium rounded text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 w-full">
                                                <i class="fas fa-edit mr-1"></i> Modificar
                                            </button>
                                            
                                            <?php if ($reserva['estado'] === 'pendiente'): ?>
                                                <!-- Botón Confirmar -->
                                                <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="m-0">
                                                    <input type="hidden" name="accion" value="confirmar">
                                                    <input type="hidden" name="reserva_id" value="<?php echo $reserva['id']; ?>">
                                                    <button type="submit" class="inline-flex items-center justify-center px-3 py-1.5 border border-transparent text-xs font-medium rounded text-green-700 bg-green-100 hover:bg-green-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 w-full">
                                                        <i class="fas fa-check mr-1"></i> Confirmar
                                                    </button>
                                                </form>
                                                
                                                <!-- Botón Rechazar -->
                                                <a href="denegar.php?id=<?php echo $reserva['id']; ?>" class="inline-flex items-center justify-center px-3 py-1.5 border border-transparent text-xs font-medium rounded text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 w-full">
                                                    <i class="fas fa-times mr-1"></i> Rechazar
                                                </a>
                                            <?php endif; ?>
                                            
                                            <!-- Botón Eliminar -->
                                            <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="m-0" onsubmit="return confirm('¿Está seguro de que desea eliminar esta reserva? Esta acción no se puede deshacer.');">
                                                <input type="hidden" name="accion" value="eliminar">
                                                <input type="hidden" name="reserva_id" value="<?php echo $reserva['id']; ?>">
                                                <button type="submit" class="inline-flex items-center justify-center px-3 py-1.5 border border-transparent text-xs font-medium rounded text-gray-700 bg-gray-100 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 w-full">
                                                    <i class="fas fa-trash-alt mr-1"></i> Eliminar
                                                </button>
                                            </form>
                                        </div>
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
        </div>
    </div>

    <!-- Modal para editar reserva -->
    <div id="modalEditarReserva" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-lg shadow-xl max-w-3xl w-full max-h-screen overflow-y-auto">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900">Modificar Reserva</h3>
                    <button type="button" onclick="cerrarModal()" class="text-gray-400 hover:text-gray-500">
                        <span class="sr-only">Cerrar</span>
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            
            <form id="formEditarReserva" method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                <input type="hidden" name="accion" value="modificar">
                <input type="hidden" id="reserva_id" name="reserva_id" value="">
                
                <div class="px-6 py-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="fecha" class="block text-sm font-medium text-gray-700 mb-1">Fecha</label>
                            <input type="date" id="fecha" name="fecha" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                        </div>
                        <div>
                            <label for="hora" class="block text-sm font-medium text-gray-700 mb-1">Hora</label>
                            <input type="time" id="hora" name="hora" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                        </div>
                        <div>
                            <label for="turno_id" class="block text-sm font-medium text-gray-700 mb-1">Turno</label>
                            <select id="turno_id" name="turno_id" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                <?php foreach ($turnos as $turno): ?>
                                    <option value="<?php echo $turno['id']; ?>"><?php echo htmlspecialchars($turno['nombre']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label for="zona" class="block text-sm font-medium text-gray-700 mb-1">Zona</label>
                            <select id="zona" name="zona" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                <option value="dentro">Interior</option>
                                <option value="fuera">Terraza</option>
                            </select>
                        </div>
                        <div>
                            <label for="cantidad_personas" class="block text-sm font-medium text-gray-700 mb-1">Número de personas</label>
                            <input type="number" id="cantidad_personas" name="cantidad_personas" min="1" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md">
                        </div>
                        <div>
                            <label for="tiene_alergenos" class="flex items-center text-sm font-medium text-gray-700 mb-1">
                                <input type="checkbox" id="tiene_alergenos" name="tiene_alergenos" value="1" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded mr-2">
                                Tiene alérgenos
                            </label>
                        </div>
                        <div class="md:col-span-2">
                            <label for="observaciones" class="block text-sm font-medium text-gray-700 mb-1">Observaciones</label>
                            <textarea id="observaciones" name="observaciones" rows="3" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"></textarea>
                        </div>
                        <div class="md:col-span-2">
                            <label for="necesidades_especiales" class="block text-sm font-medium text-gray-700 mb-1">Necesidades especiales</label>
                            <textarea id="necesidades_especiales" name="necesidades_especiales" rows="3" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"></textarea>
                        </div>
                    </div>
                </div>
                
                <div class="px-6 py-4 bg-gray-50 text-right">
                    <button type="button" onclick="cerrarModal()" class="py-2 px-4 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 mr-2">
                        Cancelar
                    </button>
                    <button type="submit" class="py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Guardar cambios
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Obtener la fecha actual en formato YYYY-MM-DD
        const hoy = new Date().toISOString().split('T')[0];
        
        // Función para abrir el modal de edición
        function abrirModalEditar(id, fecha, hora, turno_id, zona, cantidad_personas, observaciones, necesidades_especiales, tiene_alergenos) {
            // Establecer los valores en el formulario
            document.getElementById('reserva_id').value = id;
            document.getElementById('fecha').value = fecha;
            document.getElementById('hora').value = hora;
            document.getElementById('turno_id').value = turno_id;
            document.getElementById('zona').value = zona;
            document.getElementById('cantidad_personas').value = cantidad_personas;
            document.getElementById('observaciones').value = observaciones;
            document.getElementById('necesidades_especiales').value = necesidades_especiales;
            document.getElementById('tiene_alergenos').checked = tiene_alergenos === 1;
            
            // Mostrar el modal
            document.getElementById('modalEditarReserva').classList.remove('hidden');
        }
        
        function cerrarModal() {
            document.getElementById('modalEditarReserva').classList.add('hidden');
        }
    </script>

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
