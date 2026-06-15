<?php
// Página de gestión de avisos personalizados
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

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

// Conectar a la base de datos
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

// Procesar formularios
$mensaje = '';
$tipo_mensaje = '';

// Añadir nuevo aviso
if (isset($_POST['accion']) && $_POST['accion'] === 'nuevo') {
    if (!empty($_POST['texto'])) {
        try {
            // Obtener el orden máximo actual
            $stmt = $pdo->query("SELECT MAX(orden) as max_orden FROM avisos_reserva");
            $resultado = $stmt->fetch();
            $nuevo_orden = ($resultado['max_orden'] ?? 0) + 1;
            
            // Insertar el nuevo aviso
            $stmt = $pdo->prepare("INSERT INTO avisos_reserva (texto, orden) VALUES (?, ?)");
            $stmt->execute([$_POST['texto'], $nuevo_orden]);
            
            $mensaje = "Aviso añadido correctamente.";
            $tipo_mensaje = "success";
        } catch (PDOException $e) {
            $mensaje = "Error al añadir el aviso: " . $e->getMessage();
            $tipo_mensaje = "error";
        }
    } else {
        $mensaje = "El texto del aviso no puede estar vacío.";
        $tipo_mensaje = "error";
    }
}

// Editar aviso existente
if (isset($_POST['accion']) && $_POST['accion'] === 'editar') {
    if (!empty($_POST['texto']) && isset($_POST['id'])) {
        try {
            $stmt = $pdo->prepare("UPDATE avisos_reserva SET texto = ? WHERE id = ?");
            $stmt->execute([$_POST['texto'], $_POST['id']]);
            
            $mensaje = "Aviso actualizado correctamente.";
            $tipo_mensaje = "success";
        } catch (PDOException $e) {
            $mensaje = "Error al actualizar el aviso: " . $e->getMessage();
            $tipo_mensaje = "error";
        }
    } else {
        $mensaje = "El texto del aviso no puede estar vacío o falta el ID.";
        $tipo_mensaje = "error";
    }
}

// Cambiar estado (activar/desactivar)
if (isset($_POST['accion']) && $_POST['accion'] === 'cambiar_estado') {
    if (isset($_POST['id']) && isset($_POST['activo'])) {
        try {
            $nuevo_estado = $_POST['activo'] == 1 ? 0 : 1;
            $stmt = $pdo->prepare("UPDATE avisos_reserva SET activo = ? WHERE id = ?");
            $stmt->execute([$nuevo_estado, $_POST['id']]);
            
            $mensaje = "Estado del aviso actualizado correctamente.";
            $tipo_mensaje = "success";
        } catch (PDOException $e) {
            $mensaje = "Error al actualizar el estado: " . $e->getMessage();
            $tipo_mensaje = "error";
        }
    }
}

// Eliminar aviso
if (isset($_POST['accion']) && $_POST['accion'] === 'eliminar') {
    if (isset($_POST['id'])) {
        try {
            $stmt = $pdo->prepare("DELETE FROM avisos_reserva WHERE id = ?");
            $stmt->execute([$_POST['id']]);
            
            $mensaje = "Aviso eliminado correctamente.";
            $tipo_mensaje = "success";
        } catch (PDOException $e) {
            $mensaje = "Error al eliminar el aviso: " . $e->getMessage();
            $tipo_mensaje = "error";
        }
    }
}

// Cambiar orden (subir/bajar)
if (isset($_POST['accion']) && ($_POST['accion'] === 'subir' || $_POST['accion'] === 'bajar')) {
    if (isset($_POST['id'])) {
        try {
            // Obtener el aviso actual
            $stmt = $pdo->prepare("SELECT id, orden FROM avisos_reserva WHERE id = ?");
            $stmt->execute([$_POST['id']]);
            $aviso_actual = $stmt->fetch();
            
            if ($aviso_actual) {
                // Determinar el nuevo orden
                $nuevo_orden = $aviso_actual['orden'];
                if ($_POST['accion'] === 'subir') {
                    $stmt = $pdo->prepare("SELECT id, orden FROM avisos_reserva WHERE orden < ? ORDER BY orden DESC LIMIT 1");
                    $stmt->execute([$aviso_actual['orden']]);
                } else { // bajar
                    $stmt = $pdo->prepare("SELECT id, orden FROM avisos_reserva WHERE orden > ? ORDER BY orden ASC LIMIT 1");
                    $stmt->execute([$aviso_actual['orden']]);
                }
                
                $aviso_intercambio = $stmt->fetch();
                
                if ($aviso_intercambio) {
                    // Intercambiar órdenes
                    $pdo->beginTransaction();
                    
                    $stmt = $pdo->prepare("UPDATE avisos_reserva SET orden = ? WHERE id = ?");
                    $stmt->execute([$aviso_intercambio['orden'], $aviso_actual['id']]);
                    
                    $stmt = $pdo->prepare("UPDATE avisos_reserva SET orden = ? WHERE id = ?");
                    $stmt->execute([$aviso_actual['orden'], $aviso_intercambio['id']]);
                    
                    $pdo->commit();
                    
                    $mensaje = "Orden actualizado correctamente.";
                    $tipo_mensaje = "success";
                } else {
                    $mensaje = "No se puede cambiar más el orden.";
                    $tipo_mensaje = "info";
                }
            }
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $mensaje = "Error al cambiar el orden: " . $e->getMessage();
            $tipo_mensaje = "error";
        }
    }
}

// Obtener todos los avisos
try {
    $stmt = $pdo->query("SELECT * FROM avisos_reserva ORDER BY orden ASC");
    $avisos = $stmt->fetchAll();
} catch (PDOException $e) {
    $mensaje = "Error al obtener los avisos: " . $e->getMessage();
    $tipo_mensaje = "error";
    $avisos = [];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Avisos - Panel de Administración</title>
    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <!-- Encabezado -->
        <header class="bg-white shadow-md rounded-lg p-6 mb-8">
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-bold text-gray-800">Gestión de Avisos</h1>
                <a href="index.php" class="text-blue-600 hover:text-blue-800">
                    <i class="fas fa-arrow-left mr-2"></i> Volver al Panel
                </a>
            </div>
        </header>
        
        <!-- Mensajes de estado -->
        <?php if (!empty($mensaje)): ?>
            <div class="mb-6 p-4 rounded-lg <?php echo $tipo_mensaje === 'success' ? 'bg-green-100 text-green-700' : ($tipo_mensaje === 'error' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700'); ?>">
                <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>
        
        <!-- Formulario para añadir nuevo aviso -->
        <div class="bg-white shadow-md rounded-lg p-6 mb-8">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Añadir Nuevo Aviso</h2>
            <form action="avisos.php" method="post">
                <div class="mb-4">
                    <label for="texto" class="block text-gray-700 text-sm font-bold mb-2">Texto del Aviso:</label>
                    <textarea id="texto" name="texto" rows="3" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="Ej: Debes llegar con al menos 10 minutos de antelación..." required></textarea>
                </div>
                <input type="hidden" name="accion" value="nuevo">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    <i class="fas fa-plus mr-2"></i> Añadir Aviso
                </button>
            </form>
        </div>
        
        <!-- Lista de avisos existentes -->
        <div class="bg-white shadow-md rounded-lg p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Avisos Existentes</h2>
            
            <?php if (empty($avisos)): ?>
                <p class="text-gray-600">No hay avisos configurados. Añade uno nuevo utilizando el formulario de arriba.</p>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Orden</th>
                                <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Texto</th>
                                <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php foreach ($avisos as $aviso): ?>
                                <tr>
                                    <td class="py-3 px-4 whitespace-nowrap">
                                        <div class="flex items-center space-x-2">
                                            <form action="avisos.php" method="post" class="inline">
                                                <input type="hidden" name="id" value="<?php echo $aviso['id']; ?>">
                                                <input type="hidden" name="accion" value="subir">
                                                <button type="submit" class="text-gray-600 hover:text-blue-600" <?php echo $aviso['orden'] <= 1 ? 'disabled' : ''; ?>>
                                                    <i class="fas fa-arrow-up"></i>
                                                </button>
                                            </form>
                                            <span><?php echo $aviso['orden']; ?></span>
                                            <form action="avisos.php" method="post" class="inline">
                                                <input type="hidden" name="id" value="<?php echo $aviso['id']; ?>">
                                                <input type="hidden" name="accion" value="bajar">
                                                <button type="submit" class="text-gray-600 hover:text-blue-600">
                                                    <i class="fas fa-arrow-down"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4">
                                        <form action="avisos.php" method="post" class="flex items-center">
                                            <input type="hidden" name="id" value="<?php echo $aviso['id']; ?>">
                                            <input type="hidden" name="accion" value="editar">
                                            <textarea name="texto" rows="2" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline mr-2" required><?php echo htmlspecialchars($aviso['texto']); ?></textarea>
                                            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-1 px-2 rounded focus:outline-none focus:shadow-outline">
                                                <i class="fas fa-save"></i>
                                            </button>
                                        </form>
                                    </td>
                                    <td class="py-3 px-4">
                                        <form action="avisos.php" method="post">
                                            <input type="hidden" name="id" value="<?php echo $aviso['id']; ?>">
                                            <input type="hidden" name="activo" value="<?php echo $aviso['activo']; ?>">
                                            <input type="hidden" name="accion" value="cambiar_estado">
                                            <button type="submit" class="<?php echo $aviso['activo'] ? 'bg-green-600 hover:bg-green-700' : 'bg-gray-500 hover:bg-gray-600'; ?> text-white font-bold py-1 px-3 rounded focus:outline-none focus:shadow-outline">
                                                <?php echo $aviso['activo'] ? 'Activo' : 'Inactivo'; ?>
                                            </button>
                                        </form>
                                    </td>
                                    <td class="py-3 px-4">
                                        <form action="avisos.php" method="post" onsubmit="return confirm('¿Estás seguro de que deseas eliminar este aviso?');">
                                            <input type="hidden" name="id" value="<?php echo $aviso['id']; ?>">
                                            <input type="hidden" name="accion" value="eliminar">
                                            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold py-1 px-3 rounded focus:outline-none focus:shadow-outline">
                                                <i class="fas fa-trash-alt"></i> Eliminar
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
